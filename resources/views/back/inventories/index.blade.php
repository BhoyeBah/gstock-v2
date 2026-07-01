@extends('back.layouts.admin')

@section('content')
    <div class="page-hero page-hero--accent mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <div class="page-hero__eyebrow mb-2">Stock & logistique</div>
                <h1 class="page-hero__title mb-0">Inventaire physique</h1>
                <p class="page-hero__subtitle">Générez un inventaire par entrepôt puis suivez les écarts et les validations.</p>
            </div>

            <form action="{{ route('inventories.store') }}" method="POST" class="d-flex flex-wrap gap-2 align-items-center">
                @csrf
                <select class="form-control" name="warehouse_id" required style="min-width: 260px;">
                    <option value="">-- Choisir un entrepôt --</option>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-light">
                    <i class="fas fa-plus mr-1"></i> Générer
                </button>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="metric-card">
                <div class="metric-card__label">Inventaires</div>
                <div class="metric-card__value">{{ number_format($inventories->count()) }}</div>
                <div class="metric-card__meta">Total enregistré</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card">
                <div class="metric-card__label">Clôturés</div>
                <div class="metric-card__value">{{ number_format($inventories->where('status', 'completed')->count()) }}</div>
                <div class="metric-card__meta">Inventaires validés</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card">
                <div class="metric-card__label">En cours</div>
                <div class="metric-card__value">{{ number_format($inventories->where('status', '!=', 'completed')->count()) }}</div>
                <div class="metric-card__meta">Inventaires ouverts</div>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1 font-weight-bold">Historique des inventaires</h5>
                <div class="text-muted">Accès rapide aux écarts, au détail et à l’impression.</div>
            </div>
            <span class="status-pill status-pill--neutral">{{ $inventories->count() }} résultats</span>
        </div>

        @if ($inventories->count())
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>N° inventaire</th>
                            <th>Entrepôt</th>
                            <th>Date</th>
                            <th class="text-center">Total produits</th>
                            <th class="text-center">Validés</th>
                            <th class="text-center">Écart</th>
                            <th class="text-center">Statut</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inventories as $inventory)
                            <tr>
                                <td class="font-weight-bold text-primary">{{ $inventory->inventory_number }}</td>
                                <td>{{ $inventory->warehouse->name ?? '-' }}</td>
                                <td>{{ $inventory->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="text-center">
                                    <span class="status-pill status-pill--info">{{ $inventory->total_products }}</span>
                                </td>
                                <td class="text-center">
                                    <strong class="text-success">{{ $inventory->validated_count }}</strong>
                                </td>
                                <td class="text-center">
                                    @php $ecart = (int) ($inventory->ecart_sum ?? 0); @endphp
                                    @if ($ecart > 0)
                                        <span class="status-pill status-pill--danger">{{ $ecart }}</span>
                                    @else
                                        <span class="status-pill status-pill--success">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($inventory->status === 'completed')
                                        <span class="status-pill status-pill--success">Clôturé</span>
                                    @else
                                        <span class="status-pill status-pill--warning">En cours</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('inventories.show', $inventory->id) }}" class="btn btn-sm btn-success">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('inventories.print', $inventory->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state__icon"><i class="fas fa-clipboard-list"></i></div>
                <h5 class="mb-2">Aucun inventaire trouvé</h5>
                <p class="mb-0">Générez un premier inventaire pour ce tenant afin de commencer le contrôle physique.</p>
            </div>
        @endif
    </div>
@endsection
