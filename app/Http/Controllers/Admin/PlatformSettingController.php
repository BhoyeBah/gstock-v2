<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Tenant;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;

class PlatformSettingController extends Controller
{
    use LogsActivity;

    public function index()
    {
        $platformTenant = Tenant::query()->where('slug', 'platform')->firstOrFail();
        $setting = Setting::query()->withoutGlobalScopes()->where('tenant_id', $platformTenant->id)->first();

        return view('back.admin.platform-settings.index', compact('setting', 'platformTenant'));
    }

    public function store(Request $request)
    {
        $platformTenant = Tenant::query()->where('slug', 'platform')->firstOrFail();

        $validated = $request->validate([
            'currency' => 'required|in:XOF,GNF,FCFA,GMD,LE',
            'tva' => 'required|numeric|min:0|max:100',
        ]);

        $setting = Setting::query()->withoutGlobalScopes()->updateOrCreate(
            ['tenant_id' => $platformTenant->id],
            [
                'currency' => $validated['currency'],
                'tva' => $validated['tva'],
            ]
        );

        $this->saveActivity(
            'Mise à jour des paramètres plateforme',
            'Paramètres globaux du SaaS',
            ['setting_id' => $setting->id, 'tenant_id' => $platformTenant->id]
        );

        return redirect()->route('admin.settings.index')->with('success', 'Paramètres plateforme enregistrés avec succès.');
    }

    public function update(Request $request, Setting $setting)
    {
        $platformTenant = Tenant::query()->where('slug', 'platform')->firstOrFail();

        if ($setting->tenant_id !== $platformTenant->id) {
            abort(403, 'Action non autorisée.');
        }

        $validated = $request->validate([
            'currency' => 'required|in:XOF,GNF,FCFA,GMD,LE',
            'tva' => 'required|numeric|min:0|max:100',
        ]);

        $setting->update($validated);

        $this->saveActivity(
            'Mise à jour des paramètres plateforme',
            'Paramètres globaux du SaaS',
            ['setting_id' => $setting->id, 'tenant_id' => $platformTenant->id]
        );

        return redirect()->route('admin.settings.index')->with('success', 'Paramètres plateforme mis à jour avec succès.');
    }
}
