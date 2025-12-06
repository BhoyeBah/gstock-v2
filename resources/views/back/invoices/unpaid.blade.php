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
        }

        .search-card .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
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

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary-gradient);
        }

        .stat-card.danger::before {
            background: var(--danger-gradient);
        }

        .stat-card.success::before {
            background: var(--success-gradient);
        }

        .stat-card.warning::before {
            background: var(--warning-gradient);
        }

        .stat-label {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #212529;
            margin-top: 0.5rem;
        }

        .stat-icon {
            position: absolute;
            right: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3rem;
            opacity: 0.1;
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

        .custom-table thead th:first-child {
            border-radius: 10px 0 0 0;
        }

        .custom-table thead th:last-child {
            border-radius: 0 10px 0 0;
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

        .client-name {
            font-weight: 600;
            color: #212529;
        }

        .phone-badge {
            background: #e7f5ff;
            color: #1971c2;
            padding: 0.3rem 0.7rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .amount-paid {
            color: #28a745;
            font-weight: 600;
        }

        .amount-balance {
            background: linear-gradient(135deg, #ffe8e8 0%, #ffcccc 100%);
            color: #dc3545;
            padding: 0.5rem 0.8rem;
            border-radius: 8px;
            font-weight: 700;
            display: inline-block;
        }

        .days-badge {
            background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%);
            color: #856404;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 700;
            display: inline-block;
        }

        .days-badge.critical {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c2c7 100%);
            color: #842029;
        }

        .address-text {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .due-date {
            font-weight: 600;
            color: #495057;
        }

        .recovery-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #667eea;
        }

        /* Masquer la colonne Recouvré à l'écran */
        .recovery-column {
            display: none;
        }

        /* Styles d'impression */
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
        <h3 class="m-0">📄 Rapport de Récouvrements — Factures Impayées</h3>

        <button onclick="printTable()" class="btn btn-print no-print">
            🖨️ Imprimer le rapport
        </button>
    </div>

    <!-- Formulaire de recherche -->
    <div class="search-card no-print">
        <form method="GET" action="">
            <div class="row g-3">
                <div class="col-md-4">
                    <label>📅 Date début</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>

                <div class="col-md-4">
                    <label>📅 Date fin</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>

                <div class="col-md-4 d-flex align-items-end">
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

            <h4 class="table-title">
                <span>📋</span>
                Liste des factures impayées (Recouvrements)
                @if (request('start_date') && request('end_date'))
                    du {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }}
                    au {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}
                @elseif(request('start_date'))
                    à partir du {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }}
                @elseif(request('end_date'))
                    jusqu'au {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}
                @endif
            </h4>

            <div class="table-responsive">
                <table class="custom-table table">
                    <thead>
                        <tr>
                            <th>N° Facture</th>
                            <th>Client</th>
                            <th>Téléphone</th>
                            <th>Adresse</th>
                            <th>Montant Total</th>
                            <th>Montant Payé</th>
                            <th>Reste à Payer</th>
                            <th>Date Échéance</th>
                            <th>Jours de Retard</th>
                            <th class="recovery-column">Recouvré</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($invoices as $invoice)
                            @php
                                $dueDate = Carbon::parse($invoice->due_date);
                                $daysOverdue = $today->diffInDays($dueDate, false);
                            @endphp
                            <tr>
                                <td>
                                    <span class="invoice-number">{{ $invoice->invoice_number }}</span>
                                </td>
                                <td>
                                    <span class="client-name">{{ $invoice->contact->fullname }}</span>
                                </td>
                                <td>
                                    <span class="phone-badge">📞 {{ $invoice->contact->phone_number }}</span>
                                </td>
                                <td>
                                    <span class="address-text">📍 {{ $invoice->contact->address }}</span>
                                </td>
                                <td>
                                    <strong>{{ number_format($invoice->total_invoice, 0, ',', ' ') }} FCFA</strong>
                                </td>
                                <td>
                                    <span class="amount-paid">
                                        {{ number_format($invoice->total_invoice - $invoice->balance, 0, ',', ' ') }} FCFA
                                    </span>
                                </td>

                                <td>
                                    <span class="amount-balance">
                                        {{ number_format($invoice->balance, 0, ',', ' ') }} FCFA
                                    </span>
                                </td>
                                <td><span class="due-date">{{ $dueDate->format('d/m/Y') }}</span></td>
                                <td>
                                    <span class="days-badge {{ $daysOverdue > 15 ? 'critical' : '' }}">{{ $daysOverdue }}
                                        jours</span>
                                </td>

                                <td class="text-center recovery-column">
                                    <input type="checkbox" class="recovery-checkbox">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="font-weight: 700;">
                            <td colspan="4" class="text-end" style="padding: 1rem;">TOTAL</td>
                            <td><strong>{{ number_format($totalMontant, 0, ',', ' ') }} FCFA</strong></td>
                            <td><strong>{{ number_format($totalPaye, 0, ',', ' ') }} FCFA</strong></td>
                            <td><strong>{{ number_format($totalReste, 0, ',', ' ') }} FCFA</strong></td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
    </div>

    <script>
        function printTable() {
            window.print();
        }
    </script>
@endsection
