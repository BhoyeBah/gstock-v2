<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExchangeRequest;
use App\Http\Requests\WarehouseRequest;
use App\Models\Batch;
use App\Models\InventoryMovement;
use App\Models\StockTransfert;
use App\Models\Warehouse;
use App\Services\DocumentNumberService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WarehouseController extends Controller
{
    public function __construct(
        private readonly DocumentNumberService $documentNumberService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $warehouses = Warehouse::all();

        return view('back.warehouses.index', compact('warehouses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(WarehouseRequest $request)
    {
        //
        $warehouse = Warehouse::create([
            'name' => $request->name,
            'address' => $request->address,
            'description' => $request->description,
            'manager_id' => $request->manager_id,
        ]);

        return back()->with('success', 'Entrêpot ajouté avec succès.');

    }

    /**
     * Display the specified resource.
     */
    public function show(Warehouse $warehouse)
    {
        // Récupérer les lots de cet entrepôt avec le produit et la facture associée
        $batches = $warehouse->batches()
            ->with(['product', 'invoice'])  // Charger relations
            ->orderBy('remaining')
            ->paginate(10);

        $movements = InventoryMovement::whereHas('batch', function ($query) use ($warehouse) {
            $query->where('warehouse_id', $warehouse->id);
        })
            ->with(['product', 'invoice.contact', 'batch'])
            ->latest()
            ->paginate(20);

        return view('back.warehouses.show', compact('warehouse', 'batches', 'movements'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Warehouse $warehouse)
    {
        //
        return view('back.warehouses.edit', compact('warehouse'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(WarehouseRequest $request, Warehouse $warehouse)
    {
        //
        $warehouse->update($request->validated());

        return redirect()->route('warehouses.index')
            ->with('success', "Les informations de l'entrêpot « {$warehouse->name} » a été mis à jour avec succès !");

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Warehouse $warehouse)
    {
        if ($warehouse->is_active) {
            return back()->with('error', 'Impossible de supprimer un entrêpot actif. Veuillez le désactiver d\'abord.');
        }

        //
        $warehouse->delete();

        return back()->with('success', 'Entrêpot supprimé avec succés');
    }

    public function exchangeIndex(string $id)
    {
        $tenantId = auth()->user()->tenant_id;
        $warehouse = Warehouse::with('batches.product')
            ->where('tenant_id', $tenantId)
            ->findOrFail($id);

        // Récupérer tous les entrepôts sauf l'entrepôt source
        $warehouses = Warehouse::with('batches')
            ->where('tenant_id', $tenantId)
            ->where('id', '!=', $warehouse->id)
            ->get();

        return view('back.warehouses.exchange', compact('warehouse', 'warehouses'));

    }

    public function exchange(ExchangeRequest $request, string $id)
    {
        $tenantId = auth()->user()->tenant_id;
        $warehouseOut = Warehouse::with('batches.product')
            ->where('tenant_id', $tenantId)
            ->findOrFail($id);

        $warehouseInId = $request->input('to_warehouse');
        $batches = $request->input('batch_id');
        $quantities = $request->input('quantity');

        try {
            DB::beginTransaction();

            foreach ($batches as $index => $batchId) {
                $quantity = $quantities[$index];

                // Récupérer le batch source
                $batch = Batch::where('tenant_id', $tenantId)->findOrFail($batchId);

                if ($batch->remaining < $quantity) {
                    throw new \Exception("La quantité demandée pour le lot {$batch->code} est supérieure au stock disponible.");
                }

                // Décrémenter le stock du batch source
                $batch->quantity -= $quantity;
                $batch->remaining -= $quantity;
                $batch->save();

                // Créer un nouveau batch dans l'entrepôt cible
                $newBatch = Batch::create([
                    'id' => (string) Str::uuid(),
                    'invoice_id' => $batch->invoice_id,
                    'tenant_id' => $batch->tenant_id,
                    'warehouse_id' => $warehouseInId,
                    'product_id' => $batch->product_id,
                    'unit_price' => $batch->unit_price,
                    'quantity' => $quantity,
                    'remaining' => $quantity,
                    'expiration_date' => $batch->expiration_date,
                ]);

                // Enregistrer le transfert
                StockTransfert::create([
                    'transfer_number' => $this->documentNumberService->generate('transfer', auth()->user()->tenant),
                    'tenant_id' => $batch->tenant_id,
                    'product_id' => $batch->product_id,
                    'source_warehouse_id' => $warehouseOut->id,
                    'target_warehouse_id' => $warehouseInId,
                    'source_batch_id' => $batch->id,
                    'target_batch_id' => $newBatch->id,
                    'quantity' => $quantity,
                ]);
            }

            DB::commit();

            return back()->with('success', 'Transfert enrégistré avec succès.');

        } catch (\Exception $e) {
            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function toggleActive(string $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->is_active = ! $warehouse->is_active;
        $warehouse->save();

        // message success
        $message = $warehouse->is_active
            ? 'L\'entrêpot a été activé avec succès.'
            : 'L\'entrêpot a été désactivé avec succès.';

        return redirect()->back()->with('success', $message);
    }
}
