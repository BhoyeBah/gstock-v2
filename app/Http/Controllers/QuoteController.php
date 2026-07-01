<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuoteRequest;
use App\Models\Contact;
use App\Models\Quote;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\QuoteConversionService;
use App\Services\QuoteService;
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
        $tenantId = $request->user()->tenant_id;
        $status = $request->input('status');

        $quotes = Quote::query()
            ->where('tenant_id', $tenantId)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->with('contact')
            ->latest()
            ->paginate(10);

        return view('back.documents.index', [
            'title' => 'Devis / Proforma',
            'subtitle' => 'Gérer les devis et les conversions commerciales.',
            'records' => $quotes,
            'status' => $status,
            'documentKind' => 'quote',
            'showRoute' => 'quotes.show',
            'createRoute' => 'quotes.create',
            'emptyMessage' => 'Aucun devis créé pour le moment.',
        ]);
    }

    public function create(Request $request)
    {
        return view('back.documents.form', [
            'title' => 'Créer un devis',
            'record' => new Quote(['quote_date' => now()->toDateString()]),
            'storeRoute' => 'quotes.store',
            'updateRoute' => null,
            'contacts' => Contact::query()->where('tenant_id', $request->user()->tenant_id)->where('type', 'client')->orderBy('fullname')->get(),
            'products' => Product::query()->where('tenant_id', $request->user()->tenant_id)->orderBy('name')->get(),
            'warehouses' => Warehouse::query()->where('tenant_id', $request->user()->tenant_id)->orderBy('name')->get(),
            'mode' => 'quote',
        ]);
    }

    public function store(QuoteRequest $request)
    {
        $quote = $this->quoteService->create($request->validated(), $request->user());
        return redirect()->route('quotes.show', $quote)->with('success', 'Devis créé avec succès.');
    }

    public function show(Request $request, Quote $quote)
    {
        $quote = $this->resolveQuote($request, $quote);
        $quote->load(['items.product', 'contact', 'invoice', 'saleOrder']);

        return view('back.documents.show', [
            'title' => 'Devis / Proforma',
            'record' => $quote,
            'items' => $quote->items,
            'type' => 'quote',
            'documentKind' => 'quote',
        ]);
    }

    public function edit(Request $request, Quote $quote)
    {
        $quote = $this->resolveQuote($request, $quote);

        return view('back.documents.form', [
            'title' => 'Modifier le devis',
            'record' => $quote->load('items'),
            'storeRoute' => null,
            'updateRoute' => 'quotes.update',
            'contacts' => Contact::query()->where('tenant_id', $request->user()->tenant_id)->where('type', 'client')->orderBy('fullname')->get(),
            'products' => Product::query()->where('tenant_id', $request->user()->tenant_id)->orderBy('name')->get(),
            'warehouses' => Warehouse::query()->where('tenant_id', $request->user()->tenant_id)->orderBy('name')->get(),
            'mode' => 'quote',
        ]);
    }

    public function update(QuoteRequest $request, Quote $quote)
    {
        $quote = $this->resolveQuote($request, $quote);
        $quote = $this->quoteService->update($quote, $request->validated(), $request->user());
        return redirect()->route('quotes.show', $quote)->with('success', 'Devis mis à jour avec succès.');
    }

    public function destroy(Request $request, Quote $quote)
    {
        $quote = $this->resolveQuote($request, $quote);
        $quote->delete();
        return redirect()->route('quotes.index')->with('success', 'Devis supprimé.');
    }

    public function send(Request $request, Quote $quote)
    {
        $quote = $this->resolveQuote($request, $quote);
        $this->quoteService->send($quote);
        return back()->with('success', 'Devis envoyé.');
    }

    public function accept(Request $request, Quote $quote)
    {
        $quote = $this->resolveQuote($request, $quote);
        $this->quoteService->accept($quote);
        return back()->with('success', 'Devis accepté.');
    }

    public function reject(Request $request, Quote $quote)
    {
        $quote = $this->resolveQuote($request, $quote);
        $this->quoteService->reject($quote);
        return back()->with('success', 'Devis rejeté.');
    }

    public function cancel(Request $request, Quote $quote)
    {
        $quote = $this->resolveQuote($request, $quote);
        $this->quoteService->cancel($quote);
        return back()->with('success', 'Devis annulé.');
    }

    public function convertToOrder(Request $request, Quote $quote)
    {
        $quote = $this->resolveQuote($request, $quote);
        $saleOrder = $this->quoteConversionService->toSaleOrder($quote);

        return redirect()->route('sale-orders.show', $saleOrder)->with('success', 'Devis converti en commande client.');
    }

    public function convertToInvoice(Request $request, Quote $quote)
    {
        $quote = $this->resolveQuote($request, $quote);
        $invoice = $this->quoteConversionService->toInvoice($quote);

        return redirect()->route('invoices.show', ['type' => 'clients', 'invoice' => $invoice])->with('success', 'Devis converti en facture.');
    }

    public function print(Request $request, Quote $quote)
    {
        $quote = $this->resolveQuote($request, $quote);
        $quote->load(['items.product', 'contact', 'invoice', 'saleOrder']);

        return view('back.documents.print', [
            'title' => 'Devis / Proforma',
            'record' => $quote,
            'items' => $quote->items,
        ]);
    }

    private function resolveQuote(Request $request, Quote $quote): Quote
    {
        return Quote::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->whereKey($quote->id)
            ->firstOrFail();
    }
}
