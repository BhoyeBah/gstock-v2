@extends('back.layouts.admin')

@section('content')
<div class="page-hero page-hero--accent">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <div class="page-hero__eyebrow mb-2">Paramètres</div>
            <h1 class="page-hero__title mb-0">👥 Gestion des utilisateurs</h1>
            <p class="page-hero__subtitle">Comptes, accès et statuts pour votre entreprise.</p>
        </div>
        @can('create_users')
            <a href="{{ route('users.create') }}" class="btn btn-light">
                <i class="fas fa-plus fa-sm mr-1"></i> Nouvel utilisateur
            </a>
        @endcan
    </div>
</div>

<div class="table-card">
    <div class="card-header text-white d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold">Liste des utilisateurs</h6>
    </div>

    <div class="card-body">
        @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table data-table">
                    <thead class="thead-light text-uppercase text-secondary">
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            @if(auth()->user()->is_platform_user())
                                <th>Entreprise</th>
                            @endif
                            <th>Rôle</th>
                            <th>Statut</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $user->name }}</strong>
                                    @if($user->is_owner)
                                        <span class="badge badge-info">Propriétaire</span>
                                    @endif
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone ?? '-' }}</td>
                                @if(auth()->user()->is_platform_user())
                                    <td>{{ $user->tenant?->name ?? '-' }}</td>
                                @endif
                                <td>
                                    @php
                                        $roleName = $user->roles->first()?->name ?? '-';
                                        $roleLabel = strtolower(\Illuminate\Support\Str::after($roleName, '_'));
                                    @endphp
                                    {{ $roleLabel === 'admin' ? ucfirst($roleLabel) : strtolower($roleLabel) }}
                                </td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge badge-success">Actif</span>
                                    @else
                                        <span class="badge badge-danger">Inactif</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <!-- Activer/Désactiver (si ce n'est pas le propriétaire) -->
                                    @if(!$user->is_owner)
                                        <form action="{{ route('users.toggle', $user->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-warning' : 'btn-success' }}" title="{{ $user->is_active ? 'Désactiver' : 'Activer' }}">
                                                <i class="fas {{ $user->is_active ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Modifier -->
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-primary" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <!-- Supprimer (sauf propriétaire, et selon les permissions) -->
                                    @if(!$user->is_owner && (
                                            (auth()->user()->tenant_id == $user->tenant_id && auth()->user()->can('delete_users')) ||
                                            (auth()->user()->tenant_id != $user->tenant_id && auth()->user()->can('delete_any_users'))
                                        ))
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirmer la suppression de cet utilisateur ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $users->links() }}
            </div>
        @else
            <div class="alert alert-info">
                Aucun utilisateur pour le moment.
            </div>
        @endif
    </div>
</div>
@endsection
