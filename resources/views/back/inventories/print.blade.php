@php
    use Carbon\Carbon;

    $inventoryDate = Carbon::parse($inventory->created_at);
    $closedDate = $inventory->closed_at ? Carbon::parse($inventory->closed_at) : null;
    $emptyLines = 9;
@endphp

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventaire N°: {{ $inventory->inventory_number }}</title>
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

        .inventory-page {
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

        .inventory-title {
            text-align: center;
        }

        .inventory-title h2 {
            font-size: 22pt;
            font-weight: bold;
            margin-bottom: 1mm;
            color: #000;
        }

        .document-type {
            font-size: 10pt;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .status-badge {
            display: inline-block;
            padding: 2mm 3mm;
            border-radius: 3mm;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 5px;
        }

        .status-completed {
            background-color: #28a745;
            color: white;
        }

        .status-pending {
            background-color: #ffc107;
            color: #000;
        }

        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6mm;
            gap: 5mm;
        }

        .warehouse-box,
        .inventory-details-box {
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

        .inventory-details-box .info-content {
            text-align: right;
        }

        .inventory-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5mm;
            font-size: 9pt;
            border: 1px solid #000;
        }

        .inventory-table th,
        .inventory-table td {
            border: 1px solid #000;
            padding: 2.5mm;
            font-size: 9pt;
        }

        .inventory-table thead {
            background-color: #000;
            color: #fff;
        }

        .inventory-table th {
            font-weight: bold;
            text-align: left;
            color: #fff;
        }

        .inventory-table tbody td {
            color: #000;
            background-color: #fff;
        }

        .inventory-table tbody tr.empty-row td {
            color: #ccc;
            height: 6mm;
        }

        .inventory-table tbody tr.validated {
            background-color: #d4edda;
        }

        .inventory-table tbody tr.pending {
            background-color: #fff3cd;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .variance-positive {
            color: #28a745;
            font-weight: bold;
        }

        .variance-negative {
            color: #dc3545;
            font-weight: bold;
        }

        .variance-zero {
            color: #6c757d;
        }

        .statistics {
            width: 100%;
            background: #f8f8f8;
            padding: 4mm;
            border-radius: 2mm;
            margin-top: 3mm;
            border: 1px solid #000;
        }

        .stat-line {
            display: flex;
            justify-content: space-between;
            padding: 2mm 0;
            font-size: 10pt;
            color: #000;
        }

        .stat-line.header {
            border-bottom: 2px solid #000;
            margin-bottom: 2mm;
            padding-bottom: 2mm;
            font-size: 11pt;
            font-weight: bold;
        }

        .stat-line.total {
            border-top: 2px solid #000;
            margin-top: 2mm;
            padding-top: 2mm;
            font-size: 11pt;
            font-weight: bold;
        }

        .stat-highlight {
            color: #007bff;
            font-weight: bold;
        }

        .stat-positive {
            color: #28a745;
            font-weight: bold;
        }

        .stat-negative {
            color: #dc3545;
            font-weight: bold;
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

        .legend {
            margin-top: 4mm;
            padding: 3mm;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 2mm;
            font-size: 8pt;
        }

        .legend-title {
            font-weight: bold;
            margin-bottom: 2mm;
            color: #000;
        }

        .legend-item {
            display: inline-block;
            margin-right: 4mm;
        }

        .legend-color {
            display: inline-block;
            width: 15px;
            height: 15px;
            margin-right: 2mm;
            vertical-align: middle;
            border: 1px solid #000;
        }

        @media print {
            body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .inventory-page {
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

            .inventory-table thead {
                background-color: #000 !important;
                color: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .inventory-table th {
                color: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .inventory-table tbody tr.validated {
                background-color: #d4edda !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .inventory-table tbody tr.pending {
                background-color: #fff3cd !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .status-badge {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .variance-positive {
                color: #28a745 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .variance-negative {
                color: #dc3545 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    <button class="print-btn" onclick="window.print()">🖨️ Imprimer</button>


    <div class="page-wrapper">
        <div class="inventory-page">
            {{-- Header --}}
            <div class="header">
                <div class="company-info">
                    <h1>{{ $inventory->tenant->name }}</h1>

                    <p>
                        Adresse: {{ $inventory->tenant->address }}<br>
                        Tél: {{ $inventory->tenant->phone }}<br>
                        Email: {{ $inventory->tenant->email }}<br>
                        NINEA: {{ $inventory->tenant->ninea }}
                    </p>
                </div>
                <div class="inventory-title">
                    @if ($inventory->tenant && $inventory->tenant->logo)
                     <img src="{{ asset('storage/' . $inventory->tenant->logo) }}" alt="Logo entreprise" style="max-width: 70px; max-height: 70px; display:block; margin:0 auto 5px;">

                    @else
                        <p class="document-type" style="margin-bottom:5px;">Inventaire Physique</p>
                    @endif

                    @php
                        $statusClass = $inventory->status === 'completed' ? 'status-completed' : 'status-pending';
                        $statusText = $inventory->status === 'completed' ? 'Clôturé' : 'En cours';
                    @endphp

                    <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                </div>
            </div>

            {{-- Info section --}}
            <div class="info-section">
                <div class="warehouse-box">
                    <div class="info-title">Entrepôt:</div>
                    <div class="info-content">
                        <strong>{{ $inventory->warehouse->name }}</strong><br>
                        {{ $inventory->warehouse->address ?? 'Adresse non renseignée' }}<br>
                        Type: {{ $inventory->warehouse->type ?? 'Standard' }}
                    </div>
                </div>
                <div class="inventory-details-box">
                    <div class="info-title">Détails:</div>
                    <div class="info-content">
                        N° Inventaire: <strong>{{ $inventory->inventory_number }}</strong><br>
                        Date ouverture: <strong>{{ $inventoryDate->format('d/m/Y à H:i') }}</strong><br>
                        @if ($closedDate)
                            Date clôture: <strong>{{ $closedDate->format('d/m/Y à H:i') }}</strong><br>
                        @endif
                        Total produits: <strong>{{ $inventory->total_products }}</strong>
                    </div>
                </div>
            </div>

            {{-- Tableau des articles --}}
            <table class="inventory-table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th class="text-center">Quanité Réelle</th>
                        <th class="text-center">Quantité trouvé</th>
                        <th class="text-center">Écart</th>
                        <th class="text-center">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($inventory->items as $item)
                        <tr class="{{ $item->validated ? 'validated' : 'pending' }}">
                            <td><strong>{{ $item->product->name }}</strong></td>
                            <td class="text-center">{{ $item->theoretical_qty }}</td>
                            <td class="text-center">
                                @if ($item->real_qty !== null)
                                    {{ $item->real_qty }}
                                @else
                                    <em style="color: #999;"></em>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($item->variance !== null)
                                    @if ($item->variance > 0)
                                        <span class="variance-positive">+{{ $item->variance }}</span>
                                    @elseif($item->variance < 0)
                                        <span class="variance-negative">{{ $item->variance }}</span>
                                    @else
                                        <span class="variance-zero">0</span>
                                    @endif
                                @else
                                    <span style="color: #999;">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($item->validated)
                                    ✓ Validé
                                @else
                                    ⏳ En attente
                                @endif
                            </td>
                        </tr>
                    @endforeach

                    {{-- Lignes vides pour compléter le tableau --}}
                    @for ($i = 0; $i < $emptyLines; $i++)
                        <tr class="empty-row">
                            <td>&nbsp;</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                        </tr>
                    @endfor
                </tbody>
            </table>

            {{-- Légende --}}
            <div class="legend">
                <div class="legend-title">Légende:</div>
                <span class="legend-item">
                    <span class="legend-color" style="background-color: #d4edda;"></span>
                    Article validé
                </span>
                <span class="legend-item">
                    <span class="legend-color" style="background-color: #fff3cd;"></span>
                    Article en attente
                </span>
                <span class="legend-item">
                    <span class="variance-positive">+X</span> = Surplus
                </span>
                <span class="legend-item">
                    <span class="variance-negative">-X</span> = Manquant
                </span>
            </div>

            {{-- Footer --}}
            <div class="footer">
                <p>{{ $inventory->tenant->name }} - NINEA: {{ $inventory->tenant->ninea }} | RC:
                    {{ $inventory->tenant->rc }}<br>
                    {{ $inventory->tenant->address }} | Tél: {{ $inventory->tenant->phone }} |
                    {{ $inventory->tenant->email }}</p>
                <p style="margin-top: 2mm; font-style: italic;">Document généré le {{ now()->format('d/m/Y à H:i') }}
                </p>
            </div>
        </div>
    </div>
</body>

</html>
