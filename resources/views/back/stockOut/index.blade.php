@extends('back.layouts.admin')

@section('content')
    @php use Carbon\Carbon; @endphp

    <div class="page-hero page-hero--accent mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <div class="page-hero__eyebrow mb-2">Stock & logistique</div>
                <h1 class="page-hero__title mb-0">Sorties de stock</h1>
                <p class="page-hero__subtitle">Gérez les sorties manuelles avec traçabilité, restitution et filtres rapides.</p>
            </div>
            <button class="btn btn-light" data-toggle="modal" data-target="#addStockOutModal">
                <i class="fas fa-plus mr-1"></i> Nouvelle sortie
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="metric-card">
                <div class="metric-card__label">Sorties</div>
                <div class="metric-card__value">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="metric-card__meta">Opérations enregistrées</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <div class="metric-card__label">Quantité</div>
                <div class="metric-card__value">{{ number_format($stats['quantity'] ?? 0) }}</div>
                <div class="metric-card__meta">Unités sorties</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <div class="metric-card__label">Lots actifs</div>
                <div class="metric-card__value">{{ number_format($stats['batches'] ?? 0) }}</div>
                <div class="metric-card__meta">Lots encore disponibles</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card">
                <div class="metric-card__label">Attention</div>
                <div class="metric-card__value">{{ number_format($stats['low'] ?? 0) }}</div>
                <div class="metric-card__meta">Lots à faible stock</div>
            </div>
        </div>
    </div>

    <div class="panel-card mb-4">
        <form method="GET" action="{{ route('stockout.index') }}">
            <div class="row">
                <div class="col-lg-12 mb-3">
                    <label class="modern-label" for="search">Recherche</label>
                    <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Numéro de sortie, produit, entrepôt ou motif">
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('stockout.index') }}" class="btn-modern btn-secondary"><i class="fas fa-redo"></i> Réinitialiser</a>
                <button type="submit" class="btn-modern btn-primary"><i class="fas fa-search"></i> Filtrer</button>
            </div>
        </form>
    </div>

    <div class="table-card">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1 font-weight-bold">Historique des sorties</h5>
                <div class="text-muted">Chaque sortie peut être annulée pour rétablir le stock du lot.</div>
            </div>
            <span class="status-pill status-pill--neutral">{{ $stockOuts->total() }} résultats</span>
        </div>

        @if ($stockOuts->count())
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Référence</th>
                            <th>Produit</th>
                            <th>Entrepôt</th>
                            <th>Lot</th>
                            <th class="text-right">Quantité</th>
                            <th>Motif</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stockOuts as $stock)
                            <tr>
                                <td>{{ $stock->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="font-weight-semibold">{{ $stock->stock_out_number ?? $stock->id }}</td>
                                <td>{{ $stock->batch->product->name ?? '-' }}</td>
                                <td>{{ $stock->batch->warehouse->name ?? '-' }}</td>
                                <td>{{ $stock->batch->id ? \Illuminate\Support\Str::limit($stock->batch->id, 8) : '-' }}</td>
                                <td class="text-right">
                                    <span class="status-pill status-pill--danger">{{ number_format($stock->quantity) }}</span>
                                </td>
                                <td>{{ $stock->reason ?: '-' }}</td>
                                <td class="text-center">
                                    <form action="{{ route('stockout.destroy', $stock->id) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Confirmer la suppression ? Le stock du lot sera restauré.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center p-4 border-top">
                <div class="text-muted small">
                    Affichage de <strong>{{ $stockOuts->firstItem() }}</strong> à <strong>{{ $stockOuts->lastItem() }}</strong> sur <strong>{{ $stockOuts->total() }}</strong> sorties
                </div>
                <div>{{ $stockOuts->links() }}</div>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state__icon"><i class="fas fa-box-open"></i></div>
                <h5 class="mb-2">Aucune sortie de stock trouvée</h5>
                <p class="mb-4">Ajoutez une nouvelle sortie pour commencer.</p>
                <button class="btn-modern btn-primary" data-toggle="modal" data-target="#addStockOutModal">
                    <i class="fas fa-plus-circle mr-2"></i> Nouvelle sortie
                </button>
            </div>
        @endif
    </div>

    <div class="modal fade" id="addStockOutModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content rounded-2xl overflow-hidden">
                <form action="{{ route('stockout.store') }}" method="POST">
                    @csrf
                    <div class="modal-header modern-header">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-box-open mr-2"></i>Nouvelle sortie de stock
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body modern-body">
                        <div class="modern-input-group">
                            <label for="batch_id" class="modern-label">
                                Produit / Entrepôt
                            </label>
                            <select name="batch_id" id="batch_id" class="form-control" required>
                                <option value="">Sélectionnez un lot</option>
                                @foreach ($batches as $batch)
                                    <option value="{{ $batch->id }}">
                                        {{ $batch->product->name ?? '-' }} / {{ $batch->warehouse->name ?? '-' }} ({{ $batch->remaining }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Choisissez le produit et son lot.</small>
                        </div>

                        <div class="modern-input-group">
                            <label for="quantity" class="modern-label">Quantité</label>
                            <input type="number" name="quantity" id="quantity" class="form-control" min="1" placeholder="Ex: 10" required>
                            <small class="form-text text-muted">Indiquez la quantité à sortir.</small>
                        </div>

                        <div class="modern-input-group mb-0">
                            <label for="reason" class="modern-label">Motif <span class="badge-soft badge-soft--neutral">Optionnel</span></label>
                            <input type="text" name="reason" id="reason" class="form-control" placeholder="Ex: Vente, casse, péremption...">
                        </div>
                    </div>

                    <div class="modal-footer modern-footer">
                        <button type="button" class="btn-modern btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>Annuler
                        </button>
                        <button type="submit" class="btn-modern btn-primary">
                            <i class="fas fa-check mr-1"></i>Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
