@extends('back.layouts.admin')

@section('content')
    <div class="page-hero page-hero--accent mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <div class="page-hero__eyebrow mb-2">Stock & logistique</div>
                <h1 class="page-hero__title mb-0">Transfert de stock</h1>
                <p class="page-hero__subtitle">Déplacez des lots d’un entrepôt à un autre avec traçabilité complète.</p>
            </div>
            <a href="{{ route('warehouses.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left mr-1"></i> Retour
            </a>
        </div>
    </div>

    <div class="panel-card">
        <form action="{{ route('warehouses.exchange', $warehouse->id) }}" method="POST">
            @csrf
            <input type="hidden" name="from_warehouse" value="{{ $warehouse->id }}">

            <div class="row">
                <div class="col-lg-6 mb-3">
                    <label class="modern-label">Entrepôt source</label>
                    <input type="text" class="form-control" value="{{ $warehouse->name }}" disabled>
                </div>

                <div class="col-lg-6 mb-3">
                    <label class="modern-label">Entrepôt destination</label>
                    <select name="to_warehouse" class="form-control" required>
                        <option value="">Choisir...</option>
                        @foreach ($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="table-card mt-3">
                <div class="p-3 border-bottom">
                    <h5 class="mb-1 font-weight-bold">Lots à transférer</h5>
                    <div class="text-muted">Ajoutez une ou plusieurs lignes selon les besoins.</div>
                </div>

                <div class="table-responsive">
                    <table class="table data-table" id="transferTable">
                        <thead>
                            <tr>
                                <th style="width: 65%">Lot (Produit)</th>
                                <th style="width: 25%">Quantité</th>
                                <th style="width: 10%" class="text-center"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="transfer-row">
                                <td>
                                    <select name="batch_id[]" class="form-control" required>
                                        <option value="">Choisir lot...</option>
                                        @foreach ($warehouse->batches as $batch)
                                            <option value="{{ $batch->id }}">
                                                {{ $batch->product->name ?? 'Produit inconnu' }} - {{ $batch->remaining }} {{ $batch->product->unit->name ?? 'unités' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="quantity[]" min="1" class="form-control text-center" placeholder="Ex: 10" required>
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

                <div class="p-3 d-flex justify-content-between flex-wrap gap-2">
                    <button type="button" id="addRow" class="btn-modern btn-secondary">
                        <i class="fas fa-plus-circle"></i> Ajouter une ligne
                    </button>

                    <button type="submit" class="btn-modern btn-primary">
                        <i class="fas fa-check"></i> Valider le transfert
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('addRow').addEventListener('click', function() {
            let row = document.querySelector('.transfer-row').cloneNode(true);
            row.querySelectorAll('input, select').forEach(input => input.value = '');
            document.querySelector('#transferTable tbody').appendChild(row);
        });

        document.addEventListener('click', function(e) {
            if (e.target.closest('.removeRow')) {
                if (document.querySelectorAll('.transfer-row').length > 1) {
                    e.target.closest('tr').remove();
                }
            }
        });
    </script>
@endpush
