@extends('back.layouts.admin')

@php use Carbon\Carbon; @endphp

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

        /* Liste des paiements (basé sur invoice-list) */
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

        /* Contact link */
        .contact-link {
            color: #4e73df;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .contact-link:hover {
            color: #224abe;
            text-decoration: underline;
        }
    </style>

    <!-- En-tête de page -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <h1>
                <i class="fas fa-money-bill-wave mr-2"></i> Liste des paiements
                ({{ $type === 'clients' ? 'Clients' : 'Fournisseurs' }})
            </h1>
            <!-- Pas de bouton "Nouveau" car les paiements se font depuis les factures -->
            {{-- <button class="btn btn-primary" data-toggle="modal" data-target="#addStockOutModal">
                <i class="fas fa-plus-circle mr-1"></i> Nouvelle payment
            </button> --}}
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addPaymentModal">
                <i class="fas fa-plus mr-1"></i> Nouveau paiement
            </button>
        </div>
    </div>

    <!-- Cartes statistiques (Nouveau) -->
    <div class="row mb-4">
        <!-- Total payé -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-2">
                                Total payé
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                {{-- $payments est la collection paginée, sum() ne fonctionnera que sur la page.
                                     Assurez-vous de passer une variable $totalPaidSum depuis le contrôleur
                                     ou de charger tous les paiements dans $payments si non paginé.
                                     Pour l'exemple, nous utilisons $payments->sum() comme dans expenses_list. --}}
                                {{ number_format($payments->sum('amount_paid'), 0, ',', ' ') }} FCFA
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="stats-icon">
                                <i class="fas fa-wallet"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payé aujourd'hui -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card border-left-info shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-2">
                                Payé Aujourd'hui
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($payments->where('payment_date', '>=', now()->startOfDay())->sum('amount_paid'), 0, ',', ' ') }}
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

        <!-- Payé cette semaine -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-2">
                                Payé cette semaine
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($payments->where('payment_date', '>=', now()->startOfWeek())->sum('amount_paid'), 0, ',', ' ') }}
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

        <!-- Payé ce mois -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card border-left-primary shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-2">
                                Payé ce mois
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($payments->where('payment_date', '>=', now()->startOfMonth())->sum('amount_paid'), 0, ',', ' ') }}
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


    <!-- ========================= FILTRES (Améliorés) ========================= -->
    <section id="payment-filters">
        <div class="search-section">
            <div class="card-header text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-filter mr-2"></i> Recherche et filtres
                </h6>
            </div>

            <div class="card-body p-4">
                <form method="GET" action="{{ route('payments.index', $type) }}">
                    <div class="form-row">
                        <div class="col-md-3 mb-3">
                            <label for="invoice_number">Numéro de facture</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                </div>
                                <input type="text" name="invoice_number" id="invoice_number" class="form-control"
                                    value="{{ request('invoice_number') }}" placeholder="N° facture">
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="tenant">Fournisseur / Client</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                </div>
                                <input type="text" name="tenant" id="tenant" class="form-control"
                                    value="{{ request('tenant') }}" placeholder="Nom du contact">
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

                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2 flex-fill">
                                <i class="fas fa-search mr-1"></i> Rechercher
                            </button>
                            <a href="{{ route('payments.index', $type) }}" class="btn btn-secondary flex-fill">
                                <i class="fas fa-redo mr-1"></i> Réinitialiser
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- ========================= LISTE DES PAIEMENTS (Améliorée) ========================= -->
    <section id="payment-list">
        <div class="invoice-list-section">
            <div class="card-header text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-list-ul mr-2"></i> Paiements enregistrés
                </h6>
            </div>

            <div class="card-body p-0">
                @if ($payments->count())
                    <div class="table-responsive">
                        <table class="table invoice-table">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Facture</th>
                                    <th>Nom</th>
                                    <th>Type</th>
                                    <th class="text-right">Montant payé</th>
                                    <th class="text-right">Reste</th>
                                    <th>Date</th>
                                    <th>Méthode</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($payments as $payment)
                                    <tr>
                                        <td class="font-weight-bold text-muted">
                                            {{ $loop->iteration + ($payments->currentPage() - 1) * $payments->perPage() }}
                                        </td>
                                        <td>
                                            <a href="{{ route('invoices.show', [$type, $payment->invoice_id]) }}"
                                                class="contact-link" title="Voir la facture">
                                                {{ $payment->invoice->invoice_number ?? '-' }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ route("$type.show", $payment->contact_id) }}"
                                                class="contact-link" title="Voir le contact">
                                                <i class="fas fa-user-circle mr-1"></i>
                                                {{ $payment->contact->fullname ?? '-' }}
                                            </a>
                                        </td>
                                        <td>
                                            @if ($payment->contact)
                                                @if ($payment->contact->type === 'supplier')
                                                    <span class="badge badge-warning">Fournisseur</span>
                                                @else
                                                    <span class="badge badge-info">Client</span>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-right font-weight-bold text-success">
                                            {{ number_format($payment->amount_paid, 0, ',', ' ') }} FCFA
                                        </td>
                                        <td class="text-right">
                                            <span
                                                class="badge badge-{{ $payment->remaining_amount > 0 ? 'warning' : 'success' }}">
                                                {{ number_format($payment->remaining_amount, 0, ',', ' ') }} FCFA
                                            </span>
                                        </td>
                                        <td>
                                            <i class="fas fa-calendar-alt text-muted mr-1"></i>
                                            {{ Carbon::parse($payment->payment_date)->format('d/m/Y') }}
                                        </td>
                                        <td>
                                            <i class="fas fa-credit-card text-muted mr-1"></i>
                                            {{ ucfirst($payment->payment_type) }}
                                        </td>
                                        <td class="text-center action-buttons">
                                            {{-- Remplacement du formulaire onsubmit par une modale --}}

                                            @can('delete_payment')
                                                <button type="button" class="btn btn-sm btn-danger confirm-action-btn"
                                                    title="Supprimer" data-toggle="modal" data-target="#confirmModal"
                                                    data-action="{{ route('payments.destroy', [$type, $payment->id]) }}"
                                                    data-method="DELETE"
                                                    data-message="Confirmez-vous la suppression de ce paiement ? Cette action est irréversible et réajustera le solde de la facture."
                                                    data-btn-class="btn-danger"
                                                    data-title="<i class='fas fa-exclamation-triangle'></i> Supprimer le paiement"
                                                    @if ($payment->amount_paid == 0)
                                                    disabled
                                    @endif>
                                    <i class="fas fa-trash-alt"></i>
                                    </button>
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
                    Affichage de <strong>{{ $payments->firstItem() }}</strong> à
                    <strong>{{ $payments->lastItem() }}</strong> sur
                    <strong>{{ $payments->total() }}</strong> paiements
                </div>
                <div>
                    {{ $payments->appends(request()->query())->links() }}
                </div>
            </div>
        @else
            <div class="p-5 text-center">
                <div class="mb-4">
                    <i class="fas fa-inbox fa-4x text-muted"></i>
                </div>
                <h5 class="text-muted">Aucun paiement trouvé</h5>
                <p class="text-muted mb-0">
                    Essayez de modifier vos filtres ou enregistrez un paiement depuis une facture.
                </p>
            </div>
            @endif
        </div>
        </div>
    </section>

    <!-- Modale de confirmation générique (POUR SUPPRIMER) -->
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header" id="confirmModalHeader"> {{-- Couleur définie par JS --}}
                    <h5 class="modal-title" id="confirmModalLabel">Confirmation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="confirmModalBody">
                    Êtes-vous sûr de vouloir continuer ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <form id="confirmForm" method="POST" action="" class="d-inline">
                        @csrf
                        <input type="hidden" name="_method" id="confirmMethod" value="POST">
                        <button type="submit" class="btn" id="confirmButton">Confirmer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ajouter un paiement -->
    <div class="modal fade" id="addPaymentModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg"
                style="border-radius: 15px; overflow:hidden; box-shadow:0 10px 30px rgba(102,126,234,0.3);">

                <form action="{{ route('payments.store', $type) }}" method="POST">
                    @csrf

                    <!-- HEADER -->
                    <div class="modal-header text-white"
                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-bottom: none;">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-money-bill-wave mr-2"></i>Nouveau paiement
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <!-- BODY -->
                    <div class="modal-body p-4">

                        <!-- Facture -->
                        <div class="form-group">
                            <label for="invoice_id" class="font-weight-semibold" style="color:#4a5568;">
                                <i class="fas fa-file-invoice mr-1"></i>Facture
                            </label>

                            <select name="invoice_id" id="invoice_id" class="form-control form-control-lg shadow-sm"
                                style="border-radius:10px;" required>
                                <option value="">Sélectionnez une facture</option>

                                @foreach ($invoices as $invoice)
                                    <option value="{{ $invoice->id }}">
                                        {{ $invoice->invoice_number }}
                                        — {{ $invoice->contact->fullname ?? '-' }}
                                        ({{ number_format($invoice->balance, 0, ',', ' ') }} FCFA restant)
                                    </option>
                                @endforeach
                            </select>

                            <small class="form-text text-muted">Choisissez la facture à régler</small>
                        </div>

                        <!-- Montant payé -->
                        <div class="form-group">
                            <label for="amount_paid" class="font-weight-semibold" style="color:#4a5568;">
                                <i class="fas fa-coins mr-1"></i>Montant payé
                            </label>

                            <input type="number" name="amount_paid" id="amount_paid"
                                class="form-control form-control-lg shadow-sm" style="border-radius:10px;" min="1"
                                step="0.01" placeholder="Ex: 50000" required>

                            <small class="form-text text-muted">Montant en FCFA</small>
                        </div>

                        <!-- Date du paiement -->
                        <div class="form-group">
                            <label for="payment_date" class="font-weight-semibold" style="color:#4a5568;">
                                <i class="fas fa-calendar-alt mr-1"></i>Date de paiement
                            </label>

                            <input type="date" name="payment_date" id="payment_date"
                                class="form-control form-control-lg shadow-sm" style="border-radius:10px;"
                                value="{{ date('Y-m-d') }}" required>

                            <small class="form-text text-muted">Date d'encaissement</small>
                        </div>

                        <!-- Méthode de paiement -->
                        <div class="form-group">
                            <label for="payment_type" class="font-weight-semibold" style="color:#4a5568;">
                                <i class="fas fa-credit-card mr-1"></i>Méthode de paiement
                            </label>

                            <select class="form-control" id="wallet_id_{{ $invoice->id }}" name="wallet_id" required>
                                <option value="">-- Sélectionnez un wallet --</option>

                                @foreach ($wallets as $wallet)
                                    <option value="{{ $wallet->id }}">
                                        {{ $wallet->name }} ({{ number_format($wallet->current_balance, 0, ',', ' ') }}
                                        FCFA)
                                    </option>
                                @endforeach

                            </select>
                        </div>

                        {{-- <div class="form-group">
                            <label for="payment_type" class="font-weight-semibold" style="color:#4a5568;">
                                <i class="fas fa-credit-card mr-1"></i>Méthode de paiement
                            </label>

                            <input type="text" name="payment_type" id="payment_type"
                                class="form-control form-control-lg shadow-sm" style="border-radius:10px;"
                                placeholder="Ex: Espèces, Wave, Chèque..." required>

                            <small class="form-text text-muted">Tapez la méthode utilisée</small>
                        </div> --}}

                    </div>

                    <!-- FOOTER -->
                    <div class="modal-footer" style="background:#f7f9fc; border-top:none;">
                        <button type="button" class="btn btn-light px-4" data-dismiss="modal"
                            style="border-radius:10px; font-weight:600;">
                            <i class="fas fa-times mr-1"></i>Annuler
                        </button>

                        <button type="submit" class="btn text-white px-4"
                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                                   border-radius:10px; box-shadow:0 5px 15px rgba(102,126,234,0.4); font-weight:600;">
                            <i class="fas fa-check mr-1"></i>Enregistrer
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

