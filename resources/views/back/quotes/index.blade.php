@extends('back.layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Devis / Proforma</h4>
        <a href="{{ route('quotes.create') }}" class="btn btn-primary">Nouveau devis</a>
    </div>

    <div class="card shadow">
        <div class="card-body table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Total TTC</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($quotes as $quote)
                        <tr>
                            <td>{{ $quote->quote_number ?? '—' }}</td>
                            <td>{{ $quote->contact?->fullname }}</td>
                            <td>{{ optional($quote->quote_date)->format('d/m/Y') }}</td>
                            <td>{{ ucfirst($quote->status) }}</td>
                            <td>{{ number_format($quote->total_ttc, 0, ',', ' ') }}</td>
                            <td class="text-right">
                                <a href="{{ route('quotes.show', $quote) }}" class="btn btn-sm btn-info">Voir</a>
                                <a href="{{ route('quotes.edit', $quote) }}" class="btn btn-sm btn-warning">Modifier</a>
                                <a href="{{ route('quotes.pdf', $quote) }}" class="btn btn-sm btn-secondary">PDF</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">Aucun devis</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $quotes->links() }}</div>
</div>
@endsection
