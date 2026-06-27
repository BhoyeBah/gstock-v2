@php
    $rows = old('items');
    if ($rows === null) {
        $rows = isset($quote) && $quote && $quote->items->count()
            ? $quote->items->map(function ($item) {
                return [
                    'warehouse_id' => $item->warehouse_id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount' => $item->discount,
                    'tax_rate_id' => $item->tax_rate_id,
                ];
            })->toArray()
            : [[]];
    }
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @isset($method)
        @method($method)
    @endisset

    <div class="form-row">
        <div class="form-group col-md-4">
            <label>Client</label>
            <select name="contact_id" class="form-control" required>
                <option value="">Sélectionner</option>
                @foreach ($contacts as $contact)
                    <option value="{{ $contact->id }}" @selected(old('contact_id', optional($quote)->contact_id) == $contact->id)>
                        {{ $contact->fullname }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-2">
            <label>Date devis</label>
            <input type="date" name="quote_date" class="form-control" value="{{ old('quote_date', optional(optional($quote)->quote_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
        </div>
        <div class="form-group col-md-2">
            <label>Date expiration</label>
            <input type="date" name="expiry_date" class="form-control" value="{{ old('expiry_date', optional(optional($quote)->expiry_date)->format('Y-m-d')) }}">
        </div>
        <div class="form-group col-md-2">
            <label>Statut</label>
            <select name="status" class="form-control">
                @foreach (['draft', 'sent', 'accepted', 'rejected', 'expired'] as $status)
                    <option value="{{ $status }}" @selected(old('status', optional($quote)->status ?? 'draft') === $status)>
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-2">
            <label>Réf</label>
            <input type="text" class="form-control" value="{{ optional($quote)->quote_number ?? 'Auto' }}" readonly>
        </div>
    </div>

    <div class="form-group">
        <label>Notes</label>
        <textarea name="notes" class="form-control" rows="3">{{ old('notes', optional($quote)->notes ?? '') }}</textarea>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Lignes de devis</strong>
            <button type="button" class="btn btn-sm btn-outline-primary" id="add-quote-row">Ajouter une ligne</button>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Entrepôt</th>
                        <th>Produit</th>
                        <th>Qté</th>
                        <th>PU</th>
                        <th>Remise</th>
                        <th>Taxe</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="quote-items-body">
                    @foreach ($rows as $index => $row)
                        @include('back.quotes._row', [
                            'index' => $index,
                            'row' => $row,
                            'warehouses' => $warehouses,
                            'products' => $products,
                            'taxRates' => $taxRates,
                        ])
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-right">
        <button class="btn btn-primary" type="submit">Enregistrer</button>
    </div>
</form>

<template id="quote-row-template">
    @include('back.quotes._row', [
        'index' => '__INDEX__',
        'row' => [],
        'warehouses' => $warehouses,
        'products' => $products,
        'taxRates' => $taxRates,
    ])
</template>

@push('scripts')
<script>
    (function () {
        const body = document.getElementById('quote-items-body');
        const template = document.getElementById('quote-row-template').innerHTML;
        const addButton = document.getElementById('add-quote-row');
        let index = body.querySelectorAll('tr').length;

        addButton?.addEventListener('click', function () {
            const rowHtml = template.replaceAll('__INDEX__', index);
            body.insertAdjacentHTML('beforeend', rowHtml);
            index += 1;
        });

        body.addEventListener('click', function (event) {
            if (event.target.closest('.remove-quote-row')) {
                const row = event.target.closest('tr');
                if (row && body.querySelectorAll('tr').length > 1) {
                    row.remove();
                }
            }
        });
    })();
</script>
@endpush
