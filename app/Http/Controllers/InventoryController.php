<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    //
    public function index()
    {

        $warehouses = Warehouse::with('batches')->get();

        $inventories = Inventory::withCount([
            'items as validated_count' => function ($query) {
                $query->where('validated', true);
            },
        ])->withSum('items as ecart_sum', 'variance')
            ->get();

        return view('back.inventories.index', compact('warehouses', 'inventories'));
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $warehouse_id = $request->warehouse_id;

        // Sécurité : vérifier que l'entrepôt appartient au tenant
        $warehouseExists = Warehouse::where('id', $warehouse_id)
            ->where('tenant_id', $tenantId)
            ->exists();

        if (!$warehouseExists) {
            abort(403, "Action non autorisée.");
        }

        $existingInventory = Inventory::where('status', '=', 'pending')
            ->where('warehouse_id', '=', $warehouse_id)
            ->first();

        $batches = Batch::where('warehouse_id', '=', $warehouse_id)
            ->where('tenant_id', $tenantId)
            ->where('remaining', '>', 0)
            ->get();

        if ($existingInventory) {
            return back()->with('error', 'Cet entrepot a deja un inventaire non cloturé.');
        }
        // Si aucun batch, on ne crée pas l'inventaire
        if ($batches->isEmpty()) {
            return back()->with('error', 'Aucun produit disponible pour générer un inventaire.');
        }
        try {

            DB::beginTransaction();
            $products = [];
            $lines = [];
            $remaining = $batches->sum('remaining');

            $inventory = Inventory::create([
                'warehouse_id' => $warehouse_id,
                'inventory_number' => Inventory::generateInventoryNumber(),
                'total_products' => $remaining,
                'status' => 'pending',
            ]);

            foreach ($batches as $batch) {

                $product_id = $batch->product_id;
                if (isset($products[$product_id])) {
                    $products[$product_id] += $batch->remaining;
                } else {
                    $products[$product_id] = $batch->remaining;
                }

            }

            foreach ($products as $product_id => $quantity) {
                $lines[] = [
                    'id' => (string) Str::uuid(),
                    'inventory_id' => $inventory->id,
                    'product_id' => $product_id,
                    'theoretical_qty' => $quantity,
                    'real_qty' => null,
                    'variance' => 0,
                    'validated' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
            DB::table('inventory_items')->insert($lines);
            DB::commit();

            return back()->with('success', 'Inventaire généré avec success');

        } catch (\Exception $e) {

            DB::rollback();

            return back()->with('error', 'Impossible de générer un inventaire');
        }

    }

    public function show(string $id)
    {
        $inventory = Inventory::where('tenant_id', auth()->user()->tenant_id)
            ->with('items.product')->findOrFail($id);
        $items = $inventory->items()->get();

        return view('back.inventories.show', compact('inventory', 'items'));
    }

    public function validateItem(Request $request, string $id)
    {
        try {
            DB::beginTransaction();

            $inventoryItem = InventoryItem::with('product', 'inventory.warehouse')
                ->whereHas('inventory', fn ($query) => $query->where('tenant_id', auth()->user()->tenant_id))
                ->findOrFail($id);
            $inventory = $inventoryItem->inventory;

            if (!$inventory || $inventory->tenant_id !== auth()->user()->tenant_id) {
                abort(403, "Action non autorisée.");
            }

            $productName = $inventoryItem->product->name;
            $warehouseId = $inventory->warehouse_id;
            $productId = $inventoryItem->product_id;

            $theoreticalQty = (int) $inventoryItem->theoretical_qty;
            $realQty = (int) $request->input('real_qty');

            if ($realQty < 0) {
                throw new \Exception("La quantité réelle ne peut pas être négative.");
            }

            $variance = $realQty - $theoreticalQty;

            $inventoryItem->real_qty = $realQty;
            $inventoryItem->variance = $variance;
            $inventoryItem->validated = true;
            $inventoryItem->save();

            if ($realQty < $theoreticalQty) {
                $quantityToRemove = $theoreticalQty - $realQty;
                $remainingToRemove = $quantityToRemove;

                $batches = Batch::where('product_id', $productId)
                    ->where('warehouse_id', $warehouseId)
                    ->where('remaining', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->lockForUpdate()
                    ->get();

                foreach ($batches as $batch) {
                    if ($remainingToRemove <= 0) {
                        break;
                    }
                    if ($batch->remaining <= 0) {
                        continue;
                    }
                    $deduct = min($batch->remaining, $remainingToRemove);
                    $batch->remaining -= $deduct;
                    $batch->save();

                    $remainingToRemove -= $deduct;
                }

                InventoryMovement::create([
                    'tenant_id' => auth()->user()->tenant_id,
                    'inventory_id' => $inventory->id,
                    'inventory_item_id' => $inventoryItem->id,
                    'batch_id' => $batches->first()?->id,
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId,
                    'quantity_before' => $theoreticalQty,
                    'quantity_after' => $realQty,
                    'variance' => $variance,
                    'quantity' => $quantityToRemove,
                    'user_id' => auth()->user()->id,
                    'reason' => $request->input('reason') ?? 'Ajustement inventaire (réel < théorique)',
                    'movement_type' => 'inventory_adjustment_out',
                ]);

            } elseif ($realQty > $theoreticalQty) {
                $quantityToAdd = $realQty - $theoreticalQty;

                $batch = Batch::where('product_id', $productId)
                    ->where('warehouse_id', $warehouseId)
                    ->orderBy('created_at', 'desc')
                    ->lockForUpdate()
                    ->first();

                if ($batch) {
                    $batch->quantity += $quantityToAdd;
                    $batch->remaining += $quantityToAdd;
                    $batch->save();
                } else {
                    $product = Product::where('tenant_id', auth()->user()->tenant_id)->findOrFail($productId);
                    $batch = Batch::create([
                        'tenant_id' => auth()->user()->tenant_id,
                        'warehouse_id' => $warehouseId,
                        'product_id' => $productId,
                        'unit_price' => $product->price ?? 0,
                        'quantity' => $quantityToAdd,
                        'remaining' => $quantityToAdd,
                    ]);
                }

                InventoryMovement::create([
                    'tenant_id' => auth()->user()->tenant_id,
                    'inventory_id' => $inventory->id,
                    'inventory_item_id' => $inventoryItem->id,
                    'batch_id' => $batch->id,
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId,
                    'quantity_before' => $theoreticalQty,
                    'quantity_after' => $realQty,
                    'variance' => $variance,
                    'quantity' => $quantityToAdd,
                    'user_id' => auth()->user()->id,
                    'reason' => $request->input('reason') ?? 'Ajustement inventaire (réel > théorique)',
                    'movement_type' => 'inventory_adjustment_in',
                ]);
            }

            $invalidatedItems = InventoryItem::where('validated', '=', false)->where('inventory_id', $inventoryItem->inventory_id)->first();
            if (!$invalidatedItems) {
                $inventory->closed_at = now();
                $inventory->status = 'completed';
                $inventory->save();

                DB::commit();
                return back()->with('success', 'Inventaire validé avec succès.');
            }

            DB::commit();
            return back()->with('success', "Inventaire du produit ($productName) validé avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Erreur lors de la validation : " . $e->getMessage());
        }
    }

    public function print(Inventory $inventory)
    {
        return view('back.inventories.print', compact('inventory'));
    }
}
