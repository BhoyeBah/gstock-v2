@extends('back.layouts.admin')

@section('content')
    @php
        use Carbon\Carbon;

        $invoiceItems = $product->invoiceItems;

        // Totaux
        $totalOut = $invoiceItems->where('type', 'out')->sum('quantity');
        $totalIn = $invoiceItems->where('type', 'in')->sum('quantity');

        $totalValueSold = $invoiceItems
            ->where('type', 'out')
            ->sum(fn($item) => $item->quantity * $item->unit_price - $item->discount);

        $totalValueIn = $invoiceItems
            ->where('type', 'in')
            ->sum(fn($item) => $item->quantity * $item->unit_price - $item->discount);

        // Moyennes
        $averagePriceOut = $invoiceItems->where('type', 'out')->avg('unit_price') ?: 0;
        $averagePriceIn = $invoiceItems->where('type', 'in')->avg('unit_price') ?: 0;

        // Quantité expirée
        $expiredQuantity = $product->batches->where('expiration_date', '<', now())->sum('quantity');

        // Total discount
        $totalDiscount = $invoiceItems->where('type', 'out')->sum('discount');

        // Quantité restante réelle
        $totalRemaining = $product->batches->sum('remaining');
    @endphp

    @push('styles')
        <style>
            /* Header moderne */
            .product-show-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 15px;
                padding: 2rem;
                margin-bottom: 2rem;
                box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            }

            .product-show-header h1 {
                color: #fff;
                font-weight: 700;
                margin: 0;
                font-size: 1.75rem;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .product-show-header .btn {
                background: rgba(255, 255, 255, 0.2);
                color: #fff;
                border: 2px solid rgba(255, 255, 255, 0.4);
                font-weight: 600;
                padding: 0.6rem 1.5rem;
                border-radius: 10px;
                transition: all 0.3s ease;
            }

            .product-show-header .btn:hover {
                background: #fff;
                color: #764ba2;
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            }

            /* Cards statistiques */
            .stat-card {
                background: #fff;
                border-radius: 12px;
                padding: 1.25rem;
                margin-bottom: 0.5rem;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
                border-left: 4px solid;
                transition: all 0.3s ease;
                height: calc(100% - 0.5rem);
            }

            .stat-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            }

            .stat-card.border-info {
                border-left-color: #36b9cc;
            }

            .stat-card.border-primary {
                border-left-color: #4e73df;
            }

            .stat-card.border-dark {
                border-left-color: #5a5c69;
            }

            .stat-card.border-success {
                border-left-color: #1cc88a;
            }

            .stat-card.border-secondary {
                border-left-color: #858796;
            }

            .stat-card.border-danger {
                border-left-color: #e74a3b;
            }

            .stat-card.border-warning {
                border-left-color: #f6c23e;
            }

            .stat-card .stat-icon {
                font-size: 2.5rem;
                opacity: 0.8;
            }

            .stat-card .stat-label {
                font-size: 0.75rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 0.5rem;
            }

            .stat-card .stat-value {
                font-size: 1.5rem;
                font-weight: 700;
                color: #5a5c69;
            }

            /* Section cards */
            .section-card {
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
                margin-bottom: 2rem;
                overflow: hidden;
            }

            .section-card .card-header {
                background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
                color: #fff;
                padding: 1.25rem 1.5rem;
                border: none;
            }

            .section-card .card-header.bg-info {
                background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
            }

            .section-card .card-header.bg-secondary {
                background: linear-gradient(135deg, #858796 0%, #60616f 100%);
            }

            .section-card .card-header.bg-success {
                background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
            }

            .section-card .card-header h6 {
                margin: 0;
                font-weight: 700;
                font-size: 1.1rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .section-card .card-header small {
                opacity: 0.9;
                font-size: 0.85rem;
            }

            .section-card .card-body {
                padding: 1.5rem;
            }

            /* Image produit */
            .product-image-box {
                text-align: center;
                padding: 1rem;
            }

            .product-image-box img {
                max-height: 300px;
                border-radius: 12px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                border: 3px solid #e3e6f0;
            }

            /* Table produit */
            .product-info-table {
                margin: 0;
            }

            .product-info-table th {
                background: #f8f9fc;
                font-weight: 600;
                color: #5a5c69;
                width: 200px;
                padding: 1rem;
                border: 1px solid #e3e6f0;
            }

            .product-info-table td {
                padding: 1rem;
                color: #858796;
                border: 1px solid #e3e6f0;
            }

            .product-info-table td a {
                color: #4e73df;
                font-weight: 600;
                text-decoration: none;
            }

            .product-info-table td a:hover {
                text-decoration: underline;
            }

            /* Badges */
            .status-badge {
                padding: 0.5rem 1rem;
                border-radius: 20px;
                font-weight: 600;
                font-size: 0.85rem;
            }

            .badge-success {
                background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
                color: #fff;
            }

            .badge-danger {
                background: linear-gradient(135deg, #e74a3b 0%, #c92a2a 100%);
                color: #fff;
            }

            /* Boutons d'action */
            .action-buttons {
                display: flex;
                gap: 0.75rem;
                justify-content: flex-end;
            }

            .btn {
                border-radius: 10px;
                padding: 0.65rem 1.5rem;
                font-weight: 600;
                font-size: 0.9rem;
                transition: all 0.3s ease;
                border: none;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            }

            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }

            .btn-warning {
                background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
                color: #fff;
            }

            .btn-warning:hover {
                background: linear-gradient(135deg, #f4b619 0%, #c9930a 100%);
                color: #fff;
            }

            .btn-danger {
                background: linear-gradient(135deg, #e74a3b 0%, #c92a2a 100%);
                color: #fff;
            }

            .btn-danger:hover {
                background: linear-gradient(135deg, #d63026 0%, #b52424 100%);
                color: #fff;
            }

            /* Tables */
            .modern-table {
                margin: 0;
            }

            .modern-table thead th {
                background: #f8f9fc;
                color: #5a5c69;
                font-weight: 600;
                border: 1px solid #e3e6f0;
                padding: 0.875rem;
                font-size: 0.875rem;
                text-transform: uppercase;
                letter-spacing: 0.3px;
            }

            .modern-table tbody td {
                padding: 0.875rem;
                border: 1px solid #e3e6f0;
                color: #858796;
                font-size: 0.9rem;
            }

            .modern-table tbody tr:hover {
                background: #f8f9fc;
            }

            .modern-table tbody td a {
                color: #4e73df;
                font-weight: 600;
                text-decoration: none;
            }

            .modern-table tbody td a:hover {
                text-decoration: underline;
            }

            .table-badge {
                padding: 0.35rem 0.75rem;
                border-radius: 15px;
                font-size: 0.8rem;
                font-weight: 600;
            }

            /* Pagination */
            .pagination {
                margin-top: 1.5rem;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .product-show-header h1 {
                    font-size: 1.4rem;
                }

                .stat-card .stat-value {
                    font-size: 1.25rem;
                }

                .product-info-table th {
                    width: auto;
                }

                .action-buttons {
                    flex-direction: column;
                }

                .btn {
                    width: 100%;
                    justify-content: center;
                }
            }

            /* Styles personnalisés pour le modal */
            .modal-content {
                border-radius: 15px;
                overflow: hidden;
            }

            .bg-gradient-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }

            .border-left-info {
                border-left: 4px solid #17a2b8 !important;
            }

            .alert-info {
                background-color: #f8f9fa;
                border: 1px solid #e9ecef;
            }

            .input-group-text {
                border: 1px solid #ced4da;
            }

            .form-control:focus {
                border-color: #667eea;
                box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            }

            .modal-header .close {
                opacity: 1;
            }

            .modal-header .close:hover {
                opacity: 0.8;
            }

            .badge-pill {
                font-size: 0.9rem;
            }

            .shadow-lg {
                box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175) !important;
            }

            .bg-opacity-25 {
                opacity: 0.25;
                background-color: white !important;
            }

            .modal-header small {
                font-size: 0.85rem;
            }
        </style>
    @endpush

    <div class="container-fluid">
        <!-- Header -->
        <div class="product-show-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h1>
                    <i class="fas fa-box-open"></i>
                    Détails du produit {{ $product->name }}
                </h1>
                <a href="{{ route('products.index') }}" class="btn mt-2 mt-md-0">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>
            </div>
        </div>


        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card border-info">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-shopping-cart stat-icon text-info"></i>
                        </div>
                        <div>
                            <div class="stat-label text-info">Total vendu</div>
                            <div class="stat-value">{{ $totalOut }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card border-primary">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-boxes stat-icon text-primary"></i>
                        </div>
                        <div>
                            <div class="stat-label text-primary">Stock actuel</div>
                            <div class="stat-value">{{ $totalRemaining }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card border-dark">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-dollar-sign stat-icon text-dark"></i>
                        </div>
                        <div>
                            <div class="stat-label text-dark">Valeur vendue</div>
                            <div class="stat-value">{{ number_format($totalValueSold, 0, ',', ' ') }} CFA</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card border-success">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-credit-card stat-icon text-success"></i>
                        </div>
                        <div>
                            <div class="stat-label text-success">Valeur achat</div>
                            <div class="stat-value">{{ number_format($totalValueIn, 0, ',', ' ') }} CFA</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card border-secondary">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-file-invoice stat-icon text-secondary"></i>
                        </div>
                        <div>
                            <div class="stat-label text-secondary">Nb factures</div>
                            <div class="stat-value">{{ $product->invoices->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card border-info">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-tag stat-icon text-info"></i>
                        </div>
                        <div>
                            <div class="stat-label text-info">Prix moyen vente</div>
                            <div class="stat-value">{{ number_format($averagePriceOut, 0, ',', ' ') }} CFA</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card border-success">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-tag stat-icon text-success"></i>
                        </div>
                        <div>
                            <div class="stat-label text-success">Prix moyen achat</div>
                            <div class="stat-value">{{ number_format($averagePriceIn, 0, ',', ' ') }} CFA</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card border-danger">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-exclamation-triangle stat-icon text-danger"></i>
                        </div>
                        <div>
                            <div class="stat-label text-danger">Quantité expirée</div>
                            <div class="stat-value">{{ $expiredQuantity }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card border-warning">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-percent stat-icon text-warning"></i>
                        </div>
                        <div>
                            <div class="stat-label text-warning">Réduction totale</div>
                            <div class="stat-value">{{ number_format($totalDiscount, 0, ',', ' ') }} CFA</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Produit -->
        <div class="section-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6>
                    <i class="fas fa-info-circle"></i>
                    Informations sur le produit
                </h6>
                @if ($product->is_active)
                    <span class="status-badge badge-success">
                        <i class="fas fa-check-circle"></i> Activé
                    </span>
                @else
                    <span class="status-badge badge-danger">
                        <i class="fas fa-times-circle"></i> Désactivé
                    </span>
                @endif
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Image du produit -->
                    <div class="col-md-4 product-image-box">
                        @if ($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="Image du produit" class="img-fluid">
                        @else
                            <img src="https://via.placeholder.com/400x300.png?text=Aucune+Image" alt="Image par défaut"
                                class="img-fluid">
                        @endif
                    </div>

                    <!-- Détails du produit -->
                    <div class="col-md-8">
                        <table class="table product-info-table">
                            <tbody>
                                <tr>
                                    <th><i class="fas fa-tag mr-2"></i>Nom</th>
                                    <td><strong>{{ $product->name }}</strong></td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-tags mr-2"></i>Catégorie</th>
                                    <td>
                                        <a href="{{ route('categories.index', $product->category->id) }}">
                                            {{ $product->category->name ?? 'Non défini' }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-balance-scale mr-2"></i>Unité de mesure</th>
                                    <td>{{ $product->unit->name ?? 'Non défini' }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-money-bill-wave mr-2"></i>Prix de vente</th>
                                    <td><strong>{{ number_format($product->price, 0, ',', ' ') }} CFA</strong></td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-exclamation-triangle mr-2"></i>Seuil d'alerte</th>
                                    <td>{{ $product->seuil_alert }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-align-left mr-2"></i>Description</th>
                                    <td>{{ $product->description ?? 'Aucune description fournie.' }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-calendar-plus mr-2"></i>Date de création</th>
                                    <td>{{ $product->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th><i class="fas fa-calendar-edit mr-2"></i>Dernière modification</th>
                                    <td>{{ $product->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="action-buttons mt-4">
                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <form action="{{ route('products.destroy', $product->id) }}" method="POST"
                                onsubmit="return confirm('Confirmer la suppression de ce produit ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash-alt"></i> Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Lots -->
        <div class="section-card">
            <div class="card-header bg-info">
                <div>
                    <h6>
                        <i class="fas fa-boxes"></i>
                        Lots ({{ $product->batches->count() }})
                    </h6>
                    <small>Stocks par lot / entrepôt</small>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table modern-table table-hover mb-0">
                        <thead>
                            <tr>
                                <th><i class="fas fa-warehouse mr-1"></i>Entrepôt</th>
                                <th><i class="fas fa-cubes mr-1"></i>Quantité</th>
                                <th><i class="fas fa-box mr-1"></i>Restante</th>
                                <th><i class="fas fa-calendar-times mr-1"></i>Expiration</th>
                                <th><i class="fas fa-clock mr-1"></i>Ajouté le</th>
                                <th class="text-center">
                                    <i class="fas fa-truck-loading mr-1"></i>
                                    Sortie
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($product->batches->where('quantity', '>', 0) as $lot)
                                <tr>
                                    <td>
                                        <a href="{{ route('warehouses.show', $lot->warehouse->id) }}">
                                            <i class="fas fa-warehouse mr-1"></i>{{ $lot->warehouse->name ?? '-' }}
                                        </a>
                                    </td>
                                    <td><strong>{{ $lot->quantity }}</strong></td>
                                    <td>
                                        <span
                                            class="table-badge {{ $lot->remaining > 0 ? 'badge-success' : 'badge-danger' }}">
                                            {{ $lot->remaining }}
                                        </span>
                                    </td>
                                    <td>{{ $lot->expiration_date ? Carbon::parse($lot->expiration_date)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td>{{ Carbon::parse($lot->created_at)->format('d/m/Y H:i') }}</td>
                                    <td class="text-center">
                                        <!-- Bouton pour créer une sortie de stock -->
                                        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal"
                                            data-target="#stockOutModal-{{ $lot->id }}">
                                              <i class="fas fa-truck-loading mr-1"></i>
                                        </button>

                                        <!-- Modal sortie de stock pour ce lot -->
                                        <div class="modal fade" id="stockOutModal-{{ $lot->id }}" tabindex="-1"
                                            role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content shadow-lg border-0">
                                                    <form action="{{ route('stockout.store') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="batch_id"
                                                            value="{{ $lot->id }}">

                                                        <!-- Modal Header -->
                                                        <div class="modal-header bg-gradient-primary text-white border-0">
                                                            <h5 class="modal-title d-flex align-items-center">
                                                                <span class="bg-white bg-opacity-25 rounded p-2 mr-3">
                                                                    <i class="fas fa-truck-loading"></i>
                                                                </span>
                                                                <div>
                                                                    <div class="font-weight-bold">Sortie de stock</div>
                                                                    <small class="font-weight-normal opacity-90">
                                                                        {{ $lot->name ?? 'Lot de ' . $lot->product->name }} -
                                                                        {{ $lot->warehouse->name ?? '' }}
                                                                    </small>
                                                                </div>
                                                            </h5>
                                                            <button type="button" class="close text-white"
                                                                data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>

                                                        <!-- Modal Body -->
                                                        <div class="modal-body p-4">
                                                            <!-- Info Card -->
                                                            <div class="alert alert-info border-left-info mb-4">
                                                                <div
                                                                    class="d-flex justify-content-between align-items-center mb-2">
                                                                    <span class="text-muted">
                                                                        <i class="fas fa-box mr-2"></i>Stock disponible
                                                                    </span>
                                                                    <span class="badge badge-success badge-pill px-3 py-2">
                                                                        {{ $lot->remaining }} unités
                                                                    </span>
                                                                </div>
                                                                <div
                                                                    class="d-flex justify-content-between align-items-center">
                                                                    <span class="text-muted">
                                                                        <i class="fas fa-calendar-alt mr-2"></i>Date
                                                                        d'expiration
                                                                    </span>
                                                                    <span class="font-weight-bold">
                                                                        {{ $lot->expiration_date ? Carbon::parse($lot->expiration_date)->format('d/m/Y') : 'Non définie' }}
                                                                    </span>
                                                                </div>
                                                            </div>

                                                            <!-- Quantité -->
                                                            <div class="form-group">
                                                                <label for="quantity-{{ $lot->id }}"
                                                                    class="font-weight-bold text-dark">
                                                                    Quantité à sortir <span class="text-danger">*</span>
                                                                </label>
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span
                                                                            class="input-group-text bg-light border-right-0">
                                                                            <i class="fas fa-boxes text-primary"></i>
                                                                        </span>
                                                                    </div>
                                                                    <input type="number" name="quantity"
                                                                        id="quantity-{{ $lot->id }}"
                                                                        class="form-control border-left-0" min="1"
                                                                        max="{{ $lot->remaining }}"
                                                                        placeholder="Entrez la quantité" required>
                                                                </div>
                                                                <small class="form-text text-muted">
                                                                    <i class="fas fa-info-circle mr-1"></i>
                                                                    Maximum: {{ $lot->remaining }} unités disponibles
                                                                </small>
                                                            </div>

                                                            <!-- Motif -->
                                                            <div class="form-group mb-0">
                                                                <label for="reason-{{ $lot->id }}"
                                                                    class="font-weight-bold text-dark">
                                                                    Motif de sortie
                                                                </label>
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span
                                                                            class="input-group-text bg-light border-right-0">
                                                                            <i class="fas fa-comment-alt text-primary"></i>
                                                                        </span>
                                                                    </div>
                                                                    <input type="text" name="reason"
                                                                        id="reason-{{ $lot->id }}"
                                                                        class="form-control border-left-0"
                                                                        placeholder="Décrivez le motif de cette sortie (optionnel)">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Modal Footer -->
                                                        <div class="modal-footer bg-light border-0">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">
                                                                <i class="fas fa-times mr-1"></i> Annuler
                                                            </button>
                                                            <button type="submit" class="btn btn-primary">
                                                                <i class="fas fa-check mr-1"></i> Enregistrer la sortie
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Fin modal -->
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        Aucun lot trouvé.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>



        <!-- Section Factures -->
        <div class="section-card">
            <div class="card-header bg-secondary">
                <div>
                    <h6>
                        <i class="fas fa-file-invoice"></i>
                        Factures ({{ $invoices->total() }})
                    </h6>
                    <small>Factures contenant ce produit</small>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table modern-table table-hover mb-0">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag mr-1"></i>Numéro</th>
                                <th><i class="fas fa-user mr-1"></i>Client / Fournisseur</th>
                                <th><i class="fas fa-tag mr-1"></i>Type</th>
                                <th><i class="fas fa-calendar mr-1"></i>Date facture</th>
                                <th><i class="fas fa-calendar-check mr-1"></i>Échéance</th>
                                <th><i class="fas fa-dollar-sign mr-1"></i>Total</th>
                                <th><i class="fas fa-balance-scale mr-1"></i>Solde</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($invoices as $invoice)
                                <tr>
                                    <td>
                                        <a
                                            href="{{ route('invoices.show', [$invoice->type . 's', $invoice->id ?? '#']) }}">
                                            <strong>{{ $invoice->invoice_number ?? '—' }}</strong>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route($invoice->type . 's.show', $invoice->contact->id) }}">
                                            <strong>{{ $invoice->contact->info() ?? '—' }}</strong>
                                        </a>
                                    </td>
                                    <td><span class="text-capitalize">{{ $invoice->type ?? '-' }}</span></td>
                                    <td>{{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td>{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td><strong>{{ number_format($invoice->total_invoice ?? 0, 0, ',', ' ') }} CFA</strong>
                                    </td>
                                    <td>{{ number_format($invoice->balance ?? 0, 0, ',', ' ') }} CFA</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        Aucune facture trouvée.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $invoices->links() }}
                </div>
            </div>
        </div>

        <!-- Section Mouvements d'Inventaire -->
        <div class="section-card">
            <div class="card-header bg-success">
                <div>
                    <h6>
                        <i class="fas fa-exchange-alt"></i>
                        Mouvements d'inventaire
                    </h6>
                    <small>Historique des mouvements</small>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table modern-table table-hover mb-0">
                        <thead>
                            <tr>
                                <th><i class="fas fa-cubes mr-1"></i>Quantité</th>
                                <th><i class="fas fa-info-circle mr-1"></i>Raison</th>
                                <th><i class="fas fa-warehouse mr-1"></i>Entrepôt</th>
                                <th><i class="fas fa-file-invoice mr-1"></i>Facture</th>
                                <th><i class="fas fa-clock mr-1"></i>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($movements as $movement)
                                <tr>
                                    <td><strong>{{ $movement->quantity }}</strong></td>
                                    <td>{{ $movement->reason ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('warehouses.show', $movement->batch->warehouse->id) }}">
                                            <i class="fas fa-warehouse mr-1"></i>
                                            {{ $movement->batch->warehouse->name ?? '-' }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="#">
                                            {{ $movement->invoice->invoice_number ?? '-' }}
                                        </a>
                                    </td>
                                    <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        Aucun mouvement trouvé.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination : en dehors de la table -->
                <div class="mt-3">
                    {{ $movements->links() }}
                </div>
            </div>
        </div>

    </div>
@endsection
