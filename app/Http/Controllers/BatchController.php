<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BatchController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();

        $query = Batch::query()
            ->with(['product', 'warehouse', 'invoice.contact'])
            ->when(! $currentUser->is_platform_user(), fn ($q) => $q->where('tenant_id', $currentUser->tenant_id))
            ->orderByDesc('created_at');

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($product) => $product->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('warehouse', fn ($warehouse) => $warehouse->where('name', 'like', "%{$search}%"));
            });
        }

        if ($warehouseId = request('warehouse_id')) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($productId = request('product_id')) {
            $query->where('product_id', $productId);
        }

        $batches = $query->paginate(20)->withQueryString();

        $statsQuery = Batch::query()->when(! $currentUser->is_platform_user(), fn ($q) => $q->where('tenant_id', $currentUser->tenant_id));

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'available' => (clone $statsQuery)->where('remaining', '>', 0)->count(),
            'expired' => (clone $statsQuery)->whereNotNull('expiration_date')->whereDate('expiration_date', '<', now())->count(),
            'stock' => (clone $statsQuery)->sum('remaining'),
        ];

        $warehouses = \App\Models\Warehouse::query()
            ->when(! $currentUser->is_platform_user(), fn ($q) => $q->where('tenant_id', $currentUser->tenant_id))
            ->orderBy('name')
            ->get();

        $products = \App\Models\Product::query()
            ->when(! $currentUser->is_platform_user(), fn ($q) => $q->where('tenant_id', $currentUser->tenant_id))
            ->orderBy('name')
            ->get();

        return view('back.batches.index', compact('batches', 'stats', 'warehouses', 'products'));
    }

    public function create()
    {
        abort(404);
    }

    public function store(Request $request)
    {
        abort(404);
    }

    public function show(Batch $batch)
    {
        abort(404);
    }

    public function edit(Batch $batch)
    {
        abort(404);
    }

    public function update(Request $request, Batch $batch)
    {
        abort(404);
    }

    public function destroy(Batch $batch)
    {
        abort(404);
    }
}
