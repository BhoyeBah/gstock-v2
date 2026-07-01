<?php

namespace App\Services;

use App\Models\Invoice;

class InvoicePaymentStatusService
{
    public function recalculate(Invoice $invoice): Invoice
    {
        if ($invoice->status === 'cancelled') {
            return $invoice;
        }

        $paidAmount = (int) $invoice->payments()
            ->where('status', 'completed')
            ->sum('amount_paid');

        $creditedAmount = (int) (
            $invoice->type === Invoice::TYPE_CLIENT
                ? $invoice->customerCreditNotes()
                : $invoice->supplierCreditNotes()
        )
            ->whereIn('status', ['applied', 'partially_applied'])
            ->sum('applied_amount');

        $total = (int) $invoice->total_invoice;
        $invoice->balance = max($total - $paidAmount - $creditedAmount, 0);

        if ($paidAmount >= $total && $invoice->balance === 0) {
            $invoice->status = 'paid';
        } elseif ($creditedAmount > 0 && $invoice->balance === 0) {
            $invoice->status = 'credited';
        } elseif ($creditedAmount > 0) {
            $invoice->status = 'partially_credited';
        } elseif ($paidAmount <= 0) {
            $invoice->status = 'unpaid';
        } elseif ($paidAmount < $total) {
            $invoice->status = 'partial';
        } else {
            $invoice->status = 'paid';
        }

        $invoice->save();

        return $invoice;
    }
}
