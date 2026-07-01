<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryMovmentController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();

        $query = InventoryMovement::query()
            ->with(['product', 'warehouse', 'batch.warehouse', 'inventory', 'invoice.contact', 'user'])
            ->when(! $currentUser->is_platform_user(), fn ($q) => $q->where('tenant_id', $currentUser->tenant_id))
            ->orderByDesc('movement_date')
            ->orderByDesc('created_at');

        if ($type = request('movement_type')) {
            $query->where('movement_type', $type);
        }

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', fn ($product) => $product->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('batch', fn ($batch) => $batch->where('code', 'like', "%{$search}%"))
                    ->orWhereHas('warehouse', fn ($warehouse) => $warehouse->where('name', 'like', "%{$search}%"));
            });
        }

        $movements = $query->paginate(20)->withQueryString();

        $statsQuery = InventoryMovement::query()->when(! $currentUser->is_platform_user(), fn ($q) => $q->where('tenant_id', $currentUser->tenant_id));

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'in' => (clone $statsQuery)->whereIn('movement_type', ['in', 'entry', 'adjustment_in'])->count(),
            'out' => (clone $statsQuery)->whereIn('movement_type', ['out', 'sale', 'adjustment_out'])->count(),
            'adjustment' => (clone $statsQuery)->where('movement_type', 'adjustment')->count(),
        ];

        return view('back.movements.index', compact('movements', 'stats'));
    }

    public function create()
    {
        abort(404);
    }

    public function store(Request $request)
    {
        abort(404);
    }

    public function show(InventoryMovement $inventoryMovement)
    {
        abort(404);
    }

    public function edit(InventoryMovement $inventoryMovement)
    {
        abort(404);
    }

    public function update(Request $request, InventoryMovement $inventoryMovement)
    {
        abort(404);
    }

    public function destroy(InventoryMovement $inventoryMovement)
    {
        abort(404);
    }
}
