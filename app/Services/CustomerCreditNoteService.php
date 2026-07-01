<?php

namespace App\Services;

use App\Models\CustomerCreditNote;
use App\Models\CustomerCreditNoteItem;
use App\Models\CustomerReturn;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Wallet;
use App\Models\walletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerCreditNoteService
{
    public function __construct(
        private readonly DocumentNumberService $documentNumberService,
        private readonly InvoicePaymentStatusService $invoicePaymentStatusService
    ) {
    }

    public function createFromReturn(CustomerReturn $return, User $user): ?CustomerCreditNote
    {
        if (! $return->invoice_id) {
            return null;
        }

        return DB::transaction(function () use ($return, $user) {
            $return = CustomerReturn::where('tenant_id', $user->tenant_id)
                ->whereKey($return->id)
                ->with(['items.product', 'invoice'])
                ->lockForUpdate()
                ->firstOrFail();

            $invoice = Invoice::where('tenant_id', $user->tenant_id)
                ->whereKey($return->invoice_id)
                ->lockForUpdate()
                ->firstOrFail();

            $existingCredit = CustomerCreditNote::where('tenant_id', $user->tenant_id)
                ->where('customer_return_id', $return->id)
                ->lockForUpdate()
                ->first();

            if ($existingCredit) {
                return $existingCredit;
            }

            $items = [];
            $totalHt = 0;
            $taxAmount = 0;
            $totalTtc = 0;

            foreach ($return->items as $returnItem) {
                $subtotalHt = (int) $returnItem->unit_price_ht * (int) $returnItem->quantity_returned;
                $itemTaxAmount = (int) round($subtotalHt * ((float) $returnItem->tax_rate / 100));
                $itemTotalTtc = $subtotalHt + $itemTaxAmount;

                $items[] = [
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $user->tenant_id,
                    'customer_credit_note_id' => null,
                    'customer_return_item_id' => $returnItem->id,
                    'product_id' => $returnItem->product_id,
                    'quantity' => $returnItem->quantity_returned,
                    'unit_price_ht' => $returnItem->unit_price_ht,
                    'discount_amount' => 0,
                    'subtotal_ht' => $subtotalHt,
                    'tax_id' => $returnItem->tax_id,
                    'tax_rate' => $returnItem->tax_rate,
                    'tax_amount' => $itemTaxAmount,
                    'total_ttc' => $itemTotalTtc,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $totalHt += $subtotalHt;
                $taxAmount += $itemTaxAmount;
                $totalTtc += $itemTotalTtc;
            }

            $currentBalance = max((int) $invoice->balance, 0);
            $appliedAmount = min($currentBalance, $totalTtc);
            $remainingAmount = max($totalTtc - $appliedAmount, 0);

            $status = 'validated';
            if ($appliedAmount > 0 && $remainingAmount > 0) {
                $status = 'partially_applied';
            } elseif ($appliedAmount > 0) {
                $status = 'applied';
            }

            $creditNote = CustomerCreditNote::create([
                'tenant_id' => $user->tenant_id,
                'credit_note_number' => $this->documentNumberService->generate('customer_credit_note', $user->tenant),
                'customer_return_id' => $return->id,
                'customer_invoice_id' => $invoice->id,
                'contact_id' => $return->contact_id,
                'status' => $status,
                'credit_date' => $return->return_date,
                'total_ht' => $totalHt,
                'total_discount' => 0,
                'tax_amount' => $taxAmount,
                'total_ttc' => $totalTtc,
                'applied_amount' => $appliedAmount,
                'remaining_amount' => $remainingAmount,
                'created_by' => $user->id,
                'validated_at' => now(),
                'validated_by' => $user->id,
                'notes' => $return->notes,
            ]);

            foreach ($items as $item) {
                $item['customer_credit_note_id'] = $creditNote->id;
                CustomerCreditNoteItem::create($item);
            }

            if ($appliedAmount > 0) {
                $invoice->balance = max($currentBalance - $appliedAmount, 0);
                $invoice->save();
                $this->invoicePaymentStatusService->recalculate($invoice->fresh());
            }

            $return->setRelation('creditNote', $creditNote);

            return $creditNote->load(['items.product', 'invoice', 'contact', 'customerReturn.warehouse']);
        });
    }

    public function refund(CustomerCreditNote $creditNote, User $user, Wallet $wallet, int $amount, ?string $note = null): CustomerCreditNote
    {
        return DB::transaction(function () use ($creditNote, $user, $wallet, $amount, $note) {
            $creditNote = CustomerCreditNote::where('tenant_id', $user->tenant_id)
                ->whereKey($creditNote->id)
                ->lockForUpdate()
                ->firstOrFail();

            $wallet = Wallet::where('tenant_id', $user->tenant_id)
                ->whereKey($wallet->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($amount <= 0 || $amount > $creditNote->remaining_amount) {
                throw ValidationException::withMessages([
                    'amount' => 'Montant de remboursement invalide.',
                ]);
            }

            if ($wallet->current_balance < $amount) {
                throw ValidationException::withMessages([
                    'wallet_id' => 'Solde insuffisant dans le wallet sélectionné.',
                ]);
            }

            $beforeBalance = (int) $wallet->current_balance;
            $wallet->current_balance = $beforeBalance - $amount;
            $wallet->save();

            $walletTransaction = walletTransaction::create([
                'tenant_id' => $user->tenant_id,
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'type' => 'out',
                'transaction_type' => 'customer_credit_refund',
                'amount' => $amount,
                'balance_before' => $beforeBalance,
                'balance_after' => $wallet->current_balance,
                'source_type' => CustomerCreditNote::class,
                'source_id' => $creditNote->id,
                'note' => $note,
                'description' => $note ?: 'Remboursement avoir client '.$creditNote->credit_note_number,
            ]);

            $creditNote->remaining_amount = max($creditNote->remaining_amount - $amount, 0);
            $creditNote->refunded_at = now();
            $creditNote->refunded_by = $user->id;
            $creditNote->wallet_id = $wallet->id;
            $creditNote->status = $creditNote->remaining_amount > 0
                ? ($creditNote->applied_amount > 0 ? 'partially_applied' : 'validated')
                : 'refunded';
            $creditNote->save();

            return $creditNote->fresh(['items.product', 'invoice', 'contact', 'wallet', 'customerReturn.warehouse']);
        });
    }
}
