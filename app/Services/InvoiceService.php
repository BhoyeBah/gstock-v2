<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceService
{
    public function createInvoice(array $data)
    {
        try {

            DB::beginTransaction();

            $invoiceData = $this->getInvoiceData($data);
            $invoiceData['total_invoice'] = $this->getTotalInvoice($data['items']);
            $invoiceData['balance'] = 0;
            $invoiceData['type'] = $data['type'];

            $invoice = Invoice::create($invoiceData);

            $lines = $this->getFormatedInvoiceLines($data['items'], $invoice->id, $invoiceData['type'])['rows'];

            if (! empty($lines)) {
                DB::table('invoice_items')->insert($lines);
            }

            DB::commit();

            return $invoice;

        } catch (\Exception $e) {
            // throw $th;
            DB::rollBack();
            throw $e;
        }

    }

    private function getInvoiceData(array $data)
    {
        return [
            'contact_id' => $data['contact_id'],
            'invoice_number' => $data['invoice_number'],
            'due_date' => $data['due_date'],
            'invoice_date' => $data['invoice_date'],
        ];
    }

    private function getFormatedInvoiceLines(array $items, string $invoice_id, string $type)
    {
        $rows = [];
        $total_invoice = 0;
        $type = $type == 'client' ? 'out' : 'in';

        $grouped = []; // clé => ligne

        foreach ($items as $item) {
            if (! isset($item['expiration_date'])) {
                $item['expiration_date'] = null;
            }

            $quantity = (int) $item['quantity'];
            $discount = (int) $item['discount'];
            $price = (int) $item['unit_price'];

            // clé unique pour regroupement : produit + prix
            $key = $item['product_id'].'-'.$price;

            if (isset($grouped[$key])) {
                // Si même produit et même prix, on additionne la quantité et la remise
                $grouped[$key]['quantity'] += $quantity;
                $grouped[$key]['discount'] += $discount;
                $grouped[$key]['total_line'] += ($price * $quantity - $discount);
            } else {
                // Nouvelle ligne
                $grouped[$key] = [
                    'type' => $type,
                    'quantity' => $quantity,
                    'discount' => $discount,
                    'unit_price' => $price,
                    'total_line' => $price * $quantity - $discount,
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $item['warehouse_id'],
                    'expiration_date' => $item['expiration_date'],
                    'invoice_id' => $invoice_id,
                    'id' => (string) Str::uuid(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }

            $total_invoice += ($price * $quantity - $discount);
        }

        // On retourne les lignes regroupées
        return [
            'rows' => array_values($grouped),
            'total_invoice' => $total_invoice,
        ];
    }

    public function getTotalInvoice(array $items)
    {
        $total_invoice = 0;

        foreach ($items as $item) {
            $quantity = (int) $item['quantity'];
            $discount = (int) $item['discount'];
            $price = (int) $item['unit_price'];
            $total_line = $price * $quantity - $discount;
            $total_invoice += $total_line;

        }

        return $total_invoice;
    }

    public function validateInvoice(Invoice $invoice)
    {

        try {
            DB::beginTransaction();

            if ($invoice->type === 'supplier') {
                $result = $this->batchLines($invoice->items, $invoice->tenant_id);
                $lines = $result['batchRows'];
                $inventoryMovements = $result['inventoryMovements'];

                if (! empty($lines)) {
                    DB::table('batches')->insert($lines);
                }

                if (! empty($inventoryMovements)) {
                    DB::table('inventory_movements')->insert($inventoryMovements);
                }

            } else {
                foreach ($invoice->items as $item) {
                    $this->applyFifo($item);
                }
            }

            // ✅ Validation de la facture (commun aux deux cas)
            $invoice->status = 'validated';
            $invoice->balance = $invoice->total_invoice;
            $invoice->generateInvoiceNumber();
            $invoice->save();

            // ✅ Création du paiement initial (une seule fois ici)
            Payment::create([
                'invoice_id' => $invoice->id,
                'tenant_id' => $invoice->tenant_id,
                'contact_id' => $invoice->contact_id,
                'amount_paid' => 0,
                'remaining_amount' => $invoice->balance,
                'payment_date' => now(),
                'payment_type' => 'initialisation paiement',
                'payment_source' => $invoice->type,
            ]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function batchLines(Collection $items, string $tenant_id)
    {
        $batchRows = [];
        $inventoryMovements = [];

        foreach ($items as $item) {
            $batchId = (string) Str::uuid();

            $batch = Batch::where('warehouse_id', $item->warehouse_id)
                ->where('product_id', $item->product_id)
                ->where('unit_price', $item->unit_price)
                ->lockForUpdate()
                ->first();

            if (! empty($batch)) {
                // On met à jour le batch existant
                $batch->quantity += $item->quantity;
                $batch->remaining += $item->quantity;
                $batch->save();

                // Préparer le mouvement
                $inventoryMovements[] = [
                    'id' => (string) Str::uuid(),
                    'invoice_item_id' => $item->id,
                    'invoice_id' => $item->invoice_id,
                    'batch_id' => $batch->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'reason' => 'Ajustement de stock',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

            } else {
                // Créer un nouveau batch
                $batchRows[] = [
                    'id' => $batchId,
                    'invoice_id' => $item->invoice_id,
                    'tenant_id' => $tenant_id,
                    'warehouse_id' => $item->warehouse_id,
                    'product_id' => $item->product_id,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'remaining' => $item->quantity,
                    'expiration_date' => $item->expiration_date,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Préparer le mouvement lié à ce batch
                $inventoryMovements[] = [
                    'id' => (string) Str::uuid(),
                    'invoice_item_id' => $item->id,
                    'invoice_id' => $item->invoice_id,
                    'batch_id' => $batchId,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'reason' => 'Entrée de stock',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        return [
            'batchRows' => $batchRows,
            'inventoryMovements' => $inventoryMovements,
        ];
    }

    public function applyFifo(InvoiceItem $invoiceItem)
    {
        $productId = $invoiceItem->product_id;
        $warehouseId = $invoiceItem->warehouse_id;
        $quantityToRemove = $invoiceItem->quantity;
        $invoiceId = $invoiceItem->invoice_id;
        $unitPrice = $invoiceItem->unit_price;

        // Récupérer les lots disponibles (FIFO) et les verrouiller
        $batches = DB::table('batches')
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('remaining', '>', 0)
            ->orderBy('created_at', 'asc')
            ->lockForUpdate()
            ->get();

        $movements = [];

        foreach ($batches as $batch) {
            if ($quantityToRemove <= 0) {
                break;
            }

            $available = $batch->remaining;
            if ($available <= 0) {
                continue;
            }

            $used = min($available, $quantityToRemove);

            // Mise à jour du lot
            $batchRecord = DB::table('batches')
                ->where('id', $batch->id)
                ->first();

            if ($batchRecord) {
                $profit = ($unitPrice - $batch->unit_price) * $used;
                $benef = $used * $unitPrice - $used * $batchRecord->unit_price;
                $newBenef = $batchRecord->benefit + $benef;
                DB::table('batches')
                    ->where('id', $batch->id)
                    ->update([
                        'remaining' => $available - $used,
                        'benefit' => $newBenef,
                        'updated_at' => now(),
                    ]);
            }


            $movement = DB::table('inventory_movements')->insert([
                'id' => (string) Str::uuid(),
                'invoice_item_id' => $invoiceItem->id,
                'invoice_id' => $invoiceId,
                'batch_id' => $batch->id,
                'product_id' => $productId,
                'profit' => $profit,
                'quantity' => $used,
                'reason' => 'vente',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            $movements[] = $movement;
            $quantityToRemove -= $used;
        }

        if ($quantityToRemove > 0) {
            throw new \Exception(
                "Impossible de retirer {$invoiceItem->quantity} unités du stock. "
                .'Il ne reste que '.($invoiceItem->quantity - $quantityToRemove)." unités disponibles pour le produit « {$invoiceItem->product->name} »."
            );
        }

        // Retourner tous les mouvements générés dans la transaction
        return $movements;
    }
}
