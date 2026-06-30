@extends('back.layouts.admin')

@php
    use Carbon\Carbon;
@endphp

@section('content')
    <style>
        /* En-tête de page */
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .page-header h1 {
            color: #fff;
            font-weight: 700;
            margin: 0;
            font-size: 1.75rem;
        }

        .page-header .btn {
            transition: all 0.3s ease;
        }

        .page-header .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* Cartes statistiques */
        .stats-card {
            border: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            overflow: hidden;
            background: #fff;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .stats-card .card-body {
            padding: 1.5rem;
        }

        .stats-card .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stats-card.border-left-secondary .stats-icon {
            background: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }

        .stats-card.border-left-info .stats-icon {
            background: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
        }

        .stats-card.border-left-warning .stats-icon {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        .stats-card.border-left-success .stats-icon {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .stats-card.border-left-danger .stats-icon {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .stats-card.border-left-primary .stats-icon {
            background: rgba(78, 115, 223, 0.1);
            color: #4e73df;
        }

        /* Section recherche */
        .search-section {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .search-section .card-header {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            border: none;
            padding: 1.25rem 1.5rem;
        }

        .search-section .form-control,
        .search-section .form-control:focus {
            border-radius: 8px;
            border: 1px solid #e3e6f0;
        }

        .search-section .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
        }

        .search-section label {
            font-weight: 600;
            color: #5a5c69;
            margin-bottom: 0.5rem;
        }

        /* Liste des factures (réutilisé pour dépenses) */
        .invoice-list-section {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .invoice-list-section .card-header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border: none;
            padding: 1.25rem 1.5rem;
        }

        .invoice-table {
            margin-bottom: 0;
        }

        .invoice-table thead th {
            background: #f8f9fc;
            color: #5a5c69;
            font-weight: 700;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            border: none;
            padding: 1rem 0.75rem;
            white-space: nowrap;
        }

        .invoice-table tbody tr {
            transition: all 0.2s ease;
            border-bottom: 1px solid #e3e6f0;
        }

        .invoice-table tbody tr:hover {
            background: #f8f9fc;
            /* Suppression de transform: scale(1.01) pour éviter les problèmes de layout */
        }

        .invoice-table tbody td {
            padding: 1rem 0.75rem;
            vertical-align: middle;
            font-size: 0.875rem;
        }

        /* Badges de statut */
        .badge {
            padding: 0.5rem 0.875rem;
            font-weight: 600;
            font-size: 0.75rem;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Boutons d'action */
        .action-buttons .btn {
            margin: 0 0.125rem;
            transition: all 0.2s ease;
            border-radius: 6px;
        }

        .action-buttons .btn:hover {
            transform: scale(1.1);
        }

        /* Modal amélioré */
        .modal-content {
            border-radius: 15px;
            border: none;
        }

        .modal-header {
            border-radius: 15px 15px 0 0;
            border: none;
            padding: 1.5rem;
        }

        .modal-body {
            padding: 2rem;
        }

        .modal-footer {
            border: none;
            padding: 1.5rem;
            background: #f8f9fc;
        }

        /* Alertes personnalisées */
        .alert {
            border: none;
            border-radius: 10px;
            padding: 1.25rem;
        }

        .alert-info {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            color: #0c5460;
        }

        /* Pagination */
        .pagination {
            margin-bottom: 0;
        }

        .page-link {
            border-radius: 6px;
            margin: 0 0.125rem;
            border: none;
            color: #4e73df;
        }

        .page-link:hover {
            background: #4e73df;
            color: #fff;
        }

        .page-item.active .page-link {
            background: #4e73df;
            border-color: #4e73df;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stats-card {
            animation: fadeInUp 0.5s ease-out;
        }

        .stats-card:nth-child(1) {
            animation-delay: 0.1s;
        }

        .stats-card:nth-child(2) {
            animation-delay: 0.2s;
        }

        .stats-card:nth-child(3) {
            animation-delay: 0.3s;
        }

        .stats-card:nth-child(4) {
            animation-delay: 0.4s;
        }

        /* Input groups */
        .input-group-text {
            border-radius: 8px 0 0 8px;
            border: 1px solid #e3e6f0;
        }

        .input-group .form-control {
            border-radius: 0 8px 8px 0;
        }
    </style>

    <!-- En-tête de page -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <h1>
                <i class="fas fa-box-open mr-2"></i> Produits
            </h1>
            <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
                <button type="button" class="btn btn-secondary m-1" data-toggle="modal" data-target="#addCategoryModal">
                    <i class="fas fa-plus-circle mr-1"></i>
                    <strong>Nouvelle catégorie</strong>
                </button>
                @can('create_products')
                    <button type="button" class="btn btn-primary m-1" data-toggle="modal" data-target="#addProductModal">
                        <i class="fas fa-plus-circle mr-1"></i>
                        <strong>Nouveau produit</strong>
                    </button>
                @endcan
            </div>
        </div>
    </div>

    <!-- Cartes statistiques -->
    <div class="row mb-4">
        <!-- Total produits -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card border-left-success shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-2">
                                Total des produits
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                {{ $products->total() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="stats-icon">
                                <i class="fas fa-boxes"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Produits actifs -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card border-left-info shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-2">
                                Produits actifs
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                {{ $products->where('is_active', true)->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="stats-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Produits inactifs -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card border-left-warning shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-2">
                                Produits inactifs
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                {{ $products->where('is_active', false)->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="stats-icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Produits en stock bas -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card border-left-danger shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-2">
                                Stock bas
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                {{ $products->filter(function ($product) {return $product->stock_total <= $product->seuil_alert;})->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="stats-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section recherche -->
    <div class="search-section">
        <div class="card-header text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-filter mr-2"></i> Filtrer les produits
            </h6>
        </div>
        <div class="card-body p-4">
            <form method="GET" action="{{ route('products.index') }}">
                <div class="form-row">
                    <div class="col-md-3 mb-3">
                        <label for="search_name">Nom</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" name="search_name" id="search_name" class="form-control"
                                value="{{ request('search_name') }}" placeholder="Rechercher par nom...">
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="category_id">Catégorie</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-tags"></i></span>
                            </div>
                            <select name="category_id" id="category_id" class="form-control">
                                <option value="">Toutes les catégories</option>
                                @foreach (\App\Models\Category::all() as $category)
                                    <option value="{{ $category->id }}"
                                        {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="status">Statut</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-toggle-on"></i></span>
                            </div>
                            <select name="status" id="status" class="form-control">
                                <option value="">Tous les statuts</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactif
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary mr-2 flex-fill">
                            <i class="fas fa-search mr-1"></i> Filtrer
                        </button>
                        <a href="{{ route('products.index') }}" class="btn btn-secondary flex-fill">
                            <i class="fas fa-redo mr-1"></i> Réinitialiser
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des produits -->
    <div class="invoice-list-section">
        <div class="card-header text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-list-ul mr-2"></i> Liste des produits
            </h6>
        </div>
        <div class="card-body p-0">
            @if ($products->count() > 0)
                <div class="table-responsive">
                    <table class="table invoice-table">
                        <thead>
                            <tr>
                                <th width="50">#</th>
                                <th>Nom</th>
                                <th>Catégorie</th>
                                <th class="text-right">Prix de vente (FCFA)</th>
                                <th>Stock disponible</th>
                                <th>Seuil d'alerte</th>
                                <th>Statut</th>
                                <th class="text-center" width="200">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr>
                                    <td class="font-weight-bold text-muted">
                                        {{ $loop->iteration + ($products->currentPage() - 1) * $products->perPage() }}
                                    </td>
                                    <td>
                                        <strong class="text-dark">{{ $product->name }}</strong>
                                    </td>
                                    <td>
                                        <a
                                            href="{{ route('categories.index') }}?search_name={{ $product->category->name ?? '' }}">
                                            {{ $product->category->name ?? '-' }}
                                        </a>
                                    </td>
                                    <td class="text-right font-weight-bold text-primary">
                                        {{ number_format($product->price, 0, ',', ' ') }} FCFA
                                    </td>
                                    <td>
                                        <span
                                            class="badge {{ $product->stock_total > $product->seuil_alert ? 'badge-success' : 'badge-warning' }}">
                                            {{ $product->stock_total ?? 0 }}
                                        </span>
                                    </td>
                                    <td>{{ $product->seuil_alert }}</td>
                                    <td>
                                        <span class="badge {{ $product->is_active ? 'badge-success' : 'badge-danger' }}">
                                            {{ $product->is_active ? 'Activé' : 'Désactivé' }}
                                        </span>
                                    </td>
                                    <td class="text-center action-buttons">
                                        <!-- Activer / Désactiver -->
                                        <form action="{{ route('products.toggle', $product->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Voulez-vous {{ $product->is_active ? 'désactiver' : 'activer' }} ce produit ?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="btn btn-sm {{ $product->is_active ? 'btn-success' : 'btn-danger' }}"
                                                title="{{ $product->is_active ? 'Désactiver' : 'Activer' }}">
                                                <i class="fas fa-toggle-{{ $product->is_active ? 'off' : 'on' }}"></i>
                                            </button>
                                        </form>

                                        <!-- Modifier -->
                                        <a href="{{ route('products.edit', $product->id) }}"
                                            class="btn btn-sm btn-warning" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <!-- Voir -->
                                        <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-info"
                                            title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <!-- Supprimer -->
                                        <form action="{{ route('products.destroy', $product->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Confirmer la suppression de ce produit ?')">
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

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center p-4 border-top">
                    <div class="text-muted small">
                        <i class="fas fa-info-circle mr-1"></i>
                        Affichage de <strong>{{ $products->firstItem() }}</strong> à
                        <strong>{{ $products->lastItem() }}</strong> sur
                        <strong>{{ $products->total() }}</strong> produits
                    </div>
                    <div>
                        {{ $products->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="p-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-inbox fa-4x text-muted"></i>
                    </div>
                    <h5 class="text-muted">Aucun produit trouvé</h5>
                    <p class="text-muted mb-4">
                        Essayez de modifier vos filtres ou créez-en un nouveau
                    </p>
                    @can('create_products')
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addProductModal">
                            <i class="fas fa-plus-circle mr-2"></i> Créer un nouveau produit
                        </button>
                    @endcan
                </div>
            @endif
        </div>
    </div>

    <!-- Modal : Ajout d'un produit -->
    @can('create_products')
        <div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalLabel"
            aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-product-compact" role="document">
                <div class="modal-content">
                    @include('back.products._form', [
                        'route' => route('products.store'),
                        'method' => 'POST',
                        'product' => new \App\Models\Product(),
                        'categories' => \App\Models\Category::all(),
                        'units' => \App\Models\Units::all(),
                    ])
                </div>
            </div>
        </div>
    @endcan

    <!-- Modal : Ajout d'une catégorie -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                @include('back.categories._form', [
                    'route' => route('categories.store'),
                    'method' => 'POST',
                    'categorie' => new \App\Models\Category(),
                ])
            </div>
        </div>
    </div>

@endsection
