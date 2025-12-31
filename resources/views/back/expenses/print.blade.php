@php
    use Carbon\Carbon;

    // Récupérer le tenant depuis la première dépense
    $tenant = $expenses->first()?->tenant ?? auth()->user()->tenant;

    // Calcul des totaux
    $totalExpenses = $expenses->sum('amount');
    $expensesByMonth = $expenses->groupBy(function ($expense) {
        return Carbon::parse($expense->expense_date)->format('Y-m');
    });

    // Nombre minimum de lignes pour remplir le tableau
    $minLines = 13;
    $currentLines = count($expenses);
    $emptyLines = max(0, $minLines - $currentLines);

    // Période couverte
    $firstExpense = $expenses->sortBy('expense_date')->first();
    $lastExpense = $expenses->sortByDesc('expense_date')->first();
    $periodStart = $firstExpense ? Carbon::parse($firstExpense->expense_date) : now();
    $periodEnd = $lastExpense ? Carbon::parse($lastExpense->expense_date) : now();
@endphp

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relevé des Dépenses</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #e5e5e5;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px 0;
        }

        .page-wrapper {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        .expense-page {
            width: 210mm;
            min-height: 297mm;
            background: white;
            padding: 15mm;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #000;
            padding-bottom: 4mm;
            margin-bottom: 5mm;
        }

        .company-info h1 {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 2mm;
            color: #000;
        }

        .company-info p {
            font-size: 9pt;
            line-height: 1.5;
            color: #333;
        }

        .report-title {
            text-align: center;
        }

        .report-title h2 {
            font-size: 22pt;
            font-weight: bold;
            margin-bottom: 1mm;
            color: #000;
        }

        .report-type {
            font-size: 10pt;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6mm;
            gap: 5mm;
        }

        .period-box,
        .summary-box {
            flex: 1;
        }

        .info-title {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2mm;
            color: #000;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 1mm;
        }

        .info-content {
            font-size: 9pt;
            line-height: 1.6;
            color: #333;
        }

        .summary-box .info-content {
            text-align: right;
        }

        .expense-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5mm;
            font-size: 9pt;
            border: 1px solid #000;
        }

        .expense-table th,
        .expense-table td {
            border: 1px solid #000;
            padding: 2.5mm;
            font-size: 9pt;
        }

        .expense-table thead {
            background-color: #000;
            color: #fff;
        }

        .expense-table th {
            font-weight: bold;
            text-align: left;
            color: #fff;
        }

        .expense-table tbody td {
            color: #000;
            background-color: #fff;
        }

        .expense-table tbody tr.empty-row td {
            color: #ccc;
            height: 6mm;
        }

        .expense-table tbody tr:hover {
            background-color: #f8f8f8;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .totals {
            width: 100%;
            background: #f8f8f8;
            padding: 4mm;
            border-radius: 2mm;
            margin-top: 3mm;
            border: 1px solid #000;
        }

        .total-line {
            display: flex;
            justify-content: space-between;
            padding: 2mm 0;
            font-size: 10pt;
            color: #000;
        }

        .total-line.count {
            border-bottom: 1px solid #ddd;
            padding-bottom: 2mm;
            margin-bottom: 2mm;
        }

        .total-line.final {
            border-top: 2px solid #000;
            margin-top: 2mm;
            padding-top: 2mm;
            font-size: 12pt;
            font-weight: bold;
            color: #000;
        }

        .footer {
            margin-top: 8mm;
            text-align: center;
            font-size: 8pt;
            color: #000;
            border-top: 1px solid #000;
            padding-top: 3mm;
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #000;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            z-index: 999;
            font-size: 11pt;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.3s;
        }

        .print-btn:hover {
            background: #333;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        /* .stat-badge { display: inline-block; padding: 1.5mm 3mm; background: #dc3545; color: white; border-radius: 2mm; font-size: 9pt; font-weight: bold; margin-left: 2mm; } */
        @media print {
            body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .expense-page {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 15mm !important;
            }

            .print-btn {
                display: none !important;
            }

            @page {
                size: A4 portrait;
                margin: 0mm !important;
            }

            .expense-table thead {
                background-color: #000 !important;
                color: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .expense-table th {
                color: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .stat-badge {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    <button class="print-btn" onclick="window.print()">🖨️ Imprimer</button>

    <div class="page-wrapper">
        <div class="expense-page">
            {{-- Header --}}
            <div class="header">
                <div class="company-info">
                    <h1>{{ $tenant->name }}</h1>
                    <p>
                        Adresse: {{ $tenant->address }}<br>
                        Tél: {{ $tenant->phone }}<br>
                        Email: {{ $tenant->email }}<br>
                        NINEA: {{ $tenant->ninea }}
                    </p>
                </div>
                <div class="report-title">
                    @if ($tenant && $tenant->logo)
                        <img src="{{ asset('storage/' . $tenant->logo) }}" alt="Logo entreprise"
                            style="max-width: 70px; max-height: 70px; display:block; margin:0 auto 5px;">
                    @else
                        <p class="report-type" style="margin-bottom:5px;">Relevé des Dépenses</p>
                    @endif
                    <h2>DÉPENSES</h2>
                </div>
            </div>

            {{-- Info section --}}
            <div class="info-section">
                <div class="period-box">
                    <div class="info-title">Période:</div>
                    <div class="info-content">
                        <strong>Du:</strong> {{ $periodStart->format('d/m/Y') }}<br>
                        <strong>Au:</strong> {{ $periodEnd->format('d/m/Y') }}<br>
                        <strong>Durée:</strong> {{ $periodStart->diffInDays($periodEnd) + 1 }} jour(s)
                    </div>
                </div>
                <div class="summary-box">
                    <div class="info-title">Résumé:</div>
                    <div class="info-content">
                        <strong>Nombre de dépenses:</strong> <span class="stat-badge">{{ count($expenses) }}</span><br>
                        <strong>Total des dépenses:</strong> <span
                            class="stat-badge">{{ number_format($totalExpenses, 0, ',', ' ') }} FCFA</span><br>
                        <strong>Généré le:</strong> {{ now()->format('d/m/Y à H:i') }}
                    </div>
                </div>
            </div>

            {{-- Tableau des dépenses --}}
            <table class="expense-table">
                <thead>
                    <tr>
                        <th style="width: 10%;">Date</th>
                        <th style="width: 50%;">Motif / Raison</th>
                        <th style="width: 20%;">Wallet</th>
                        <th class="text-right" style="width: 50%;">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($expenses->sortByDesc('expense_date') as $expense)
                        <tr>
                            <td>{{ Carbon::parse($expense->expense_date)->format('d/m/Y') }}</td>
                            <td><strong>{{ $expense->reason }}</strong></td>
                            <td><strong>{{ $expense->wallet->name ?? '-' }}</strong></td>
                            <td class="text-right">{{ number_format($expense->amount, 0, ',', ' ') }} FCFA</td>
                        </tr>
                    @endforeach

                    {{-- Lignes vides pour compléter le tableau --}}
                    @for ($i = 0; $i < $emptyLines; $i++)
                        <tr class="empty-row">
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td class="text-right">-</td>
                        </tr>
                    @endfor
                </tbody>
            </table>

            {{-- Totaux --}}
            <div class="totals">
                <div class="total-line count">
                    <span>Nombre total de dépenses</span>
                    <span><strong>{{ count($expenses) }}</strong></span>
                </div>
                <div class="total-line">
                    <span>Montant moyen par dépense</span>
                    <span>{{ count($expenses) > 0 ? number_format($totalExpenses / count($expenses), 0, ',', ' ') : 0 }}
                        FCFA</span>
                </div>
                <div class="total-line final">
                    <span>TOTAL DES DÉPENSES</span>
                    <span>{{ number_format($totalExpenses, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>

            {{-- Footer --}}
            <div class="footer">
                <p>{{ $tenant->name }} - NINEA: {{ $tenant->ninea }} | RC: {{ $tenant->rc }}<br>
                    {{ $tenant->address }} | Tél: {{ $tenant->phone }} | {{ $tenant->email }}</p>
            </div>
        </div>
    </div>
</body>

</html>
