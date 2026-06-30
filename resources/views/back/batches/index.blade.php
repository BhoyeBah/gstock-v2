@extends('back.layouts.admin')

@section('content')
    @php use Carbon\Carbon; @endphp

    <div class="page-hero page-hero--accent mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <div class="page-hero__eyebrow mb-2">Stock & logistique</div>
                <h1 class="page-hero__title mb-0">Lots / Batches</h1>
                <p class="page-hero__subtitle">Vision consolidée des lots disponibles, expirés et ventilés par entrepôt.</p>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="metric-card">
                <div class="metric-card__label">Lots</div>
                <div class="metric-card__value">{{ number_format($stats['total']) }}</div>
                <div class="metric-card__meta">Lots enregistrés</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <div class="metric-card__label">Disponible</div>
                <div class="metric-card__value">{{ number_format($stats['available']) }}</div>
                <div class="metric-card__meta">Lots en stock</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <div class="metric-card__label">Expirés</div>
                <div class="metric-card__value">{{ number_format($stats['expired']) }}</div>
                <div class="metric-card__meta">Lots à surveiller</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <div class="metric-card__label">Stock total</div>
                <div class="metric-card__value">{{ number_format($stats['stock']) }}</div>
                <div class="metric-card__meta">Unités restantes</div>
            </div>
        </div>
    </div>

    <div class="panel-card mb-4">
        <form method="GET" action="{{ route('batches.index') }}">
            <div class="row">
                <div class="col-lg-4 mb-3">
                    <label class="modern-label" for="search">Recherche</label>
                    <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Produit, entrepôt ou ID">
                </div>
                <div class="col-lg-4 mb-3">
                    <label class="modern-label" for="warehouse_id">Entrepôt</label>
                    <select name="warehouse_id" id="warehouse_id" class="form-control">
                        <option value="">Tous les entrepôts</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-4 mb-3">
                    <label class="modern-label" for="product_id">Produit</label>
                    <select name="product_id" id="product_id" class="form-control">
                        <option value="">Tous les produits</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('batches.index') }}" class="btn-modern btn-secondary"><i class="fas fa-redo"></i> Réinitialiser</a>
                <button type="submit" class="btn-modern btn-primary"><i class="fas fa-search"></i> Filtrer</button>
            </div>
        </form>
    </div>

    <div class="table-card">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1 font-weight-bold">Liste des lots</h5>
                <div class="text-muted">Détail des lots disponibles par produit et entrepôt.</div>
            </div>
            <span class="status-pill status-pill--neutral">{{ $batches->total() }} résultats</span>
        </div>

        @if ($batches->count())
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Entrepôt</th>
                            <th class="text-right">Quantité</th>
                            <th class="text-right">Restant</th>
                            <th class="text-right">Prix unitaire</th>
                            <th>Facture</th>
                            <th>Expiration</th>
                            <th>Créé le</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($batches as $batch)
                            <tr>
                                <td>
                                    <div class="font-weight-bold">{{ $batch->product->name ?? '-' }}</div>
                                    <div class="text-muted small">{{ optional($batch->product->unit)->name ?? '' }}</div>
                                </td>
                                <td>{{ $batch->warehouse->name ?? '-' }}</td>
                                <td class="text-right">{{ number_format($batch->quantity) }}</td>
                                <td class="text-right">
                                    <span class="status-pill {{ $batch->remaining > 0 ? 'status-pill--success' : 'status-pill--danger' }}">
                                        {{ number_format($batch->remaining) }}
                                    </span>
                                </td>
                                <td class="text-right">{{ number_format($batch->unit_price, 0, ',', ' ') }} FCFA</td>
                                <td>
                                    @if ($batch->invoice)
                                        <a href="{{ route('invoices.show', [$batch->invoice->type . 's', $batch->invoice_id]) }}" class="text-primary font-weight-semibold">
                                            {{ $batch->invoice->invoice_number ?? '-' }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if ($batch->expiration_date)
                                        <span class="status-pill {{ $batch->isExpired() ? 'status-pill--danger' : 'status-pill--info' }}">
                                            {{ Carbon::parse($batch->expiration_date)->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="status-pill status-pill--neutral">Non définie</span>
                                    @endif
                                </td>
                                <td>{{ $batch->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center p-4 border-top">
                <div class="text-muted small">
                    Affichage de <strong>{{ $batches->firstItem() }}</strong> à <strong>{{ $batches->lastItem() }}</strong> sur <strong>{{ $batches->total() }}</strong> lots
                </div>
                <div>{{ $batches->links() }}</div>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state__icon"><i class="fas fa-boxes"></i></div>
                <h5 class="mb-2">Aucun lot trouvé</h5>
                <p class="mb-0">Essayez de retirer vos filtres ou vérifiez que des stocks ont déjà été réceptionnés.</p>
            </div>
        @endif
    </div>
@endsection
