<?php

namespace App\Services;

use App\Models\CashSession;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Wallet;
use App\Models\walletTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Sprint 2 - Vente rapide / POS.
 *
 * Orchestration d'une vente comptoir en une seule transaction :
 *   1. création de la facture client (brouillon) ;
 *   2. validation -> décrémentation FIFO du stock (réutilise InvoiceService) ;
 *   3. encaissement(s) total/partiel avec mise à jour du wallet.
 *
 * Une vente non soldée (dette) exige un client. Une vente sans client doit
 * être payée intégralement.
 */
class PosService
{
    public function __construct(private InvoiceService $invoiceService) {}

    /**
     * @param  array  $data  [
     *     'contact_id' => ?string,
     *     'warehouse_id' => string,
     *     'items' => [ ['product_id','quantity','unit_price','discount'] ... ],
     *     'payments' => [ ['wallet_id','amount'] ... ],   // peut être vide
     * ]
     */
    public function createSale(array $data): Invoice
    {
        $warehouseId = $data['warehouse_id'];
        $contactId = $data['contact_id'] ?? null;

        $items = $this->normalizeItems($data['items'], $warehouseId);
        $payments = $this->normalizePayments($data['payments'] ?? []);

        $total = $this->invoiceService->getTotalInvoice($items);
        $totalPaid = array_sum(array_column($payments, 'amount'));

        $this->guard($total, $totalPaid, $contactId);

        return DB::transaction(function () use ($items, $payments, $contactId, $total) {
            // 1. Création de la facture (brouillon). Le numéro est généré à la validation.
            $invoice = $this->invoiceService->createInvoice([
                'contact_id' => $contactId,
                'invoice_number' => null,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->toDateString(),
                'type' => 'client',
                'items' => $items,
            ]);

            // 2. Validation -> stock FIFO + paiement d'initialisation (balance = total).
            $invoice->load('items');
            $this->invoiceService->validateInvoice($invoice);

            // 3. Encaissements.
            foreach ($payments as $payment) {
                $this->applyPayment($invoice, $payment['wallet_id'], $payment['amount']);
            }

            return $invoice->fresh(['contact', 'items.product', 'payments']);
        });
    }

    /**
     * Applique un encaissement sur la facture et crédite le wallet (vente = entrée).
     */
    private function applyPayment(Invoice $invoice, string $walletId, int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        if ($amount > $invoice->balance) {
            throw new \DomainException('Le montant encaissé dépasse le solde restant de la facture.');
        }

        /** @var Wallet $wallet */
        $wallet = Wallet::where('id', $walletId)->lockForUpdate()->firstOrFail();

        $beforeBalance = $wallet->current_balance;
        $wallet->increment('current_balance', $amount);

        $invoice->balance -= $amount;
        $invoice->status = $invoice->balance <= 0 ? 'paid' : 'partial';
        $invoice->save();

        // Rattachement à une session de caisse ouverte pour ce wallet (clôture journalière).
        $cashSessionId = CashSession::open()
            ->where('wallet_id', $wallet->id)
            ->value('id');

        $payment = Payment::create([
            'wallet_id' => $wallet->id,
            'cash_session_id' => $cashSessionId,
            'invoice_id' => $invoice->id,
            'tenant_id' => $invoice->tenant_id,
            'contact_id' => $invoice->contact_id,
            'amount_paid' => $amount,
            'remaining_amount' => $invoice->balance,
            'payment_date' => now(),
            'payment_type' => $wallet->name,
            'payment_source' => 'client',
        ]);

        walletTransaction::create([
            'tenant_id' => $invoice->tenant_id,
            'wallet_id' => $wallet->id,
            'payment_id' => $payment->id,
            'user_id' => Auth::id(),
            'type' => 'in',
            'transaction_type' => 'payment',
            'amount' => $amount,
            'balance_before' => $beforeBalance,
            'balance_after' => $wallet->current_balance,
            'source_type' => $wallet->name,
            'source_id' => $invoice->id,
            'description' => 'Vente POS '.$invoice->invoice_number,
            'note' => 'Encaissement POS sur facture '.$invoice->invoice_number,
        ]);
    }

    /**
     * Refuse les incohérences métier avant d'ouvrir la transaction.
     */
    private function guard(int $total, int $totalPaid, ?string $contactId): void
    {
        if ($total <= 0) {
            throw new \DomainException('La vente doit contenir au moins un article valorisé.');
        }

        if ($totalPaid > $total) {
            throw new \DomainException('Le total encaissé dépasse le montant de la vente.');
        }

        if ($totalPaid < $total && empty($contactId)) {
            throw new \DomainException('Une vente à crédit (paiement partiel) nécessite un client.');
        }
    }

    private function normalizeItems(array $items, string $warehouseId): array
    {
        return array_map(function ($item) use ($warehouseId) {
            return [
                'product_id' => $item['product_id'],
                'warehouse_id' => $warehouseId,
                'quantity' => (int) $item['quantity'],
                'unit_price' => (int) $item['unit_price'],
                'discount' => (int) ($item['discount'] ?? 0),
                'expiration_date' => null,
            ];
        }, $items);
    }

    private function normalizePayments(array $payments): array
    {
        $normalized = [];

        foreach ($payments as $payment) {
            $amount = (int) ($payment['amount'] ?? 0);

            if ($amount <= 0 || empty($payment['wallet_id'])) {
                continue;
            }

            $normalized[] = [
                'wallet_id' => $payment['wallet_id'],
                'amount' => $amount,
            ];
        }

        return $normalized;
    }
}
