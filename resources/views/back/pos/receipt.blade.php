@extends('back.layouts.admin')

@section('content')
<div class="container" style="max-width: 600px;">

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- Reçu --}}
    <div class="card shadow-sm mb-4" id="receipt-card">
        <div class="card-body p-4">

            <div class="text-center mb-4">
                <h4 class="font-weight-bold mb-1">
                    <i class="fas fa-receipt mr-2 text-primary"></i>REÇU DE VENTE
                </h4>
                <div class="text-muted small">{{ config('app.name') }}</div>
                <hr>
                <div class="font-weight-bold text-primary h5">{{ $invoice->invoice_number }}</div>
                <div class="text-muted small">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</div>
            </div>

            {{-- Client --}}
            @if($invoice->contact && $invoice->contact->fullname !== 'Client comptoir')
                <div class="mb-3 p-2 bg-light rounded">
                    <div class="small text-muted">Client</div>
                    <div class="font-weight-bold">{{ $invoice->contact->fullname }}</div>
                </div>
            @endif

            {{-- Lignes --}}
            <table class="table table-sm mb-3">
                <thead class="thead-light">
                    <tr>
                        <th>Produit</th>
                        <th class="text-center">Qté</th>
                        <th class="text-right">P.U</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                        <tr>
                            <td>{{ $item->product?->name ?? '—' }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-right">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                            <td class="text-right">{{ number_format($item->total_line, 0, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <hr>

            {{-- Totaux --}}
            <div class="d-flex justify-content-between mb-1">
                <span>Total HT</span>
                <span>{{ number_format($invoice->total_ht, 0, ',', ' ') }} FCFA</span>
            </div>
            @if($invoice->tax_amount > 0)
                <div class="d-flex justify-content-between mb-1 text-muted small">
                    <span>Taxes</span>
                    <span>{{ number_format($invoice->tax_amount, 0, ',', ' ') }} FCFA</span>
                </div>
            @endif
            @if($invoice->discount_amount > 0)
                <div class="d-flex justify-content-between mb-1 text-muted small">
                    <span>Remises</span>
                    <span>- {{ number_format($invoice->discount_amount, 0, ',', ' ') }} FCFA</span>
                </div>
            @endif
            <div class="d-flex justify-content-between font-weight-bold h5 mt-2 pt-2 border-top">
                <span>TOTAL TTC</span>
                <span class="text-primary">{{ number_format($invoice->total_invoice, 0, ',', ' ') }} FCFA</span>
            </div>

            <hr>

            {{-- Paiement --}}
            @foreach($invoice->payments as $payment)
                <div class="d-flex justify-content-between mb-1 text-success">
                    <span><i class="fas fa-check-circle mr-1"></i>Montant reçu ({{ $payment->payment_type }})</span>
                    <span class="font-weight-bold">{{ number_format($payment->amount_paid, 0, ',', ' ') }} FCFA</span>
                </div>
            @endforeach

            @if((int)$invoice->balance > 0)
                <div class="d-flex justify-content-between text-danger font-weight-bold">
                    <span>Reste dû</span>
                    <span>{{ number_format($invoice->balance, 0, ',', ' ') }} FCFA</span>
                </div>
            @else
                <div class="d-flex justify-content-between text-success font-weight-bold">
                    <span>Rendu monnaie</span>
                    <span>{{ number_format(abs((int)$invoice->balance), 0, ',', ' ') }} FCFA</span>
                </div>
            @endif

            <hr>

            <div class="text-center text-muted small mt-3">
                Merci pour votre achat !
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="d-flex gap-2 justify-content-between mb-4">
        <a href="{{ route('sales.index') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Nouvelle vente
        </a>
        <button type="button" class="btn btn-outline-secondary" onclick="printReceipt()">
            <i class="fas fa-print mr-1"></i> Imprimer le reçu
        </button>
        <a href="{{ route('invoices.show', ['type' => 'client', 'invoice' => $invoice]) }}" class="btn btn-outline-info">
            <i class="fas fa-file-invoice mr-1"></i> Voir la facture
        </a>
    </div>
</div>

@push('scripts')
<script>
function printReceipt() {
    const card = document.getElementById('receipt-card');
    const win = window.open('', '_blank');
    win.document.write('<html><head><title>Reçu</title>');
    win.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">');
    win.document.write('</head><body class="p-4">');
    win.document.write(card.innerHTML);
    win.document.write('</body></html>');
    win.document.close();
    win.focus();
    setTimeout(() => { win.print(); win.close(); }, 500);
}
</script>
@endpush
@endsection
