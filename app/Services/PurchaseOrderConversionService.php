<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;

class PurchaseOrderConversionService
{
    public function __construct(private readonly DocumentNumberService $documentNumberService)
    {
    }

    public function toSupplierInvoice(PurchaseOrder $purchaseOrder): Invoice
    {
        return DB::transaction(function () use ($purchaseOrder) {
            $invoice = Invoice::create([
                'contact_id' => $purchaseOrder->contact_id,
                'invoice_number' => $this->documentNumberService->generate('supplier_invoice', $purchaseOrder->tenant),
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'type' => Invoice::TYPE_SUPPLIER,
                'total_invoice' => $purchaseOrder->total_ttc,
                'total_ht' => $purchaseOrder->total_ht,
                'tax_amount' => $purchaseOrder->tax_amount,
                'discount_amount' => $purchaseOrder->total_discount,
                'balance' => 0,
                'status' => 'draft',
            ]);

            foreach ($purchaseOrder->items as $item) {
                $invoice->items()->create([
                    'warehouse_id' => $item->warehouse_id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity_ordered,
                    'type' => 'in',
                    'unit_price' => $item->unit_cost_ht,
                    'discount' => $item->discount_amount,
                    'tax_rate' => $item->tax_rate,
                    'tax_amount' => $item->tax_amount,
                    'total_ht' => $item->subtotal_ht,
                    'total_ttc' => $item->total_ttc,
                    'total_line' => $item->total_ttc,
                    'expiration_date' => $item->expiration_date,
                ]);
            }

            $purchaseOrder->update(['status' => PurchaseOrder::STATUS_INVOICED]);

            return $invoice->fresh(['items.product', 'contact']);
        });
    }
}
