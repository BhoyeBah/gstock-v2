@extends('back.layouts.admin')

@section('content')
<style>
    /* En-tête de page */
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

    .page-header .btn { transition: .2s; }
    .page-header .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    /* Cartes statistiques */
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

    .stats-card .card-body { padding: 1.5rem; }

    .stats-card .stats-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .stats-card.border-left-primary .stats-icon {
        background: rgba(78, 115, 223, 0.1);
        color: #4e73df;
    }

    .stats-card.border-left-success .stats-icon {
        background: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }

    /* Section recherche */
    .search-section {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
        overflow: hidden;
    }

    .search-section .card-header {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        border: none;
        padding: 1.25rem 1.5rem;
    }

    .search-section .form-control,
    .search-section .form-control:focus {
        border-radius: 8px;
        border: 1px solid #e3e6f0;
    }

    .search-section .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
    }

    .search-section label {
        font-weight: 600;
        color: #5a5c69;
        margin-bottom: 0.5rem;
    }

    /* Section liste */
    .invoice-list-section {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .invoice-list-section .card-header {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        border: none;
        padding: 1.25rem 1.5rem;
    }

    .invoice-table { margin-bottom: 0; }

    .invoice-table thead th {
        background: #f8f9fc;
        color: #5a5c69;
        font-weight: 700;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border: none;
        padding: 1rem 0.75rem;
        white-space: nowrap;
    }

    .invoice-table tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid #e3e6f0;
    }

    .invoice-table tbody tr:hover { background: #f8f9fc; }

    .invoice-table tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        font-size: 0.875rem;
    }

    .badge {
        padding: 0.45rem 0.7rem;
        font-weight: 700;
        font-size: 0.72rem;
        border-radius: 8px;
        letter-spacing: 0.3px;
    }

    .badge-soft-primary { background: rgba(78,115,223,.12); color:#4e73df; }
    .badge-soft-dark { background: rgba(90,92,105,.12); color:#5a5c69; }
    .badge-soft-success { background: rgba(40,167,69,.12); color:#28a745; }
    .badge-soft-danger { background: rgba(231,74,59,.12); color:#e74a3b; }

    .avatar-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        color: #4e73df;
        background: rgba(78,115,223,.12);
        border: 1px solid rgba(78,115,223,.25);
    }

    .action-buttons .btn {
        margin: 0 0.125rem;
        transition: all 0.2s ease;
        border-radius: 8px;
    }

    .action-buttons .btn:hover { transform: translateY(-1px); }

    /* Modal */
    .modal-content { border-radius: 15px; border: none; }
    .modal-header { border-radius: 15px 15px 0 0; border: none; padding: 1.5rem; }
    .modal-body { padding: 2rem; }
    .modal-footer { border: none; padding: 1.5rem; background: #f8f9fc; }
</style>

{{-- ===================== HEADER ===================== --}}
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <h1><i class="fas fa-users mr-2"></i> Gestion des employés</h1>
        <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-primary m-1" data-toggle="modal" data-target="#addEmployeModal">
                <i class="fas fa-plus-circle mr-1"></i>
                <strong>Nouvel employé</strong>
            </button>
        </div>
    </div>
</div>

{{-- ===================== STATS ===================== --}}
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card border-left-primary shadow h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-2">Total employés</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $employes->total() }}</div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon"><i class="fas fa-user-tie"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card border-left-success shadow h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-2">Salaires (page)</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800">
                            {{ number_format($employes->sum('salary'), 0, ',', ' ') }} FCFA
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="stats-icon"><i class="fas fa-coins"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===================== SEARCH ===================== --}}
<div class="search-section">
    <div class="card-header text-white">
        <h6 class="m-0 font-weight-bold"><i class="fas fa-filter mr-2"></i> Filtrer le personnel</h6>
    </div>
    <div class="card-body p-4">
        <form method="GET" action="{{ route('employes.index') }}">
            <div class="form-row">
                <div class="col-md-4 mb-3">
                    <label for="search_name">Nom complet</label>
                    <input type="text" name="search_name" id="search_name" class="form-control"
                           value="{{ request('search_name') }}" placeholder="Ex : Jean Dupont">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="search_position">Poste / Fonction</label>
                    <input type="text" name="search_position" id="search_position" class="form-control"
                           value="{{ request('search_position') }}" placeholder="Ex : Développeur">
                </div>

                <div class="col-md-4 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary mr-2 flex-fill">
                        <i class="fas fa-search mr-1"></i> Filtrer
                    </button>
                    <a href="{{ route('employes.index') }}" class="btn btn-secondary flex-fill">
                        <i class="fas fa-redo mr-1"></i> Réinitialiser
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ===================== LIST ===================== --}}
<div class="invoice-list-section">
    <div class="card-header text-white">
        <h6 class="m-0 font-weight-bold"><i class="fas fa-list-ul mr-2"></i> Annuaire des employés</h6>
    </div>

    <div class="card-body p-0">
        @if ($employes->count() > 0)
            <div class="table-responsive">
                <table class="table invoice-table">
                    <thead>
                    <tr>
                        <th>Matricule</th>
                        <th>Nom complet</th>
                        <th>Poste</th>
                        <th>Téléphone</th>
                        <th>Salaire</th>
                        <th>Statut</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach ($employes as $employe)
                        <tr>
                            {{-- ✅ FIX: Matricule (tu affichais position ici) --}}
                            <td>
                                @if(!empty($employe->matricule))
                                    <span class="badge badge-soft-dark">{{ $employe->matricule }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle mr-3">
                                        {{ strtoupper(mb_substr($employe->full_name ?? 'E', 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong class="text-dark d-block">{{ $employe->full_name }}</strong>
                                        <small class="text-muted">
                                            Créé le {{ optional($employe->created_at)->format('d/m/Y') }}
                                        </small>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="badge badge-soft-primary">
                                    {{ $employe->position ?? '—' }}
                                </span>
                            </td>

                            <td>{{ $employe->phone ?? 'N/A' }}</td>

                            <td>
                                @if($employe->salary)
                                    <span class="font-weight-bold text-success">
                                        {{ number_format($employe->salary, 0, ',', ' ') }} FCFA
                                    </span>
                                @else
                                    <span class="text-muted">Non défini</span>
                                @endif
                            </td>

                            {{-- ✅ à la place de “Solde”, je mets “Statut” (plus logique ici).
                                 Si tu veux absolument “Solde”, dis-moi et je le remets.
                            --}}
                            <td>
                                @if($employe->is_active)
                                    <span class="badge badge-soft-success">
                                        <i class="fas fa-check-circle mr-1"></i> Actif
                                    </span>
                                @else
                                    <span class="badge badge-soft-danger">
                                        <i class="fas fa-times-circle mr-1"></i> Inactif
                                    </span>
                                @endif
                            </td>

                            <td class="text-center action-buttons">
                                <a href="{{ route('employes.show', $employe->id) }}"
                                   class="btn btn-sm btn-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>

                                {{-- ✅ FIX: message “employé” et pas “entrepôt” --}}
                                <form action="{{ route('employes.toggleActive', $employe->id) }}" method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Voulez-vous {{ $employe->is_active ? 'désactiver' : 'activer' }} cet employé ?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="btn btn-sm {{ $employe->is_active ? 'btn-success' : 'btn-danger' }}"
                                            title="{{ $employe->is_active ? 'Désactiver' : 'Activer' }}">
                                        <i class="fas fa-toggle-{{ $employe->is_active ? 'off' : 'on' }}"></i>
                                    </button>
                                </form>

                                <a href="{{ route('employes.edit', $employe->id) }}"
                                   class="btn btn-sm btn-warning" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <form action="{{ route('employes.destroy', $employe->id) }}" method="POST"
                                      class="d-inline" onsubmit="return confirm('Supprimer cet employé ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center p-4 border-top">
                <div class="text-muted small">
                    Affichage de <strong>{{ $employes->firstItem() }}</strong> à
                    <strong>{{ $employes->lastItem() }}</strong> sur
                    <strong>{{ $employes->total() }}</strong> employés
                </div>
                <div>
                    {{ $employes->appends(request()->query())->links() }}
                </div>
            </div>
        @else
            <div class="p-5 text-center">
                <div class="mb-4">
                    <i class="fas fa-user-slash fa-4x text-muted"></i>
                </div>
                <h5 class="text-muted">Aucun employé trouvé</h5>
                <p class="text-muted mb-4">Essayez de modifier vos filtres ou créez-en un nouveau</p>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addEmployeModal">
                    <i class="fas fa-plus-circle mr-2"></i> Créer un employé
                </button>
            </div>
        @endif
    </div>
</div>

{{-- ===================== MODAL: ADD EMPLOYEE ===================== --}}
<div class="modal fade" id="addEmployeModal" tabindex="-1" role="dialog" aria-labelledby="addEmployeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            @include('back.employes._form', [
                'route' => route('employes.store'),
                'method' => 'POST',
                'employe' => new \App\Models\Employe(),
            ])
        </div>
    </div>
</div>
@endsection
