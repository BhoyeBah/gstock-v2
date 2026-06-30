<?php

namespace App\Http\Controllers;

use App\Models\DocumentSequence;
use App\Services\DocumentNumberService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DocumentSequenceController extends Controller
{
    public function __construct(
        private readonly DocumentNumberService $documentNumberService
    ) {}

    public function index()
    {
        $tenant = auth()->user()->tenant;

        foreach ($this->documentNumberService->supportedDocumentTypes() as $documentType) {
            $this->documentNumberService->ensureSequenceExists($documentType, $tenant);
        }

        $sequences = DocumentSequence::where('tenant_id', $tenant->id)
            ->orderBy('document_type')
            ->orderByDesc('year')
            ->orderByDesc('created_at')
            ->get()
            ->unique('document_type')
            ->values();

        $previews = [];
        foreach ($sequences as $sequence) {
            $previews[$sequence->id] = $this->documentNumberService->preview($sequence->document_type, $tenant);
        }

        return view('back.document_sequences.index', compact('sequences', 'previews'));
    }

    public function update(Request $request, string $id)
    {
        $tenantId = auth()->user()->tenant_id;

        $sequence = DocumentSequence::where('tenant_id', $tenantId)->findOrFail($id);

        $validated = $request->validate([
            'prefix' => ['required', 'string', 'max:10'],
            'padding' => ['required', 'integer', 'min:2', 'max:8'],
            'reset_period' => ['required', Rule::in(['never', 'yearly', 'monthly'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DocumentSequence::where('tenant_id', $tenantId)
            ->where('document_type', $sequence->document_type)
            ->update([
                'prefix' => strtoupper($validated['prefix']),
                'padding' => $validated['padding'],
                'reset_period' => $validated['reset_period'],
                'is_active' => $request->boolean('is_active', true),
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Sequence document mise a jour avec succes.');
    }
}
