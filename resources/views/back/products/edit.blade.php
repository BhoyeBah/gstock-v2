@extends('back.layouts.admin')

@section('content')
    @push('styles')
        <style>
        /* Namespace product- pour éviter les collisions globales */
        .product-page-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .product-page-header h1 {
            color: #fff;
            font-weight: 700;
            margin: 0;
            font-size: 1.75rem;
        }
        
        .product-page-header h1 .product-name {
            font-weight: 400;
            opacity: 0.95;
        }

        .product-page-header .btn {
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            border: 2px solid rgba(255, 255, 255, 0.4);
            font-weight: 600;
            padding: 0.6rem 1.5rem;
            border-radius: 10px;
        }

        .product-page-header .btn:hover {
            background: #fff;
            color: #7c3aed;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            border-color: #fff;
        }

        .product-info-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            border: none;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .product-info-card .card-header {
            background: linear-gradient(135deg, #4f46e5 0%, #224abe 100%);
            color: #fff;
            border-bottom: none;
            padding: 1.5rem;
        }

        .product-info-card .card-header h5 {
            color: #fff;
            margin: 0;
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .product-info-card .card-body {
            padding: 2rem;
        }

        .product-detail-row {
            display: flex;
            padding: 1rem 0;
            border-bottom: 1px solid #e3e6f0;
            align-items: center;
        }

        .product-detail-row:last-child {
            border-bottom: none;
        }

        .product-detail-label {
            font-weight: 600;
            color: #5a5c69;
            min-width: 180px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .product-detail-label i {
            color: #4f46e5;
            width: 20px;
        }

        .product-detail-value {
            color: #858796;
            flex: 1;
        }

        .product-image-display {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #e3e6f0;
        }

        .product-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-perishable {
            background: linear-gradient(135deg, #475569 0%, #334155 100%);
            color: #fff;
        }

        .badge-non-perishable {
            background: linear-gradient(135deg, #858796 0%, #60616f 100%);
            color: #fff;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }

        /* BOUTONS MODERNES */
        .btn {
            border-radius: 10px;
            padding: 0.65rem 1.75rem;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #224abe 100%);
            color: #fff;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
            color: #fff;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #858796 0%, #60616f 100%);
            color: #fff;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #6c6d7c 0%, #4e4f5c 100%);
            color: #fff;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
            color: #fff;
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #f4b619 0%, #c9930a 100%);
            color: #fff;
        }

        /* Modal styling */
        .modal-content {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .product-page-header h1 {
                font-size: 1.5rem;
            }
            
            .btn {
                padding: 0.5rem 1.25rem;
                font-size: 0.9rem;
            }

            .product-detail-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .product-detail-label {
                min-width: auto;
            }
        }
        </style>
    @endpush

    <!-- En-tête de page -->
    <div class="product-page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <h1>
                <i class="fas fa-box-open me-2"></i>
                Produit <span class="product-name d-none d-sm-inline">- {{ $product->name }}</span>
            </h1>
            <a href="{{ route('products.index') }}" class="btn mt-2 mt-md-0">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Colonne principale : Informations du produit -->
        <div class="col-lg-8 mb-4">
            <div class="card product-info-card">
                <div class="card-header">
                    <h5>
                        <i class="fas fa-info-circle"></i>
                        Détails du produit
                    </h5>
                </div>
                <div class="card-body">
                    <div class="product-detail-row">
                        <div class="product-detail-label">
                            <i class="fas fa-tag"></i>
                            Nom
                        </div>
                        <div class="product-detail-value">
                            <strong>{{ $product->name }}</strong>
                        </div>
                    </div>

                    <div class="product-detail-row">
                        <div class="product-detail-label">
                            <i class="fas fa-tags"></i>
                            Catégorie
                        </div>
                        <div class="product-detail-value">
                            {{ $product->category->name ?? 'Non définie' }}
                        </div>
                    </div>

                    <div class="product-detail-row">
                        <div class="product-detail-label">
                            <i class="fas fa-balance-scale"></i>
                            Unité
                        </div>
                        <div class="product-detail-value">
                            {{ $product->unit->name ?? 'Non définie' }}
                        </div>
                    </div>

                    <div class="product-detail-row">
                        <div class="product-detail-label">
                            <i class="fas fa-money-bill-wave"></i>
                            Prix de vente
                        </div>
                        <div class="product-detail-value">
                            <strong>{{ number_format($product->price, 0, ',', ' ') }} FCFA</strong>
                        </div>
                    </div>

                    <div class="product-detail-row">
                        <div class="product-detail-label">
                            <i class="fas fa-exclamation-triangle"></i>
                            Seuil d'alerte
                        </div>
                        <div class="product-detail-value">
                            {{ $product->seuil_alert ?? 'Non défini' }}
                        </div>
                    </div>

                    <div class="product-detail-row">
                        <div class="product-detail-label">
                            <i class="fas fa-leaf"></i>
                            Périssable
                        </div>
                        <div class="product-detail-value">
                            @if($product->is_perishable)
                                <span class="product-badge badge-perishable">
                                    <i class="fas fa-check-circle"></i>
                                    Oui
                                </span>
                            @else
                                <span class="product-badge badge-non-perishable">
                                    <i class="fas fa-times-circle"></i>
                                    Non
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($product->description)
                    <div class="product-detail-row">
                        <div class="product-detail-label">
                            <i class="fas fa-align-left"></i>
                            Description
                        </div>
                        <div class="product-detail-value">
                            {{ $product->description }}
                        </div>
                    </div>
                    @endif

                    <div class="action-buttons">
                        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#editProductModal">
                            <i class="fas fa-edit"></i> Modifier le produit
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne latérale : Image -->
        <div class="col-lg-4 mb-4">
            <div class="card product-info-card">
                <div class="card-header">
                    <h5>
                        <i class="fas fa-image"></i>
                        Image du produit
                    </h5>
                </div>
                <div class="card-body text-center">
                    @if($product->image)
                        <img src="{{ Storage::url($product->image) }}" 
                             alt="{{ $product->name }}" 
                             class="product-image-display">
                    @else
                        <img src="https://via.placeholder.com/400x300.png?text=Aucune+Image" 
                             alt="Pas d'image" 
                             class="product-image-display">
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour éditer le produit -->
    <div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-labelledby="editProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                @include('back.products._form', [
                    'route' => route('products.update', $product->id),
                    'method' => 'PUT',
                    'product' => $product,
                    'categories' => $categories,
                    'units' => $units
                ])
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Si des erreurs de validation existent, ouvrir automatiquement le modal
        @if($errors->any())
            $('#editProductModal').modal('show');
        @endif
    });
</script>
@endpush