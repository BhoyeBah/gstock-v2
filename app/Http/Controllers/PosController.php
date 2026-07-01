<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePosSaleRequest;
use App\Models\Batch;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\Wallet;
use App\Models\Warehouse;
use App\Services\PosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PosController extends Controller
{
    public function __construct(private PosService $posService) {}

    /**
     * Écran de vente rapide (POS).
     */
    public function index()
    {
        $warehouses = Warehouse::orderBy('name')->get();
        $wallets = Wallet::where('is_active', true)->orderBy('name')->get();
        $clients = Contact::type('client')->orderBy('fullname')->get();

        return view('back.sales.index', compact('warehouses', 'wallets', 'clients'));
    }

    /**
     * Recherche produit tenant-safe avec disponibilité par entrepôt (JSON).
     */
    public function products(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'warehouse_id' => ['required', 'uuid', Rule::exists('warehouses', 'id')->where('tenant_id', $tenantId)],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $search = $validated['q'] ?? null;

        $products = \App\Models\Product::query()
            ->where('is_active', true)
            ->when($search, fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->limit(30)
            ->get(['id', 'name', 'price']);

        $availability = Batch::where('warehouse_id', $validated['warehouse_id'])
            ->whereIn('product_id', $products->pluck('id'))
            ->selectRaw('product_id, SUM(remaining) as available')
            ->groupBy('product_id')
            ->pluck('available', 'product_id');

        $payload = $products->map(fn ($product) => [
            'id' => $product->id,
            'name' => $product->name,
            'price' => (int) $product->price,
            'available' => (int) ($availability[$product->id] ?? 0),
        ]);

        return response()->json($payload);
    }

    /**
     * Enregistre une vente comptoir : facture + stock FIFO + encaissement(s).
     */
    public function store(StorePosSaleRequest $request)
    {
        try {
            $invoice = $this->posService->createSale($request->validated());
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('pos.receipt', $invoice->id)
            ->with('success', 'Vente enregistrée : '.$invoice->invoice_number);
    }

    /**
     * Ticket de caisse imprimable d'une vente.
     */
    public function receipt(Invoice $invoice)
    {
        if ($invoice->type !== 'client') {
            abort(404);
        }

        $invoice->load(['items.product', 'contact', 'payments']);
        $setting = Setting::first();

        return view('back.sales.receipt', compact('invoice', 'setting'));
    }
}
