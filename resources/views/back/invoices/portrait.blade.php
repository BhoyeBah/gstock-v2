@php
    use Carbon\Carbon;

    $invoiceDate = Carbon::parse($invoice->invoice_date);
    $dueDate = Carbon::parse($invoice->due_date);
    $daysNet = $invoiceDate->diffInDays($dueDate);

    $totalDiscount = 0;
    foreach ($invoice->items as $item) {
        $totalDiscount += $item->discount;
    }

    // Vérifier s'il y a des retours
    $hasReturns = false;
    $totalReturns = 0;
    foreach ($invoice->items as $item) {
        if ($item->returnProducts && $item->returnProducts->count() > 0) {
            $hasReturns = true;
            foreach ($item->returnProducts as $return) {
                $totalReturns += $return->quantity * $item->unit_price;
            }
        }
    }

    // Nombre minimum de lignes pour remplir le tableau
    $minLines = 13;
    $currentLines = count($invoice->items);
    $emptyLines = max(0, $minLines - $currentLines);
@endphp

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture N°: {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #e5e5e5; font-family: 'Arial', sans-serif; margin: 0; padding: 20px 0; }
        .page-wrapper { display: flex; justify-content: center; align-items: flex-start; min-height: 100vh; }
        .invoice-page { width: 210mm; min-height: 297mm; background: white; padding: 15mm; box-shadow: 0 0 10px rgba(0,0,0,0.1); position: relative; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #000; padding-bottom: 4mm; margin-bottom: 5mm; }
        .company-info h1 { font-size: 18pt; font-weight: bold; margin-bottom: 2mm; color: #000; }
        .company-info p { font-size: 9pt; line-height: 1.5; color: #333; }
        .invoice-title { text-align: center; }
        .invoice-title h2 { font-size: 22pt; font-weight: bold; margin-bottom: 1mm; color: #000; }
        .invoice-type { font-size: 10pt; color: #666; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        .status-badge { display: inline-block; padding: 2mm 3mm; border-radius: 3mm; font-size: 9pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 5px; }
        .status-paid { background-color: #28a745; color: white; }
        .status-unpaid { background-color: #007bff; color: white; }
        .status-partial { background-color: #ffc107; color: #000; }
        .status-credited { background-color: #28a745; color: white; }
        .status-partially_credited { background-color: #ffc107; color: #000; }
        .status-pending { background-color: #6c757d; color: white; }
        .status-cancelled { background-color: #dc3545; color: white; }
        .info-section { display: flex; justify-content: space-between; margin-bottom: 6mm; gap: 5mm; }
        .recipient-box, .invoice-details-box { flex: 1; }
        .info-title { font-size: 10pt; font-weight: bold; text-transform: uppercase; margin-bottom: 2mm; color: #000; border-bottom: 1px solid #f0f0f0; padding-bottom: 1mm; }
        .info-content { font-size: 9pt; line-height: 1.6; color: #333; }
        .invoice-details-box .info-content { text-align: right; }
        .invoice-table { width: 100%; border-collapse: collapse; margin-bottom: 5mm; font-size: 9pt; border: 1px solid #000; }
        .invoice-table th, .invoice-table td { border: 1px solid #000; padding: 2.5mm; font-size: 9pt; }
        .invoice-table thead { background-color: #000; color: #fff; }
        .invoice-table th { font-weight: bold; text-align: left; color: #fff; }
        .invoice-table tbody td { color: #000; background-color: #fff; }
        .invoice-table tbody tr.empty-row td { color: #ccc; height: 6mm; }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .totals { width: 100%; background: #f8f8f8; padding: 4mm; border-radius: 2mm; margin-top: 3mm; border: 1px solid #000; }
        .total-line { display: flex; justify-content: space-between; padding: 2mm 0; font-size: 10pt; color: #000; }
        .total-line.subtotal { border-top: 1px solid #000; padding-top: 2mm; }
        .total-line.final { border-top: 2px solid #000; margin-top: 2mm; padding-top: 2mm; font-size: 12pt; font-weight: bold; color: #000; }
        .total-line.balance { border-top: 2px solid #dc3545; margin-top: 2mm; padding-top: 2mm; font-size: 11pt; font-weight: bold; color: #dc3545; }
        .total-line.returns { color: #ff6b6b; font-weight: bold; }
        .returns-section { margin-top: 4mm; padding: 4mm; background: #fff3cd; border: 1px solid #ffc107; border-radius: 2mm; }
        .returns-title { font-size: 10pt; font-weight: bold; color: #856404; margin-bottom: 2mm; text-transform: uppercase; }
        .return-item { font-size: 9pt; padding: 1mm 0; color: #856404; display: flex; justify-content: space-between; }
        .return-motif { font-style: italic; color: #666; font-size: 8pt; margin-left: 3mm; }
        .footer { margin-top: 8mm; text-align: center; font-size: 8pt; color: #000; border-top: 1px solid #000; padding-top: 3mm; }
        .print-btn { position: fixed; top: 20px; right: 20px; background: #000; color: #fff; border: none; padding: 12px 24px; border-radius: 5px; cursor: pointer; z-index: 999; font-size: 11pt; font-weight: bold; box-shadow: 0 2px 8px rgba(0,0,0,0.2); transition: all 0.3s; }
        .print-btn:hover { background: #333; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
        @media print {
            body { background: white !important; margin: 0 !important; padding: 0 !important; }
            .invoice-page { box-shadow: none !important; margin: 0 !important; padding: 15mm !important; }
            .print-btn { display: none !important; }
            @page { size: A4 portrait; margin: 0mm !important; }
            .invoice-table thead { background-color: #000 !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .invoice-table th { color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .status-badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .returns-section { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>

<body>

    <button class="print-btn" onclick="window.print()">🖨️ Imprimer</button>

    <div class="page-wrapper">
        <div class="invoice-page">
            {{-- Header --}}
            <div class="header">
                <div class="company-info">
                    <h1>{{ $invoice->tenant->name }}</h1>
                    <p>
                        Adresse: {{ $invoice->tenant->address }}<br>
                        Tél: {{ $invoice->tenant->phone }}<br>
                        Email: {{ $invoice->tenant->email }}<br>
                        NINEA: {{ $invoice->tenant->ninea }}
                    </p>
                </div>
                <div class="invoice-title">
                    @if ($invoice->tenant && $invoice->tenant->logo)
                        <img src="{{ asset('storage/' . $invoice->tenant->logo) }}" alt="Logo entreprise" style="max-width: 70px; max-height: 70px; display:block; margin:0 auto 5px;">
                    @else
                        <p class="invoice-type" style="margin-bottom:5px;">
                            @if ($invoice->type === 'client') Facture Client @else Facture Fournisseur @endif
                        </p>
                    @endif

                    @php
                        $statusClass = match ($invoice->status) {
                            'paid' => 'status-paid',
                            'validated' => 'status-unpaid',
                            'partial' => 'status-partial',
                            'credited' => 'status-credited',
                            'partially_credited' => 'status-partially_credited',
                            'cancelled' => 'status-cancelled',
                            default => 'status-pending',
                        };
                        $statusText = match ($invoice->status) {
                            'paid' => 'Payée',
                            'validated' => 'Validée(non payée)',
                            'partial' => 'Paiement partiel',
                            'credited' => 'Créditée',
                            'partially_credited' => 'Partiellement payée',
                            'cancelled' => 'Annulée',
                            default => 'Brouillon',
                        };
                    @endphp

                    <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                </div>
            </div>

            {{-- Info section --}}
            <div class="info-section">
                <div class="recipient-box">
                    <div class="info-title">Facturé à:</div>
                    <div class="info-content">
                        <strong>{{ $invoice->contact->fullname }}</strong><br>
                        {{ $invoice->contact->address }}<br>
                        Tél: {{ $invoice->contact->phone_number }}
                    </div>
                </div>
                <div class="invoice-details-box">
                    <div class="info-title">Détails:</div>
                    <div class="info-content">
                        N° Facture: <strong>{{ $invoice->invoice_number }}</strong><br>
                        Date: <strong>{{ $invoiceDate->format('d/m/Y') }}</strong><br>
                        Échéance: <strong>{{ $dueDate->format('d/m/Y') }}</strong><br>
                        Conditions: <strong>{{ $daysNet }} jour(s)</strong>
                    </div>
                </div>
            </div>

            {{-- Tableau des articles --}}
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Désignation</th>
                        <th class="text-center">Quantité</th>
                        <th class="text-right">Prix Unitaire</th>
                        <th class="text-center">Remise</th>
                        <th class="text-right">Total HT</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->items as $item)
                        <tr>
                            <td><strong>{{ $item->product->name }}</strong></td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-right">{{ number_format($item->unit_price, 0, ',', ' ') }} FCFA</td>
                            <td class="text-center">{{ number_format($item->discount, 0, ',', ' ') }} FCFA</td>
                            <td class="text-right">{{ number_format($item->total_price, 0, ',', ' ') }} FCFA</td>
                        </tr>
                        @if($item->returnProducts)
                            @foreach ($item->returnProducts as $return)
                                <tr style="background-color:#fff3cd;">
                                    <td style="padding-left:5mm; color:#856404;"><em>↩ Retour: {{ $return->name }}</em></td>
                                    <td class="text-center" style="color:#856404;">-{{ $return->quantity }}</td>
                                    <td class="text-right" style="color:#856404;">{{ number_format($item->unit_price, 0, ',', ' ') }} FCFA</td>
                                    <td class="text-center" style="color:#856404;">-</td>
                                    <td class="text-right" style="color:#856404;">-{{ number_format($return->quantity * $item->unit_price, 0, ',', ' ') }} FCFA</td>
                                </tr>
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Lignes vides pour compléter le tableau --}}
                    @for($i = 0; $i < $emptyLines; $i++)
                        <tr class="empty-row">
                            <td>&nbsp;</td>
                            <td class="text-center">-</td>
                            <td class="text-right">-</td>
                            <td class="text-center">-</td>
                            <td class="text-right">-</td>
                        </tr>
                    @endfor
                </tbody>
            </table>

            {{-- Totaux --}}
            <div class="totals">
                <div class="total-line">
                    <span>Sous-total HT</span>
                    <span>{{ number_format($invoice->total_invoice, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="total-line subtotal">
                    <span>Total remise</span>
                    <span>{{ number_format($totalDiscount, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="total-line returns">
                    <span>Total retours</span>
                    <span>{{ number_format($totalReturns, 0, ',', ' ') }} FCFA</span>
                </div>
                @if($invoice->balance > 0)
                    <div class="total-line balance">
                        <span>RESTE À PAYER</span>
                        <span>{{ number_format($invoice->balance, 0, ',', ' ') }} FCFA</span>
                    </div>
                @endif
                <div class="total-line final">
                    <span>TOTAL TTC</span>
                    <span>{{ number_format($invoice->total_invoice, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>

            {{-- Footer --}}
            <div class="footer">
                <p>{{ $invoice->tenant->name }} - NINEA: {{ $invoice->tenant->ninea }} | RC: {{ $invoice->tenant->rc }}<br>
                {{ $invoice->tenant->address }} | Tél: {{ $invoice->tenant->phone }} | {{ $invoice->tenant->email }}</p>
            </div>
        </div>
    </div>
</body>
</html>
