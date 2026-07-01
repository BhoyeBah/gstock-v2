@extends('back.layouts.admin')

@section('content')
    <div class="container mt-4">
        <div class="card border-0 shadow-sm rounded-lg">
            <!-- HEADER -->
            <div class="card-header bg-gradient-info text-white d-flex justify-content-between align-items-center py-3"
                style="background: linear-gradient(45deg, #475569, #334155);">
                <h5 class="mb-0"><i class="fas fa-exchange-alt"></i> Transfert de Stock</h5>
                <a href="{{ route('warehouses.index') }}" class="btn btn-light btn-sm shadow-sm">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>

            <!-- BODY -->
            <div class="card-body p-4">
                <form action="{{ route('warehouses.exchange', $warehouse->id) }}" method="POST">
                    @csrf
                    <!-- ID entrepôt source caché -->
                    <input type="hidden" name="from_warehouse" value="{{ $warehouse->id }}">

                    <!-- ENTREPÔTS -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Entrepôt source</label>
                            <input type="text" class="form-control form-control-lg rounded"
                                value="{{ $warehouse->name }}" disabled>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Entrepôt destination</label>
                            <select name="to_warehouse" class="form-control form-control-lg rounded" required>
                                <option selected disabled>Choisir...</option>
                                @foreach ($warehouses as $wh)
                                    <!-- <-- Utiliser $warehouses ici -->
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>


                    </div>

                    <hr class="my-4">

                    <!-- TABLEAU PRODUITS -->
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-hover align-middle" id="transferTable"
                            style="background: #f9f9f9;">
                            <thead class="thead-light">
                                <tr class="text-center text-secondary">
                                    <th style="width: 65%">Lot (Produit)</th>
                                    <th style="width: 25%">Quantité</th>
                                    <th style="width: 10%"></th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr class="transfer-row">
                                    <td>
                                        <select name="batch_id[]" class="form-control rounded" required>
                                            <option selected disabled>Choisir lot...</option>
                                            @foreach ($warehouse->batches as $batch)
                                                <option value="{{ $batch->id }}">
                                                    Lot de {{ $batch->remaining }} {{ $batch->product->unit->name }}
                                                    ({{ $batch->product->name ?? 'Produit inconnu' }})

                                                </option>
                                            @endforeach
                                        </select>

                                    </td>



                                    <td>
                                        <input type="number" name="quantity[]" min="1"
                                            class="form-control text-center rounded" placeholder="Ex: 10" required>
                                    </td>

                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger removeRow">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- ADD ROW BUTTON -->
                    <div class="text-right mb-4">
                        <button type="button" id="addRow" class="btn btn-outline-secondary btn-sm shadow-sm rounded">
                            <i class="fas fa-plus-circle"></i> Ajouter un produit
                        </button>
                    </div>

                    <!-- VALIDATION BUTTON -->
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary px-5 py-2 shadow-sm rounded-lg">
                            <i class="fas fa-check"></i> Valider le transfert
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Ajouter une ligne
        document.getElementById('addRow').addEventListener('click', function() {
            let row = document.querySelector('.transfer-row').cloneNode(true);
            row.querySelectorAll('input, select').forEach(input => input.value = '');
            document.querySelector('#transferTable tbody').appendChild(row);
        });

        // Supprimer une ligne
        document.addEventListener('click', function(e) {
            if (e.target.closest('.removeRow')) {
                if (document.querySelectorAll('.transfer-row').length > 1) {
                    e.target.closest('tr').remove();
                }
            }
        });
    </script>
@endpush