@endsection


{{-- JS pour alimenter la modale de confirmation --}}
@push('scripts')
    <script>
        $(function() {
            // Gérer le clic sur les boutons de confirmation
            $('.confirm-action-btn').on('click', function(e) {
                var $button = $(this);

                // Récupérer les données du bouton
                var action = $button.data('action');
                var method = $button.data('method');
                var message = $button.data('message');
                var title = $button.data('title');
                var btnClass = $button.data('btn-class'); // ex: 'btn-danger'

                // Références de la modale
                var $modal = $('#confirmModal');
                var $form = $modal.find('#confirmForm');
                var $confirmBtn = $modal.find('#confirmButton');
                var $header = $modal.find('#confirmModalHeader');

                // Remplir la modale avec les données
                $form.attr('action', action);
                $modal.find('#confirmMethod').val(method);
                $modal.find('#confirmModalBody').text(message);
                $modal.find('#confirmModalLabel').html(title || 'Confirmation'); // Titre avec icône

                // Appliquer la bonne couleur au bouton et à l'en-tête
                $confirmBtn.removeClass('btn-primary btn-success btn-danger btn-warning').addClass(
                    btnClass);
                $header.removeClass('bg-primary bg-success bg-danger bg-warning text-white').addClass(
                    'text-white');

                if (btnClass.includes('danger')) {
                    $header.addClass('bg-danger');
                } else if (btnClass.includes('success')) {
                    $header.addClass('bg-success');
                } else {
                    $header.addClass('bg-primary');
                }
            });
        });
    </script>
@endpush
