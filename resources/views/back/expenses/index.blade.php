@extends('back.layouts.admin')

@php
    use Carbon\Carbon;
@endphp

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

        .page-header .btn {
            transition: all 0.3s ease;
        }

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

        .stats-card.border-left-secondary .stats-icon {
            background: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }

        .stats-card.border-left-info .stats-icon {
            background: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
        }

        .stats-card.border-left-warning .stats-icon {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        .stats-card.border-left-success .stats-icon {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .stats-card.border-left-danger .stats-icon {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .stats-card.border-left-primary .stats-icon {
            background: rgba(78, 115, 223, 0.1);
            color: #4e73df;
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

        /* Liste des factures (réutilisé pour dépenses) */
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

        .invoice-table {
            margin-bottom: 0;
        }

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

        .invoice-table tbody tr:hover {
            background: #f8f9fc;
            /* Suppression de transform: scale(1.01) pour éviter les problèmes de layout */
        }

        .invoice-table tbody td {
            padding: 1rem 0.75rem;
            vertical-align: middle;
            font-size: 0.875rem;
        }

        /* Badges de statut */
        .badge {
            padding: 0.5rem 0.875rem;
            font-weight: 600;
            font-size: 0.75rem;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Boutons d'action */
        .action-buttons .btn {
            margin: 0 0.125rem;
            transition: all 0.2s ease;
            border-radius: 6px;
        }

        .action-buttons .btn:hover {
            transform: scale(1.1);
        }

        /* Modal amélioré */
        .modal-content {
            border-radius: 15px;
            border: none;
        }

        .modal-header {
            border-radius: 15px 15px 0 0;
            border: none;
            padding: 1.5rem;
        }

        .modal-body {
            padding: 2rem;
        }

        .modal-footer {
            border: none;
            padding: 1.5rem;
            background: #f8f9fc;
        }

        /* Alertes personnalisées */
        .alert {
            border: none;
            border-radius: 10px;
            padding: 1.25rem;
        }

        .alert-info {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            color: #0c5460;
        }

        /* Pagination */
        .pagination {
            margin-bottom: 0;
        }

        .page-link {
            border-radius: 6px;
            margin: 0 0.125rem;
            border: none;
            color: #4e73df;
        }

        .page-link:hover {
            background: #4e73df;
            color: #fff;
        }

        .page-item.active .page-link {
            background: #4e73df;
            border-color: #4e73df;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stats-card {
            animation: fadeInUp 0.5s ease-out;
        }

        .stats-card:nth-child(1) {
            animation-delay: 0.1s;
        }

        .stats-card:nth-child(2) {
            animation-delay: 0.2s;
        }

        .stats-card:nth-child(3) {
            animation-delay: 0.3s;
        }

        .stats-card:nth-child(4) {
            animation-delay: 0.4s;
        }

        /* Input groups */
        .input-group-text {
            border-radius: 8px 0 0 8px;
            border: 1px solid #e3e6f0;
        }

        .input-group .form-control {
            border-radius: 0 8px 8px 0;
        }
    </style>

    <!-- En-tête de page -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <h1>
                <i class="fas fa-money-bill-wave mr-2"></i> Dépenses internes
            </h1>
            <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
                <button type="button" class="btn btn-warning m-1" data-toggle="modal" data-target="#addExpenseModal">
                    <i class="fas fa-plus-circle mr-1"></i>
                    <strong>Nouvelle dépense</strong>
                </button>
            </div>
        </div>
    </div>

    <!-- Cartes statistiques -->
    <div class="row mb-4">

        <!-- Total général -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-2">
                                Total des dépenses
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($expenses->sum('amount'), 0, ',', ' ') }} FCFA
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="stats-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dépenses aujourd'hui -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card border-left-info shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-2">
                                Aujourd'hui
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($expenses->where('expense_date', '>=', now()->startOfDay())->sum('amount'), 0, ',', ' ') }}
                                FCFA
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="stats-icon">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dépenses cette semaine -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-2">
                                Cette semaine
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($expenses->where('expense_date', '>=', now()->startOfWeek())->sum('amount'), 0, ',', ' ') }}
                                FCFA
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="stats-icon">
                                <i class="fas fa-calendar-week"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dépenses ce mois -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-2">
                                Ce mois
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($expenses->where('expense_date', '>=', now()->startOfMonth())->sum('amount'), 0, ',', ' ') }}
                                FCFA
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="stats-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section recherche (Nouveau) -->
    <div class="search-section">
        <div class="card-header text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-filter mr-2"></i> Filtrer les dépenses
            </h6>
        </div>
        <div class="card-body p-4">
            <form method="GET" action="{{ route('expenses.index') }}">
                <div class="form-row">
                    <div class="col-md-3 mb-3">
                        <label for="search_reason">Motif</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" name="search_reason" id="search_reason" class="form-control"
                                value="{{ request('search_reason') }}" placeholder="Rechercher par motif...">
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="date_start">Date de début</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            </div>
                            <input type="date" name="date_start" id="date_start" class="form-control"
                                value="{{ request('date_start') }}">
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="date_end">Date de fin</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            </div>
                            <input type="date" name="date_end" id="date_end" class="form-control"
                                value="{{ request('date_end') }}">
                        </div>
                    </div>

                    <div class="col-md-3 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary mr-2 flex-fill">
                            <i class="fas fa-search mr-1"></i> Filtrer
                        </button>
                        <a href="{{ route('expenses.index') }}" class="btn btn-secondary flex-fill">
                            <i class="fas fa-redo mr-1"></i> Réinitialiser
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <!-- Liste des dépenses -->
    <div class="invoice-list-section">
        <div class="card-header text-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-list-ul mr-2"></i> Liste des dépenses
            </h6>
            <a href="{{ route('expenses.print', request()->query()) }}" class="btn btn-primary" target="_blank">
                Imprimer
            </a>
        </div>

        <div class="card-body p-0">
            @if ($expenses->count() > 0)
                <div class="table-responsive">
                    <table class="table invoice-table">
                        <thead>
                            <tr>
                                <th width="50">#</th>
                                <th>Motif</th>
                                <th>Wallet</th>
                                <th class="text-right">Montant (FCFA)</th>
                                <th>Date dépense</th>
                                {{-- <th class="text-center" width="150">Actions</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($expenses as $expense)
                                <tr>
                                    <td class="font-weight-bold text-muted">
                                        {{ $loop->iteration + ($expenses->currentPage() - 1) * $expenses->perPage() }}
                                    </td>
                                    <td>
                                        <strong class="text-dark">{{ $expense->reason }}</strong>
                                    </td>
                                    <td>
                                        <strong class="text-dark">{{ $expense->wallet->name ?? '-' }}</strong>
                                    </td>

                                    <td class="text-right font-weight-bold text-danger">
                                        {{ number_format($expense->amount, 0, ',', ' ') }} FCFA
                                    </td>
                                    <td>
                                        <i class="fas fa-calendar-alt text-muted mr-1"></i>
                                        {{ $expense->expense_date ? $expense->expense_date->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="text-center action-buttons">
                                        {{-- TODO: Ajouter un bouton "Modifier" si nécessaire --}}
                                        {{-- <button class="btn btn-sm btn-warning" title="Modifier"><i class="fas fa-edit"></i></button> --}}
                                        @can('delete_expenses')
                                            <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Confirmer la suppression de cette dépense ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        @endcan

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center p-4 border-top">
                    <div class="text-muted small">
                        <i class="fas fa-info-circle mr-1"></i>
                        Affichage de <strong>{{ $expenses->firstItem() }}</strong> à
                        <strong>{{ $expenses->lastItem() }}</strong> sur
                        <strong>{{ $expenses->total() }}</strong> dépenses
                    </div>
                    <div>
                        {{ $expenses->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="p-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-inbox fa-4x text-muted"></i>
                    </div>
                    <h5 class="text-muted">Aucune dépense trouvée</h5>
                    <p class="text-muted mb-4">
                        Essayez de modifier vos filtres ou créez-en une nouvelle
                    </p>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addExpenseModal">
                        <i class="fas fa-plus-circle mr-2"></i> Créer une nouvelle dépense
                    </button>
                </div>
            @endif
        </div>
    </div>


    <!-- Modal : Ajout d'une dépense -->
    <div class="modal fade" id="addExpenseModal" tabindex="-1" role="dialog" aria-labelledby="addExpenseModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                @include('back.expenses._form', [
                    'route' => route('expenses.store'),
                    'method' => 'POST',
                    'expense' => new \App\Models\Expense(),
                ])
            </div>
        </div>
    </div>

@endsection
