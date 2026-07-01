@extends('back.layouts.admin')

@section('content')
    <div class="page-hero page-hero--accent mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <div class="page-hero__eyebrow mb-2">Stock & logistique</div>
                <h1 class="page-hero__title mb-0">Mouvements de stock</h1>
                <p class="page-hero__subtitle">Suivi unifié des entrées, sorties et ajustements générés dans l’application.</p>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="metric-card">
                <div class="metric-card__label">Mouvements</div>
                <div class="metric-card__value">{{ number_format($stats['total']) }}</div>
                <div class="metric-card__meta">Toutes opérations</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <div class="metric-card__label">Entrées</div>
                <div class="metric-card__value">{{ number_format($stats['in']) }}</div>
                <div class="metric-card__meta">Réceptions et ajustements +</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <div class="metric-card__label">Sorties</div>
                <div class="metric-card__value">{{ number_format($stats['out']) }}</div>
                <div class="metric-card__meta">Ventes et ajustements -</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <div class="metric-card__label">Ajustements</div>
                <div class="metric-card__value">{{ number_format($stats['adjustment']) }}</div>
                <div class="metric-card__meta">Corrections d’inventaire</div>
            </div>
        </div>
    </div>

    <div class="panel-card mb-4">
        <form method="GET" action="{{ route('movements.index') }}">
            <div class="row">
                <div class="col-lg-6 mb-3">
                    <label class="modern-label" for="search">Recherche</label>
                    <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Produit, lot ou entrepôt">
                </div>
                <div class="col-lg-6 mb-3">
                    <label class="modern-label" for="movement_type">Type</label>
                    <select name="movement_type" id="movement_type" class="form-control">
                        <option value="">Tous les types</option>
                        <option value="in" @selected(request('movement_type') === 'in')>Entrée</option>
                        <option value="out" @selected(request('movement_type') === 'out')>Sortie</option>
                        <option value="adjustment" @selected(request('movement_type') === 'adjustment')>Ajustement</option>
                        <option value="sale" @selected(request('movement_type') === 'sale')>Vente</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('movements.index') }}" class="btn-modern btn-secondary"><i class="fas fa-redo"></i> Réinitialiser</a>
                <button type="submit" class="btn-modern btn-primary"><i class="fas fa-search"></i> Filtrer</button>
            </div>
        </form>
    </div>

    <div class="table-card">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1 font-weight-bold">Journal des mouvements</h5>
                <div class="text-muted">Les écritures de stock générées par les ventes, inventaires et retours.</div>
            </div>
            <span class="status-pill status-pill--neutral">{{ $movements->total() }} résultats</span>
        </div>

        @if ($movements->count())
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Produit</th>
                            <th>Entrepôt</th>
                            <th>Lot</th>
                            <th class="text-right">Qté</th>
                            <th class="text-right">Avant</th>
                            <th class="text-right">Après</th>
                            <th>Type</th>
                            <th>Référence</th>
                            <th>Utilisateur</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($movements as $movement)
                            <tr>
                                <td>{{ $movement->movement_date?->format('d/m/Y H:i') ?? $movement->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="font-weight-semibold">{{ $movement->product->name ?? '-' }}</td>
                                <td>{{ $movement->warehouse->name ?? $movement->batch->warehouse->name ?? '-' }}</td>
                                <td>{{ $movement->batch_id ? \Illuminate\Support\Str::limit($movement->batch_id, 8) : '-' }}</td>
                                <td class="text-right font-weight-semibold">{{ number_format($movement->quantity) }}</td>
                                <td class="text-right">{{ number_format($movement->quantity_before ?? 0) }}</td>
                                <td class="text-right">{{ number_format($movement->quantity_after ?? 0) }}</td>
                                <td>
                                    @php
                                        $type = $movement->movement_type ?? 'adjustment';
                                        $pill = match ($type) {
                                            'in', 'entry' => 'status-pill--success',
                                            'out', 'sale', 'adjustment_out' => 'status-pill--danger',
                                            'adjustment' => 'status-pill--warning',
                                            default => 'status-pill--info',
                                        };
                                    @endphp
                                    <span class="status-pill {{ $pill }}">{{ strtoupper($type) }}</span>
                                </td>
                                <td>
                                    {{ $movement->invoice->invoice_number ?? $movement->inventory->inventory_number ?? '-' }}
                                </td>
                                <td>{{ $movement->user->name ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center p-4 border-top">
                <div class="text-muted small">
                    Affichage de <strong>{{ $movements->firstItem() }}</strong> à <strong>{{ $movements->lastItem() }}</strong> sur <strong>{{ $movements->total() }}</strong> mouvements
                </div>
                <div>{{ $movements->links() }}</div>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state__icon"><i class="fas fa-exchange-alt"></i></div>
                <h5 class="mb-2">Aucun mouvement trouvé</h5>
                <p class="mb-0">Les mouvements apparaîtront ici dès qu’un inventaire, une vente ou un ajustement sera validé.</p>
            </div>
        @endif
    </div>
@endsection
