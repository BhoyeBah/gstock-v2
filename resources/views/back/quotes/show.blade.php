@extends('back.layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Devis {{ $quote->quote_number ?? '—' }}</h4>
            <small class="text-muted">Statut: {{ ucfirst($quote->status) }}</small>
        </div>
        <div>
            <a href="{{ route('quotes.pdf', $quote) }}" class="btn btn-secondary">PDF</a>
            @if ($quote->status === \App\Models\Quote::STATUS_ACCEPTED)
                <form action="{{ route('quotes.convert', $quote) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-success">Convertir en facture</button>
                </form>
            @endif
        </div>
    </div>

    <div class="card shadow mb-3">
        <div class="card-body">
            <p><strong>Client:</strong> {{ $quote->contact?->fullname }}</p>
            <p><strong>Date:</strong> {{ optional($quote->quote_date)->format('d/m/Y') }}</p>
            <p><strong>Expiration:</strong> {{ optional($quote->expiry_date)->format('d/m/Y') }}</p>
            <p><strong>Notes:</strong> {{ $quote->notes ?? '—' }}</p>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body table-responsive">
            <table class="table table-bordered">
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
            <div class="text-right mt-3">
                <p><strong>HT:</strong> {{ number_format($quote->subtotal_ht, 0, ',', ' ') }}</p>
                <p><strong>Taxe:</strong> {{ number_format($quote->tax_total, 0, ',', ' ') }}</p>
                <p><strong>TTC:</strong> {{ number_format($quote->total_ttc, 0, ',', ' ') }}</p>
                @if ($quote->convertedInvoice)
                    <p><strong>Facture liée:</strong> {{ $quote->convertedInvoice->invoice_number ?? $quote->convertedInvoice->id }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
