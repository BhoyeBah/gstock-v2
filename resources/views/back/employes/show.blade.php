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

        // Avances en cours = avances - remboursements
        $avancesNettes = (int) $avances - (int) $remboursements;
        $avancesNettesDisplay = $avancesNettes;

        // Total payé = primes + paiements de salaire
        $totalPaye = (int) $primes + (int) $paymentSalary;

        // ===== CALCUL CORRIGÉ DU NOMBRE DE MOIS (INCLUSIF, BASÉ SUR LES MOIS) =====
        $hasDateFilter = request('start_date') && request('end_date');
        $nombreMois = 1; // par défaut : 1 mois

        if ($hasDateFilter) {
            $startDate = Carbon::parse(request('start_date'))->startOfMonth();
            $endDate   = Carbon::parse(request('end_date'))->startOfMonth();

            // Sécurité si l'utilisateur inverse les dates
            if ($endDate->lt($startDate)) {
                [$startDate, $endDate] = [$endDate, $startDate];
            }

            // Inclusif : Jan -> Jan = 1, Jan -> Fév = 2, etc.
            $nombreMois = $startDate->diffInMonths($endDate) + 1;
        }

        // Salaire dû sur la période
        $salaireDu = $salaireBase * $nombreMois;

        // Reste à payer = salaire dû - total payé + avances nettes - déductions
        $resteAPayer = $salaireDu - $totalPaye + $avancesNettes - $deductions;

        // Solde global "effet paie"
        $soldeTransactions = (int) $primes
            - (int) $avances
            + (int) $remboursements
            - (int) $deductions
            - (int) ($paymentSalary ?? 0);

        $soldeColor = $soldeTransactions >= 0 ? 'success' : 'danger';
        $resteAPayerColor = $resteAPayer >= 0 ? 'warning' : 'success';
    @endphp

    <!-- Filtres actifs -->
    @if(request('start_date') && request('end_date'))
        <div class="alert alert-info alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="fas fa-filter mr-3 fa-2x"></i>
            <div class="flex-grow-1">
                <strong>Filtre actif :</strong>
                <span class="badge badge-primary ml-2 px-3 py-2" style="font-size: 0.9rem;">
                    <i class="fas fa-calendar-range mr-1"></i>
                    Du {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }} au {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}
                </span>
                @if($filterType)
                    @php
                        $typeLabels = [
                            'salary_payment' => 'Paiement de salaire',
                            'advance' => 'Avance',
                            'advance_repayment' => 'Remboursement',
                            'bonus' => 'Prime',
                            'deduction' => 'Déduction'
                        ];
                    @endphp
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
                @php
                    $typeLabels = [
                        'salary_payment' => 'Paiement de salaire',
                        'advance' => 'Avance',
                        'advance_repayment' => 'Remboursement',
                        'bonus' => 'Prime',
                        'deduction' => 'Déduction'
                    ];
                @endphp
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

                        {{-- ✅ CORRIGÉ : on réutilise $nombreMois calculé plus haut --}}
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

                    {{-- ✅ CORRIGÉ : on réutilise $nombreMois --}}
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
                                +{{ number_format($primes/1000, 0) }}K
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
                            @if($avancesNettesDisplay > 0)
                                -{{ number_format($avancesNettesDisplay/1000, 0) }}K
                            @else
                                0
                            @endif
                        </span>
                    </div>
                    <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                        Avances nettes
                    </h6>
                    <h3 class="mb-0 font-weight-bold text-warning">
                        {{ number_format($avancesNettesDisplay, 0, ',', ' ') }}
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
                                -{{ number_format($deductions/1000, 0) }}K
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

    <!-- Statistiques - Deuxième ligne (Total payé et Reste à payer) -->
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
                            @php
                                $pourcentagePaye = $salaireDu > 0 ? ($totalPaye / $salaireDu) * 100 : 0;
                            @endphp
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
                        @if($hasDateFilter)
                            Primes ({{ number_format($primes, 0, ',', ' ') }}) + Paiements salaire ({{ number_format($paymentSalary, 0, ',', ' ') }})
                            <br><strong>Salaire dû période : {{ number_format($salaireDu, 0, ',', ' ') }} FCFA</strong>
                        @else
                            Primes ({{ number_format($primes, 0, ',', ' ') }}) + Paiements salaire ({{ number_format($paymentSalary, 0, ',', ' ') }})
                        @endif
                    </small>
                </div>
            </div>
        </div>

        <!-- Reste à payer -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-0 shadow-lg h-100 card-hover border-left-{{ $resteAPayerColor }}"
                 style="border-left: 5px solid {{ $resteAPayer >= 0 ? '#f6c23e' : '#1cc88a' }} !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="icon-circle-lg bg-{{ $resteAPayerColor }}-soft text-{{ $resteAPayerColor }}">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="text-right">
                            @php
                                $pourcentageRestant = $salaireDu > 0 ? ($resteAPayer / $salaireDu) * 100 : 0;
                            @endphp
                            @if($resteAPayer >= 0)
                                <span class="badge badge-warning px-3 py-2" style="font-size: 0.9rem;">
                                    {{ number_format(abs($pourcentageRestant), 1) }}% restant
                                </span>
                            @else
                                <span class="badge badge-success px-3 py-2" style="font-size: 0.9rem;">
                                    <i class="fas fa-check mr-1"></i> Payé en excédent
                                </span>
                            @endif
                        </div>
                    </div>
                    <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                        <i class="fas fa-hourglass-half mr-2"></i>Reste à payer
                    </h6>
                    <h2 class="mb-2 font-weight-bold text-{{ $resteAPayerColor }}">
                        {{ number_format(abs($resteAPayer), 0, ',', ' ') }}
                        <small class="text-muted">FCFA</small>
                    </h2>
                    <div class="progress mb-2" style="height: 6px;">
                        @if($resteAPayer >= 0)
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
                        {{-- ✅ CORRIGÉ : on réutilise $nombreMois, plus de recalcul ici --}}
                        @if($hasDateFilter)
                            Salaire dû ({{ $nombreMois }} mois × {{ number_format($salaireBase, 0, ',', ' ') }}) - Total payé + Avances - Déductions
                        @else
                            Salaire de base ({{ number_format($salaireBase, 0, ',', ' ') }}) - Total payé ({{ number_format($totalPaye, 0, ',', ' ') }}) + Avances - Déductions
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
                        @if(!$showAll && request('start_date') && request('end_date'))
                            Transactions du {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }} au {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}
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

                <!-- Footer du tableau -->
                <div class="bg-light border-top px-4 py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <small class="text-muted">
                                {{ $transactions->count() }} transaction(s)
                                @if(!$showAll && request('start_date') && request('end_date'))
                                    du {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }} au {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}
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
                        @if(!$showAll && ($filterType || (request('start_date') && request('end_date'))))
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

                    <!-- Filtre par intervalle de dates -->
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
                                       value="{{ request('start_date') ?? '' }}"
                                       min="{{ $employe->created_at->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">
                                    <i class="fas fa-calendar-times text-danger mr-2"></i>Date de fin
                                </label>
                                <input type="date" class="form-control" id="end_date" name="end_date"
                                       value="{{ request('end_date') ?? '' }}">
                            </div>
                        </div>

                        <small class="text-muted d-block mb-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            Employé créé le {{ $employe->created_at->format('d/m/Y') }}. Laissez vide pour voir toutes les transactions.
                        </small>
                    </div>

                    <!-- Raccourcis de période -->
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

                    <!-- Type de transaction -->
                    <div class="form-group mb-4">
                        <label for="filter_type" class="font-weight-bold mb-3">
                            <i class="fas fa-exchange-alt text-primary mr-2"></i>Type de transaction
                        </label>
                        <select class="form-control" id="filter_type" name="filter_type">
                            <option value="">-- Tous les types --</option>
                            <option value="salary_payment" {{ request('filter_type') == 'salary_payment' ? 'selected' : '' }}>
                                💰 Paiement de salaire
                            </option>
                            <option value="advance" {{ request('filter_type') == 'advance' ? 'selected' : '' }}>
                                ⚡ Avance sur salaire
                            </option>
                            <option value="advance_repayment" {{ request('filter_type') == 'advance_repayment' ? 'selected' : '' }}>
                                🔄 Remboursement d'avance
                            </option>
                            <option value="bonus" {{ request('filter_type') == 'bonus' ? 'selected' : '' }}>
                                🎁 Prime/Bonus
                            </option>
                            <option value="deduction" {{ request('filter_type') == 'deduction' ? 'selected' : '' }}>
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
                                @elseif(request('start_date') && request('end_date'))
                                    Du {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }} au {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}
                                @else
                                    Toutes les transactions (depuis création)
                                @endif
                            </strong>
                            @if(request('filter_type'))
                                @php
                                    $typeLabels = [
                                        'salary_payment' => 'Paiement de salaire',
                                        'advance' => 'Avance',
                                        'advance_repayment' => 'Remboursement',
                                        'bonus' => 'Prime',
                                        'deduction' => 'Déduction'
                                    ];
                                @endphp
                                <br>Type : <strong>{{ $typeLabels[request('filter_type')] ?? request('filter_type') }}</strong>
                            @endif
                        </small>
                    </div>

                </div>

                <div class="modal-footer bg-light border-0">
                    <a href="{{ route('employes.show', $employe) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo mr-1"></i> Réinitialiser
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-search mr-1"></i> Appliquer le filtre
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Modal Paiement -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-success text-white border-0">
                <h5 class="modal-title font-weight-bold" id="paymentModalLabel">
                    <i class="fas fa-money-check-alt mr-2"></i>Effectuer un paiement
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="{{ route('employes.pay', $employe) }}" method="POST">
                @csrf
                <input type="hidden" name="employee_id" value="{{ $employe->id }}">

                <div class="modal-body p-4">

                    <div class="alert alert-light border d-flex align-items-center mb-4">
                        <div class="icon-circle bg-primary-soft text-primary mr-3">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Employé sélectionné</small>
                            <strong class="text-dark">{{ $employe->full_name }}</strong>
                            <small class="text-muted ml-2">({{ $employe->matricule }})</small>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Type -->
                        <div class="col-md-6 mb-3">
                            <label for="type" class="font-weight-bold">
                                Type de transaction <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0">
                                        <i class="fas fa-exchange-alt text-muted"></i>
                                    </span>
                                </div>
                                <select class="form-control border-left-0" id="type" name="type" required>
                                    <option value="">-- Sélectionner --</option>
                                    <option value="salary_payment">💰 Paiement de salaire</option>
                                    <option value="advance">⚡ Avance sur salaire</option>
                                    <option value="advance_repayment">🔄 Remboursement d'avance</option>
                                    <option value="bonus">🎁 Prime/Bonus</option>
                                    <option value="deduction">➖ Déduction</option>
                                </select>
                            </div>
                        </div>

                        <!-- Montant -->
                        <div class="col-md-6 mb-3">
                            <label for="payment_amount" class="font-weight-bold">
                                Montant <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0">
                                        <i class="fas fa-coins text-muted"></i>
                                    </span>
                                </div>
                                <input type="number" class="form-control border-left-0 border-right-0"
                                       id="payment_amount" name="amount" min="1" step="1"
                                       placeholder="Ex: 100000" required>
                                <div class="input-group-append">
                                    <span class="input-group-text bg-light">FCFA</span>
                                </div>
                            </div>
                            <small class="text-muted">Montant minimum: 1 FCFA</small>
                        </div>

                        <!-- Wallet -->
                        <div class="col-md-6 mb-3">
                            <label for="payment_wallet_id" class="font-weight-bold">
                                Source de paiement <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0">
                                        <i class="fas fa-wallet text-muted"></i>
                                    </span>
                                </div>
                                <select class="form-control border-left-0" id="payment_wallet_id" name="wallet_id" required>
                                    <option value="">-- Sélectionner --</option>
                                    @foreach ($wallets as $wallet)
                                        <option value="{{ $wallet->id }}">
                                            {{ $wallet->name }} ({{ number_format($wallet->current_balance, 0, ',', ' ') }} FCFA)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Date -->
                        <div class="col-md-6 mb-3">
                            <label for="payment_date" class="font-weight-bold">
                                Date de paiement <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0">
                                        <i class="fas fa-calendar text-muted"></i>
                                    </span>
                                </div>
                                <input type="date" class="form-control border-left-0" id="payment_date"
                                       name="date" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <!-- Référence -->
                        <div class="col-md-12 mb-3">
                            <label for="payment_reference" class="font-weight-bold">Référence</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0">
                                        <i class="fas fa-hashtag text-muted"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control border-left-0" id="payment_reference"
                                       name="reference" placeholder="Ex: PAY-2026-001">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" onclick="generateReference()">
                                        <i class="fas fa-sync-alt"></i> Auto
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Note -->
                        <div class="col-md-12 mb-3">
                            <label for="payment_note" class="font-weight-bold">Note / Description</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0">
                                        <i class="fas fa-sticky-note text-muted"></i>
                                    </span>
                                </div>
                                <textarea class="form-control border-left-0" id="payment_note" name="note" rows="3"
                                          placeholder="Ajoutez une description ou des détails supplémentaires..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 bg-info-soft">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle fa-2x text-info mr-3"></i>
                            <div>
                                <strong>Information importante</strong>
                                <p class="mb-0 small">Cette transaction sera enregistrée dans l'historique et affectera
                                    le solde de l'employé.</p>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fas fa-check mr-1"></i> Confirmer
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .bg-gradient-primary { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); }
    .bg-gradient-success { background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%); }

    .icon-circle { width: 45px; height: 45px; border-radius: 50%; display:flex; align-items:center; justify-content:center; font-size:1.2rem; }
    .icon-circle-lg { width: 60px; height: 60px; border-radius: 50%; display:flex; align-items:center; justify-content:center; font-size:1.5rem; }

    .bg-primary-soft { background-color: rgba(78, 115, 223, 0.1); }
    .bg-success-soft { background-color: rgba(28, 200, 138, 0.1); }
    .bg-warning-soft { background-color: rgba(246, 194, 62, 0.1); }
    .bg-danger-soft  { background-color: rgba(231, 74, 59, 0.1); }
    .bg-info-soft    { background-color: rgba(54, 185, 204, 0.1); }

    .badge-primary-soft { background-color: rgba(78, 115, 223, 0.15); color:#4e73df; font-weight:600; }
    .badge-success-soft { background-color: rgba(28, 200, 138, 0.15); color:#1cc88a; font-weight:600; }
    .badge-warning-soft { background-color: rgba(246, 194, 62, 0.15); color:#f6c23e; font-weight:600; }
    .badge-danger-soft  { background-color: rgba(231, 74, 59, 0.15); color:#e74a3b; font-weight:600; }
    .badge-info-soft    { background-color: rgba(54, 185, 204, 0.15); color:#36b9cc; font-weight:600; }
    .badge-light { background-color: #f8f9fc; color:#858796; }

    .card-hover { transition: all .3s ease; }
    .card-hover:hover { transform: translateY(-5px); box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,.15)!important; }

    .table tbody tr { transition: background-color .2s ease; }
    .table tbody tr:hover { background-color: rgba(78, 115, 223, 0.05); }

    .modal-content { border-radius:.5rem; overflow:hidden; }
    .modal-header { border-bottom:none; }

    .form-control:focus { border-color:#4e73df; box-shadow: 0 0 0 0.2rem rgba(78,115,223,.25); }

    .breadcrumb-item+.breadcrumb-item::before { content: "›"; }
    .breadcrumb-item a { color:#4e73df; text-decoration:none; }
    .breadcrumb-item a:hover { color:#224abe; text-decoration:underline; }

    .input-group-text { background-color:#f8f9fc; border:1px solid #d1d3e2; }
    .opacity-90 { opacity: .9; }

    .period-shortcut { transition: all .2s ease; }
    .period-shortcut:hover { transform: translateY(-2px); }

    @media print {
        .btn, .breadcrumb, .modal, .alert { display:none!important; }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialiser la date du jour
        $('#payment_date').val(new Date().toISOString().split('T')[0]);

        // Raccourcis de période
        $('.period-shortcut').on('click', function() {
            const period = $(this).data('period');
            const today = new Date();
            const createdAt = new Date('{{ $employe->created_at->format('Y-m-d') }}');

            let startDate, endDate;

            switch(period) {
                case 'today':
                    startDate = today;
                    endDate = today;
                    break;

                case 'this_week':
                    const dayOfWeek = today.getDay();
                    const diff = today.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1);
                    startDate = new Date(today.setDate(diff));
                    endDate = new Date();
                    break;

                case 'this_month':
                    startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    endDate = new Date();
                    break;

                case 'last_month':
                    startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    endDate = new Date(today.getFullYear(), today.getMonth(), 0);
                    break;

                case 'last_3_months':
                    startDate = new Date(today.getFullYear(), today.getMonth() - 3, 1);
                    endDate = new Date();
                    break;

                case 'this_year':
                    startDate = new Date(today.getFullYear(), 0, 1);
                    endDate = new Date();
                    break;

                case 'since_creation':
                    startDate = createdAt;
                    endDate = new Date();
                    break;

                default:
                    return;
            }

            // Formater les dates au format YYYY-MM-DD
            const formatDate = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };

            $('#start_date').val(formatDate(startDate));
            $('#end_date').val(formatDate(endDate));
        });

        // ===== VALIDATION DES DATES (CORRIGÉE) =====
        $('#start_date').on('change', function() {
            const startDateInput = $('#start_date').val();

            // Si le champ est vide, on l'autorise
            if (!startDateInput) {
                return;
            }

            const startDate = new Date(startDateInput);
            const createdAt = new Date('{{ $employe->created_at->format('Y-m-d') }}');

            // Normaliser les dates
            startDate.setHours(0, 0, 0, 0);
            createdAt.setHours(0, 0, 0, 0);

            // SEULE VÉRIFICATION : date de début ne peut pas être < date de création
            if (startDate < createdAt) {
                alert('La date de début ne peut pas être antérieure à la date de création de l\'employé (' + createdAt.toLocaleDateString('fr-FR') + ')');
                $('#start_date').val('{{ $employe->created_at->format('Y-m-d') }}');
                return;
            }

            // Vérifier cohérence avec date de fin si elle est renseignée
            const endDateInput = $('#end_date').val();
            if (endDateInput) {
                const endDate = new Date(endDateInput);
                endDate.setHours(0, 0, 0, 0);

                if (startDate > endDate) {
                    alert('La date de début doit être antérieure ou égale à la date de fin');
                    return;
                }
            }
        });

        // Validation pour la date de fin
        $('#end_date').on('change', function() {
            const endDateInput = $('#end_date').val();

            // Si le champ est vide, on l'autorise
            if (!endDateInput) {
                return;
            }

            // Vérifier cohérence avec date de début si elle est renseignée
            const startDateInput = $('#start_date').val();
            if (startDateInput) {
                const startDate = new Date(startDateInput);
                const endDate = new Date(endDateInput);

                startDate.setHours(0, 0, 0, 0);
                endDate.setHours(0, 0, 0, 0);

                if (endDate < startDate) {
                    alert('La date de fin doit être postérieure ou égale à la date de début');
                    return;
                }
            }
        });

        // Confirmation suppression
        $('.delete-form').on('submit', function(e) {
            e.preventDefault();
            if (confirm('Êtes-vous sûr de vouloir supprimer cette transaction ? Cette action est irréversible.')) {
                this.submit();
            }
        });

        // Validation formulaire paiement
        $('#paymentModal form').on('submit', function(e) {
            const amount = parseFloat($('#payment_amount').val());
            const type = $('#type').val();
            const walletId = $('#payment_wallet_id').val();

            if (!type) {
                e.preventDefault();
                alert('Veuillez sélectionner un type de transaction');
                return false;
            }
            if (!walletId) {
                e.preventDefault();
                alert('Veuillez sélectionner un portefeuille');
                return false;
            }
            if (amount <= 0 || isNaN(amount)) {
                e.preventDefault();
                alert('Le montant doit être supérieur à 0');
                return false;
            }
        });
    });

    function generateReference() {
        const date = new Date();
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        const reference = `PAY-${year}${month}${day}-${random}`;
        $('#payment_reference').val(reference);
    }

    function exportTransactions() {
        const params = new URLSearchParams(window.location.search);
        params.set('export', 'csv');

        alert('Fonctionnalité d\'export en cours de développement\nURL: ' + window.location.pathname + '?' + params.toString());
    }
</script>
@endpush

@endsection
