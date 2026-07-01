<?php

namespace App\Http\Controllers;

use App\Models\CustomerCreditNote;
use App\Models\Wallet;
use App\Services\CustomerCreditNoteService;
use Illuminate\Http\Request;

class CustomerCreditNoteController extends Controller
{
    public function __construct(private readonly CustomerCreditNoteService $customerCreditNoteService)
    {
    }

    public function index(Request $request)
    {
        $records = CustomerCreditNote::query()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('credit_note_number', 'like', "%{$search}%")
                        ->orWhereHas('contact', fn ($contactQuery) => $contactQuery->where('fullname', 'like', "%{$search}%"))
                        ->orWhereHas('invoice', fn ($invoiceQuery) => $invoiceQuery->where('invoice_number', 'like', "%{$search}%"));
                });
            })
            ->with(['contact', 'invoice', 'customerReturn'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('back.credit-notes.index', [
            'title' => 'Avoirs client',
            'subtitle' => 'Retrouver, ouvrir, imprimer ou rembourser les avoirs clients.',
            'records' => $records,
            'module' => 'customer',
            'emptyMessage' => 'Aucun avoir client pour le moment.',
        ]);
    }

    public function show(Request $request, CustomerCreditNote $customerCreditNote)
    {
        abort_unless($customerCreditNote->tenant_id === $request->user()->tenant_id, 404);

        $customerCreditNote->load(['items.product', 'invoice', 'contact', 'customerReturn.warehouse', 'wallet']);

        return view('back.customer-credit-notes.show', [
            'title' => 'Avoir client',
            'record' => $customerCreditNote,
        ]);
    }

    public function print(Request $request, CustomerCreditNote $customerCreditNote)
    {
        abort_unless($customerCreditNote->tenant_id === $request->user()->tenant_id, 404);

        $customerCreditNote->load(['items.product', 'invoice', 'contact', 'customerReturn.warehouse', 'wallet']);

        return view('back.customer-credit-notes.print', [
            'title' => 'Avoir client',
            'record' => $customerCreditNote,
        ]);
    }

    public function refund(Request $request, CustomerCreditNote $customerCreditNote)
    {
        abort_unless($customerCreditNote->tenant_id === $request->user()->tenant_id, 404);

        $data = $request->validate([
            'wallet_id' => ['required', 'uuid'],
            'amount' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $wallet = Wallet::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->whereKey($data['wallet_id'])
            ->firstOrFail();

        $customerCreditNote = $this->customerCreditNoteService->refund(
            $customerCreditNote,
            $request->user(),
            $wallet,
            (int) $data['amount'],
            $data['note'] ?? null
        );

        return redirect()->route('customer-credit-notes.show', $customerCreditNote)->with('success', 'Remboursement client enregistré avec succès.');
    }
}
