<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Wallet;
use App\Models\walletTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentCancellationService
{
    public function __construct(
        private readonly InvoicePaymentStatusService $invoicePaymentStatusService
    ) {}

    public function cancel(Payment $payment, User $user, ?string $reason = null): Payment
    {
        return DB::transaction(function () use ($payment, $user, $reason) {
            $tenantId = $user->tenant_id;

            $payment = Payment::where('tenant_id', $tenantId)
                ->whereKey($payment->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($payment->isCancelled()) {
                throw ValidationException::withMessages([
                    'payment' => 'Ce paiement est déjà annulé.',
                ]);
            }

            $invoice = Invoice::where('tenant_id', $tenantId)
                ->whereKey($payment->invoice_id)
                ->lockForUpdate()
                ->firstOrFail();

            $wallet = Wallet::where('tenant_id', $tenantId)
                ->whereKey($payment->wallet_id)
                ->lockForUpdate()
                ->first();

            if (! $wallet) {
                throw ValidationException::withMessages([
                    'wallet' => 'Le wallet lié à ce paiement est introuvable ou ne vous appartient plus. Annulation impossible.',
                ]);
            }

            $amount = (int) $payment->amount_paid;
            $beforeBalance = (int) $wallet->current_balance;

            if ($invoice->type === Invoice::TYPE_CLIENT) {
                if ($beforeBalance < $amount) {
                    throw ValidationException::withMessages([
                        'wallet' => 'Solde insuffisant pour annuler ce paiement client.',
                    ]);
                }

                $wallet->current_balance = $beforeBalance - $amount;
                $legacyType = 'out';
            } else {
                $wallet->current_balance = $beforeBalance + $amount;
                $legacyType = 'in';
            }

            $wallet->save();

            $payment->forceFill([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => $user->id,
                'cancellation_reason' => $reason,
            ])->save();

            walletTransaction::create([
                'tenant_id' => $tenantId,
                'wallet_id' => $wallet->id,
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'type' => $legacyType,
                'transaction_type' => 'payment_cancel_reverse',
                'amount' => $amount,
                'balance_before' => $beforeBalance,
                'balance_after' => $wallet->current_balance,
                'source_type' => Payment::class,
                'source_id' => $payment->id,
                'note' => $reason,
                'description' => $reason ?: 'Annulation du paiement '.$payment->id,
            ]);

            $this->invoicePaymentStatusService->recalculate($invoice);

            return $payment->fresh(['invoice', 'wallet']);
        });
    }
}
