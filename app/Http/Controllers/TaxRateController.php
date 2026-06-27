<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaxRateRequest;
use App\Models\TaxRate;
use Illuminate\Http\Request;

class TaxRateController extends Controller
{
    public function create()
    {
        return redirect()->route('tax_rates.index');
    }

    public function edit(TaxRate $taxRate)
    {
        return redirect()->route('tax_rates.index');
    }

    public function show(TaxRate $taxRate)
    {
        return redirect()->route('tax_rates.index');
    }

    public function index(Request $request)
    {
        $taxRates = TaxRate::where('tenant_id', auth()->user()->tenant_id)
            ->latest()
            ->paginate(10);

        return view('back.tax_rates.index', compact('taxRates'));
    }

    public function store(TaxRateRequest $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $validated = $request->validated();

        if (! empty($validated['is_default'])) {
            TaxRate::where('tenant_id', $tenantId)->update(['is_default' => false]);
        }

        TaxRate::create([
            'tenant_id' => $tenantId,
            'name' => $validated['name'],
            'rate' => $validated['rate'],
            'is_default' => (bool) ($validated['is_default'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return back()->with('success', 'Taxe enregistrée avec succès.');
    }

    public function update(TaxRateRequest $request, TaxRate $taxRate)
    {
        if ($taxRate->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Action non autorisée.');
        }

        $validated = $request->validated();

        if (! empty($validated['is_default'])) {
            TaxRate::where('tenant_id', auth()->user()->tenant_id)
                ->where('id', '!=', $taxRate->id)
                ->update(['is_default' => false]);
        }

        $taxRate->update([
            'name' => $validated['name'],
            'rate' => $validated['rate'],
            'is_default' => (bool) ($validated['is_default'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return back()->with('success', 'Taxe mise à jour avec succès.');
    }

    public function destroy(TaxRate $taxRate)
    {
        if ($taxRate->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Action non autorisée.');
        }

        $taxRate->delete();

        return back()->with('success', 'Taxe supprimée avec succès.');
    }
}
