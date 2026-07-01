@extends('back.layouts.admin')

@section('content')
    @php
        $isCustomer = ($module ?? 'customer') === 'customer';
        $statusLabels = [
            'draft' => ['label' => 'Brouillon', 'class' => 'neutral', 'icon' => 'pen'],
            'validated' => ['label' => 'Validé', 'class' => 'info', 'icon' => 'check-circle'],
            'applied' => ['label' => 'Appliqué', 'class' => 'success', 'icon' => 'file-invoice-dollar'],
            'partially_applied' => ['label' => 'Partiellement appliqué', 'class' => 'warning', 'icon' => 'file-invoice-dollar'],
            'refunded' => ['label' => 'Remboursé', 'class' => 'success', 'icon' => 'money-bill-wave'],
            'cancelled' => ['label' => 'Annulé', 'class' => 'danger', 'icon' => 'ban'],
        ];
    @endphp

    <div class="container-fluid">
        <div class="page-hero page-hero--accent mb-4">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                <div>
                    <div class="page-hero__eyebrow mb-2">{{ $isCustomer ? 'Ventes' : 'Achats' }}</div>
                    <h1 class="page-hero__title">{{ $title }}</h1>
                    <p class="page-hero__subtitle mb-0">{{ $subtitle }}</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <span class="status-pill status-pill--info">
                        <i class="fas fa-file-invoice-dollar"></i>{{ $records->total() }} document(s)
                    </span>
                    <a href="{{ route($isCustomer ? 'customer-returns.index' : 'supplier-returns.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-undo mr-1"></i> Voir les retours
                    </a>
                </div>
            </div>
        </div>

        <div class="card table-card mb-4">
            <div class="card-body">
                <form method="GET" class="row align-items-end">
                    <div class="col-md-5 mb-3">
                        <label class="font-weight-bold">Recherche</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Numéro, client/fournisseur, facture">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="font-weight-bold">Statut</label>
                        <select name="status" class="form-control">
                            <option value="">Tous</option>
                            @foreach($statusLabels as $value => $meta)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $meta['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3 d-flex gap-2">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search mr-1"></i> Filtrer
                        </button>
                        <a href="{{ route($isCustomer ? 'customer-credit-notes.index' : 'supplier-credit-notes.index') }}" class="btn btn-outline-secondary">
                            Réinitialiser
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card table-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table data-table">
                        <thead>
                            <tr>
                                <th>Numéro</th>
                                <th>Partenaire</th>
                                <th>Source</th>
                                <th class="text-right">Total TTC</th>
                                <th class="text-right">Appliqué</th>
                                <th class="text-right">Restant</th>
                                <th>Statut</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $record)
                                @php $state = $statusLabels[$record->status] ?? ['label' => ucfirst((string) $record->status), 'class' => 'info', 'icon' => 'circle-info']; @endphp
                                <tr>
                                    <td class="font-weight-bold">{{ $record->credit_note_number }}</td>
                                    <td>{{ $record->contact?->fullname ?? 'N/A' }}</td>
                                    <td>
                                        {{ $isCustomer
                                            ? ($record->invoice?->invoice_number ?? $record->customerReturn?->return_number ?? 'N/A')
                                            : ($record->invoice?->invoice_number ?? $record->supplierReturn?->return_number ?? 'N/A') }}
                                    </td>
                                    <td class="text-right">{{ number_format((int) $record->total_ttc, 0, ',', ' ') }} FCFA</td>
                                    <td class="text-right">{{ number_format((int) $record->applied_amount, 0, ',', ' ') }} FCFA</td>
                                    <td class="text-right">{{ number_format((int) $record->remaining_amount, 0, ',', ' ') }} FCFA</td>
                                    <td>
                                        <span class="status-pill status-pill--{{ $state['class'] }}">
                                            <i class="fas fa-{{ $state['icon'] }}"></i>{{ $state['label'] }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route($isCustomer ? 'customer-credit-notes.show' : 'supplier-credit-notes.show', $record) }}" class="btn btn-sm btn-outline-primary">
                                            Voir
                                        </a>
                                        <a href="{{ route($isCustomer ? 'customer-credit-notes.print' : 'supplier-credit-notes.print', $record) }}" class="btn btn-sm btn-outline-secondary">
                                            Imprimer
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="empty-state my-5">
                                            <div class="empty-state__icon"><i class="fas fa-file-invoice-dollar"></i></div>
                                            <div class="font-weight-bold mb-1">{{ $emptyMessage }}</div>
                                            <div class="text-muted">Les avoirs apparaîtront ici après validation d’un retour.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $records->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
