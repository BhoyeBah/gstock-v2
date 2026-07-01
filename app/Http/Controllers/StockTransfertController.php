<?php

namespace App\Http\Controllers;

use App\Models\StockTransfert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockTransfertController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();

        $query = StockTransfert::query()
            ->with(['product', 'sourceWarehouse', 'targetWarehouse', 'sourceBatch', 'targetBatch'])
            ->when(! $currentUser->is_platform_user(), fn ($q) => $q->where('tenant_id', $currentUser->tenant_id))
            ->orderByDesc('created_at');

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('transfer_number', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($product) => $product->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('sourceWarehouse', fn ($warehouse) => $warehouse->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('targetWarehouse', fn ($warehouse) => $warehouse->where('name', 'like', "%{$search}%"));
            });
        }

        $transfers = $query->paginate(20)->withQueryString();

        $statsQuery = StockTransfert::query()->when(! $currentUser->is_platform_user(), fn ($q) => $q->where('tenant_id', $currentUser->tenant_id));

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'quantity' => (clone $statsQuery)->sum('quantity'),
            'products' => (clone $statsQuery)->distinct('product_id')->count('product_id'),
            'warehouses' => (clone $statsQuery)->distinct('source_warehouse_id')->count('source_warehouse_id'),
        ];

        return view('back.transfers.index', compact('transfers', 'stats'));
    }

    public function create()
    {
        abort(404);
    }

    public function store(Request $request)
    {
        abort(404);
    }
    public function show(StockTransfert $stockTransfert)
    {
        abort(404);
    }
    public function edit(StockTransfert $stockTransfert)
    {
        abort(404);
    }
    public function update(Request $request, StockTransfert $stockTransfert)
    {
        abort(404);
    }
    public function destroy(StockTransfert $stockTransfert)
    {
        abort(404);
    }
}
