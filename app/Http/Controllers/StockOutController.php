<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\StockOut;
use App\Services\DocumentNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockOutController extends Controller
{
    public function __construct(
        private readonly DocumentNumberService $documentNumberService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currentUser = Auth::user();

        $stockQuery = StockOut::query()
            ->with(['batch.product', 'batch.warehouse'])
            ->when(! $currentUser->is_platform_user(), fn ($q) => $q->where('tenant_id', $currentUser->tenant_id))
            ->orderByDesc('created_at');

        if ($search = request('search')) {
            $stockQuery->where(function ($q) use ($search) {
                $q->where('stock_out_number', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhereHas('batch.product', fn ($product) => $product->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('batch.warehouse', fn ($warehouse) => $warehouse->where('name', 'like', "%{$search}%"));
            });
        }

        $stockOuts = $stockQuery->paginate(15)->withQueryString();

        $batchQuery = Batch::query()
            ->with([
            'product:id,name',
            'warehouse:id,name',
        ])
            ->when(! $currentUser->is_platform_user(), fn ($q) => $q->where('tenant_id', $currentUser->tenant_id));

        $batches = $batchQuery->get(['id', 'product_id', 'warehouse_id', 'remaining'])->where('remaining', '>', 0);

        $stats = [
            'total' => (clone $stockQuery)->count(),
            'quantity' => (clone $stockQuery)->sum('quantity'),
            'batches' => $batches->count(),
            'low' => $batches->where('remaining', '<=', 10)->count(),
        ];

        return view('back.stockOut.index', compact('stockOuts', 'batches', 'stats'));
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
        $batch = Batch::where('tenant_id', $currentUser->tenant_id)
            ->findOrFail($request->batch_id);
        $quantityOut = (int) $request->input('quantity');

        if ($quantityOut > $batch->remaining) {
            return back()->with('error', 'La quantité demandée dépasse le stock disponible.');
        }

        StockOut::create([
            'stock_out_number' => $this->documentNumberService->generate('stock_out', $currentUser->tenant),
            'tenant_id' => $currentUser->tenant_id,
            'batch_id' => $batch->id,
            'quantity' => $request->quantity,
            'reason' => $request->reason,
        ]);

        $batch->remaining -= $request->quantity;
        $batch->save();

        return back()->with('success', 'Sortie de stock enrégistrée avec succées');
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
        $currentUser = auth()->user();

        DB::transaction(function () use ($id, $currentUser) {
            $stockOut = StockOut::where('tenant_id', $currentUser->tenant_id)->findOrFail($id);
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
