<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Plan;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;

class PlanPermissionController extends Controller
{
    use LogsActivity;

    public function index()
    {
        $plans = Plan::with('permissions')->orderBy('price')->get();
        $permissions = Permission::orderBy('name')->get();

        return view('back.admin.plan-permissions.index', compact('plans', 'permissions'));
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $plan->permissions()->sync($validated['permissions'] ?? []);
        $plan->load('permissions');

        $this->saveActivity(
            "Mise à jour des permissions d'un plan",
            "Plan: {$plan->name}",
            ['plan_id' => $plan->id, 'permissions' => $plan->permissions->pluck('name')->all()]
        );

        return redirect()->route('admin.plan-permissions.index')->with('success', 'Permissions du plan mises à jour avec succès.');
    }
}
