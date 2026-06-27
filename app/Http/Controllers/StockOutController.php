<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\StockOut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $stockOuts = StockOut::paginate(10);
        $batches = Batch::with([
            'product:id,name',
            'warehouse:id,name',
        ])->get(['id', 'product_id', 'warehouse_id','remaining'])->where('remaining', '>', 0);

        return view('back.stockOut.index', compact('stockOuts', 'batches'));
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
    public function store(Request $request)
    {
        $currentUser = auth()->user();

        // Sécurité : vérifier que le batch appartient au tenant
        $batch = Batch::where('id', $request->batch_id)
            ->where('tenant_id', $currentUser->tenant_id)
            ->firstOrFail();

        $quantityOut = (int) $request->input('quantity');
        $reason = $request->input('reason');

        if ($quantityOut > $batch->remaining) {
            return back()->with('error', 'La quantité demandée dépasse le stock disponible.');
        }

        DB::transaction(function () use ($currentUser, $batch, $quantityOut, $reason) {
            StockOut::create([
                'tenant_id' => $currentUser->tenant_id,
                'batch_id'  => $batch->id,
                'quantity'  => $quantityOut,
                'reason'    => $reason,
            ]);

            $batch->remaining -= $quantityOut;
            $batch->save();
        });

        return back()->with('success', 'Sortie de stock enrégistrée avec succès');
    }

    /**
     * Display the specified resource.
     */
    public function show(StockOut $stockOut)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StockOut $stockOut)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StockOut $stockOut)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::transaction(function () use ($id) {
            $stockOut = StockOut::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);
            $batch = $stockOut->batch;

            if ($batch) {
                // Restaurer la quantité dans le lot
                $batch->remaining += $stockOut->quantity;
                $batch->save();
            }

            $stockOut->delete();
        });

        return back()->with('success', 'Sortie supprimée et stock restauré.');
    }
}
