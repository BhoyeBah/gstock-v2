@extends('back.layouts.admin')
@php
    use Carbon\Carbon;
    $today = Carbon::now();
@endphp

@section('content')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --danger-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .page-header {
            background: var(--primary-gradient);
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: pulse 8s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.5;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        .page-header h3 {
            font-weight: 700;
            font-size: 1.8rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 1;
        }

        .btn-print {
            background: white;
            color: #667eea;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }

        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            background: #f8f9fa;
        }

        .search-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            border: none;
        }

        .search-card label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .search-card .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 0.75rem;
            transition: all 0.3s ease;
            background-color: white;
            height: calc(1.5em + 1.5rem + 4px);
        }

        .search-card .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }

        /* Fix pour le select - correction du problème d'affichage */
        .search-card select.form-control {
            background-color: white !important;
            position: relative;
            z-index: 10;
            appearance: auto;
            -webkit-appearance: menulist;
            -moz-appearance: menulist;
            height: calc(1.5em + 1.5rem + 4px);
            line-height: 1.5;
            display: block;
            opacity: 1;
            visibility: visible;
        }

        .search-card select.form-control:focus {
            z-index: 20;
        }

        .btn-search {
            background: var(--primary-gradient);
            border: none;
            border-radius: 10px;
            padding: 0.75rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .main-table-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            border: none;
        }

        .table-title {
            color: #667eea;
            font-weight: 700;
            font-size: 1.4rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .custom-table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .custom-table thead th {
            background: var(--primary-gradient);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 1rem;
            border: none;
            white-space: nowrap;
        }

        .custom-table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #f1f3f5;
        }

        .custom-table tbody tr:hover {
            background: #f8f9fa;
            transform: scale(1.01);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }

        .custom-table tbody td {
            padding: 1rem;
            vertical-align: middle;
            color: #495057;
        }

        .invoice-number {
            background: linear-gradient(135deg, #667eea20 0%, #764ba220 100%);
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            font-weight: 700;
            color: #667eea;
            display: inline-block;
        }

        @media print {

            /* Mode paysage pour plus d'espace */
            @page {
                size: A4 landscape;
                margin: 0.3cm;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            html,
            body {
                width: 100%;
                height: 100%;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden;
                background: white !important;
            }

            /* IMPORTANT : on cache tout sauf la zone #print-area (même approche que ton template d'origine) */
            body * {
                visibility: hidden;
            }

            #print-area,
            #print-area * {
                visibility: visible;
            }

            #print-area {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
                height: auto !important;
                margin: 0 !important;
                padding: 0.3cm !important;
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
                transform: scale(0.95);
                transform-origin: top left;
                background: white !important;
            }

            .no-print {
                display: none !important;
            }

            .main-table-card {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                border-radius: 0 !important;
                page-break-inside: avoid !important;
                background: white !important;
            }

            .table-title {
                color: #000 !important;
                font-size: 0.9rem !important;
                margin-bottom: 0.3rem !important;
                page-break-after: avoid !important;
                background: white !important;
            }

            .table-title span {
                font-size: 0.9rem !important;
            }

            .table-responsive {
                overflow: visible !important;
                page-break-inside: avoid !important;
                background: white !important;
            }

            .custom-table {
                width: 100% !important;
                font-size: 0.6rem !important;
                margin: 0 !important;
                border-collapse: collapse !important;
                page-break-inside: avoid !important;
                background: white !important;
            }

            .custom-table thead {
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
                background: white !important;
            }

            .custom-table thead th {
                background: white !important;
                color: #000 !important;
                border: 1px solid #999 !important;
                padding: 0.25rem 0.15rem !important;
                font-size: 0.55rem !important;
                font-weight: 700 !important;
                white-space: nowrap !important;
                line-height: 1.2 !important;
            }

            .custom-table tbody {
                page-break-inside: avoid !important;
                background: white !important;
            }

            .custom-table tbody tr {
                page-break-inside: avoid !important;
                page-break-after: auto !important;
                background: white !important;
            }

            .custom-table tbody tr:hover {
                transform: none !important;
                box-shadow: none !important;
                background: white !important;
            }

            .custom-table tbody td {
                border: 1px solid #ccc !important;
                padding: 0.25rem 0.15rem !important;
                white-space: nowrap !important;
                font-size: 0.55rem !important;
                line-height: 1.2 !important;
                color: #000 !important;
                background: white !important;
            }

            /* Enlever tous les styles visuels pour l'impression */
            .invoice-number,
            .phone-badge,
            .amount-balance,
            .days-badge,
            .client-name,
            .address-text,
            .amount-paid,
            .due-date {
                background: white !important;
                padding: 0 !important;
                border-radius: 0 !important;
                display: inline !important;
                color: #000 !important;
                font-weight: normal !important;
                box-shadow: none !important;
            }

            .recovery-checkbox {
                width: 15px !important;
                height: 15px !important;
                -webkit-appearance: checkbox !important;
                appearance: checkbox !important;
            }

            /* Afficher la colonne Recouvré à l'impression */
            .recovery-column {
                display: table-cell !important;
            }

            strong {
                font-weight: normal !important;
            }
        }

        @media (max-width: 768px) {
            .page-header h3 {
                font-size: 1.3rem;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            .table-responsive {
                border-radius: 10px;
            }
        }
    </style>

    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <h3 class="m-0">📦 Rapport des paiements fournisseurs</h3>

        <button onclick="printTable()" class="btn btn-print no-print">
            🖨️ Imprimer le rapport
        </button>
    </div>

    <!-- Formulaire de recherche -->
    <div class="search-card no-print">
        <form method="GET" action="">
            <div class="row g-3">

                <div class="col-md-3">
                    <label>📅 Date début</label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $startDate) }}">
                </div>

                <div class="col-md-3">
                    <label>📅 Date fin</label>
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $endDate) }}">
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <label>🛒 Fournisseurs</label>
                    <select id="productSelect" name="supplier_id" class="form-control">
                        <option value="">-- Tous les fournisseurs --</option>

                        @foreach ($suppliers as $s)
                            <option value="{{ $s->id }}"
                                {{ (string) old('supplier_id', $supplierId) === (string) $s->id ? 'selected' : '' }}>
                                {{ $s->fullname }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-search w-100">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                </div>

            </div>
        </form>
    </div>


    <!-- Zone imprimable -->
    <div id="print-area" class="main-table-card">
        <h4 class="table-title">
            <span>📋</span>
            Résultats des paiements fournisseurs
            @if ($startDate && $endDate)
                du {{ Carbon::parse($startDate)->format('d/m/Y') }}
                au {{ Carbon::parse($endDate)->format('d/m/Y') }}
            @elseif($startDate)
                à partir du {{ Carbon::parse($startDate)->format('d/m/Y') }}
            @elseif($endDate)
                jusqu'au {{ Carbon::parse($endDate)->format('d/m/Y') }}
            @endif
        </h4>

        <div class="table-responsive">
            <table class="custom-table table">
                <thead>
                    <tr>
                        <th>Date paiement</th>
                        <th>Motif</th>
                        <th>N° factures</th>
                        <th>Montant payé</th>
                        <th>Reste à payé</th>
                        <th>Total Facture</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <!-- Date paiement -->
                            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</td>

                            <!-- Motif -->
                            <td>
                                <strong>{{ $payment->payment_type }}</strong>
                            </td>

                            <!-- Numéro facture -->
                            <td>
                                <strong>{{ $payment->invoice->invoice_number ?? '-' }}</strong>
                            </td>

                            <!-- Montant payé -->
                            <td>
                                {{ number_format($payment->amount_paid, 0, ',', ' ') }} CFA
                            </td>

                            <!-- Reste à payer -->
                            <td>
                                {{ number_format($payment->remaining_amount, 0, ',', ' ') }} CFA
                            </td>

                            <!-- Total facture -->
                            <td>
                                {{ number_format($payment->invoice->total_invoice ?? 0, 0, ',', ' ') }} CFA
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                Aucun résultat trouvé
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        <hr>

        <div class="row mt-3">
            <div class="col-md-4">
                <h5><strong>Total débit : </strong> {{ number_format($totalPaid, 0, ',', ' ') }} CFA</h5>
            </div>

            <div class="col-md-4 text-end">
                <h5><strong>Total crédit : </strong> {{ number_format($totalRemaining, 0, ',', ' ') }} CFA</h5>
            </div>

            <div class="col-md-4 text-end">
                <h5><strong>Solde : </strong> {{ number_format($solde, 0, ',', ' ') }} CFA</h5>
            </div>
        </div>


    </div>

    <script>
        function printTable() {
            window.print();
        }
    </script>
@endsection
