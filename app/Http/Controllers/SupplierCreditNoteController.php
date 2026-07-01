<?php

namespace App\Http\Controllers;

use App\Models\SupplierCreditNote;
use App\Models\Wallet;
use App\Services\SupplierCreditNoteService;
use Illuminate\Http\Request;

class SupplierCreditNoteController extends Controller
{
    public function __construct(private readonly SupplierCreditNoteService $supplierCreditNoteService)
    {
    }

    public function index(Request $request)
    {
        $records = SupplierCreditNote::query()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('credit_note_number', 'like', "%{$search}%")
                        ->orWhereHas('contact', fn ($contactQuery) => $contactQuery->where('fullname', 'like', "%{$search}%"))
                        ->orWhereHas('invoice', fn ($invoiceQuery) => $invoiceQuery->where('invoice_number', 'like', "%{$search}%"));
                });
            })
            ->with(['contact', 'invoice', 'supplierReturn'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('back.credit-notes.index', [
            'title' => 'Avoirs fournisseur',
            'subtitle' => 'Retrouver, ouvrir, imprimer ou rembourser les avoirs fournisseurs.',
            'records' => $records,
            'module' => 'supplier',
            'emptyMessage' => 'Aucun avoir fournisseur pour le moment.',
        ]);
    }

    public function show(Request $request, SupplierCreditNote $supplierCreditNote)
    {
        abort_unless($supplierCreditNote->tenant_id === $request->user()->tenant_id, 404);

        $supplierCreditNote->load(['items.product', 'invoice', 'contact', 'supplierReturn.warehouse', 'wallet']);

        return view('back.credit-notes.show', [
            'title' => 'Avoir fournisseur',
            'record' => $supplierCreditNote,
        ]);
    }

    public function print(Request $request, SupplierCreditNote $supplierCreditNote)
    {
        abort_unless($supplierCreditNote->tenant_id === $request->user()->tenant_id, 404);

        $supplierCreditNote->load(['items.product', 'invoice', 'contact', 'supplierReturn.warehouse', 'wallet']);

        return view('back.credit-notes.print', [
            'title' => 'Avoir fournisseur',
            'record' => $supplierCreditNote,
        ]);
    }

    public function refund(Request $request, SupplierCreditNote $supplierCreditNote)
    {
        abort_unless($supplierCreditNote->tenant_id === $request->user()->tenant_id, 404);

        $data = $request->validate([
            'wallet_id' => ['required', 'uuid'],
            'amount' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $wallet = Wallet::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->whereKey($data['wallet_id'])
            ->firstOrFail();

        $supplierCreditNote = $this->supplierCreditNoteService->refund(
            $supplierCreditNote,
            $request->user(),
            $wallet,
            (int) $data['amount'],
            $data['note'] ?? null
        );

        return redirect()->route('supplier-credit-notes.show', $supplierCreditNote)->with('success', 'Remboursement fournisseur enregistré avec succès.');
    }
}
