@extends('back.layouts.admin')

@section('content')
    @php
        $isCustomer = ($module ?? 'customer') === 'customer';
        $status = $record->status ?? 'draft';
        $statusMap = [
            'draft' => ['label' => 'Brouillon', 'class' => 'neutral', 'icon' => 'pen'],
            'validated' => ['label' => 'Validé', 'class' => 'success', 'icon' => 'check-circle'],
            'cancelled' => ['label' => 'Annulé', 'class' => 'danger', 'icon' => 'ban'],
        ];
        $state = $statusMap[$status] ?? ['label' => ucfirst($status), 'class' => 'info', 'icon' => 'circle-info'];
        $canEdit = $status === 'draft';
        $canCancel = in_array($status, ['draft', 'validated'], true);
        $printRoute = $isCustomer ? 'customer-returns.print' : 'supplier-returns.print';
        $editRoute = $isCustomer ? 'customer-returns.edit' : 'supplier-returns.edit';
        $validateRoute = $isCustomer ? 'customer-returns.validate' : 'supplier-returns.validate';
        $cancelRoute = $isCustomer ? 'customer-returns.cancel' : 'supplier-returns.cancel';
        $indexRoute = $isCustomer ? 'customer-returns.index' : 'supplier-returns.index';
        $creditNoteRoute = $record->creditNote
            ? route($isCustomer ? 'customer-credit-notes.show' : 'supplier-credit-notes.show', $record->creditNote)
            : null;
        $sourceRoute = $isCustomer
            ? ($record->invoice ? route('invoices.show', ['type' => 'clients', 'invoice' => $record->invoice]) : ($record->deliveryNote ? route('delivery-notes.show', $record->deliveryNote) : route($indexRoute)))
            : ($record->supplierInvoice ? route('invoices.show', ['type' => 'suppliers', 'invoice' => $record->supplierInvoice]) : ($record->goodsReceipt ? route('goods-receipts.show', $record->goodsReceipt) : route($indexRoute)));
        $movementType = $isCustomer ? 'customer_return_in' : 'supplier_return_out';

        if ($status === 'draft') {
            $primaryActions = [
                ['label' => 'Valider', 'icon' => 'check', 'type' => 'post', 'url' => route($validateRoute, $record), 'variant' => 'success'],
                ['label' => 'Imprimer / Télécharger', 'icon' => 'print', 'type' => 'link', 'url' => route($printRoute, $record), 'variant' => 'outline-primary'],
                ['label' => 'Voir source', 'icon' => 'link', 'type' => 'link', 'url' => $sourceRoute, 'variant' => 'outline-secondary'],
            ];
            $secondaryActions = [];

            if ($canEdit) {
                $secondaryActions[] = ['label' => 'Modifier', 'icon' => 'pen', 'type' => 'link', 'url' => route($editRoute, $record), 'danger' => false];
            }

            if ($canCancel) {
                $secondaryActions[] = ['label' => 'Annuler', 'icon' => 'ban', 'type' => 'post', 'url' => route($cancelRoute, $record), 'danger' => true, 'confirm' => 'Confirmer l’annulation ?'];
            }
        } elseif ($status === 'validated') {
            $primaryActions = [
                ['label' => 'Voir mouvements de stock', 'icon' => 'exchange-alt', 'type' => 'link', 'url' => route('movements.index', ['movement_type' => $movementType]), 'variant' => 'primary'],
                ['label' => 'Imprimer / Télécharger', 'icon' => 'print', 'type' => 'link', 'url' => route($printRoute, $record), 'variant' => 'outline-primary'],
                ['label' => 'Voir source', 'icon' => 'link', 'type' => 'link', 'url' => $sourceRoute, 'variant' => 'outline-secondary'],
            ];
            if ($creditNoteRoute) {
                array_unshift($primaryActions, ['label' => $isCustomer ? 'Voir avoir client' : 'Voir avoir fournisseur', 'icon' => 'file-invoice-dollar', 'type' => 'link', 'url' => $creditNoteRoute, 'variant' => 'success']);
            }
            $secondaryActions = $canCancel
                ? [['label' => 'Annuler', 'icon' => 'ban', 'type' => 'post', 'url' => route($cancelRoute, $record), 'danger' => true, 'confirm' => 'Confirmer l’annulation ?']]
                : [];
        } else {
            $primaryActions = [
                ['label' => 'Imprimer / Télécharger', 'icon' => 'print', 'type' => 'link', 'url' => route($printRoute, $record), 'variant' => 'primary'],
                ['label' => 'Voir source', 'icon' => 'link', 'type' => 'link', 'url' => $sourceRoute, 'variant' => 'outline-secondary'],
            ];
            $secondaryActions = [];
        }
    @endphp

    <div class="container-fluid">
        <div class="page-hero page-hero--accent mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="page-hero__eyebrow mb-2">Retour {{ $isCustomer ? 'client' : 'fournisseur' }}</div>
                    <h1 class="page-hero__title mb-2">{{ $record->return_number }}</h1>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="status-pill status-pill--{{ $state['class'] }}">
                            <i class="fas fa-{{ $state['icon'] }}"></i>{{ $state['label'] }}
                        </span>
                        <span class="status-pill status-pill--info">{{ $record->contact?->fullname ?? 'N/A' }}</span>
                        <span class="status-pill status-pill--neutral">{{ optional($record->return_date)->format('d/m/Y') }}</span>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="status-pill status-pill--neutral">
                        <i class="fas fa-bolt"></i> Actions adaptées au statut
                    </span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card table-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h4 class="mb-1">Lignes du retour</h4>
                                <p class="mb-0 text-muted">Référence source, quantités et impact stock.</p>
                            </div>
                            <span class="status-pill status-pill--info">{{ $items->count() }} ligne(s)</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table data-table">
                                <thead>
                                    <tr>
                                        <th>Produit</th>
                                        <th>Référence source</th>
                                        <th class="text-right">Qté vendue / reçue</th>
                                        <th class="text-right">Qté retournée</th>
                                        <th class="text-right">PU HT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $item)
                                        <tr>
                                            <td>
                                                <div class="font-weight-bold">{{ $item->product?->name ?? 'Produit' }}</div>
                                                <div class="text-muted small">Ligne {{ $isCustomer ? 'client' : 'fournisseur' }}</div>
                                            </td>
                                            <td class="font-weight-semibold">{{ $item->invoiceItem?->invoice?->invoice_number ?? $item->deliveryNoteItem?->deliveryNote?->delivery_number ?? $item->goodsReceiptItem?->goodsReceipt?->receipt_number ?? 'N/A' }}</td>
                                            <td class="text-right">{{ $isCustomer ? $item->quantity_sold : $item->quantity_received }}</td>
                                            <td class="text-right">{{ $item->quantity_returned }}</td>
                                            <td class="text-right">{{ number_format((int) ($item->unit_price_ht ?? $item->unit_cost_ht ?? 0), 0, ',', ' ') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5">
                                                <div class="empty-state my-4">
                                                    <div class="empty-state__icon"><i class="fas fa-box-open"></i></div>
                                                    <div class="font-weight-bold mb-1">Aucune ligne disponible</div>
                                                    <div>Le document n’a pas encore de lignes exploitables.</div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card table-card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h4 class="mb-1">Actions</h4>
                                <p class="mb-0 text-muted">Commandes disponibles selon le statut du retour.</p>
                            </div>
                            <span class="status-pill status-pill--{{ $state['class'] }}">
                                <i class="fas fa-{{ $state['icon'] }}"></i>{{ $state['label'] }}
                            </span>
                        </div>

                        <div class="d-flex flex-column gap-2">
                            @foreach ($primaryActions as $action)
                                @if ($action['type'] === 'link')
                                    <a href="{{ $action['url'] }}" class="btn btn-{{ $action['variant'] ?? 'outline-secondary' }} btn-block text-left">
                                        <i class="fas fa-{{ $action['icon'] }} mr-1"></i>{{ $action['label'] }}
                                    </a>
                                @else
                                    <form action="{{ $action['url'] }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-{{ $action['variant'] ?? 'primary' }} btn-block text-left">
                                            <i class="fas fa-{{ $action['icon'] }} mr-1"></i>{{ $action['label'] }}
                                        </button>
                                    </form>
                                @endif
                            @endforeach
                        </div>

                        @if (!empty($secondaryActions))
                            <div class="mt-4">
                                <div class="text-uppercase small font-weight-bold text-muted mb-2">Plus</div>
                                <div class="d-flex flex-column gap-2">
                                    @foreach ($secondaryActions as $action)
                                        @if ($action['type'] === 'link')
                                            <a href="{{ $action['url'] }}" class="btn btn-outline-secondary btn-block text-left">
                                                <i class="fas fa-{{ $action['icon'] }} mr-1"></i>{{ $action['label'] }}
                                            </a>
                                        @else
                                            <form action="{{ $action['url'] }}" method="POST" onsubmit="return confirm('{{ $action['confirm'] }}');">
                                                @csrf
                                                <button type="submit" class="btn btn-block text-left {{ !empty($action['danger']) ? 'btn-outline-danger' : 'btn-outline-secondary' }}">
                                                    <i class="fas fa-{{ $action['icon'] }} mr-1"></i>{{ $action['label'] }}
                                                </button>
                                            </form>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card table-card">
                    <div class="card-body">
                        <h4 class="mb-3">Résumé</h4>

                        <div class="info-row">
                            <div>
                                <div class="info-row__label">Document source</div>
                                <div class="info-row__value">
                                    @if($isCustomer)
                                        {{ $record->invoice?->invoice_number ?? $record->deliveryNote?->delivery_number ?? 'N/A' }}
                                    @else
                                        {{ $record->supplierInvoice?->invoice_number ?? $record->goodsReceipt?->receipt_number ?? 'N/A' }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div>
                                <div class="info-row__label">Entrepôt</div>
                                <div class="info-row__value">{{ $record->warehouse?->name ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div>
                                <div class="info-row__label">Impact financier</div>
                                <div class="info-row__value">
                                    @if($record->creditNote)
                                        @if($record->creditNote->applied_amount > 0)
                                            Avoir appliqué: {{ number_format($record->creditNote->applied_amount, 0, ',', ' ') }} FCFA
                                        @else
                                            Avoir disponible: {{ number_format($record->creditNote->total_ttc, 0, ',', ' ') }} FCFA
                                        @endif
                                    @else
                                        Aucun impact financier
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div>
                                <div class="info-row__label">Motif</div>
                                <div class="info-row__value">{{ $record->reason ?? 'Non renseigné' }}</div>
                            </div>
                        </div>

                        @if($record->notes)
                            <div class="alert alert-light border mt-3 mb-0">
                                {{ $record->notes }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
