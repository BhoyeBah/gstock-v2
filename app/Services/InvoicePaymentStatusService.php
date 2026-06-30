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

        $total = (int) $invoice->total_invoice;
        $invoice->balance = max($total - $paidAmount, 0);

        if ($paidAmount <= 0) {
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
