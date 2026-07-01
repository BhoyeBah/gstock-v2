<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class TenantController extends Controller
{
    use LogsActivity;

    public function index()
    {
        $tenants = Tenant::latest()->paginate(100);

        return view('back.admin.tenants.index', compact('tenants'));
    }

    public function create()
    {
        return view('back.admin.tenants.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tenant.name' => 'required|string|max:255',
            'tenant.slug' => 'required|string|alpha_dash|unique:tenants,slug',
            'tenant.email' => 'nullable|email|required',
            'tenant.phone' => 'nullable|string|max:20',
            'tenant.logo' => 'nullable|image|max:2048',
            'user.name' => 'required|string|max:255',
            'user.email' => 'required|email|unique:users,email',
            'user.password' => 'required|confirmed|min:8',
            'user.phone' => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();

        try {
            $tenantData = $request->input('tenant');
            $tenant = new Tenant([
                'name' => $tenantData['name'],
                'slug' => $tenantData['slug'],
                'email' => $tenantData['email'] ?? null,
                'phone' => $tenantData['phone'] ?? null,
                'address' => $tenantData['address'] ?? null,
                'ninea' => $tenantData['ninea'] ?? null,
                'rc' => $tenantData['rc'] ?? null,
            ]);

            if ($request->hasFile('tenant.logo')) {
                $tenant->logo = $request->file('tenant.logo')->store('logos', 'public');
            }

            $tenant->save();

            $adminRole = Role::create([
                'name' => $roleName = $tenant->slug.'_Admin',
                'guard_name' => 'web',
                'tenant_id' => $tenant->id,
            ]);

            $adminRole->givePermissionTo(['manage_roles', 'create_pos_sales', 'manage_employee']);

            $userData = $request->input('user');

            $user = new User([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'phone' => $userData['phone'] ?? null,
                'password' => Hash::make($userData['password']),
                'tenant_id' => $tenant->id,
                'is_owner' => true,
                'is_active' => true,
            ]);
            $user->save();

            $user->assignRole($adminRole);

            DB::commit();

            // 🔹 Sauvegarde activité
            $this->saveActivity(
                "Création d'une entreprise",
                "Entreprise: {$tenant->name}",
                ['tenant_id' => $tenant->id]
            );

            return redirect()->route('admin.tenants.index')->with('success', 'Entreprise créée avec succès.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->with('error', 'Une erreur est survenue lors de la création.')->withInput();
        }
    }

    public function show(Tenant $tenant)
    {
        //
    }

    public function edit(Tenant $tenant)
    {
        return view('back.admin.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $request->merge([
            'is_active' => $request->has('is_active'),
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|alpha_dash|unique:tenants,slug,'.$tenant->id,
            'email' => 'nullable|email|required',
            'ninea' => 'nullable|string|required',
            'phone' => 'nullable|string|max:20',
            'rc' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:100',
            'logo' => 'nullable|image|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $tenant->name = $request->name;
            if ($tenant->slug !== 'platform') {
                $tenant->slug = $request->slug;
            }
            $tenant->email = $request->email ?? null;
            $tenant->phone = $request->phone ?? null;
            $tenant->is_active = $request->boolean('is_active');

            if ($request->hasFile('logo')) {
                if ($tenant->logo && Storage::disk('public')->exists($tenant->logo)) {
                    Storage::disk('public')->delete($tenant->logo);
                }
                $tenant->logo = $request->file('logo')->store('logos', 'public');
            }

            $tenant->save();

            // 🔹 Sauvegarde activité
            $this->saveActivity(
                "Mise à jour d'une entreprise",
                "Entreprise: {$tenant->name}",
                ['tenant_id' => $tenant->id]
            );

            return redirect()->route('admin.tenants.index')->with('success', 'Entreprise mise à jour avec succès.');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Une erreur est survenue lors de la mise à jour.')->withInput();
        }
    }

    public function destroy(Tenant $tenant)
    {
        if ($tenant->slug === 'plateform') {
            return redirect()->back()->with('error', 'Ce tenant ne peut pas être supprimé car il est réservé à la plateforme.');
        }

        return back()->with('error', 'Une erreur est survenue lors de la suppression.');

    }
}
