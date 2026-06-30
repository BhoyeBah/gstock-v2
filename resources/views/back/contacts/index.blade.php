@extends('back.layouts.admin')

@section('title', 'Liste des ' . $contactType)

@section('content')
    @php
        // Calculs pour les cartes de statistiques
        $totalContacts = $contacts->total();
        // Pour des stats précises non affectées par la pagination
        $allContactsOfType = \App\Models\Contact::where('type', $type);
        $activeContacts = (clone $allContactsOfType)->where('is_active', true)->count();
        $inactiveContacts = $totalContacts - $activeContacts;
    @endphp

    <div class="page-hero page-hero--accent">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="page-hero__eyebrow mb-2">Contacts</div>
                <h1 class="page-hero__title"><i class="fas fa-users mr-2"></i> {{ $contactType }}</h1>
                <p class="page-hero__subtitle">Clients et fournisseurs tenant-safe, avec suivi rapide des statuts.</p>
            </div>
            <button type="button" class="btn btn-light" data-toggle="modal" data-target="#addContactModal">
                <i class="fas fa-plus-circle mr-1"></i>
                Nouveau {{ $type === 'clients' ? 'client' : 'fournisseur' }}
            </button>
        </div>
    </div>

    <!-- Cartes statistiques -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="metric-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-2">Total {{ $contactType }}</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $totalContacts }}</div>
                        </div>
                        <div class="col-auto"><div class="stats-icon"><i class="fas fa-users"></i></div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="metric-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-2">Contacts Actifs</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $activeContacts }}</div>
                        </div>
                        <div class="col-auto"><div class="stats-icon"><i class="fas fa-user-check"></i></div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="metric-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-2">Contacts Inactifs</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $inactiveContacts }}</div>
                        </div>
                        <div class="col-auto"><div class="stats-icon"><i class="fas fa-user-times"></i></div></div>
                    </div>
                </div>
            </div>
        </div>
      
    </div>

    <!-- Section recherche -->
    <div class="search-section">
        <div class="card-header text-white">
            <h6 class="m-0 font-weight-bold"><i class="fas fa-filter mr-2"></i> Filtrer les {{ $contactType }}</h6>
        </div>
        <div class="card-body p-4">
            <form method="GET" action="{{ route("$type.index") }}">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="search">Nom ou Téléphone</label>
                        <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Rechercher...">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="status">Statut</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">Tous les statuts</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactif</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary mr-2 w-100"><i class="fas fa-search mr-1"></i> Filtrer</button>
                        <a href="{{ route("$type.index") }}" class="btn btn-secondary w-100"><i class="fas fa-redo mr-1"></i> Réinitialiser</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des contacts -->
    <div class="table-card">
        <div class="card-header text-white">
            <h6 class="m-0 font-weight-bold"><i class="fas fa-list-ul mr-2"></i> Liste des {{ $contactType }}</h6>
        </div>
        <div class="card-body p-0">
            @if ($contacts->count() > 0)
                <div class="table-responsive">
                    <table class="table data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nom complet</th>
                                <th>Téléphone</th>
                                <th>Adresse</th>
                                <th>Solde</th>
                                <th>Statut</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($contacts as $contact)
                                <tr>
                                    <td class="font-weight-bold text-muted">{{ $loop->iteration + ($contacts->currentPage() - 1) * $contacts->perPage() }}</td>
                                    <td><strong class="text-dark">{{ $contact->fullname }}</strong></td>
                                    <td>{{ $contact->phone_number }}</td>
                                    <td>{{ Str::limit($contact->address, 30) ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $contact->balance_total > 0 ? 'badge-danger' : 'badge-success' }}">
                                            {{ number_format($contact->balance_total ?? 0, 0, ',', ' ') }} CFA
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $contact->is_active ? 'badge-success' : 'badge-danger' }}">
                                            {{ $contact->is_active ? 'Activé' : 'Désactivé' }}
                                        </span>
                                    </td>
                                    <td class="text-center action-buttons">
                                        <form action="{{ route("$type.toggle", $contact->id) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Voulez-vous {{ $contact->is_active ? 'désactiver' : 'activer' }} ce contact ?')">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $contact->is_active ? 'btn-success' : 'btn-danger' }}" title="{{ $contact->is_active ? 'Désactiver' : 'Activer' }}">
                                                <i class="fas fa-toggle-{{ $contact->is_active ? 'on' : 'off' }}"></i>
                                            </button>
                                        </form>
                                        <a href="{{ route("$type.show", $contact->id) }}" class="btn btn-sm btn-info" title="Voir"><i class="fas fa-eye"></i></a>
                                        <a href="{{ route("$type.edit", $contact->id) }}" class="btn btn-sm btn-warning" title="Modifier"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route("$type.destroy", $contact->id) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Confirmer la suppression de ce contact ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Supprimer"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center p-4 border-top">
                    <div class="text-muted small">
                        Affichage de <strong>{{ $contacts->firstItem() }}</strong> à <strong>{{ $contacts->lastItem() }}</strong> sur <strong>{{ $contacts->total() }}</strong> contacts
                    </div>
                    <div>
                        {{ $contacts->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="p-5 text-center">
                    <div class="mb-4"><i class="fas fa-user-slash fa-4x text-muted"></i></div>
                    <h5 class="text-muted">Aucun {{ Str::lower($contactType) }} trouvé</h5>
                    <p class="text-muted mb-4">Essayez de modifier vos filtres ou créez-en un nouveau.</p>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addContactModal">
                        <i class="fas fa-plus-circle mr-2"></i> Créer un nouveau contact
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal ajout -->
    <div class="modal fade" id="addContactModal" tabindex="-1" role="dialog" aria-labelledby="addContactModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                @include('back.contacts._form', [
                    'route' => route("$type.store"),
                    'method' => 'POST',
                    'contact' => new \App\Models\Contact(),
                    'type' => $type,
                ])
            </div>
        </div>
    </div>
@endsection
