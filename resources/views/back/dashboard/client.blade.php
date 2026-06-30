@extends('back.layouts.admin')

@section('content')
    @php
        $money = fn ($value) => number_format((int) $value, 0, ',', ' ') . ' FCFA';
        $sales = (int) ($stats['invoices']->total_ventes ?? 0);
        $payments = (int) ($stats['paiements']->total_paiement_clients ?? 0);
        $unpaid = (int) ($stats['balance_clients'] ?? 0);
        $lowStockCount = collect($lowStockProducts ?? [])->count();
        $dateRange = 'Du ' . optional($stats['start'])->format('d/m/Y') . ' au ' . optional($stats['end'])->format('d/m/Y');

        $invoiceBadge = function ($status) {
            return match ($status) {
                'paid' => ['class' => 'success', 'label' => 'Payée', 'icon' => 'check-circle'],
                'partial' => ['class' => 'warning', 'label' => 'Partielle', 'icon' => 'hourglass-half'],
                'draft' => ['class' => 'neutral', 'label' => 'Brouillon', 'icon' => 'pen'],
                'cancelled' => ['class' => 'danger', 'label' => 'Annulée', 'icon' => 'times-circle'],
                default => ['class' => 'info', 'label' => 'Validée', 'icon' => 'badge-check'],
            };
        };
    @endphp

    @can('access_dashboard')
        <div class="container-fluid">
            <div class="page-hero page-hero--accent mb-4">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                    <div class="mr-4">
                        <div class="page-hero__eyebrow mb-2">StockPro SaaS</div>
                        <h1 class="page-hero__title">Tableau de bord opérationnel</h1>
                        <p class="page-hero__subtitle">Vue claire des ventes, paiements, impayés et alertes stock sur la période sélectionnée.</p>
                        <div class="mt-3 d-flex flex-wrap align-items-center gap-2">
                            <span class="badge badge-light px-3 py-2">{{ $dateRange }}</span>
                            <span class="badge badge-light px-3 py-2">
                                <i class="fas fa-store mr-1"></i>{{ $stats['counts']->nb_clients ?? 0 }} clients actifs
                            </span>
                            <span class="badge badge-light px-3 py-2">
                                <i class="fas fa-truck mr-1"></i>{{ $stats['counts']->nb_fournisseurs ?? 0 }} fournisseurs
                            </span>
                            <span class="badge badge-light px-3 py-2">
                                <i class="fas fa-boxes mr-1"></i>{{ $stats['nbProduits'] ?? 0 }} produits
                            </span>
                        </div>
                    </div>

                    <form method="GET" class="panel-card p-3 bg-white text-dark" style="min-width: 320px; max-width: 420px;">
                        <div class="form-group mb-3">
                            <label class="small text-uppercase font-weight-bold text-muted mb-2">Période</label>
                            <select name="period" class="form-control" onchange="this.form.submit()">
                                <option value="lastMonth" {{ $period == 'lastMonth' ? 'selected' : '' }}>Mois précédent</option>
                                <option value="day" {{ $period == 'day' ? 'selected' : '' }}>Aujourd'hui</option>
                                <option value="week" {{ $period == 'week' ? 'selected' : '' }}>Cette semaine</option>
                                <option value="month" {{ $period == 'month' ? 'selected' : '' }}>Ce mois-ci</option>
                                <option value="year" {{ $period == 'year' ? 'selected' : '' }}>Cette année</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="form-group mb-0">
                                    <label class="small text-uppercase font-weight-bold text-muted mb-2">Du</label>
                                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group mb-0">
                                    <label class="small text-uppercase font-weight-bold text-muted mb-2">Au</label>
                                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between mt-3">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-filter mr-1"></i> Appliquer
                            </button>
                            @if (request('start_date') || request('end_date') || request('period') !== 'month')
                                <a href="{{ route('dashboard') }}" class="btn btn-link text-muted">Réinitialiser</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="metric-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="metric-card__icon" style="background: linear-gradient(135deg, #1d4ed8, #2563eb);">
                                <i class="fas fa-coins"></i>
                            </div>
                            <span class="status-pill status-pill--success"><i class="fas fa-arrow-up"></i>CA</span>
                        </div>
                        <div class="metric-card__label mb-1">Chiffre d'affaires</div>
                        <div class="metric-card__value">{{ $money($sales) }}</div>
                        <div class="metric-card__meta mt-2">
                            {{ number_format((int) ($stats['invoices']->nb_factures_clients ?? 0)) }} ventes sur la période
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="metric-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="metric-card__icon" style="background: linear-gradient(135deg, #16a34a, #059669);">
                                <i class="fas fa-hand-holding-dollar"></i>
                            </div>
                            <span class="status-pill status-pill--info"><i class="fas fa-check"></i>Entrées</span>
                        </div>
                        <div class="metric-card__label mb-1">Paiements reçus</div>
                        <div class="metric-card__value">{{ $money($payments) }}</div>
                        <div class="metric-card__meta mt-2">
                            Encaissements clients confirmés
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="metric-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="metric-card__icon" style="background: linear-gradient(135deg, #d97706, #f59e0b);">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <span class="status-pill status-pill--warning"><i class="fas fa-clock"></i>À suivre</span>
                        </div>
                        <div class="metric-card__label mb-1">Factures impayées</div>
                        <div class="metric-card__value">{{ $money($unpaid) }}</div>
                        <div class="metric-card__meta mt-2">
                            Total des balances clients ouvertes
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="metric-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="metric-card__icon" style="background: linear-gradient(135deg, #dc2626, #b91c1c);">
                                <i class="fas fa-triangle-exclamation"></i>
                            </div>
                            <span class="status-pill status-pill--danger"><i class="fas fa-box-open"></i>Stock</span>
                        </div>
                        <div class="metric-card__label mb-1">Produits sous alerte</div>
                        <div class="metric-card__value">{{ $lowStockCount }}</div>
                        <div class="metric-card__meta mt-2">
                            Produits à réapprovisionner rapidement
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="panel-card p-3 p-lg-4">
                        <div class="section-title">
                            <div>
                                <h3>Actions rapides</h3>
                                <p>Les raccourcis les plus utilisés par un commerce ou une PME.</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 col-lg-2 mb-3">
                                <a class="quick-action h-100" href="{{ route('quotes.create') }}">
                                    <span class="quick-action__icon"><i class="fas fa-file-signature"></i></span>
                                    <span>
                                        <span class="quick-action__title d-block">Nouveau devis</span>
                                        <span class="quick-action__meta">Proforma client</span>
                                    </span>
                                </a>
                            </div>
                            <div class="col-md-4 col-lg-2 mb-3">
                                <a class="quick-action h-100" href="{{ route('products.create') }}">
                                    <span class="quick-action__icon"><i class="fas fa-box"></i></span>
                                    <span>
                                        <span class="quick-action__title d-block">Nouveau produit</span>
                                        <span class="quick-action__meta">Catalogue stock</span>
                                    </span>
                                </a>
                            </div>
                            <div class="col-md-4 col-lg-2 mb-3">
                                <a class="quick-action h-100" href="{{ route('inventories.index') }}">
                                    <span class="quick-action__icon"><i class="fas fa-clipboard-list"></i></span>
                                    <span>
                                        <span class="quick-action__title d-block">Inventaire</span>
                                        <span class="quick-action__meta">Contrôle physique</span>
                                    </span>
                                </a>
                            </div>
                            <div class="col-md-4 col-lg-2 mb-3">
                                <a class="quick-action h-100" href="{{ route('purchase-orders.create') }}">
                                    <span class="quick-action__icon"><i class="fas fa-cart-shopping"></i></span>
                                    <span>
                                        <span class="quick-action__title d-block">Commande achat</span>
                                        <span class="quick-action__meta">Réapprovisionnement</span>
                                    </span>
                                </a>
                            </div>
                            <div class="col-md-4 col-lg-2 mb-3">
                                <a class="quick-action h-100" href="{{ route('reports.index') }}">
                                    <span class="quick-action__icon"><i class="fas fa-chart-line"></i></span>
                                    <span>
                                        <span class="quick-action__title d-block">Rapports</span>
                                        <span class="quick-action__meta">Pilotage métier</span>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="table-card">
                        <div class="card-body p-0">
                            <div class="d-flex align-items-center justify-content-between px-4 pt-4 pb-3">
                                <div>
                                    <h4 class="mb-1">Dernières factures</h4>
                                    <p class="mb-0">Suivi des ventes et des achats récents.</p>
                                </div>
                                <a href="{{ route('invoices.index', ['type' => 'clients']) }}" class="btn btn-outline-primary btn-sm">
                                    Voir toutes
                                </a>
                            </div>

                            <div class="table-responsive">
                                <table class="table data-table">
                                    <thead>
                                        <tr>
                                            <th>N°</th>
                                            <th>Client / Fournisseur</th>
                                            <th>Date</th>
                                            <th>Statut</th>
                                            <th class="text-right">Montant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($dernieresFactures as $invoice)
                                            @php $badge = $invoiceBadge($invoice->status); @endphp
                                            <tr>
                                                <td class="font-weight-bold text-primary">
                                                    <a href="{{ route('invoices.show', ['type' => $invoice->contact_type === 'supplier' ? 'suppliers' : 'clients', 'invoice' => $invoice->id]) }}">
                                                        {{ $invoice->invoice_number }}
                                                    </a>
                                                </td>
                                                <td>{{ $invoice->client }}</td>
                                                <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</td>
                                                <td>
                                                    <span class="status-pill status-pill--{{ $badge['class'] }}">
                                                        <i class="fas fa-{{ $badge['icon'] }}"></i>{{ $badge['label'] }}
                                                    </span>
                                                </td>
                                                <td class="text-right font-weight-bold">{{ $money($invoice->total_invoice) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5">
                                                    <div class="empty-state my-3">
                                                        <div class="empty-state__icon"><i class="fas fa-file-invoice"></i></div>
                                                        <div class="font-weight-bold mb-1">Aucune facture sur cette période</div>
                                                        <div>Les factures apparaîtront ici dès qu’une vente ou un achat est validé.</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="table-card mt-4">
                        <div class="card-body p-0">
                            <div class="d-flex align-items-center justify-content-between px-4 pt-4 pb-3">
                                <div>
                                    <h4 class="mb-1">Derniers paiements</h4>
                                    <p class="mb-0">Vue rapide sur les encaissements et sorties de trésorerie.</p>
                                </div>
                                <a href="{{ route('payments.index', ['type' => 'clients']) }}" class="btn btn-outline-primary btn-sm">
                                    Voir paiements
                                </a>
                            </div>

                            <div class="table-responsive">
                                <table class="table data-table">
                                    <thead>
                                        <tr>
                                            <th>Contact</th>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th class="text-right">Montant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($derniersPaiements as $payment)
                                            <tr>
                                                <td>{{ $payment->client }}</td>
                                                <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</td>
                                                <td>
                                                    <span class="status-pill status-pill--{{ $payment->contact_type === 'supplier' ? 'warning' : 'success' }}">
                                                        {{ $payment->contact_type === 'supplier' ? 'Fournisseur' : 'Client' }}
                                                    </span>
                                                </td>
                                                <td class="text-right font-weight-bold">{{ $money($payment->amount_paid) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4">
                                                    <div class="empty-state my-3">
                                                        <div class="empty-state__icon"><i class="fas fa-wallet"></i></div>
                                                        <div class="font-weight-bold mb-1">Aucun paiement trouvé</div>
                                                        <div>Les paiements reçus ou effectués seront visibles ici.</div>
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
                    <div class="panel-card p-4 mb-4">
                        <div class="section-title">
                            <div>
                                <h4>Balance et activité</h4>
                                <p>Indicateurs simples pour suivre le rythme de l’entreprise.</p>
                            </div>
                        </div>

                        <div class="info-row">
                            <div>
                                <div class="info-row__label">Balance clients</div>
                                <div class="info-row__value">{{ $money($stats['balance_clients'] ?? 0) }}</div>
                            </div>
                            <span class="status-pill status-pill--warning">À encaisser</span>
                        </div>
                        <div class="info-row">
                            <div>
                                <div class="info-row__label">Balance fournisseurs</div>
                                <div class="info-row__value">{{ $money($stats['balance_fournisseurs'] ?? 0) }}</div>
                            </div>
                            <span class="status-pill status-pill--danger">À payer</span>
                        </div>
                        <div class="info-row">
                            <div>
                                <div class="info-row__label">Bénéfice brut</div>
                                <div class="info-row__value">{{ $money($stats['benefice'] ?? 0) }}</div>
                            </div>
                            <span class="status-pill status-pill--success">Marge</span>
                        </div>
                        <div class="info-row">
                            <div>
                                <div class="info-row__label">Dépenses</div>
                                <div class="info-row__value">{{ $money($stats['depenses'] ?? 0) }}</div>
                            </div>
                            <span class="status-pill status-pill--neutral">Charges</span>
                        </div>
                    </div>

                    <div class="panel-card p-4">
                        <div class="section-title">
                            <div>
                                <h4>Alertes stock</h4>
                                <p>Les produits à surveiller en priorité.</p>
                            </div>
                            <span class="status-pill status-pill--danger">{{ $lowStockCount }} alertes</span>
                        </div>

                        @forelse ($lowStockProducts as $product)
                            <div class="info-row">
                                <div class="pr-2">
                                    <div class="info-row__label">{{ $product->name }}</div>
                                    <div class="info-row__value">Stock: {{ (int) $product->stock_total }}</div>
                                </div>
                                <span class="status-pill status-pill--warning">Seuil {{ (int) $product->seuil_alert }}</span>
                            </div>
                        @empty
                            <div class="empty-state">
                                <div class="empty-state__icon"><i class="fas fa-boxes"></i></div>
                                <div class="font-weight-bold mb-1">Aucune alerte stock</div>
                                <div>Les produits critiques apparaîtront ici si le stock descend sous le seuil défini.</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection
