<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\SupplierCreditNote;
use App\Models\SupplierCreditNoteItem;
use App\Models\SupplierReturn;
use App\Models\Wallet;
use App\Models\walletTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SupplierCreditNoteService
{
    public function __construct(
        private readonly DocumentNumberService $documentNumberService,
        private readonly InvoicePaymentStatusService $invoicePaymentStatusService
    ) {
    }

    public function createFromReturn(SupplierReturn $return, User $user): ?SupplierCreditNote
    {
        if (! $return->supplier_invoice_id) {
            return null;
        }

        return DB::transaction(function () use ($return, $user) {
            $return = SupplierReturn::where('tenant_id', $user->tenant_id)
                ->whereKey($return->id)
                ->with(['items.product', 'supplierInvoice'])
                ->lockForUpdate()
                ->firstOrFail();

            $invoice = Invoice::where('tenant_id', $user->tenant_id)
                ->whereKey($return->supplier_invoice_id)
                ->lockForUpdate()
                ->firstOrFail();

            $existingCredit = SupplierCreditNote::where('tenant_id', $user->tenant_id)
                ->where('supplier_return_id', $return->id)
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
                $subtotalHt = (int) $returnItem->unit_cost_ht * (int) $returnItem->quantity_returned;
                $itemTaxAmount = (int) round($subtotalHt * ((float) $returnItem->tax_rate / 100));
                $itemTotalTtc = $subtotalHt + $itemTaxAmount;

                $items[] = [
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $user->tenant_id,
                    'supplier_credit_note_id' => null,
                    'supplier_return_item_id' => $returnItem->id,
                    'product_id' => $returnItem->product_id,
                    'quantity' => $returnItem->quantity_returned,
                    'unit_cost_ht' => $returnItem->unit_cost_ht,
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

            $creditNote = SupplierCreditNote::create([
                'tenant_id' => $user->tenant_id,
                'credit_note_number' => $this->documentNumberService->generate('supplier_credit_note', $user->tenant),
                'supplier_return_id' => $return->id,
                'supplier_invoice_id' => $invoice->id,
                'contact_id' => $return->contact_id,
                'status' => $appliedAmount > 0 ? 'applied' : 'validated',
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
                $item['supplier_credit_note_id'] = $creditNote->id;
                SupplierCreditNoteItem::create($item);
            }

            if ($appliedAmount > 0) {
                $invoice->balance = max($currentBalance - $appliedAmount, 0);

                if ($invoice->balance === 0) {
                    $invoice->status = 'credited';
                } elseif ($appliedAmount > 0) {
                    $invoice->status = 'partially_credited';
                }

                $invoice->save();
            } elseif ($invoice->balance === 0) {
                $invoice->status = 'credited';
                $invoice->save();
            }

            $return->setRelation('creditNote', $creditNote);

            return $creditNote->load(['items.product', 'invoice', 'contact', 'supplierReturn.warehouse']);
        });
    }

    public function refund(SupplierCreditNote $creditNote, User $user, Wallet $wallet, int $amount, ?string $note = null): SupplierCreditNote
    {
        return DB::transaction(function () use ($creditNote, $user, $wallet, $amount, $note) {
            $creditNote = SupplierCreditNote::where('tenant_id', $user->tenant_id)
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

            $beforeBalance = (int) $wallet->current_balance;
            $wallet->current_balance = $beforeBalance + $amount;
            $wallet->save();

            walletTransaction::create([
                'tenant_id' => $user->tenant_id,
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'type' => 'in',
                'transaction_type' => 'supplier_credit_refund',
                'amount' => $amount,
                'balance_before' => $beforeBalance,
                'balance_after' => $wallet->current_balance,
                'source_type' => SupplierCreditNote::class,
                'source_id' => $creditNote->id,
                'note' => $note,
                'description' => $note ?: 'Remboursement avoir fournisseur '.$creditNote->credit_note_number,
            ]);

            $creditNote->remaining_amount = max($creditNote->remaining_amount - $amount, 0);
            $creditNote->applied_amount = min($creditNote->total_ttc, $creditNote->applied_amount + $amount);
            $creditNote->wallet_id = $wallet->id;
            $creditNote->refunded_at = now();
            $creditNote->refunded_by = $user->id;
            $creditNote->status = $creditNote->remaining_amount > 0 ? 'applied' : 'refunded';
            $creditNote->save();

            return $creditNote->fresh(['items.product', 'invoice', 'contact', 'wallet', 'supplierReturn.warehouse']);
        });
    }
}
