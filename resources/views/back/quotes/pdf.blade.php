<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Devis {{ $quote->quote_number ?? $quote->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        .header { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f4f4f4; }
        .totals { margin-top: 15px; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Devis {{ $quote->quote_number ?? '—' }}</h2>
        <p><strong>Client:</strong> {{ $quote->contact?->fullname }}</p>
        <p><strong>Date:</strong> {{ optional($quote->quote_date)->format('d/m/Y') }}</p>
        <p><strong>Expiration:</strong> {{ optional($quote->expiry_date)->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Entrepôt</th>
                <th>Produit</th>
                <th>Qté</th>
                <th>PU</th>
                <th>Remise</th>
                <th>Taxe</th>
                <th>Total TTC</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($quote->items as $item)
                <tr>
                    <td>{{ $item->warehouse?->name }}</td>
                    <td>{{ $item->product?->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                    <td>{{ number_format($item->discount, 0, ',', ' ') }}</td>
                    <td>{{ $item->taxRate?->name ?? 'TVA par défaut' }}</td>
                    <td>{{ number_format($item->total_ttc, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <p><strong>HT:</strong> {{ number_format($quote->subtotal_ht, 0, ',', ' ') }}</p>
        <p><strong>Taxe:</strong> {{ number_format($quote->tax_total, 0, ',', ' ') }}</p>
        <p><strong>TTC:</strong> {{ number_format($quote->total_ttc, 0, ',', ' ') }}</p>
    </div>
</body>
</html>
