<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\Warehouse;
use App\Services\InventoryReconciliationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    public function __construct(
        private readonly InventoryReconciliationService $inventoryReconciliationService
    ) {}

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
        $request->validate([
            'warehouse_id' => [
                'required',
                Rule::exists('warehouses', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
        ]);

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
        $inventory = Inventory::with([
            'warehouse',
            'items.product',
            'items.movements.batch',
            'items.validatedBy',
            'items.reconciledBy',
        ])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        $items = $inventory->items;

        return view('back.inventories.show', compact('inventory', 'items'));
    }

    public function validateItem(Request $request, string $id)
    {
        $validated = $request->validate([
            'real_qty' => ['required', 'integer', 'min:0'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $inventoryItem = $this->inventoryReconciliationService->reconcileItem(
                inventoryItemId: $id,
                user: auth()->user(),
                realQuantity: (int) $validated['real_qty'],
                reason: $validated['reason'] ?? null
            );
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        $productName = $inventoryItem->product->name;

        if ($inventoryItem->inventory->status === 'completed') {
            return back()->with('success', 'Inventaire validé avec succès.');
        }

        return back()->with('success', "Inventaire du produit ($productName) ajusté avec succès.");
    }

    public function print(Inventory $inventory)
    {
        return view('back.inventories.print', compact('inventory'));
    }
}
