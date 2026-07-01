@extends('back.layouts.admin')

@section('content')
    @php
        $badge = fn ($status) => match ($status) {
            'draft' => ['class' => 'neutral', 'label' => 'Brouillon', 'icon' => 'pen'],
            'validated' => ['class' => 'success', 'label' => 'Validé', 'icon' => 'check-circle'],
            'cancelled' => ['class' => 'danger', 'label' => 'Annulé', 'icon' => 'ban'],
            default => ['class' => 'info', 'label' => ucfirst((string) $status), 'icon' => 'circle-info'],
        };
        $isCustomer = ($module ?? 'customer') === 'customer';
        $sourceLabel = $isCustomer ? 'Facture / BL source' : 'Facture / Réception source';
        $movementType = $isCustomer ? 'customer_return_in' : 'supplier_return_out';
    @endphp

    <div class="container-fluid">
        <div class="page-hero page-hero--accent mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="page-hero__eyebrow mb-2">Retour ERP</div>
                    <h1 class="page-hero__title">{{ $title }}</h1>
                    <p class="page-hero__subtitle">{{ $subtitle }}</p>
                </div>
                <a href="{{ route($createRoute) }}" class="btn btn-light px-4">
                    <i class="fas fa-plus mr-1"></i> Nouveau document
                </a>
            </div>
        </div>

        <div class="table-card">
            <div class="card-body p-0">
                <div class="d-flex flex-wrap align-items-center justify-content-between px-4 pt-4 pb-3">
                    <div>
                        <h4 class="mb-1">{{ $title }}</h4>
                        <p class="mb-0 text-muted">Retours tenant-safe, statuts lisibles et accès rapide au détail.</p>
                    </div>
                    <span class="status-pill status-pill--info">
                        <i class="fas fa-clipboard-list"></i> {{ $records->total() }} document(s)
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table data-table">
                        <thead>
                            <tr>
                                <th>Numéro</th>
                                <th>Partenaire</th>
                                <th>{{ $sourceLabel }}</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $record)
                                @php $state = $badge($record->status); @endphp
                                <tr>
                                    <td>
                                        <div class="font-weight-bold text-primary">{{ $record->return_number }}</div>
                                        <div class="text-muted small">#{{ $record->id }}</div>
                                    </td>
                                    <td>{{ $record->contact?->fullname ?? 'N/A' }}</td>
                                    <td>
                                        @if($isCustomer)
                                            {{ $record->invoice?->invoice_number ?? $record->deliveryNote?->delivery_number ?? 'N/A' }}
                                        @else
                                            {{ $record->supplierInvoice?->invoice_number ?? $record->goodsReceipt?->receipt_number ?? 'N/A' }}
                                        @endif
                                    </td>
                                    <td>
                                        <div class="font-weight-semibold">{{ optional($record->return_date)->format('d/m/Y') }}</div>
                                        <div class="text-muted small">{{ $isCustomer ? 'Retour client' : 'Retour fournisseur' }}</div>
                                    </td>
                                    <td>
                                        <span class="status-pill status-pill--{{ $state['class'] }}">
                                            <i class="fas fa-{{ $state['icon'] }}"></i>{{ $state['label'] }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                            <a href="{{ route($showRoute, $record) }}" class="btn btn-sm btn-primary">Voir</a>
                                            <a href="{{ route($isCustomer ? 'customer-returns.print' : 'supplier-returns.print', $record) }}" class="btn btn-sm btn-outline-secondary">Imprimer</a>

                                            @if ($record->status === 'draft')
                                                <a href="{{ route($isCustomer ? 'customer-returns.edit' : 'supplier-returns.edit', $record) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                                                <form action="{{ route($isCustomer ? 'customer-returns.validate' : 'supplier-returns.validate', $record) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">Valider</button>
                                                </form>
                                            @elseif ($record->status === 'validated')
                                                <a href="{{ route('movements.index', ['movement_type' => $movementType]) }}" class="btn btn-sm btn-outline-primary">Mouvements</a>
                                                <form action="{{ route($isCustomer ? 'customer-returns.cancel' : 'supplier-returns.cancel', $record) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirmer l’annulation ?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Annuler</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state my-4">
                                            <div class="empty-state__icon"><i class="fas fa-undo"></i></div>
                                            <div class="font-weight-bold mb-1">{{ $emptyMessage }}</div>
                                            <div>Créez votre premier retour pour réintégrer ou sortir du stock.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3">
            {{ $records->links() }}
        </div>
    </div>
@endsection
