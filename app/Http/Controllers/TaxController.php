<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaxRequest;
use App\Models\Tax;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function index(Request $request)
    {
        $taxes = Tax::withTrashed()
            ->where('tenant_id', $request->user()->tenant_id)
            ->latest()
            ->paginate(20);

        return view('back.taxes.index', compact('taxes'));
    }

    public function store(TaxRequest $request)
    {
        Tax::create([
            'tenant_id' => $request->user()->tenant_id,
            'name' => $request->name,
            'rate' => $request->rate,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Taxe créée avec succès.');
    }

    public function update(TaxRequest $request, Tax $tax)
    {
        abort_unless($tax->tenant_id === $request->user()->tenant_id, 403);

        $tax->update([
            'name' => $request->name,
            'rate' => $request->rate,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Taxe mise à jour avec succès.');
    }

    public function destroy(Request $request, Tax $tax)
    {
        abort_unless($tax->tenant_id === $request->user()->tenant_id, 403);

        $tax->delete();

        return back()->with('success', 'Taxe supprimée.');
    }

    public function restore(Request $request, string $id)
    {
        $tax = Tax::withTrashed()
            ->where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->firstOrFail();

        $tax->restore();

        return back()->with('success', 'Taxe restaurée.');
    }
}
