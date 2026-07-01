@extends('back.layouts.admin')

@section('content')
    <style>
        /* Style pour l'en-tête amélioré */
        .gap-3 {
            gap: 1rem;
        }

        .input-group-text {
            border-color: #e3e6f0;
        }

        .custom-select:focus,
        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
        }

        .input-group .input-group-text {
            background-color: #fff;
            border-right: none;
        }

        .input-group .form-control,
        .input-group .custom-select {
            border-left: none;
        }

        .input-group .form-control:focus,
        .input-group .custom-select:focus {
            border-color: #e3e6f0;
        }

        /* Responsive adjustments */
        @media (max-width: 991.98px) {
            .gap-3 {
                gap: 0.5rem;
            }
        }
    </style>

    @can('access_dashboard')
        <div class="container-fluid">


            <!-- En-tête avec période -->
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <!-- Titre -->
                                <div class="col-lg-4 mb-3 mb-lg-0">
                                    <h1 class="h3 mb-2 text-gray-800 font-weight-bold">
                                        <i class="fas fa-tachometer-alt text-primary"></i> Tableau de bord
                                    </h1>
                                    <p class="text-muted mb-0 small">Vue d'ensemble de votre activité</p>
                                </div>

                                <!-- Filtres -->
                                <div class="col-lg-8">
                                    <div class="d-flex flex-column flex-lg-row justify-content-lg-end gap-3">
                                        <!-- Sélecteur de période -->
                                        <form method="GET" class="mb-2 mb-lg-0">
                                            <div class="input-group input-group-sm shadow-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white border-right-0">
                                                        <i class="fas fa-calendar-alt text-primary"></i>
                                                    </span>
                                                </div>
                                                <select name="period" class="custom-select custom-select-sm border-left-0"
                                                    style="min-width: 160px;" onchange="this.form.submit()">
                                                    <option value="lastMonth" {{ $period == 'lastMonth' ? 'selected' : '' }}>
                                                        Mois précédent</option>
                                                    <option value="day" {{ $period == 'day' ? 'selected' : '' }}>Aujourd'hui
                                                    </option>
                                                    <option value="week" {{ $period == 'week' ? 'selected' : '' }}>Cette
                                                        semaine</option>
                                                    <option value="month" {{ $period == 'month' ? 'selected' : '' }}>Ce
                                                        mois-ci</option>
                                                    <option value="year" {{ $period == 'year' ? 'selected' : '' }}>Cette
                                                        année</option>
                                                </select>
                                            </div>
                                        </form>

                                        <!-- Séparateur vertical -->
                                        <div class="d-none d-lg-flex align-items-center">
                                            <div class="border-left" style="height: 35px;"></div>
                                        </div>

                                        <!-- Plage de dates personnalisée -->
                                        <form method="GET" class="d-flex align-items-center flex-wrap flex-lg-nowrap">
                                            <div class="input-group input-group-sm shadow-sm mr-2 mb-2 mb-lg-0"
                                                style="min-width: 140px;">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white">
                                                        <i class="fas fa-calendar text-success"></i>
                                                    </span>
                                                </div>
                                                <input type="date" name="start_date" class="form-control form-control-sm"
                                                    placeholder="Du" value="{{ request('start_date') }}">
                                            </div>

                                            <div class="input-group input-group-sm shadow-sm mr-2 mb-2 mb-lg-0"
                                                style="min-width: 140px;">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white">
                                                        <i class="fas fa-calendar text-danger"></i>
                                                    </span>
                                                </div>
                                                <input type="date" name="end_date" class="form-control form-control-sm"
                                                    placeholder="Au" value="{{ request('end_date') }}">
                                            </div>

                                            <button type="submit" class="btn btn-primary btn-sm shadow-sm" title="Filtrer">
                                                <i class="fas fa-search"></i>
                                            </button>

                                            @if (request('start_date') || request('end_date'))
                                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm ml-2"
                                                    title="Réinitialiser">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            @endif

                                            @if (request('period'))
                                                <input type="hidden" name="period" value="{{ request('period') }}">
                                            @endif
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiques principales -->
            <div class="row">
                <!-- Chiffre d'affaires -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100 hover-lift">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="icon-circle bg-gradient-success">
                                    <i class="fas fa-dollar-sign text-white"></i>
                                </div>
                                <span class="badge badge-success badge-pill">
                                    <i class="fas fa-arrow-up"></i> Actif
                                </span>
                            </div>
                            <h6 class="text-muted text-uppercase font-weight-bold small mb-2">Chiffre d'affaires</h6>
                            <h3 class="font-weight-bold text-dark mb-0">
                                {{ number_format($stats['invoices']->total_ventes ?? 0, 0, ',', ' ') }} <small
                                    class="text-muted">FCFA</small>
                            </h3>
                            <p class="text-muted small mb-0 mt-2">
                                <i class="fas fa-shopping-cart mr-1"></i>
                                {{ number_format($stats['invoices']->nb_factures_clients ?? 0) }} ventes
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Bénéfice -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100 hover-lift">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="icon-circle bg-gradient-primary">
                                    <i class="fas fa-chart-line text-white"></i>
                                </div>
                                <span class="badge badge-primary badge-pill">Brut</span>
                            </div>
                            <h6 class="text-muted text-uppercase font-weight-bold small mb-2">Bénéfice</h6>
                            <h3 class="font-weight-bold text-dark mb-0">
                                {{ number_format($stats['benefice'], 0, ',', ' ') }} <small class="text-muted">FCFA</small>
                            </h3>
                            <p class="text-muted small mb-0 mt-2">
                                <i class="fas fa-percentage mr-1"></i>
                                Marge brute
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Bénéfice net -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100 hover-lift">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="icon-circle bg-gradient-info">
                                    <i class="fas fa-hand-holding-usd text-white"></i>
                                </div>
                                <span class="badge badge-info badge-pill">Net</span>
                            </div>
                            <h6 class="text-muted text-uppercase font-weight-bold small mb-2">Bénéfice net</h6>
                            <h3 class="font-weight-bold text-dark mb-0">
                                {{ number_format($stats['benefice'] - $stats['depenses'], 0, ',', ' ') }} <small
                                    class="text-muted">FCFA</small>
                            </h3>
                            <p class="text-muted small mb-0 mt-2">
                                <i class="fas fa-calculator mr-1"></i>
                                Après dépenses
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Dépenses -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100 hover-lift">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="icon-circle bg-gradient-danger">
                                    <i class="fas fa-wallet text-white"></i>
                                </div>
                                <span class="badge badge-danger badge-pill">
                                    <i class="fas fa-arrow-down"></i>
                                </span>
                            </div>
                            <h6 class="text-muted text-uppercase font-weight-bold small mb-2">Dépenses</h6>
                            <h3 class="font-weight-bold text-dark mb-0">
                                {{ number_format($stats['depenses'], 0, ',', ' ') }} <small class="text-muted">FCFA</small>
                            </h3>
                            <p class="text-muted small mb-0 mt-2">
                                <i class="fas fa-receipt mr-1"></i>
                                Frais d'exploitation
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Solde wallet -->
                {{-- <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100 hover-lift">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="icon-circle bg-gradient-warning">
                                    <i class="fas fa-coins text-white"></i>
                                </div>
                                <span class="badge badge-warning badge-pill">
                                    <i class="fas fa-arrow-down"></i>
                                </span>
                            </div>
                            <h6 class="text-muted text-uppercase font-weight-bold small mb-2">Solde </h6>
                            <h3 class="font-weight-bold text-dark mb-0">
                                {{ number_format($stats['depenses'], 0, ',', ' ') }} <small class="text-muted">FCFA</small>
                            </h3>
                            <p class="text-muted small mb-0 mt-2">
                                <i class="fas fa-receipt mr-1"></i>
                                Frais d'exploitation
                            </p>
                        </div>
                    </div>
                </div> --}}
            </div>

            <!-- Graphiques et Balances -->
            <div class="row">
                <!-- Graphique principal -->
                <div class="col-lg-8 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="m-0 font-weight-bold text-primary">Évolution financière</h6>
                                    <small class="text-muted">Ventes, bénéfices et dépenses</small>
                                </div>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary active" data-chart="bar">
                                        <i class="fas fa-chart-bar"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" data-chart="line">
                                        <i class="fas fa-chart-line"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" data-chart="pie">
                                        <i class="fas fa-chart-pie"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="salesChart" height="80"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Balances et stats -->
                <div class="col-lg-4 mb-4">
                    <!-- Balance Clients -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle-sm bg-gradient-success mr-3">
                                    <i class="fas fa-user-friends text-white"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="text-muted small mb-1">Balance Clients</h6>
                                    <h5 class="font-weight-bold mb-0">
                                        {{ number_format($stats['balance_clients'] ?? 0, 0, ',', ' ') }} FCFA
                                    </h5>
                                </div>
                            </div>
                            <div class="progress mt-3" style="height: 5px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 70%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Balance Fournisseurs -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle-sm bg-gradient-danger mr-3">
                                    <i class="fas fa-user-tie text-white"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="text-muted small mb-1">Balance Fournisseurs</h6>
                                    <h5 class="font-weight-bold mb-0">
                                        {{ number_format($stats['balance_fournisseurs'], 0, ',', ' ') }} FCFA
                                    </h5>
                                </div>
                            </div>
                            <div class="progress mt-3" style="height: 5px;">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: 45%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats rapides -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="font-weight-bold mb-3">
                                <i class="fas fa-chart-pie text-primary mr-2"></i>Statistiques
                            </h6>

                            {{-- Clients --}}
                            <div class="stat-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">
                                        <i class="fas fa-user-friends text-primary mr-2"></i>Clients
                                    </span>
                                    <span class="font-weight-bold">{{ $stats['counts']->nb_clients }}</span>
                                </div>
                            </div>

                            {{-- Fournisseurs --}}
                            <div class="stat-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">
                                        <i class="fas fa-truck-loading text-warning mr-2"></i>Fournisseurs
                                    </span>
                                    <span class="font-weight-bold">{{ $stats['counts']->nb_fournisseurs }}</span>
                                </div>
                            </div>

                            {{-- Produits --}}
                            <div class="stat-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">
                                        <i class="fas fa-cubes text-success mr-2"></i>Produits
                                    </span>
                                    <span class="font-weight-bold">{{ $stats['nbProduits'] }}</span>
                                </div>
                            </div>

                            {{-- Entrepôts --}}
                            <div class="stat-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">
                                        <i class="fas fa-warehouse text-info mr-2"></i>Entrepôts
                                    </span>
                                    <span class="font-weight-bold">{{ $stats['nbEntrepots'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tableaux des transactions -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-file-invoice-dollar mr-2"></i>Transactions récentes
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <!-- Tabs Navigation -->
                            <ul class="nav nav-tabs nav-fill border-0 px-3 pt-3" id="transactionTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="factures-clients-tab" data-toggle="tab"
                                        href="#factures-clients" role="tab">
                                        <i class="fas fa-receipt mr-2"></i>Factures Clients
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="factures-fournisseurs-tab" data-toggle="tab"
                                        href="#factures-fournisseurs" role="tab">
                                        <i class="fas fa-file-invoice mr-2"></i>Factures Fournisseurs
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="paiements-clients-tab" data-toggle="tab"
                                        href="#paiements-clients" role="tab">
                                        <i class="fas fa-money-bill-wave mr-2"></i>Paiements Clients
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="paiements-fournisseurs-tab" data-toggle="tab"
                                        href="#paiements-fournisseurs" role="tab">
                                        <i class="fas fa-hand-holding-usd mr-2"></i>Paiements Fournisseurs
                                    </a>
                                </li>
                            </ul>

                            <!-- Tabs Content -->
                            <div class="tab-content p-3" id="transactionTabsContent">
                                <!-- Factures Clients -->
                                <div class="tab-pane fade show active" id="factures-clients" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="border-0">N° Facture</th>
                                                    <th class="border-0">Client</th>
                                                    <th class="border-0">Date</th>
                                                    <th class="border-0 text-right">Montant</th>
                                                    <th class="border-0 text-center">Statut</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($dernieresFactures->where('contact_type', 'client') as $f)
                                                    <tr>
                                                        <td class="font-weight-bold text-primary">
                                                            <a href="{{ route('invoices.show', ['client' . 's', $f->id]) }}">
                                                                #{{ $f->invoice_number }}
                                                            </a>

                                                        </td>
                                                        <td>
                                                            <i class="fas fa-user-circle text-muted mr-2"></i>
                                                            <a href="{{ route('clients.show', $f->contact_id) }}">
                                                                {{ $f->client }}
                                                            </a>
                                                        </td>
                                                        <td class="text-muted">
                                                            {{ \Carbon\Carbon::parse($f->invoice_date)->format('d/m/Y') }}</td>
                                                        <td class="text-right font-weight-bold">
                                                            {{ number_format($f->total_invoice, 0, ',', ' ') }} FCFA</td>
                                                        <td class="text-center">
                                                            @php
                                                                $statusConfig = match ($f->status) {
                                                                    'paid' => [
                                                                        'class' => 'success',
                                                                        'icon' => 'check-circle',
                                                                        'label' => 'Payée',
                                                                    ],
                                                                    'partial' => [
                                                                        'class' => 'warning',
                                                                        'icon' => 'clock',
                                                                        'label' => 'Partiellement payé',
                                                                    ],
                                                                    'draft' => [
                                                                        'class' => 'secondary',
                                                                        'icon' => 'edit',
                                                                        'label' => 'Brouillon',
                                                                    ],
                                                                    'cancelled' => [
                                                                        'class' => 'danger',
                                                                        'icon' => 'times-circle',
                                                                        'label' => 'Annulée',
                                                                    ],
                                                                    default => [
                                                                        'class' => 'info',
                                                                        'icon' => 'info-circle',
                                                                        'label' => 'Validée(non payéé)',
                                                                    ],
                                                                };
                                                            @endphp
                                                            <span class="badge badge-{{ $statusConfig['class'] }} badge-pill">
                                                                <i
                                                                    class="fas fa-{{ $statusConfig['icon'] }} mr-1"></i>{{ $statusConfig['label'] }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Factures Fournisseurs -->
                                <div class="tab-pane fade" id="factures-fournisseurs" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="border-0">N° Facture</th>
                                                    <th class="border-0">Fournisseur</th>
                                                    <th class="border-0">Date</th>
                                                    <th class="border-0 text-right">Montant</th>
                                                    <th class="border-0 text-center">Statut</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($dernieresFactures->where('contact_type', 'supplier') as $f)
                                                    <tr>

                                                        <td class="font-weight-bold text-secondary">
                                                            <a
                                                                href="{{ route('invoices.show', ['supplier' . 's', $f->id]) }}">
                                                                #{{ $f->invoice_number }}
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <i class="fas fa-building text-muted mr-2"></i>
                                                            <a href="{{ route('suppliers.show', $f->contact_id) }}">
                                                                {{ $f->client }}
                                                            </a>
                                                        </td>
                                                        <td class="text-muted">
                                                            {{ \Carbon\Carbon::parse($f->invoice_date)->format('d/m/Y') }}</td>
                                                        <td class="text-right font-weight-bold">
                                                            {{ number_format($f->total_invoice, 0, ',', ' ') }} FCFA</td>
                                                        <td class="text-center">
                                                            @php
                                                                $statusConfig = match ($f->status) {
                                                                    'paid' => [
                                                                        'class' => 'success',
                                                                        'icon' => 'check-circle',
                                                                        'label' => 'Payée',
                                                                    ],
                                                                    'partial' => [
                                                                        'class' => 'warning',
                                                                        'icon' => 'clock',
                                                                        'label' => 'Partiellement payé',
                                                                    ],
                                                                    'draft' => [
                                                                        'class' => 'secondary',
                                                                        'icon' => 'edit',
                                                                        'label' => 'Brouillon',
                                                                    ],
                                                                    'cancelled' => [
                                                                        'class' => 'danger',
                                                                        'icon' => 'times-circle',
                                                                        'label' => 'Annulée',
                                                                    ],
                                                                    default => [
                                                                        'class' => 'info',
                                                                        'icon' => 'info-circle',
                                                                        'label' => 'Validée(non payée)',
                                                                    ],
                                                                };
                                                            @endphp
                                                            <span class="badge badge-{{ $statusConfig['class'] }} badge-pill">
                                                                <i
                                                                    class="fas fa-{{ $statusConfig['icon'] }} mr-1"></i>{{ $statusConfig['label'] }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Paiements Clients -->
                                <div class="tab-pane fade" id="paiements-clients" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="border-0">Client</th>
                                                    <th class="border-0">Date</th>
                                                    <th class="border-0 text-right">Montant</th>
                                                    <th class="border-0 text-center">Type</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($derniersPaiements->where('contact_type', 'client') as $p)
                                                    <tr>
                                                        <td>
                                                            <i class="fas fa-user-circle text-success mr-2"></i>
                                                            <a href="{{ route('clients.show', $p->contact_id) }}">
                                                                {{ $p->client }}
                                                            </a>
                                                        </td>
                                                        <td class="text-muted">
                                                            {{ \Carbon\Carbon::parse($p->payment_date)->format('d/m/Y') }}</td>
                                                        <td class="text-right">
                                                            <span class="font-weight-bold text-success">
                                                                +{{ number_format($p->amount_paid, 0, ',', ' ') }} FCFA
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-success badge-pill">
                                                                <i class="fas fa-arrow-down mr-1"></i>Reçu
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Paiements Fournisseurs -->
                                <div class="tab-pane fade" id="paiements-fournisseurs" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="border-0">Fournisseur</th>
                                                    <th class="border-0">Date</th>
                                                    <th class="border-0 text-right">Montant</th>
                                                    <th class="border-0 text-center">Type</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($derniersPaiements->where('contact_type', 'supplier') as $p)
                                                    <tr>
                                                        <td>
                                                            <i class="fas fa-building text-danger mr-2"></i>
                                                            <a href="{{ route('suppliers.show', $p->contact_id) }}">
                                                                {{ $p->client }}
                                                            </a>
                                                        </td>
                                                        <td class="text-muted">
                                                            {{ \Carbon\Carbon::parse($p->payment_date)->format('d/m/Y') }}</td>
                                                        <td class="text-right">
                                                            <span class="font-weight-bold text-danger">
                                                                -{{ number_format($p->amount_paid, 0, ',', ' ') }} FCFA
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-danger badge-pill">
                                                                <i class="fas fa-arrow-up mr-1"></i>Envoyé
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    @endcan


    <style>
        /* Custom Styles */
        .hover-lift {
            transition: all 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
        }

        .icon-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-circle-sm {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .bg-gradient-success {
            background: linear-gradient(135deg, #475569 0%, #334155 100%);
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #224abe 100%);
        }

        .bg-gradient-info {
            background: linear-gradient(135deg, #475569 0%, #334155 100%);
        }

        .bg-gradient-danger {
            background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);
        }

        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link:hover {
            color: #4f46e5;
            background: #f8f9fc;
        }

        .nav-tabs .nav-link.active {
            color: #4f46e5;
            background: #f8f9fc;
            border-bottom: 3px solid #4f46e5;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: #f8f9fc;
            transform: scale(1.01);
        }

        .badge-pill {
            padding: 0.4rem 0.8rem;
            font-weight: 500;
        }

        .stat-item {
            border-bottom: 1px solid #e3e6f0;
        }

        .stat-item:last-child {
            border-bottom: none;
        }

        .card {
            border-radius: 0.5rem;
        }

        .btn-group .btn {
            border-radius: 0.25rem !important;
        }
    </style>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let salesChart;
        const ctx = document.getElementById('salesChart').getContext('2d');

        const chartData = {
            labels: ['Ventes', 'Bénéfice', 'Dépenses'],
            datasets: [{
                label: 'Montants (FCFA)',
                data: [
                    {{ $stats['invoices']->total_ventes ?? 0 }},
                    {{ $stats['benefice'] ?? 0 }},
                    {{ $stats['depenses'] ?? 0 }}
                ],
                backgroundColor: [
                    'rgba(28, 200, 138, 0.8)',
                    'rgba(79, 70, 229, 0.8)',
                    'rgba(231, 74, 59, 0.8)'
                ],
                borderColor: [
                    'rgba(28, 200, 138, 1)',
                    'rgba(79, 70, 229, 1)',
                    'rgba(231, 74, 59, 1)'
                ],
                borderWidth: 2
            }]
        };

        function createChart(type) {
            if (salesChart) {
                salesChart.destroy();
            }

            const config = {
                type: type,
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: type === 'pie',
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + context.raw.toLocaleString() + ' FCFA';
                                }
                            }
                        }
                    },
                    scales: type !== 'pie' ? {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString() + ' FCFA';
                                }
                            }
                        }
                    } : {}
                }
            };

            salesChart = new Chart(ctx, config);
        }

        // Initialiser avec un graphique en barres
        createChart('bar');

        // Gérer les changements de type de graphique
        document.querySelectorAll('[data-chart]').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('[data-chart]').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                createChart(this.dataset.chart);
            });
        });
    </script>
@endpush
