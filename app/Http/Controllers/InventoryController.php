<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Inventory;
use App\Models\InventoryItem;
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

        $warehouse_id = $request->warehouse_id;
        $existingInventory = Inventory::where('status', '=', 'pending')->where('warehouse_id', '=', $warehouse_id)->first();
        $batches = Batch::where('warehouse_id', '=', $warehouse_id)->where('remaining', '>', 0)->get();

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
        $inventory = Inventory::with('items.product')->findOrFail($id);
        $items = $inventory->items()->get();

        return view('back.inventories.show', compact('inventory', 'items'));
    }

    public function validateItem(Request $request, string $id)
    {

        $inventoryItem = InventoryItem::with('product', 'inventory')->findOrFail($id);
        $productName = $inventoryItem->product->name;
        $inventory = $inventoryItem->inventory;
        $real_qty = $request->input('real_qty');
        $inventoryItem->real_qty = $real_qty;

        $inventoryItem->variance = $inventoryItem->theoretical_qty - $real_qty;
        $inventoryItem->validated = true;

        $inventoryItem->save();

        $invalidatedItems = InventoryItem::where('validated', '=', false)->where('inventory_id', $inventoryItem->inventory_id)->first();
        if (! $invalidatedItems) {
            $inventory->closed_at = now();
            $inventory->status = 'completed';
            $inventory->save();

            return back()->with('success', 'Inventaire validé avec succès.');
        }

        return back()->with('success', "Inventaire du produit ($productName) validé avec succès.");
    }

    public function print(Inventory $inventory)
    {
        return view('back.inventories.print', compact('inventory'));
    }
}
