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

    <style>
        /* Styles copiés de votre exemple pour une cohérence parfaite */
        .page-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .page-header h1 {
            color: #fff;
            font-weight: 700;
            margin: 0;
            font-size: 1.75rem;
        }

        .page-header .btn {
            transition: all 0.3s ease;
        }

        .page-header .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .stats-card {
            border: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            overflow: hidden;
            background: #fff;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .stats-card .card-body {
            padding: 1.5rem;
        }

        .stats-card .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stats-card.border-left-primary .stats-icon { background: rgba(79, 70, 229, 0.1); color: #4f46e5; }
        .stats-card.border-left-success .stats-icon { background: rgba(40, 167, 69, 0.1); color: #28a745; }
        .stats-card.border-left-warning .stats-icon { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
        .stats-card.border-left-danger .stats-icon { background: rgba(220, 53, 69, 0.1); color: #dc3545; }

        .search-section {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .search-section .card-header {
            background: linear-gradient(135deg, #475569 0%, #334155 100%);
            border: none;
            padding: 1.25rem 1.5rem;
        }

        .search-section .form-control,
        .search-section .form-control:focus {
            border-radius: 8px;
            border: 1px solid #e3e6f0;
        }

        .search-section .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.15);
        }

        .search-section label {
            font-weight: 600;
            color: #5a5c69;
            margin-bottom: 0.5rem;
        }

        .list-section {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .list-section .card-header {
            background: linear-gradient(135deg, #4f46e5 0%, #224abe 100%);
            border: none;
            padding: 1.25rem 1.5rem;
        }

        .custom-table {
            margin-bottom: 0;
        }

        .custom-table thead th {
            background: #f8f9fc;
            color: #5a5c69;
            font-weight: 700;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            border: none;
            padding: 1rem 0.75rem;
            white-space: nowrap;
        }

        .custom-table tbody tr {
            transition: all 0.2s ease;
            border-bottom: 1px solid #e3e6f0;
        }
        .custom-table tbody tr:last-child {
            border-bottom: none;
        }

        .custom-table tbody tr:hover {
            background: #f8f9fc;
        }

        .custom-table tbody td {
            padding: 1rem 0.75rem;
            vertical-align: middle;
            font-size: 0.875rem;
        }

        .badge {
            padding: 0.5rem 0.875rem;
            font-weight: 600;
            font-size: 0.75rem;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .action-buttons .btn {
            margin: 0 0.125rem;
            transition: all 0.2s ease;
            border-radius: 6px;
        }

        .action-buttons .btn:hover {
            transform: scale(1.1);
        }
        
        .pagination { margin-bottom: 0; }
        .page-link { border-radius: 6px; margin: 0 0.125rem; border: none; color: #4f46e5; }
        .page-link:hover { background: #4f46e5; color: #fff; }
        .page-item.active .page-link { background: #4f46e5; border-color: #4f46e5; }
        
        .modal-content { border-radius: 15px; border: none; }
        .modal-header { border-radius: 15px 15px 0 0; border: none; padding: 1.5rem; }
        .modal-body { padding: 2rem; }
        .modal-footer { border: none; padding: 1.5rem; background: #f8f9fc; }
    </style>

    <!-- En-tête de page -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <h1>
                <i class="fas fa-users mr-2"></i> {{ $contactType }}
            </h1>
            <button type="button" class="btn btn-primary m-1" data-toggle="modal" data-target="#addContactModal">
                <i class="fas fa-plus-circle mr-1"></i>
                <strong>Nouveau {{ $type === 'clients' ? 'client' : 'fournisseur' }}</strong>
            </button>
        </div>
    </div>

    <!-- Cartes statistiques -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card border-left-primary shadow h-100">
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
            <div class="card stats-card border-left-success shadow h-100">
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
            <div class="card stats-card border-left-warning shadow h-100">
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
    <div class="list-section">
        <div class="card-header text-white">
            <h6 class="m-0 font-weight-bold"><i class="fas fa-list-ul mr-2"></i> Liste des {{ $contactType }}</h6>
        </div>
        <div class="card-body p-0">
            @if ($contacts->count() > 0)
                <div class="table-responsive">
                    <table class="table custom-table">
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