<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Contact;
use App\Models\InventoryMovement;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Wallet;
use App\Models\Warehouse;
use App\Models\walletTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class PosSaleService
{
    public function __construct(
        private readonly DocumentNumberService $documentNumberService,
        private readonly InvoicePaymentStatusService $invoicePaymentStatusService
    ) {}

    public function createSale(array $data, $user): Invoice
    {
        return DB::transaction(function () use ($data, $user) {
            $tenantId = $user->tenant_id;
            $paymentDate = ! empty($data['payment_date'])
                ? Carbon::parse($data['payment_date'])
                : now();

            $warehouse = Warehouse::query()
                ->where('tenant_id', $tenantId)
                ->whereKey($data['warehouse_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $wallet = Wallet::query()
                ->where('tenant_id', $tenantId)
                ->whereKey($data['wallet_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $contact = $this->resolveContact($tenantId, $data['contact_id'] ?? null);
            $taxRate = (float) (Setting::query()->value('tva') ?? 0);

            $groupedItems = $this->groupItems($data['items']);
            $invoiceLines = [];
            $inventoryMovements = [];
            $totalHt = 0;
            $totalTax = 0;
            $totalDiscount = 0;
            $totalInvoice = 0;

            foreach ($groupedItems as $item) {
                $product = Product::query()
                    ->where('tenant_id', $tenantId)
                    ->whereKey($item['product_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $product->is_active) {
                    throw ValidationException::withMessages([
                        'items' => "Le produit {$product->name} est désactivé.",
                    ]);
                }

                $itemWarehouseId = $item['warehouse_id'] ?? $warehouse->id;
                if ($itemWarehouseId !== $warehouse->id) {
                    throw ValidationException::withMessages([
                        'items' => 'Tous les articles doivent provenir de l’entrepôt sélectionné.',
                    ]);
                }

                $unitPrice = (int) ($item['unit_price'] ?? $product->price ?? 0);
                $quantity = (int) $item['quantity'];
                $discount = max(0, (int) ($item['discount'] ?? 0));
                $lineBase = max(($unitPrice * $quantity) - $discount, 0);
                $lineTax = (int) round($lineBase * $taxRate / 100);
                $lineTotal = $lineBase + $lineTax;

                $availableStock = (int) Batch::query()
                    ->where('tenant_id', $tenantId)
                    ->where('warehouse_id', $warehouse->id)
                    ->where('product_id', $product->id)
                    ->where('remaining', '>', 0)
                    ->sum('remaining');

                if ($availableStock < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Stock insuffisant pour {$product->name}. Disponible : {$availableStock}.",
                    ]);
                }

                $invoiceLines[] = [
                    'id' => (string) Str::uuid(),
                    'invoice_id' => null,
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'type' => 'out',
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $lineTax,
                    'total_ht' => $lineBase,
                    'total_ttc' => $lineTotal,
                    'total_line' => $lineTotal,
                    'expiration_date' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $totalHt += $lineBase;
                $totalTax += $lineTax;
                $totalDiscount += $discount;
                $totalInvoice += $lineTotal;
            }

            if ($data['amount_paid'] > $totalInvoice) {
                throw ValidationException::withMessages([
                    'amount_paid' => "Le montant payé ne peut pas dépasser le total de la vente ({$totalInvoice} FCFA).",
                ]);
            }

            $invoice = Invoice::create([
                'contact_id' => $contact->id,
                'invoice_number' => $this->documentNumberService->generate('customer_invoice', $user->tenant),
                'invoice_date' => $paymentDate->toDateString(),
                'due_date' => $paymentDate->toDateString(),
                'type' => Invoice::TYPE_CLIENT,
                'total_invoice' => $totalInvoice,
                'total_ht' => $totalHt,
                'tax_amount' => $totalTax,
                'discount_amount' => $totalDiscount,
                'balance' => $totalInvoice - (int) $data['amount_paid'],
                'status' => (int) $data['amount_paid'] >= $totalInvoice ? 'paid' : 'partial',
            ]);

            foreach ($invoiceLines as $index => $line) {
                $line['invoice_id'] = $invoice->id;
                $invoiceLines[$index] = $line;
            }

            DB::table('invoice_items')->insert($invoiceLines);

            $payment = Payment::create([
                'payment_number' => $this->documentNumberService->generate('payment', $user->tenant),
                'wallet_id' => $wallet->id,
                'invoice_id' => $invoice->id,
                'tenant_id' => $tenantId,
                'contact_id' => $contact->id,
                'amount_paid' => (int) $data['amount_paid'],
                'remaining_amount' => max($totalInvoice - (int) $data['amount_paid'], 0),
                'payment_date' => $paymentDate,
                'payment_type' => $wallet->name,
                'payment_source' => 'client',
                'status' => 'completed',
            ]);

            $beforeBalance = (int) $wallet->current_balance;
            $wallet->current_balance = $beforeBalance + (int) $data['amount_paid'];
            $wallet->save();

            walletTransaction::create([
                'tenant_id' => $tenantId,
                'wallet_id' => $wallet->id,
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'type' => 'in',
                'transaction_type' => 'sale_payment_in',
                'amount' => (int) $data['amount_paid'],
                'balance_before' => $beforeBalance,
                'balance_after' => $wallet->current_balance,
                'source_type' => Payment::class,
                'source_id' => $payment->id,
                'note' => 'Vente POS '.$invoice->invoice_number,
                'description' => 'Vente POS '.$invoice->invoice_number,
            ]);

            $this->consumeStock($invoice, $warehouse, $invoiceLines, $user);
            $this->invoicePaymentStatusService->recalculate($invoice);
            $payment->remaining_amount = $invoice->fresh()->balance;
            $payment->save();

            return $invoice->fresh(['items.product', 'items.warehouse', 'contact', 'payments.wallet']);
        });
    }

    private function resolveContact(string $tenantId, ?string $contactId): Contact
    {
        if ($contactId) {
            return Contact::query()
                ->where('tenant_id', $tenantId)
                ->where('type', 'client')
                ->whereKey($contactId)
                ->firstOrFail();
        }

        return Contact::firstOrCreate(
            [
                'tenant_id' => $tenantId,
                'phone_number' => '0000000000',
            ],
            [
                'fullname' => 'Client comptoir',
                'address' => null,
                'type' => 'client',
                'is_active' => true,
            ]
        );
    }

    private function groupItems(array $items): array
    {
        $grouped = [];

        foreach ($items as $item) {
            $warehouseId = $item['warehouse_id'] ?? null;
            $key = implode(':', [
                $item['product_id'],
                $warehouseId ?? 'default',
                (int) ($item['unit_price'] ?? 0),
                (int) ($item['discount'] ?? 0),
            ]);

            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $warehouseId,
                    'quantity' => 0,
                    'unit_price' => $item['unit_price'] ?? null,
                    'discount' => (int) ($item['discount'] ?? 0),
                ];
            }

            $grouped[$key]['quantity'] += (int) $item['quantity'];
            if (array_key_exists('unit_price', $item) && $item['unit_price'] !== null) {
                $grouped[$key]['unit_price'] = (int) $item['unit_price'];
            }
        }

        return array_values($grouped);
    }

    private function consumeStock(Invoice $invoice, Warehouse $warehouse, array $invoiceLines, $user): void
    {
        foreach ($invoiceLines as $line) {
            $quantityToRemove = (int) $line['quantity'];

            $batches = Batch::query()
                ->where('tenant_id', $invoice->tenant_id)
                ->where('warehouse_id', $warehouse->id)
                ->where('product_id', $line['product_id'])
                ->where('remaining', '>', 0)
                ->orderBy('created_at')
                ->lockForUpdate()
                ->get();

            foreach ($batches as $batch) {
                if ($quantityToRemove <= 0) {
                    break;
                }

                $available = (int) $batch->remaining;
                if ($available <= 0) {
                    continue;
                }

                $used = min($available, $quantityToRemove);
                $before = $available;
                $after = $available - $used;
                $batchCost = (int) $batch->unit_price;
                $salePrice = (int) $line['unit_price'];
                $profit = ($salePrice - $batchCost) * $used;

                DB::table('batches')
                    ->where('id', $batch->id)
                    ->update([
                        'remaining' => $after,
                        'benefit' => ((int) $batch->benefit) + $profit,
                        'updated_at' => now(),
                    ]);

                InventoryMovement::create([
                    'tenant_id' => $invoice->tenant_id,
                    'invoice_item_id' => $line['id'],
                    'invoice_id' => $invoice->id,
                    'batch_id' => $batch->id,
                    'product_id' => $line['product_id'],
                    'warehouse_id' => $warehouse->id,
                    'quantity' => $used,
                    'quantity_before' => $before,
                    'quantity_after' => $after,
                    'variance' => -$used,
                    'movement_type' => 'sale_out',
                    'source_type' => Invoice::class,
                    'source_id' => $invoice->id,
                    'user_id' => $user->id,
                    'movement_date' => now(),
                    'reason' => 'vente',
                ]);

                $quantityToRemove -= $used;
            }
        }
    }
}
