<?php

namespace App\Http\Controllers;

use App\Http\Requests\PosSaleRequest;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Wallet;
use App\Models\Warehouse;
use App\Services\PosSaleService;
use Illuminate\Http\Request;

class PosSaleController extends Controller
{
    public function __construct(private readonly PosSaleService $posSaleService) {}

    public function create(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $warehouses = Warehouse::where('tenant_id', $tenantId)->orderBy('name')->get();
        $wallets = Wallet::where('tenant_id', $tenantId)->orderBy('name')->get();
        $clients = Contact::where('tenant_id', $tenantId)->where('type', 'client')->orderBy('fullname')->get();
        $products = Product::where('tenant_id', $tenantId)->where('is_active', true)->orderBy('name')->get();

        return view('back.pos.create', compact('warehouses', 'wallets', 'clients', 'products'));
    }

    public function store(PosSaleRequest $request)
    {
        $invoice = $this->posSaleService->createSale($request->validated(), $request->user());

        return redirect()->route('sales.receipt', $invoice)->with('success', 'Vente enregistrée avec succès.');
    }

    public function receipt(Request $request, Invoice $invoice)
    {
        abort_unless($invoice->tenant_id === $request->user()->tenant_id, 403);

        $invoice->load(['items.product', 'items.warehouse', 'contact', 'payments.wallet']);

        return view('back.pos.receipt', compact('invoice'));
    }
}
