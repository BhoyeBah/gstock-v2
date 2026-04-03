@extends('back.layouts.admin')

@section('content')
<div class="container-fluid">

    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 font-weight-bold">Détails de l'employé</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('employes.index') }}">Employés</a></li>
                    <li class="breadcrumb-item active">{{ $employe->full_name }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('employes.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <button class="btn btn-primary ml-2" data-toggle="modal" data-target="#paymentModal">
                <i class="fas fa-money-check-alt"></i> Nouveau paiement
            </button>
        </div>
    </div>

    <!-- Carte employé -->
    <div class="card shadow-lg border-0 mb-4">
        <div class="card-body p-0">
            <div class="bg-gradient-primary text-white p-4 rounded-top">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-1 font-weight-bold">{{ $employe->full_name }}</h3>
                        <p class="mb-1 opacity-90">
                            <i class="fas fa-briefcase mr-2"></i>{{ $employe->position }}
                        </p>
                        <p class="mb-0 opacity-90">
                            <i class="fas fa-id-badge mr-2"></i>Matricule: {{ $employe->matricule }}
                        </p>
                    </div>
                    <div class="col-auto text-right">
                        <a href="{{ route('employes.edit', $employe) }}" class="btn btn-light btn-sm mb-2">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <br>
                        <button class="btn btn-outline-light btn-sm" onclick="window.print()">
                            <i class="fas fa-file-pdf"></i> Générer fiche
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        use Carbon\Carbon;

        $typeLabels = [
            'salary_payment' => 'Paiement de salaire',
            'advance' => 'Avance',
            'advance_repayment' => 'Remboursement',
            'bonus' => 'Prime',
            'deduction' => 'Déduction'
        ];

        // Avances nettes = avances - remboursements
        $avancesNettes = (int) ($avances ?? 0) - (int) ($remboursements ?? 0);

        // Total payé = paiements de salaire uniquement
        $totalPaye = (int) ($paymentSalary ?? 0);

        // Détection filtre date
        $hasDateFilter = !empty($startDate) && !empty($endDate);

        // Nombre de mois
        $nombreMois = 1;
        if ($hasDateFilter) {
            $start = Carbon::parse($startDate)->startOfMonth();
            $end = Carbon::parse($endDate)->startOfMonth();

            if ($end->lt($start)) {
                [$start, $end] = [$end, $start];
            }

            $nombreMois = $start->diffInMonths($end) + 1;
        }

        // Salaire dû
        $salaireDu = (int) ($salaireBase ?? 0) * $nombreMois;

        // Gains totaux = salaire dû + primes
        $totalGains = $salaireDu + (int) ($primes ?? 0);

        // Reste à payer = gains - paiements - avances nettes - déductions
        $resteAPayer = $totalGains - $totalPaye - $avancesNettes - (int) ($deductions ?? 0);

        // Solde des transactions
        $soldeTransactions = (int) ($primes ?? 0)
            - (int) ($avances ?? 0)
            + (int) ($remboursements ?? 0)
            - (int) ($deductions ?? 0)
            - (int) ($paymentSalary ?? 0);

        $soldeColor = $soldeTransactions >= 0 ? 'success' : 'danger';
        $resteAPayerColor = $resteAPayer > 0 ? 'warning' : 'success';

        $pourcentagePaye = $totalGains > 0 ? ($totalPaye / $totalGains) * 100 : 0;
        $pourcentageRestant = $totalGains > 0 ? ($resteAPayer / $totalGains) * 100 : 0;
    @endphp

    <!-- Filtres actifs -->
    @if($startDate && $endDate)
        <div class="alert alert-info alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="fas fa-filter mr-3 fa-2x"></i>
            <div class="flex-grow-1">
                <strong>Filtre actif :</strong>
                <span class="badge badge-primary ml-2 px-3 py-2" style="font-size: 0.9rem;">
                    <i class="fas fa-calendar-range mr-1"></i>
                    Du {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                </span>
                @if($filterType)
                    <span class="badge badge-secondary ml-2 px-3 py-2" style="font-size: 0.9rem;">
                        <i class="fas fa-tag mr-1"></i>
                        {{ $typeLabels[$filterType] ?? $filterType }}
                    </span>
                @endif
            </div>
            <a href="{{ route('employes.show', $employe) }}" class="btn btn-sm btn-outline-danger ml-3">
                <i class="fas fa-times mr-1"></i> Retirer le filtre
            </a>
            <button class="btn btn-sm btn-outline-secondary ml-2" data-toggle="modal" data-target="#filterModal">
                <i class="fas fa-edit"></i> Modifier
            </button>
        </div>
    @elseif($filterType)
        <div class="alert alert-info alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="fas fa-filter mr-3 fa-2x"></i>
            <div class="flex-grow-1">
                <strong>Filtre actif :</strong>
                <span class="badge badge-secondary ml-2 px-3 py-2" style="font-size: 0.9rem;">
                    <i class="fas fa-tag mr-1"></i>
                    {{ $typeLabels[$filterType] ?? $filterType }}
                </span>
            </div>
            <a href="{{ route('employes.show', $employe) }}" class="btn btn-sm btn-outline-danger ml-3">
                <i class="fas fa-times mr-1"></i> Retirer le filtre
            </a>
            <button class="btn btn-sm btn-outline-secondary ml-2" data-toggle="modal" data-target="#filterModal">
                <i class="fas fa-edit"></i> Modifier
            </button>
        </div>
    @elseif($showAll)
        <div class="alert alert-secondary alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="fas fa-infinity mr-3 fa-2x"></i>
            <div class="flex-grow-1">
                <strong>Affichage :</strong>
                <span class="badge badge-secondary ml-2 px-3 py-2" style="font-size: 0.9rem;">
                    <i class="fas fa-history mr-1"></i>
                    Toutes les périodes
                </span>
            </div>
            <a href="{{ route('employes.show', $employe) }}" class="btn btn-sm btn-outline-primary ml-3">
                <i class="fas fa-calendar-day"></i> Vue normale
            </a>
        </div>
    @else
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="fas fa-check-circle mr-3 fa-2x"></i>
            <div class="flex-grow-1">
                <strong>Vue par défaut :</strong>
                <span class="badge badge-success ml-2 px-3 py-2" style="font-size: 0.9rem;">
                    <i class="fas fa-user-clock mr-1"></i>
                    Toutes les transactions depuis la création ({{ $employe->created_at->format('d/m/Y') }})
                </span>
            </div>
            <button class="btn btn-sm btn-outline-primary ml-3" data-toggle="modal" data-target="#filterModal">
                <i class="fas fa-filter"></i> Appliquer un filtre
            </button>
        </div>
    @endif

    <!-- Statistiques - Première ligne -->
    <div class="row mb-4">

        <!-- Salaire de base / Salaire dû -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 card-hover">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="icon-circle-lg bg-primary-soft text-primary">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>

                        @if($hasDateFilter)
                            <span class="badge badge-info">{{ $nombreMois }} mois</span>
                        @else
                            <span class="badge badge-light">Mensuel</span>
                        @endif
                    </div>

                    <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                        @if($hasDateFilter)
                            Salaire dû (période)
                        @else
                            Salaire de base
                        @endif
                    </h6>

                    <h3 class="mb-0 font-weight-bold text-gray-800">
                        {{ number_format($salaireDu, 0, ',', ' ') }}
                        <small class="text-muted">FCFA</small>
                    </h3>

                    @if($hasDateFilter)
                        <small class="text-muted">
                            Base: {{ number_format($salaireBase, 0, ',', ' ') }} × {{ $nombreMois }}
                        </small>
                    @endif
                </div>
            </div>
        </div>

        <!-- Primes -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 card-hover">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="icon-circle-lg bg-success-soft text-success">
                            <i class="fas fa-gift"></i>
                        </div>
                        <span class="badge badge-success-soft">
                            @if((int)$primes > 0)
                                +{{ number_format($primes / 1000, 0) }}K
                            @else
                                0
                            @endif
                        </span>
                    </div>
                    <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                        Primes
                    </h6>
                    <h3 class="mb-0 font-weight-bold text-success">
                        {{ number_format($primes, 0, ',', ' ') }}
                        <small class="text-muted">FCFA</small>
                    </h3>
                    <small class="text-muted">
                        @if($hasDateFilter)
                            Période filtrée
                        @else
                            Total cumulé
                        @endif
                    </small>
                </div>
            </div>
        </div>

        <!-- Avances nettes -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 card-hover">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="icon-circle-lg bg-warning-soft text-warning">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <span class="badge badge-warning-soft">
                            @if($avancesNettes > 0)
                                -{{ number_format($avancesNettes / 1000, 0) }}K
                            @else
                                0
                            @endif
                        </span>
                    </div>
                    <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                        Avances nettes
                    </h6>
                    <h3 class="mb-0 font-weight-bold text-warning">
                        {{ number_format($avancesNettes, 0, ',', ' ') }}
                        <small class="text-muted">FCFA</small>
                    </h3>
                    <small class="text-muted">
                        @if($hasDateFilter)
                            Période filtrée
                        @else
                            En cours
                        @endif
                    </small>
                </div>
            </div>
        </div>

        <!-- Déductions -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 card-hover">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="icon-circle-lg bg-danger-soft text-danger">
                            <i class="fas fa-minus-circle"></i>
                        </div>
                        <span class="badge badge-danger-soft">
                            @if((int)$deductions > 0)
                                -{{ number_format($deductions / 1000, 0) }}K
                            @else
                                0
                            @endif
                        </span>
                    </div>
                    <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                        Déductions
                    </h6>
                    <h3 class="mb-0 font-weight-bold text-danger">
                        {{ number_format($deductions, 0, ',', ' ') }}
                        <small class="text-muted">FCFA</small>
                    </h3>
                    <small class="text-muted">
                        @if($hasDateFilter)
                            Période filtrée
                        @else
                            Total cumulé
                        @endif
                    </small>
                </div>
            </div>
        </div>

    </div>

    <!-- Statistiques - Deuxième ligne -->
    <div class="row mb-4">

        <!-- Total payé -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-0 shadow-lg h-100 card-hover border-left-success"
                 style="border-left: 5px solid #1cc88a !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="icon-circle-lg bg-success-soft text-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-success px-3 py-2" style="font-size: 0.9rem;">
                                {{ number_format($pourcentagePaye, 1) }}% payé
                            </span>
                        </div>
                    </div>
                    <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                        <i class="fas fa-hand-holding-usd mr-2"></i>Total payé
                    </h6>
                    <h2 class="mb-2 font-weight-bold text-success">
                        {{ number_format($totalPaye, 0, ',', ' ') }}
                        <small class="text-muted">FCFA</small>
                    </h2>
                    <div class="progress mb-2" style="height: 6px;">
                        <div class="progress-bar bg-success" role="progressbar"
                             style="width: {{ min($pourcentagePaye, 100) }}%"
                             aria-valuenow="{{ $pourcentagePaye }}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        Paiements salaire : {{ number_format($paymentSalary, 0, ',', ' ') }} FCFA
                        @if($primes > 0)
                            <br>Primes ajoutées au dû : {{ number_format($primes, 0, ',', ' ') }} FCFA
                        @endif
                        <br><strong>Total des gains : {{ number_format($totalGains, 0, ',', ' ') }} FCFA</strong>
                    </small>
                </div>
            </div>
        </div>

        <!-- Reste à payer / Excédent / Soldé -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-0 shadow-lg h-100 card-hover border-left-{{ $resteAPayerColor }}"
                 style="border-left: 5px solid {{ $resteAPayer > 0 ? '#f6c23e' : '#1cc88a' }} !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="icon-circle-lg bg-{{ $resteAPayerColor }}-soft text-{{ $resteAPayerColor }}">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="text-right">
                            @if($resteAPayer > 0)
                                <span class="badge badge-warning px-3 py-2" style="font-size: 0.9rem;">
                                    {{ number_format(abs($pourcentageRestant), 1) }}% restant
                                </span>
                            @elseif($resteAPayer < 0)
                                <span class="badge badge-success px-3 py-2" style="font-size: 0.9rem;">
                                    <i class="fas fa-check mr-1"></i> Payé en excédent
                                </span>
                            @else
                                <span class="badge badge-success px-3 py-2" style="font-size: 0.9rem;">
                                    <i class="fas fa-check mr-1"></i> Soldé
                                </span>
                            @endif
                        </div>
                    </div>

                    <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                        <i class="fas fa-hourglass-half mr-2"></i>
                        @if($resteAPayer > 0)
                            Reste à payer
                        @elseif($resteAPayer < 0)
                            Excédent payé
                        @else
                            Solde
                        @endif
                    </h6>

                    <h2 class="mb-2 font-weight-bold text-{{ $resteAPayerColor }}">
                        @if($resteAPayer > 0)
                            {{ number_format($resteAPayer, 0, ',', ' ') }}
                            <small class="text-muted">FCFA</small>
                        @elseif($resteAPayer < 0)
                            {{ number_format(abs($resteAPayer), 0, ',', ' ') }}
                            <small class="text-muted">FCFA</small>
                        @else
                            0 <small class="text-muted">FCFA</small>
                        @endif
                    </h2>

                    <div class="progress mb-2" style="height: 6px;">
                        @if($resteAPayer > 0)
                            <div class="progress-bar bg-warning" role="progressbar"
                                 style="width: {{ min(abs($pourcentageRestant), 100) }}%"
                                 aria-valuenow="{{ abs($pourcentageRestant) }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        @else
                            <div class="progress-bar bg-success" role="progressbar"
                                 style="width: 100%"
                                 aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        @endif
                    </div>

                    <small class="text-muted">
                        <i class="fas fa-calculator mr-1"></i>
                        @if($hasDateFilter)
                            Salaire dû ({{ $nombreMois }} mois × {{ number_format($salaireBase, 0, ',', ' ') }})
                            - Total payé
                            - Avances nettes
                            - Déductions
                        @else
                            Salaire de base ({{ number_format($salaireBase, 0, ',', ' ') }})
                            - Total payé ({{ number_format($totalPaye, 0, ',', ' ') }})
                            - Avances nettes
                            - Déductions
                        @endif
                    </small>
                </div>
            </div>
        </div>

    </div>

    <!-- Transactions -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1 font-weight-bold">
                        <i class="fas fa-history text-primary mr-2"></i>Historique des transactions
                    </h5>
                    <small class="text-muted">
                        @if(!$showAll && $startDate && $endDate)
                            Transactions du {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                        @elseif(!$showAll)
                            Toutes les transactions depuis création ({{ $employe->created_at->format('d/m/Y') }})
                        @else
                            Toutes les opérations enregistrées
                        @endif
                    </small>
                </div>
                <div>
                    <button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#filterModal">
                        <i class="fas fa-filter"></i> Filtrer
                    </button>
                    <button class="btn btn-sm btn-outline-success ml-2" onclick="exportTransactions()">
                        <i class="fas fa-download"></i> Exporter
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            @if($transactions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 px-4 py-3">Date</th>
                                <th class="border-0 py-3">Type</th>
                                <th class="border-0 py-3">Description</th>
                                <th class="border-0 py-3 text-right">Montant</th>
                                <th class="border-0 py-3 text-center" style="width: 100px;">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                        @foreach($transactions as $transaction)
                            <tr>
                                <td class="px-4 py-3">
                                    <i class="fas fa-calendar-day text-muted mr-2"></i>
                                    <strong>{{ \Carbon\Carbon::parse($transaction->date ?? $transaction->created_at)->format('d/m/Y') }}</strong>
                                </td>

                                <td class="py-3">
                                    @switch($transaction->type)
                                        @case('bonus')
                                            <span class="badge badge-success-soft px-3 py-2">
                                                <i class="fas fa-gift"></i> Prime
                                            </span>
                                            @break

                                        @case('advance')
                                            <span class="badge badge-warning-soft px-3 py-2">
                                                <i class="fas fa-hand-holding-usd"></i> Avance
                                            </span>
                                            @break

                                        @case('advance_repayment')
                                            <span class="badge badge-info-soft px-3 py-2">
                                                <i class="fas fa-undo"></i> Remboursement
                                            </span>
                                            @break

                                        @case('deduction')
                                            <span class="badge badge-danger-soft px-3 py-2">
                                                <i class="fas fa-minus-circle"></i> Déduction
                                            </span>
                                            @break

                                        @case('salary_payment')
                                            <span class="badge badge-primary-soft px-3 py-2">
                                                <i class="fas fa-money-bill"></i> Paiement salaire
                                            </span>
                                            @break

                                        @default
                                            <span class="badge badge-light px-3 py-2">
                                                <i class="fas fa-question"></i> Autre
                                            </span>
                                    @endswitch
                                </td>

                                <td class="py-3">
                                    {{ $transaction->note ?? 'Aucune description' }}
                                    @if($transaction->reference)
                                        <br><small class="text-muted">Réf: {{ $transaction->reference }}</small>
                                    @endif
                                </td>

                                <td class="py-3 text-right">
                                    @php
                                        $isPositive = in_array($transaction->type, ['bonus', 'advance_repayment']);
                                        $sign = $isPositive ? '+' : '-';

                                        $colorClass = match($transaction->type) {
                                            'bonus' => 'success',
                                            'advance_repayment' => 'info',
                                            'advance' => 'warning',
                                            'deduction' => 'danger',
                                            'salary_payment' => 'primary',
                                            default => 'secondary'
                                        };
                                    @endphp

                                    <strong class="text-{{ $colorClass }}">
                                        {{ $sign }}{{ number_format($transaction->amount, 0, ',', ' ') }} FCFA
                                    </strong>
                                </td>

                                <td class="py-3 text-center">
                                    <form action="#" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>

                    </table>
                </div>

                <div class="bg-light border-top px-4 py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <small class="text-muted">
                                {{ $transactions->count() }} transaction(s)
                                @if(!$showAll && $startDate && $endDate)
                                    du {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                                @elseif(!$showAll)
                                    depuis création
                                @else
                                    au total
                                @endif
                            </small>
                        </div>
                        <div class="col-md-6 text-right">
                            <span class="text-muted mr-3">Solde des transactions:</span>
                            <h5 class="d-inline-block mb-0 font-weight-bold text-{{ $soldeColor }}">
                                {{ $soldeTransactions >= 0 ? '+' : '-' }}{{ number_format(abs($soldeTransactions), 0, ',', ' ') }} FCFA
                            </h5>
                        </div>
                    </div>
                </div>

            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucune transaction enregistrée</h5>
                    <p class="text-muted">
                        @if(!$showAll && ($filterType || ($startDate && $endDate)))
                            Aucune transaction ne correspond à vos critères de recherche.
                        @else
                            Cliquez sur "Nouveau paiement" pour ajouter une transaction
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>

</div>

<!-- Modal Filtre -->
<div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-primary text-white border-0">
                <h5 class="modal-title font-weight-bold" id="filterModalLabel">
                    <i class="fas fa-filter mr-2"></i>Filtrer les transactions
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="{{ route('employes.show', $employe) }}" method="GET" id="filterForm">
                <div class="modal-body p-4">

                    <div class="form-group mb-4">
                        <label class="font-weight-bold mb-3">
                            <i class="fas fa-calendar-alt text-primary mr-2"></i>Période de filtrage
                        </label>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">
                                    <i class="fas fa-calendar-check text-success mr-2"></i>Date de début
                                </label>
                                <input type="date" class="form-control" id="start_date" name="start_date"
                                       value="{{ $startDate ?? '' }}"
                                       min="{{ $employe->created_at->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">
                                    <i class="fas fa-calendar-times text-danger mr-2"></i>Date de fin
                                </label>
                                <input type="date" class="form-control" id="end_date" name="end_date"
                                       value="{{ $endDate ?? '' }}">
                            </div>
                        </div>

                        <small class="text-muted d-block mb-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            Employé créé le {{ $employe->created_at->format('d/m/Y') }}. Laissez vide pour voir toutes les transactions.
                        </small>
                    </div>

                    <div class="mb-4">
                        <label class="small text-muted mb-2">
                            <i class="fas fa-clock mr-1"></i>Raccourcis rapides
                        </label>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary period-shortcut mr-2 mb-2" data-period="today">
                                <i class="fas fa-calendar-day mr-1"></i> Aujourd'hui
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary period-shortcut mr-2 mb-2" data-period="this_week">
                                <i class="fas fa-calendar-week mr-1"></i> Cette semaine
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary period-shortcut mr-2 mb-2" data-period="this_month">
                                <i class="fas fa-calendar mr-1"></i> Ce mois
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary period-shortcut mr-2 mb-2" data-period="last_month">
                                <i class="fas fa-calendar-minus mr-1"></i> Mois dernier
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary period-shortcut mr-2 mb-2" data-period="last_3_months">
                                <i class="fas fa-calendar-alt mr-1"></i> 3 derniers mois
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary period-shortcut mr-2 mb-2" data-period="this_year">
                                <i class="fas fa-calendar-check mr-1"></i> Cette année
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-info period-shortcut mb-2" data-period="since_creation">
                                <i class="fas fa-user-clock mr-1"></i> Depuis création
                            </button>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="form-group mb-4">
                        <label for="filter_type" class="font-weight-bold mb-3">
                            <i class="fas fa-exchange-alt text-primary mr-2"></i>Type de transaction
                        </label>
                        <select class="form-control" id="filter_type" name="filter_type">
                            <option value="">-- Tous les types --</option>
                            <option value="salary_payment" {{ $filterType == 'salary_payment' ? 'selected' : '' }}>
                                💰 Paiement de salaire
                            </option>
                            <option value="advance" {{ $filterType == 'advance' ? 'selected' : '' }}>
                                ⚡ Avance sur salaire
                            </option>
                            <option value="advance_repayment" {{ $filterType == 'advance_repayment' ? 'selected' : '' }}>
                                🔄 Remboursement d'avance
                            </option>
                            <option value="bonus" {{ $filterType == 'bonus' ? 'selected' : '' }}>
                                🎁 Prime/Bonus
                            </option>
                            <option value="deduction" {{ $filterType == 'deduction' ? 'selected' : '' }}>
                                ➖ Déduction
                            </option>
                        </select>
                    </div>

                    <div class="alert alert-light border">
                        <small class="text-muted">
                            <i class="fas fa-info-circle mr-2"></i>
                            Actuellement affiché :
                            <strong>
                                @if($showAll)
                                    Toutes les périodes
                                @elseif($startDate && $endDate)
                                    Du {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                                @else
                                    Toutes les transactions (depuis création)
                                @endif
                            </strong>
                            @if($filterType)
                                <br>Type : <strong>{{ $typeLabels[$filterType] ?? $filterType }}</strong>
                            @endif
                        </small>
                    </div>

                </div>

                <div class="modal-footer bg-light border-0">
                    <a href="{{ route('employes.show', $employe) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo mr-1"></i> Réinitialiser
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-search mr-1"></i> Appliquer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
