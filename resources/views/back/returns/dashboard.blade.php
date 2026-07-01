@extends('back.layouts.admin')

@section('title', $title)

@section('content')
    @php
        $money = fn ($value) => number_format((int) $value, 0, ',', ' ') . ' FCFA';
        $returnStatus = fn ($status) => match ($status) {
            'draft' => ['label' => 'Brouillon', 'class' => 'neutral', 'icon' => 'pen'],
            'validated' => ['label' => 'Validé', 'class' => 'success', 'icon' => 'check-circle'],
            'cancelled' => ['label' => 'Annulé', 'class' => 'danger', 'icon' => 'ban'],
            default => ['label' => ucfirst((string) $status), 'class' => 'info', 'icon' => 'circle-info'],
        };
        $creditStatus = fn ($status) => match ($status) {
            'draft' => ['label' => 'Brouillon', 'class' => 'neutral', 'icon' => 'pen'],
            'validated' => ['label' => 'Validé', 'class' => 'info', 'icon' => 'check-circle'],
            'applied' => ['label' => 'Appliqué', 'class' => 'success', 'icon' => 'file-invoice-dollar'],
            'partially_applied' => ['label' => 'Partiel', 'class' => 'warning', 'icon' => 'file-invoice-dollar'],
            'refunded' => ['label' => 'Remboursé', 'class' => 'success', 'icon' => 'money-bill-wave'],
            'cancelled' => ['label' => 'Annulé', 'class' => 'danger', 'icon' => 'ban'],
            default => ['label' => ucfirst((string) $status), 'class' => 'info', 'icon' => 'circle-info'],
        };
        $summary = $summary ?? [];
    @endphp

    <div class="container-fluid">
        <div class="page-hero page-hero--accent mb-4">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                <div>
                    <div class="page-hero__eyebrow mb-2">Retours / Avoirs</div>
                    <h1 class="page-hero__title">{{ $title }}</h1>
                    <p class="page-hero__subtitle mb-0">
                        Vue synthétique des retours clients et fournisseurs, avec les avoirs générés et les derniers documents traités.
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @can('read_customer_returns')
                        <a href="{{ route('customer-returns.index') }}" class="btn btn-light">
                            <i class="fas fa-undo mr-1"></i> Retours clients
                        </a>
                    @endcan
                    @can('read_supplier_returns')
                        <a href="{{ route('supplier-returns.index') }}" class="btn btn-outline-light">
                            <i class="fas fa-undo-alt mr-1"></i> Retours fournisseurs
                        </a>
                    @endcan
                    @can('manage_client_invoices')
                        <a href="{{ route('customer-credit-notes.index') }}" class="btn btn-outline-light">
                            <i class="fas fa-file-invoice-dollar mr-1"></i> Avoirs clients
                        </a>
                    @endcan
                    @can('manage_supplier_invoices')
                        <a href="{{ route('supplier-credit-notes.index') }}" class="btn btn-outline-light">
                            <i class="fas fa-receipt mr-1"></i> Avoirs fournisseurs
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="metric-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="metric-card__icon" style="background: linear-gradient(135deg, #0ea5e9, #2563eb);">
                            <i class="fas fa-undo"></i>
                        </div>
                        <span class="status-pill status-pill--info"><i class="fas fa-store"></i> Clients</span>
                    </div>
                    <div class="metric-card__label mb-1">Retours clients</div>
                    <div class="metric-card__value">{{ $summary['customer_returns'] ?? 0 }}</div>
                    <div class="metric-card__meta mt-2">Documents saisis côté vente</div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="metric-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="metric-card__icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                            <i class="fas fa-undo-alt"></i>
                        </div>
                        <span class="status-pill status-pill--warning"><i class="fas fa-truck"></i> Fournisseurs</span>
                    </div>
                    <div class="metric-card__label mb-1">Retours fournisseurs</div>
                    <div class="metric-card__value">{{ $summary['supplier_returns'] ?? 0 }}</div>
                    <div class="metric-card__meta mt-2">Documents saisis côté achat</div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="metric-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="metric-card__icon" style="background: linear-gradient(135deg, #16a34a, #059669);">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <span class="status-pill status-pill--success"><i class="fas fa-arrow-down"></i> Clients</span>
                    </div>
                    <div class="metric-card__label mb-1">Avoirs clients</div>
                    <div class="metric-card__value">{{ $summary['customer_credits'] ?? 0 }}</div>
                    <div class="metric-card__meta mt-2">{{ $money($summary['customer_credit_value'] ?? 0) }} cumulés</div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="metric-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="metric-card__icon" style="background: linear-gradient(135deg, #7c3aed, #4f46e5);">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <span class="status-pill status-pill--neutral"><i class="fas fa-arrow-up"></i> Fournisseurs</span>
                    </div>
                    <div class="metric-card__label mb-1">Avoirs fournisseurs</div>
                    <div class="metric-card__value">{{ $summary['supplier_credits'] ?? 0 }}</div>
                    <div class="metric-card__meta mt-2">{{ $money($summary['supplier_credit_value'] ?? 0) }} cumulés</div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-lg-6 mb-3 mb-lg-0">
                <div class="table-card h-100">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                            <div>
                                <h4 class="mb-1">Synthèse des statuts</h4>
                                <p class="mb-0 text-muted">Vue rapide des documents encore ouverts ou finalisés.</p>
                            </div>
                            <span class="status-pill status-pill--info">
                                <i class="fas fa-layer-group"></i> {{ ($summary['validated_returns'] ?? 0) + ($summary['draft_returns'] ?? 0) }} retours
                            </span>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="metric-card" style="border-radius: 18px;">
                                    <div class="metric-card__label mb-1">Retours validés</div>
                                    <div class="metric-card__value">{{ $summary['validated_returns'] ?? 0 }}</div>
                                    <div class="metric-card__meta mt-2">Documents prêts pour les mouvements et avoirs.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="metric-card" style="border-radius: 18px;">
                                    <div class="metric-card__label mb-1">Brouillons</div>
                                    <div class="metric-card__value">{{ $summary['draft_returns'] ?? 0 }}</div>
                                    <div class="metric-card__meta mt-2">Documents en attente de validation.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="table-card h-100">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                            <div>
                                <h4 class="mb-1">Accès rapides</h4>
                                <p class="mb-0 text-muted">Aller directement aux écrans de travail les plus utilisés.</p>
                            </div>
                        </div>

                        <div class="row">
                            @can('create_customer_returns')
                                <div class="col-sm-6 mb-3">
                                    <a href="{{ route('customer-returns.create') }}" class="quick-action h-100">
                                        <span class="quick-action__icon"><i class="fas fa-plus-circle"></i></span>
                                        <span>
                                            <span class="quick-action__title d-block">Nouveau retour client</span>
                                            <span class="quick-action__meta">Depuis facture ou BL</span>
                                        </span>
                                    </a>
                                </div>
                            @endcan
                            @can('create_supplier_returns')
                                <div class="col-sm-6 mb-3">
                                    <a href="{{ route('supplier-returns.create') }}" class="quick-action h-100">
                                        <span class="quick-action__icon"><i class="fas fa-plus-circle"></i></span>
                                        <span>
                                            <span class="quick-action__title d-block">Nouveau retour fournisseur</span>
                                            <span class="quick-action__meta">Depuis facture ou réception</span>
                                        </span>
                                    </a>
                                </div>
                            @endcan
                            @can('manage_client_invoices')
                                <div class="col-sm-6 mb-3">
                                    <a href="{{ route('customer-credit-notes.index') }}" class="quick-action h-100">
                                        <span class="quick-action__icon"><i class="fas fa-file-invoice-dollar"></i></span>
                                        <span>
                                            <span class="quick-action__title d-block">Voir les avoirs clients</span>
                                            <span class="quick-action__meta">Suivi remboursements</span>
                                        </span>
                                    </a>
                                </div>
                            @endcan
                            @can('manage_supplier_invoices')
                                <div class="col-sm-6 mb-3">
                                    <a href="{{ route('supplier-credit-notes.index') }}" class="quick-action h-100">
                                        <span class="quick-action__icon"><i class="fas fa-receipt"></i></span>
                                        <span>
                                            <span class="quick-action__title d-block">Voir les avoirs fournisseurs</span>
                                            <span class="quick-action__meta">Crédits en attente</span>
                                        </span>
                                    </a>
                                </div>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-6 mb-4">
                <div class="table-card h-100">
                    <div class="card-body p-0">
                        <div class="d-flex align-items-center justify-content-between px-4 pt-4 pb-3">
                            <div>
                                <h4 class="mb-1">Retours clients récents</h4>
                                <p class="mb-0 text-muted">Les derniers bons de retour saisis côté ventes.</p>
                            </div>
                            @can('read_customer_returns')
                                <a href="{{ route('customer-returns.index') }}" class="btn btn-outline-primary btn-sm">Voir tout</a>
                            @endcan
                        </div>

                        <div class="table-responsive">
                            <table class="table data-table">
                                <thead>
                                    <tr>
                                        <th>Numéro</th>
                                        <th>Client</th>
                                        <th>Source</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($recentCustomerReturns as $record)
                                        @php $state = $returnStatus($record->status); @endphp
                                        <tr>
                                            <td class="font-weight-bold text-primary">{{ $record->return_number }}</td>
                                            <td>{{ $record->contact?->fullname ?? 'N/A' }}</td>
                                            <td>{{ $record->invoice?->invoice_number ?? $record->deliveryNote?->delivery_number ?? 'N/A' }}</td>
                                            <td>{{ optional($record->return_date)->format('d/m/Y') }}</td>
                                            <td>
                                                <span class="status-pill status-pill--{{ $state['class'] }}">
                                                    <i class="fas fa-{{ $state['icon'] }}"></i>{{ $state['label'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5">
                                                <div class="empty-state my-4">
                                                    <div class="empty-state__icon"><i class="fas fa-undo"></i></div>
                                                    <div class="font-weight-bold mb-1">Aucun retour client</div>
                                                    <div class="text-muted">Les nouveaux documents apparaîtront ici.</div>
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

            <div class="col-xl-6 mb-4">
                <div class="table-card h-100">
                    <div class="card-body p-0">
                        <div class="d-flex align-items-center justify-content-between px-4 pt-4 pb-3">
                            <div>
                                <h4 class="mb-1">Retours fournisseurs récents</h4>
                                <p class="mb-0 text-muted">Les derniers bons de retour saisis côté achats.</p>
                            </div>
                            @can('read_supplier_returns')
                                <a href="{{ route('supplier-returns.index') }}" class="btn btn-outline-primary btn-sm">Voir tout</a>
                            @endcan
                        </div>

                        <div class="table-responsive">
                            <table class="table data-table">
                                <thead>
                                    <tr>
                                        <th>Numéro</th>
                                        <th>Fournisseur</th>
                                        <th>Source</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($recentSupplierReturns as $record)
                                        @php $state = $returnStatus($record->status); @endphp
                                        <tr>
                                            <td class="font-weight-bold text-primary">{{ $record->return_number }}</td>
                                            <td>{{ $record->contact?->fullname ?? 'N/A' }}</td>
                                            <td>{{ $record->supplierInvoice?->invoice_number ?? $record->goodsReceipt?->receipt_number ?? 'N/A' }}</td>
                                            <td>{{ optional($record->return_date)->format('d/m/Y') }}</td>
                                            <td>
                                                <span class="status-pill status-pill--{{ $state['class'] }}">
                                                    <i class="fas fa-{{ $state['icon'] }}"></i>{{ $state['label'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5">
                                                <div class="empty-state my-4">
                                                    <div class="empty-state__icon"><i class="fas fa-undo-alt"></i></div>
                                                    <div class="font-weight-bold mb-1">Aucun retour fournisseur</div>
                                                    <div class="text-muted">Les nouveaux documents apparaîtront ici.</div>
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

            <div class="col-xl-6 mb-4">
                <div class="table-card h-100">
                    <div class="card-body p-0">
                        <div class="d-flex align-items-center justify-content-between px-4 pt-4 pb-3">
                            <div>
                                <h4 class="mb-1">Avoirs clients récents</h4>
                                <p class="mb-0 text-muted">Avoirs générés après validation des retours clients.</p>
                            </div>
                            @can('manage_client_invoices')
                                <a href="{{ route('customer-credit-notes.index') }}" class="btn btn-outline-primary btn-sm">Voir tout</a>
                            @endcan
                        </div>

                        <div class="table-responsive">
                            <table class="table data-table">
                                <thead>
                                    <tr>
                                        <th>Numéro</th>
                                        <th>Client</th>
                                        <th>Source</th>
                                        <th class="text-right">Montant</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($recentCustomerCredits as $record)
                                        @php $state = $creditStatus($record->status); @endphp
                                        <tr>
                                            <td class="font-weight-bold text-primary">{{ $record->credit_note_number }}</td>
                                            <td>{{ $record->contact?->fullname ?? 'N/A' }}</td>
                                            <td>{{ $record->invoice?->invoice_number ?? $record->customerReturn?->return_number ?? 'N/A' }}</td>
                                            <td class="text-right">{{ $money($record->total_ttc) }}</td>
                                            <td>
                                                <span class="status-pill status-pill--{{ $state['class'] }}">
                                                    <i class="fas fa-{{ $state['icon'] }}"></i>{{ $state['label'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5">
                                                <div class="empty-state my-4">
                                                    <div class="empty-state__icon"><i class="fas fa-file-invoice-dollar"></i></div>
                                                    <div class="font-weight-bold mb-1">Aucun avoir client</div>
                                                    <div class="text-muted">Les avoirs apparaîtront ici après validation.</div>
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

            <div class="col-xl-6 mb-4">
                <div class="table-card h-100">
                    <div class="card-body p-0">
                        <div class="d-flex align-items-center justify-content-between px-4 pt-4 pb-3">
                            <div>
                                <h4 class="mb-1">Avoirs fournisseurs récents</h4>
                                <p class="mb-0 text-muted">Avoirs générés après validation des retours fournisseurs.</p>
                            </div>
                            @can('manage_supplier_invoices')
                                <a href="{{ route('supplier-credit-notes.index') }}" class="btn btn-outline-primary btn-sm">Voir tout</a>
                            @endcan
                        </div>

                        <div class="table-responsive">
                            <table class="table data-table">
                                <thead>
                                    <tr>
                                        <th>Numéro</th>
                                        <th>Fournisseur</th>
                                        <th>Source</th>
                                        <th class="text-right">Montant</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($recentSupplierCredits as $record)
                                        @php $state = $creditStatus($record->status); @endphp
                                        <tr>
                                            <td class="font-weight-bold text-primary">{{ $record->credit_note_number }}</td>
                                            <td>{{ $record->contact?->fullname ?? 'N/A' }}</td>
                                            <td>{{ $record->invoice?->invoice_number ?? $record->supplierReturn?->return_number ?? 'N/A' }}</td>
                                            <td class="text-right">{{ $money($record->total_ttc) }}</td>
                                            <td>
                                                <span class="status-pill status-pill--{{ $state['class'] }}">
                                                    <i class="fas fa-{{ $state['icon'] }}"></i>{{ $state['label'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5">
                                                <div class="empty-state my-4">
                                                    <div class="empty-state__icon"><i class="fas fa-receipt"></i></div>
                                                    <div class="font-weight-bold mb-1">Aucun avoir fournisseur</div>
                                                    <div class="text-muted">Les avoirs apparaîtront ici après validation.</div>
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
        </div>
    </div>
@endsection
