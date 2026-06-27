<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuoteRequest;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Quote;
use App\Models\TaxRate;
use App\Models\Warehouse;
use App\Services\QuoteConversionService;
use App\Services\QuoteService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function __construct(
        private readonly QuoteService $quoteService,
        private readonly QuoteConversionService $quoteConversionService
    ) {
    }

    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $quotes = Quote::where('tenant_id', $tenantId)
            ->with(['contact', 'items.product'])
            ->latest()
            ->paginate(10);

        return view('back.quotes.index', compact('quotes'));
    }

    public function create()
    {
        $tenantId = auth()->user()->tenant_id;

        $contacts = Contact::where('tenant_id', $tenantId)
            ->where('type', 'client')
            ->orderBy('fullname')
            ->get();
        $products = Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $warehouses = Warehouse::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $taxRates = TaxRate::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('back.quotes.create', compact('contacts', 'products', 'warehouses', 'taxRates'));
    }

    public function store(StoreQuoteRequest $request)
    {
        $quote = $this->quoteService->createQuote($request->validated());

        return redirect()->route('quotes.show', $quote)->with('success', 'Devis enregistré avec succès.');
    }

    public function show(Quote $quote)
    {
        $this->ensureTenantOwnership($quote);

        $quote->load(['contact', 'items.product', 'items.taxRate', 'items.warehouse', 'convertedInvoice']);

        return view('back.quotes.show', compact('quote'));
    }

    public function edit(Quote $quote)
    {
        $this->ensureTenantOwnership($quote);

        $tenantId = auth()->user()->tenant_id;
        $quote->load('items');

        $contacts = Contact::where('tenant_id', $tenantId)
            ->where('type', 'client')
            ->orderBy('fullname')
            ->get();
        $products = Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $warehouses = Warehouse::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $taxRates = TaxRate::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('back.quotes.edit', compact('quote', 'contacts', 'products', 'warehouses', 'taxRates'));
    }

    public function update(StoreQuoteRequest $request, Quote $quote)
    {
        $this->ensureTenantOwnership($quote);

        $quote = $this->quoteService->updateQuote($quote, $request->validated());

        return redirect()->route('quotes.show', $quote)->with('success', 'Devis mis à jour avec succès.');
    }

    public function destroy(Quote $quote)
    {
        $this->ensureTenantOwnership($quote);

        if ($quote->status === Quote::STATUS_CONVERTED || $quote->converted_invoice_id) {
            return back()->with('error', 'Impossible de supprimer un devis déjà converti.');
        }

        $quote->delete();

        return back()->with('success', 'Devis supprimé avec succès.');
    }

    public function convert(Quote $quote)
    {
        $this->ensureTenantOwnership($quote);

        try {
            $invoice = $this->quoteConversionService->convert($quote->fresh());

            return redirect()
                ->route('invoices.show', ['type' => 'clients', 'invoice' => $invoice->id])
                ->with('success', 'Devis converti en facture avec succès.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function pdf(Quote $quote)
    {
        $this->ensureTenantOwnership($quote);

        $quote->load(['contact', 'items.product', 'items.taxRate', 'items.warehouse']);
        $pdf = Pdf::loadView('back.quotes.pdf', compact('quote'));

        return $pdf->download('devis-' . ($quote->quote_number ?? $quote->id) . '.pdf');
    }

    private function ensureTenantOwnership(Quote $quote): void
    {
        if ($quote->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Action non autorisée.');
        }
    }
}
