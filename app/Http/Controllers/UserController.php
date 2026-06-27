<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Traits\LogsActivity;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use LogsActivity;

    public function index()
    {
        $current = Auth::user();

        $users = User::with(['roles', 'tenant'])
            ->when(!$current->is_platform_user(), fn ($q) => $q->where('tenant_id', $current->tenant_id))
            ->join('tenants', 'users.tenant_id', '=', 'tenants.id')
            ->orderBy('tenants.name', 'asc')
            ->select('users.*')
            ->paginate(50);

        return view('back.users.index', compact('users'));
    }

    public function create()
    {
        $current = Auth::user();

        if (!$current->can('create_users')) {
            abort(403, "Vous n'avez pas l'autorisation de créer des utilisateurs.");
        }

        $roles = Role::where('tenant_id', $current->tenant_id)->get();

        return view('back.users.add', compact('roles'));
    }

    public function store(Request $request)
    {
        $current = Auth::user();

        if (!$current->can('create_users')) {
            abort(403, "Vous n'avez pas l'autorisation de créer des utilisateurs.");
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'role' => [
                'required',
                'string',
                Rule::exists('roles', 'id')->where('tenant_id', $current->tenant_id)
            ],
            'is_active' => 'nullable',
        ]);

        $validated['is_active'] = $request->has('is_active');

        try {
            DB::beginTransaction();

            $newUser = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'tenant_id' => $current->tenant_id,
                'is_active' => $validated['is_active'],
                'password' => Hash::make($validated['password']),
            ]);

            $role = Role::where('tenant_id', $current->tenant_id)->findOrFail($validated['role']);
            if ($role->tenant_id !== $current->tenant_id) {
                abort(403, "Action non autorisée.");
            }
            $newUser->syncRoles([$role]);

            DB::commit();

            // 🔹 Log activité
            $this->saveActivity(
                "Création d'un utilisateur",
                "Utilisateur: {$newUser->name}",
                ['tenant_id' => $current->tenant_id]
            );

            return redirect()->route('users.index')->with('success', '✅ Utilisateur créé avec succès.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->with('error', '❌ Une erreur est survenue lors de la création de l’utilisateur.')->withInput();
        }
    }

    public function edit(User $user)
    {
        $current = Auth::user();

        if (!$current->is_platform_user() && $user->tenant_id != $current->tenant_id) {
            abort(403, "Vous n'avez pas l'autorisation de modifier cet utilisateur.");
        }

        if ($current->is_platform_user() && !$current->is_owner && $user->id != $current->id) {
            abort(403, "Vous n'avez pas l'autorisation de modifier cet utilisateur.");
        }

        $roles = Role::where('tenant_id', $user->tenant_id)->get();

        return view('back.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $current = Auth::user();

        if (!$current->is_platform_user() && $user->tenant_id != $current->tenant_id) {
            abort(403, "Vous n'avez pas l'autorisation de modifier cet utilisateur.");
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
            'role' => [
                'required',
                'string',
                Rule::exists('roles', 'name')->where('tenant_id', $user->tenant_id)
            ],
            'is_active' => 'nullable',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $isActive = $validated['is_active'] ?? true;

        if ($user->is_owner) {
            $isActive = true;
        }

        try {
            DB::beginTransaction();

            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'is_active' => $isActive,
                'password' => !empty($validated['password']) ? Hash::make($validated['password']) : $user->password,
            ]);

            $role = Role::where('name', $validated['role'])
                ->where('tenant_id', $user->tenant_id)
                ->firstOrFail();
            if ($role->tenant_id !== $user->tenant_id) {
                abort(403, "Action non autorisée.");
            }
            $user->syncRoles([$role]);

            DB::commit();

            // 🔹 Log activité
            $this->saveActivity(
                "Mise à jour d'un utilisateur",
                "Utilisateur: {$user->name}",
                ['tenant_id' => $user->tenant_id]
            );

            return redirect()->route('users.index')->with('success', '✅ Utilisateur mis à jour avec succès.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->with('error', '❌ Une erreur est survenue lors de la mise à jour.')->withInput();
        }
    }

    public function destroy(User $user)
    {
        $current = Auth::user();

        if ($user->tenant_id != $current->tenant_id && !$current->can('delete_any_users')) {
            abort(403, "Vous n'avez pas l'autorisation de supprimer cet utilisateur.");
        }

        try {
            $userName = $user->name;
            $tenantId = $user->tenant_id;
            $user->delete();

            // 🔹 Log activité
            $this->saveActivity(
                "Suppression d'un utilisateur",
                "Utilisateur: {$userName}",
                ['tenant_id' => $tenantId]
            );

            return back()->with('success', "✅ L'utilisateur \"{$userName}\" a bien été supprimé.");
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', '❌ Une erreur est survenue lors de la suppression.');
        }
    }

    public function toggle(String $id)
    {
        $user = User::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);
        $current = Auth::user();

        if ($user->is_owner) {
            return back()->with('error', "Vous ne pouvez pas activer/désactiver le propriétaire de l'entreprise.");
        }

        if ($user->id == $current->id) {
            return back()->with('error', "Vous ne pouvez pas activer/désactiver votre propre compte");
        }

        if ($current->tenant_id !== $user->tenant_id && !$current->can('delete_any_users') && !$current->is_platform_user()) {
            return back()->with('error', "Vous n'avez pas le droit de modifier le statut de cet utilisateur.");
        }

        try {
            $user->is_active = !$user->is_active;
            $user->save();

            $status = $user->is_active ? 'activé' : 'désactivé';

            // 🔹 Log activité
            $this->saveActivity(
                "Changement de statut utilisateur",
                "Utilisateur: {$user->name} -> {$status}",
                ['tenant_id' => $user->tenant_id]
            );

            return back()->with('success', "✅ L'utilisateur « {$user->name} » a été {$status}.");
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', "❌ Une erreur est survenue lors de la mise à jour de l'utilisateur.");
        }
    }
}
