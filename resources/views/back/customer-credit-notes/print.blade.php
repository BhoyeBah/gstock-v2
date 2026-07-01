@extends('back.layouts.admin')

@section('content')
    @php
        $items = $record->items;
    @endphp

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <div>
                <h1 class="h3 mb-1">Avoir client</h1>
                <p class="mb-0 text-muted">{{ $record->credit_note_number }}</p>
            </div>
            <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print mr-1"></i> Imprimer</button>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-4">
                    <div>
                        <h2 class="h4 mb-1">{{ $record->credit_note_number }}</h2>
                        <div class="text-muted">Client: {{ $record->contact?->fullname ?? 'N/A' }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-muted">Date</div>
                        <div class="font-weight-bold">{{ optional($record->credit_date)->format('d/m/Y') }}</div>
                    </div>
                </div>

                <table class="table table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Produit</th>
                            <th class="text-right">Qté</th>
                            <th class="text-right">PU HT</th>
                            <th class="text-right">Total TTC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>{{ $item->product?->name ?? 'Produit' }}</td>
                                <td class="text-right">{{ $item->quantity }}</td>
                                <td class="text-right">{{ number_format($item->unit_price_ht, 0, ',', ' ') }}</td>
                                <td class="text-right">{{ number_format($item->total_ttc, 0, ',', ' ') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Aucune ligne.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="border rounded p-3">
                            <div class="text-muted text-uppercase small font-weight-bold mb-1">Montant appliqué</div>
                            <div class="h5 mb-0">{{ number_format($record->applied_amount, 0, ',', ' ') }} FCFA</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3">
                            <div class="text-muted text-uppercase small font-weight-bold mb-1">Crédit restant</div>
                            <div class="h5 mb-0">{{ number_format($record->remaining_amount, 0, ',', ' ') }} FCFA</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3">
                            <div class="text-muted text-uppercase small font-weight-bold mb-1">Facture liée</div>
                            <div class="h5 mb-0">{{ $record->invoice?->invoice_number ?? 'Aucune' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
