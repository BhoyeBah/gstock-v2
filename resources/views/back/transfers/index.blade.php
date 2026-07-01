@extends('back.layouts.admin')

@section('content')
    <div class="page-hero page-hero--accent mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <div class="page-hero__eyebrow mb-2">Stock & logistique</div>
                <h1 class="page-hero__title mb-0">Transferts internes</h1>
                <p class="page-hero__subtitle">Historique des mouvements entre entrepôts avec traçabilité complète des lots.</p>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="metric-card">
                <div class="metric-card__label">Transferts</div>
                <div class="metric-card__value">{{ number_format($stats['total']) }}</div>
                <div class="metric-card__meta">Opérations enregistrées</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <div class="metric-card__label">Quantité</div>
                <div class="metric-card__value">{{ number_format($stats['quantity']) }}</div>
                <div class="metric-card__meta">Unités déplacées</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <div class="metric-card__label">Produits</div>
                <div class="metric-card__value">{{ number_format($stats['products']) }}</div>
                <div class="metric-card__meta">Produits concernés</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <div class="metric-card__label">Entrepôts source</div>
                <div class="metric-card__value">{{ number_format($stats['warehouses']) }}</div>
                <div class="metric-card__meta">Points d’origine</div>
            </div>
        </div>
    </div>

    <div class="panel-card mb-4">
        <form method="GET" action="{{ route('transfers.index') }}">
            <div class="row">
                <div class="col-lg-12 mb-3">
                    <label class="modern-label" for="search">Recherche</label>
                    <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Numéro de transfert, produit ou entrepôt">
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('transfers.index') }}" class="btn-modern btn-secondary"><i class="fas fa-redo"></i> Réinitialiser</a>
                <button type="submit" class="btn-modern btn-primary"><i class="fas fa-search"></i> Filtrer</button>
            </div>
        </form>
    </div>

    <div class="table-card">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1 font-weight-bold">Liste des transferts</h5>
                <div class="text-muted">Traçabilité complète des transferts internes validés.</div>
            </div>
            <span class="status-pill status-pill--neutral">{{ $transfers->total() }} résultats</span>
        </div>

        @if ($transfers->count())
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Référence</th>
                            <th>Produit</th>
                            <th>Source</th>
                            <th>Cible</th>
                            <th class="text-right">Quantité</th>
                            <th>Lot source</th>
                            <th>Lot cible</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transfers as $transfer)
                            <tr>
                                <td>{{ $transfer->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="font-weight-semibold">{{ $transfer->transfer_number ?? $transfer->id }}</td>
                                <td>{{ $transfer->product->name ?? '-' }}</td>
                                <td>{{ $transfer->sourceWarehouse->name ?? '-' }}</td>
                                <td>{{ $transfer->targetWarehouse->name ?? '-' }}</td>
                                <td class="text-right">{{ number_format($transfer->quantity) }}</td>
                                <td>{{ $transfer->source_batch_id ?? $transfer->sourceBatch?->id ?? '-' }}</td>
                                <td>{{ $transfer->target_batch_id ?? $transfer->targetBatch?->id ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center p-4 border-top">
                <div class="text-muted small">
                    Affichage de <strong>{{ $transfers->firstItem() }}</strong> à <strong>{{ $transfers->lastItem() }}</strong> sur <strong>{{ $transfers->total() }}</strong> transferts
                </div>
                <div>{{ $transfers->links() }}</div>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state__icon"><i class="fas fa-random"></i></div>
                <h5 class="mb-2">Aucun transfert trouvé</h5>
                <p class="mb-0">Dès qu’un transfert est validé depuis un entrepôt, il apparaîtra ici.</p>
            </div>
        @endif
    </div>
@endsection
