@extends('back.layouts.admin')

@section('content')
    @php
        use Carbon\Carbon;
    @endphp

    <style>
        /* Styles similaires à ceux des produits pour uniformité */
        .page-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
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

        .stock-table thead th {
            background: #f8f9fc;
            font-weight: 700;
            font-size: 0.75rem;
            padding: 1rem 0.75rem;
        }

        .stock-table tbody td {
            padding: 1rem 0.75rem;
            vertical-align: middle;
        }
    </style>

    <!-- En-tête de page -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-truck-loading mr-2"></i>Liste des Sorties de stock</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#addStockOutModal">
            <i class="fas fa-plus-circle mr-1"></i> Nouvelle sortie
        </button>
    </div>

    <!-- Liste des sorties -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h6 class="m-0 font-weight-bold">Historique des sorties de stock</h6>
        </div>
        <div class="card-body p-0">
            @if ($stockOuts->count() > 0)
                <div class="table-responsive">
                    <table class="table stock-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Produit / Lot</th>
                                <th>Quantité</th>
                                <th>Motif</th>
                                <th>Date</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stockOuts as $stock)
                                <tr>
                                    <td>{{ $loop->iteration + ($stockOuts->currentPage() - 1) * $stockOuts->perPage() }}
                                    </td>
                                    <td>{{ $stock->batch->product->name ?? '-' }} / {{ $stock->batch->name ?? '-' }}</td>
                                    <td>{{ $stock->quantity }}</td>
                                    <td>{{ $stock->reason ?? '-' }}</td>
                                    <td>{{ $stock->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('stockout.destroy', $stock->id) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('Confirmer la suppression ?')">
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
                        Affichage de <strong>{{ $stockOuts->firstItem() }}</strong> à
                        <strong>{{ $stockOuts->lastItem() }}</strong> sur
                        <strong>{{ $stockOuts->total() }}</strong> sorties
                    </div>
                    <div>{{ $stockOuts->links() }}</div>
                </div>
            @else
                <div class="p-5 text-center">
                    <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucune sortie de stock trouvée</h5>
                    {{-- <p class="text-muted mb-4">Ajoutez une nouvelle sortie pour commencer.</p>
                <button class="btn btn-primary" data-toggle="modal" data-target="#addStockOutModal">
                    <i class="fas fa-plus-circle mr-2"></i> Nouvelle sortie
                </button> --}}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal : Ajout d'une sortie de stock -->
    <div class="modal fade" id="addStockOutModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('stockout.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white border-0">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-box-open mr-2"></i>Nouvelle sortie de stock
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="form-group">
                            <label for="batch_id" class="font-weight-semibold text-secondary">
                                <i class="fas fa-cubes mr-1"></i>Produit / Entrêpot
                            </label>
                            <select name="batch_id" id="batch_id" class="form-control form-control-lg shadow-sm" required>

                                <option value="">Sélectionnez produit </option>

                                @foreach ($batches as $batch)

                                    <option value="{{ $batch->id }}">
                                        {{ $batch->product->name ?? '-' }} / {{ $batch->warehouse->name }} ({{ $batch->remaining }})
                                    </option>
                                @endforeach

                            </select>
                            <small class="form-text text-muted">Choisissez le produit et son lot</small>
                        </div>

                        <div class="form-group">
                            <label for="quantity" class="font-weight-semibold text-secondary">
                                <i class="fas fa-sort-numeric-up mr-1"></i>Quantité
                            </label>
                            <input type="number" name="quantity" id="quantity"
                                class="form-control form-control-lg shadow-sm" min="1" placeholder="Ex: 10" required>
                            <small class="form-text text-muted">Indiquez la quantité à sortir</small>
                        </div>

                        <div class="form-group mb-0">
                            <label for="reason" class="font-weight-semibold text-secondary">
                                <i class="fas fa-comment-dots mr-1"></i>Motif <span
                                    class="badge badge-secondary badge-sm">Optionnel</span>
                            </label>
                            <input type="text" name="reason" id="reason"
                                class="form-control form-control-lg shadow-sm"
                                placeholder="Ex: Vente, casse, péremption...">
                        </div>
                    </div>

                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i>Annuler
                        </button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="fas fa-check mr-1"></i>Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


@endsection
