@extends('back.layouts.admin')

@php use Carbon\Carbon; @endphp

@section('content')
    <style>
        /* ====================================
                                                   FIXES GLOBALES & FONDAMENTAUX
                                                   ==================================== */
        /* Assurez une police lisible et une taille de police de base */
        body {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            color: #1a202c;
            /* Couleur de texte sombre standard */
        }

        /* Conteneur pour repousser le footer (si le layout parent utilise flex) */
        /* Si le layout parent n'utilise pas flex, cette règle aide quand même */
        .content-wrapper-fix {
            min-height: calc(100vh - 100px);
            /* Ajustez 100px selon la hauteur réelle de votre header/footer */
            padding-bottom: 2rem;
            /* Espace sous le dernier élément */
        }

        /* Réinitialisation Bootstrap/Admin template */
        .row.no-gutters {
            margin-right: 0;
            margin-left: 0;
        }

        .row.no-gutters>[class*="col-"] {
            padding-right: 0;
            padding-left: 0;
        }

        /* ====================================
                                                   EN-TÊTE DE PAGE
                                                   ==================================== */
        .page-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            /* S'assurer que le texte est lisible sur le dégradé */
            color: #fff;
        }

        .page-header h1 {
            color: #fff;
            font-weight: 700;
            margin: 0;
            font-size: 1.75rem;
        }

        .page-header .btn {
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .page-header .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* ====================================
                                                   CARTES STATISTIQUES
                                                   ==================================== */
        .stats-card {
            border: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            overflow: hidden;
            background: #fff;
            /* Animation pour une meilleure perception */
            animation: fadeInUp 0.5s ease-out;
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
            opacity: 0.8;
            /* Légèrement plus discret */
        }

        /* Couleurs pour les icônes */
        .stats-card.border-left-primary .stats-icon {
            background: rgba(79, 70, 229, 0.1);
            color: #4f46e5;
        }

        .stats-card.border-left-success .stats-icon {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .stats-card.border-left-danger .stats-icon {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }


        /* ====================================
                                                   CARTES D'INFORMATION & LISTE (ONGLETS)
                                                   ==================================== */
        .invoice-list-section {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            height: 100%;
            /* Important pour que les cartes soient égales */
        }

        .invoice-list-section .card-header {
            background: linear-gradient(135deg, #4f46e5 0%, #224abe 100%);
            border: none;
            color: #fff;
            /* Texte blanc sur l'en-tête */
            padding: 1.25rem 1.5rem;
        }

        .invoice-list-section.h-100 .card-body {
            display: flex;
            /* Pour centrer ou aligner le contenu si nécessaire */
            flex-direction: column;
        }

        /* Liste de description (Informations & Contact) */
        .info-list dt {
            font-weight: 600;
            color: #5a5c69;
            margin-bottom: 0.5rem;
        }

        .info-list dd {
            color: #1a202c;
            margin-bottom: 0.5rem;
        }

        .contact-link {
            color: #4f46e5;
            font-weight: 600;
            text-decoration: none;
        }

        .contact-link:hover {
            color: #224abe;
            text-decoration: underline;
        }

        /* ====================================
                                                   TABLEAUX DANS LES ONGLETS
                                                   ==================================== */
        .invoice-table {
            width: 100%;
            margin-bottom: 0;
            table-layout: auto;
            /* Permet un redimensionnement fluide */
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

        /* Style spécifique pour les retours (table-warning) */
        .table-warning {
            background-color: #fffaf0 !important;
            color: #7a5c00;
            font-style: italic;
        }

        .table-warning td {
            padding: 0.5rem 0.75rem !important;
            font-size: 0.8rem;
        }

        /* ====================================
                                                   ONGLETS
                                                   ==================================== */
        .nav-tabs .nav-link {
            color: #ffff;
            font-weight: 500;
            border: none;
            border-bottom: 3px solid transparent;
            padding: 1rem 1.25rem;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link.active {
            color: #ffffffe5;
            border-bottom-color: #4f46e5;
            font-weight: 700;
            background: none;
        }

        .nav-tabs .nav-link:hover {
            border-bottom-color: #e3e6f0;
        }

        /* ====================================
                                                   ANIMATIONS
                                                   ==================================== */
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

        /* Appliquer l'animation aux éléments principaux */
        .stats-card,
        .invoice-list-section {
            animation: fadeInUp 0.5s ease-out;
        }
    </style>

    @php
        // Calculs
        $totalInvoice = $invoice->items->sum('total_line');
        $totalPaid = $payments->sum('amount_paid');
        $remaining = max(0, $totalInvoice - $totalPaid);

        // Type de contact
        $type = $invoice->type == 'client' ? 'client' : 'supplier';

        $totalReturned = $invoice->items->sum(function ($item) {
            return $item->returns->sum(function ($return) use ($item) {
                return $return->quantity * $item->unit_price;
            });
        });
    @endphp

    <!-- Conteneur principal (avec la classe de correction pour la hauteur) -->
    <div class="content-wrapper-fix">

        <!-- En-tête de page -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h1>
                        <i class="fas fa-file-invoice mr-2"></i> Facture N° <span
                            class="text-warning">{{ $invoice->invoice_number }}</span>
                    </h1>
                    <p class="mb-0 text-white-50 small">
                        Créée le :
                        {{ $invoice->created_at ? \Carbon\Carbon::parse($invoice->created_at)->format('d/m/Y') : '-' }}
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
                    {{--
                    <a href="{{ route('invoices.print', [$invoice->type . 's', $invoice->id]) }}" class="btn btn-info m-1"
                        title="Imprimer" onclick="event.preventDefault(); window.open(this.href,'_blank').print();">
                        <i class="fas fa-print"></i>
                        <strong>Imprimer</strong>
                    </a> --}}

                    <!-- Bouton Imprimer qui ouvre le modal -->
                    <button type="button" class="btn btn-info m-1" data-toggle="modal"
                        data-target="#printChoiceModal{{ $invoice->id }}">
                        <i class="fas fa-print"></i>
                        <strong>Imprimer</strong>
                    </button>


                    <a href="{{ route('invoices.index', $invoice->type . 's') }}" class="btn btn-light m-1">
                        <i class="fas fa-arrow-left mr-1"></i>
                        <strong>Retour à la liste</strong>
                    </a>

                </div>
            </div>
        </div>

        <!-- Cartes statistiques -->
        <div class="row mb-4">
            <!-- Total Facture -->
            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card stats-card border-left-primary shadow h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-2">
                                    Total Facture
                                </div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($totalInvoice, 0, ',', ' ') }} FCFA
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="stats-icon border-left-primary">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Payé -->
            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card stats-card border-left-success shadow h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-2">
                                    Total Payé
                                </div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($totalPaid, 0, ',', ' ') }} FCFA
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="stats-icon border-left-success">
                                    <i class="fas fa-wallet"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reste à Payer -->
            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card stats-card border-left-danger shadow h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-2">
                                    Reste à Payer
                                </div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($remaining, 0, ',', ' ') }} FCFA
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="stats-icon border-left-danger">
                                    <i class="fas fa-coins"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cartes d'information -->
        <div class="row mb-4">
            <div class="col-lg-6 mb-3">
                <div class="invoice-list-section h-100">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold"><i class="fas fa-info-circle mr-2"></i>Informations de la facture
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <dl class="row mb-0 info-list">
                            <dt class="col-sm-4">Numéro</dt>
                            <dd class="col-sm-8 font-weight-bold">{{ $invoice->invoice_number ?? '-' }}</dd>

                            <dt class="col-sm-4">Date</dt>
                            <dd class="col-sm-8">
                                {{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') : '-' }}
                            </dd>

                            <dt class="col-sm-4">Échéance</dt>
                            <dd class="col-sm-8">
                                {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') : '-' }}
                            </dd>

                            <dt class="col-sm-4">Statut</dt>
                            <dd class="col-sm-8">
                                @php
                                    $statusConfig = [
                                        'draft' => ['color' => 'secondary', 'icon' => 'fa-file'],
                                        'validated' => ['color' => 'info', 'icon' => 'fa-check-circle'],
                                        'partial' => ['color' => 'warning', 'icon' => 'fa-clock'],
                                        'paid' => ['color' => 'success', 'icon' => 'fa-check-double'],
                                        'cancelled' => ['color' => 'danger', 'icon' => 'fa-times-circle'],
                                    ];
                                    $config = $statusConfig[$invoice->status] ?? [
                                        'color' => 'secondary',
                                        'icon' => 'fa-file',
                                    ];
                                @endphp
                                <span class="badge badge-{{ $config['color'] }}">
                                    <i class="fas {{ $config['icon'] }} mr-1"></i>
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </dd>

                            <dt class="col-sm-4 mt-2">Note</dt>
                            <dd class="col-sm-8 mt-2 text-wrap">{{ $invoice->note ?? '-' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="invoice-list-section h-100">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold"><i
                                class="fas fa-user-tie mr-2"></i>{{ ucfirst($invoice->type == 'client' ? 'Client' : 'Fournisseur') }}
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <dl class="row mb-0 info-list">
                            <dt class="col-sm-4">Nom</dt>
                            <dd class="col-sm-8 font-weight-bold">
                                <a href="{{ route($type . 's.show', $invoice->contact->id) }}" class="contact-link"
                                    title="Voir le contact">
                                    {{ optional($invoice->contact)->fullname ?? '-' }}
                                </a>
                            </dd>

                            <dt class="col-sm-4">Téléphone</dt>
                            <dd class="col-sm-8">{{ optional($invoice->contact)->phone_number ?? '-' }}</dd>

                            <dt class="col-sm-4">Email</dt>
                            <dd class="col-sm-8">{{ optional($invoice->contact)->email ?? '-' }}</dd>

                            <dt class="col-sm-4">Adresse</dt>
                            <dd class="col-sm-8 text-wrap">{{ optional($invoice->contact)->address ?? '-' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Onglets -->
        <div class="invoice-list-section">
            <div class="card-header bg-white border-bottom p-0">
                <ul class="nav nav-tabs card-header-tabs" id="invoiceTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="lines-tab" data-toggle="tab" href="#lines" role="tab"
                            aria-controls="lines" aria-selected="true">
                            <i class="fas fa-list mr-1"></i>
                            Lignes de la facture
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="payments-tab" data-toggle="tab" href="#payments" role="tab"
                            aria-controls="payments" aria-selected="false">
                            <i class="fas fa-credit-card mr-1"></i>
                            Paiements
                        </a>
                    </li>
                    @if ($invoice->type === 'supplier')
                        <li class="nav-item">
                            <a class="nav-link" id="batches-tab" data-toggle="tab" href="#batches" role="tab"
                                aria-controls="batches" aria-selected="false">
                                <i class="fas fa-boxes mr-1"></i>
                                Lots
                            </a>
                        </li>
                    @endif
                </ul>
            </div>

            <div class="card-body p-0">
                <div class="tab-content" id="invoiceTabsContent">

                    {{-- TAB Lignes --}}
                    <div class="tab-pane fade show active" id="lines" role="tabpanel" aria-labelledby="lines-tab">
                        @if ($invoice->items->count())
                            <div class="table-responsive">
                                <table class="table invoice-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Entrepôt</th>
                                            <th>Produit</th>
                                            <th class="text-center">Qté</th>
                                            <th class="text-right">Prix U</th>
                                            <th class="text-right">Remise</th>
                                            <th class="text-right">Total</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($invoice->items as $item)
                                            {{-- Ligne principale du produit --}}
                                            <tr>
                                                <td class="text-muted">{{ $loop->iteration }}</td>
                                                <td>{{ $item->warehouse->name ?? '-' }}</td>
                                                <td><strong class="text-dark">{{ $item->product->name ?? '-' }}</strong>
                                                </td>
                                                <td class="text-center font-weight-bold">{{ $item->quantity }}</td>
                                                <td class="text-right">{{ number_format($item->unit_price, 0, ',', ' ') }}
                                                    FCFA
                                                </td>
                                                <td class="text-right text-danger">
                                                    {{ number_format($item->discount ?? 0, 0, ',', ' ') }}</td>
                                                <td class="text-right font-weight-bold">
                                                    {{ number_format($item->total_line, 0, ',', ' ') }}</td>

                                                @if ($item->invoice && !in_array($item->invoice->status, ['draft', 'cancelled']))
                                                    <td class="text-center action-buttons">
                                                        <button type="button" class="btn btn-sm btn-secondary"
                                                            data-toggle="modal"
                                                            data-target="#returnModal-{{ $item->id }}"
                                                            title="Enregistrer un retour">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                    </td>
                                                @endif
                                            </tr>

                                            {{-- Lignes des retours --}}
                                            @foreach ($item->returns as $return)
                                                <tr class="table-warning">
                                                    <td></td>
                                                    <td colspan="2">
                                                        <i class="fas fa-undo-alt mr-1"></i>
                                                        <strong>Retourné</strong> le
                                                        {{ $return->created_at->format('d/m/Y') }}
                                                    </td>
                                                    <td class="text-center font-weight-bold">{{ $return->quantity }}</td>
                                                    <td class="text-right">

                                                        {{ number_format($return->invoiceItem->unit_price * $return->quantity, 0, ',', ' ') }}
                                                        FCFA
                                                    </td>
                                                    <td colspan="2" class="text-right">
                                                        <em class="text-muted small">{{ $return->motif ?? '-' }}</em>
                                                    </td>
                                                    <td class="text-center"></td>
                                                </tr>
                                            @endforeach
                                        @endforeach

                                        {{-- Total Remise --}}
                                        <tr class="table-secondary">
                                            <td colspan="5" class="text-right font-weight-bold">Total Remise</td>
                                            <td class="text-right font-weight-bold text-danger">
                                                {{ number_format($invoice->items->sum('discount'), 0, ',', ' ') }} FCFA
                                            </td>
                                            <td colspan="2"></td>
                                        </tr>

                                        <tr class="table-secondary">
                                            <td colspan="5" class="text-right font-weight-bold">Total retourné</td>
                                            <td class="text-right font-weight-bold text-primary">
                                                {{ number_format($totalReturned, 0, ',', ' ') }} FCFA
                                            </td>
                                            <td colspan="2"></td>
                                        </tr>

                                        {{-- Total Général --}}
                                        <tr class="bg-light">
                                            <td colspan="5" class="text-right font-weight-bold h5">Total Général</td>
                                            <td colspan="2" class="text-right font-weight-bold h5 text-success">
                                                {{ number_format($totalInvoice, 0, ',', ' ') }} FCFA
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="p-5 text-center">
                                <i class="fas fa-list-alt fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Aucune ligne enregistrée.</h5>
                            </div>
                        @endif
                    </div>

                    {{-- TAB Paiements --}}
                    <div class="tab-pane fade" id="payments" role="tabpanel" aria-labelledby="payments-tab">
                        @if ($payments->count())
                            <div class="table-responsive">
                                <table class="table invoice-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th class="text-right">Montant payé</th>
                                            <th class="text-right">Reste</th>
                                            <th>Type</th>
                                            <th>Source</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($payments as $payment)
                                            <tr>
                                                <td class="text-muted">{{ $loop->iteration }}</td>
                                                <td><i class="fas fa-calendar-alt text-muted mr-1"></i>
                                                    {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}
                                                </td>
                                                <td class="text-right font-weight-bold text-success">
                                                    {{ number_format($payment->amount_paid, 0, ',', ' ') }}
                                                </td>
                                                <td class="text-right font-weight-bold text-warning">
                                                    {{ number_format($payment->remaining_amount, 0, ',', ' ') }}
                                                </td>
                                                <td><i class="fas fa-credit-card text-muted mr-1"></i>
                                                    {{ ucfirst($payment->payment_type ?? '-') }}</td>
                                                <td>{{ ucfirst($payment->payment_source ?? '-') }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-light">
                                            <td colspan="2" class="text-right font-weight-bold h5">Total payé</td>
                                            <td class="text-right font-weight-bold h5 text-success">
                                                {{ number_format($totalPaid, 0, ',', ' ') }} FCFA
                                            </td>
                                            <td colspan="3"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="p-3 d-flex justify-content-center">{{ $payments->links() }}</div>
                        @else
                            <div class="p-5 text-center">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Aucun paiement enregistré.</h5>
                            </div>
                        @endif
                    </div>

                    {{-- TAB Lots --}}
                    <div class="tab-pane fade" id="batches" role="tabpanel" aria-labelledby="batches-tab">
                        @if ($batches->count())
                            <div class="table-responsive">
                                <table class="table invoice-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Produit</th>
                                            <th class="text-right">Prix</th>
                                            <th class="text-center">Qté initiale</th>
                                            <th class="text-center">Restante</th>
                                            <th class="text-center">Expiration</th>
                                            <th class="text-center">Créé le</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($batches as $batch)
                                            <tr>
                                                <td class="text-muted">{{ $loop->iteration }}</td>
                                                <td><strong
                                                        class="text-dark">{{ optional($batch->product)->name ?? '-' }}</strong>
                                                </td>
                                                <td class="text-right">
                                                    {{ number_format($batch->unit_price, 0, ',', ' ') }}
                                                </td>
                                                <td class="text-center">{{ number_format($batch->quantity, 0, ',', ' ') }}
                                                </td>
                                                <td class="text-center font-weight-bold">
                                                    {{ number_format($batch->remaining, 0, ',', ' ') }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $batch->expiry_date ? \Carbon\Carbon::parse($batch->expiry_date)->format('d/m/Y') : '-' }}
                                                </td>
                                                <td class="text-center">{{ $batch->created_at->format('d/m/Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="p-3 d-flex justify-content-center">{{ $batches->links() }}</div>
                        @else
                            <div class="p-5 text-center">
                                <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Aucun lot enregistré.</h5>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Modals Retour produit (Style mis à jour) --}}
    @foreach ($invoice->items as $item)
        <div class="modal fade" id="returnModal-{{ $item->id }}" tabindex="-1" role="dialog"
            aria-labelledby="returnModalLabel-{{ $item->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                <div class="modal-content">
                    <form action="{{ route('invoices.returnProduct', [$type, $invoice->id]) }}" method="POST">
                        @csrf
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="returnModalLabel-{{ $item->id }}">
                                <i class="fas fa-undo-alt mr-2"></i> Retour produit :
                                {{ $item->product->name ?? 'Produit' }}
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <input type="hidden" name="invoice_item_id" value="{{ $item->id }}">

                            <div class="form-group">
                                <label for="quantity-{{ $item->id }}" class="font-weight-bold">Quantité retournée
                                    <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" id="quantity-{{ $item->id }}"
                                    class="form-control form-control-lg" min="1" max="{{ $item->quantity }}"
                                    placeholder="Ex: 1" required>
                                <small class="text-muted">Quantité maximale : {{ $item->quantity }}</small>
                            </div>

                            <div class="form-group">
                                <label for="motif-{{ $item->id }}" class="font-weight-bold">Motif <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="motif" id="motif-{{ $item->id }}"
                                    class="form-control form-control-lg" placeholder="Raison du retour" maxlength="255"
                                    required>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fas fa-times mr-1"></i> Annuler
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check mr-1"></i> Valider le retour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach


    @include('back.invoices._model_invoice')
@endsection
