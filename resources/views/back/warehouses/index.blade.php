@extends('back.layouts.admin')

@section('content')
    <div class="page-hero page-hero--accent mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <div class="page-hero__eyebrow mb-2">Stock & logistique</div>
                <h1 class="page-hero__title mb-0"><i class="fas fa-warehouse mr-2"></i> Entrepôts</h1>
                <p class="page-hero__subtitle">Centralisez vos points de stockage, leurs responsables et leurs transferts.</p>
            </div>
            <button type="button" class="btn btn-light" data-toggle="modal" data-target="#addWarehouseModal">
                <i class="fas fa-plus mr-1"></i> Nouvel entrepôt
            </button>
        </div>
    </div>

    <div class="table-card">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1 font-weight-bold">Liste des entrepôts</h5>
                <div class="text-muted">Vue globale des dépôts avec accès direct au détail et au transfert.</div>
            </div>
            <span class="status-pill status-pill--neutral">{{ $warehouses->count() }} résultats</span>
        </div>

        @if ($warehouses->count())
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Adresse</th>
                            <th>Responsable</th>
                            <th>Description</th>
                            <th>Statut</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($warehouses as $warehouse)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="font-weight-bold">{{ $warehouse->name }}</td>
                                <td>{{ $warehouse->address ?? '-' }}</td>
                                <td>
                                    @if ($warehouse->manager)
                                        <a href="{{ route('users.edit', $warehouse->manager->id) }}" class="text-primary font-weight-semibold">
                                            {{ $warehouse->manager->name }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ \Illuminate\Support\Str::limit($warehouse->description ?? '-', 60) }}</td>
                                <td>
                                    @if ($warehouse->is_active)
                                        <span class="status-pill status-pill--success">Activé</span>
                                    @else
                                        <span class="status-pill status-pill--danger">Désactivé</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                                        <a href="{{ route('warehouses.exchange', $warehouse->id) }}" class="btn btn-sm btn-info" title="Transférer">
                                            <i class="fas fa-exchange-alt"></i>
                                        </a>
                                        <a href="{{ route('warehouses.show', $warehouse->id) }}" class="btn btn-sm btn-success" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('warehouses.edit', $warehouse->id) }}" class="btn btn-sm btn-warning" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('warehouses.toggle', $warehouse->id) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Voulez-vous {{ $warehouse->is_active ? 'désactiver' : 'activer' }} cet entrepôt ?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $warehouse->is_active ? 'btn-secondary' : 'btn-success' }}" title="Statut">
                                                <i class="fas fa-toggle-{{ $warehouse->is_active ? 'off' : 'on' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('warehouses.destroy', $warehouse->id) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Confirmer la suppression de cet entrepôt ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state__icon"><i class="fas fa-warehouse"></i></div>
                <h5 class="mb-2">Aucun entrepôt disponible</h5>
                <p class="mb-4">Créez un entrepôt pour commencer à structurer vos stocks.</p>
                <button type="button" class="btn-modern btn-primary" data-toggle="modal" data-target="#addWarehouseModal">
                    <i class="fas fa-plus-circle mr-1"></i> Nouvel entrepôt
                </button>
            </div>
        @endif
    </div>

    <div class="modal fade" id="addWarehouseModal" tabindex="-1" role="dialog" aria-labelledby="addWarehouseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content rounded-2xl overflow-hidden">
                @include('back.warehouses._form', [
                    'route' => route('warehouses.store'),
                    'method' => 'POST',
                    'warehouse' => new \App\Models\Warehouse(),
                ])
            </div>
        </div>
    </div>
@endsection
