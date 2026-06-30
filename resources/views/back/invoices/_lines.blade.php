@php
    $invoiceItems = isset($invoice) && $invoice->items ? $invoice->items : collect([]);
    $type = request()->route('type') ?? ($invoice->type ?? 'clients');
    $isSupplier = in_array($type, ['fournisseur', 'fournisseurs', 'supplier', 'suppliers']);
@endphp

<div class="table-responsive">
    <table class="table table-bordered" id="invoiceLinesTable">
        <thead class="thead-light">
            <tr>
                <th>Entrêpot</th>
                {{-- <th>Produit</th> --}}
                 <th style="min-width: 250px;">Produit</th>
                <th>Quantité</th>
                <th>Prix d'achat</th>
                <th>Remise</th>

                @if ($isSupplier)
                    <th>Date d'expiration</th>
                @endif

                <th>Total</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse($invoiceItems as $index => $item)
                <tr>
                    <td>
                        <select name="items[{{ $index }}][warehouse_id]" class="form-control warehouseSelect"
                            required>
                            <option value="">Sélectionnez un entrepôt</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}"
                                    {{ $item->warehouse_id == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>

                    <td>
                        <select name="items[{{ $index }}][product_id]" class="form-control productSelect"
                            required>
                            <option value="">Sélectionnez un produit</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->price }}"
                                    data-is-perishable="{{ $product->is_perishable }}"
                                    {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>

                    <td><input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity"
                            value="{{ $item->quantity }}" min="1" required></td>
                    <td><input type="number" name="items[{{ $index }}][unit_price]"
                            class="form-control unit_price" value="{{ $item->unit_price ?? 0 }}" min="0"
                            required></td>
                    <td><input type="number" name="items[{{ $index }}][discount]" class="form-control discount"
                            value="{{ $item->discount ?? 0 }}" min="0">
                    </td>

                    @if ($isSupplier)
                        <td>
                            <input type="date" name="items[{{ $index }}][expiration_date]"
                                class="form-control expiration_date" value="{{ $item->expiration_date ?? '' }}">
                        </td>
                    @endif

                    <td class="total_line">
                        {{ $item->quantity * ($item->unit_price ?? 0) - ($item->discount ?? 0) }}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger removeLineBtn"><i
                                class="fas fa-trash"></i></button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td>
                        <select name="items[0][warehouse_id]" class="form-control warehouseSelect" required>
                            <option value="">Sélectionnez un entrepôt</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select name="items[0][product_id]" class="form-control productSelect" required>
                            <option value="">Sélectionnez un produit</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->price }}"
                                    data-is-perishable="{{ $product->is_perishable }}">
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>

                    <td><input type="number" name="items[0][quantity]" class="form-control quantity" value="1"
                            min="1" required></td>
                    <td><input type="number" name="items[0][unit_price]" class="form-control unit_price" value="0"
                            min="0" required></td>
                    <td><input type="number" name="items[0][discount]" class="form-control discount" value="0"
                            min="0"></td>

                    @if ($isSupplier)
                        <td><input type="date" name="items[0][expiration_date]" class="form-control expiration_date"
                                disabled></td>
                    @endif

                    <td class="total_line">0</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger removeLineBtn"><i
                                class="fas fa-trash"></i></button>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<button type="button" class="btn btn-sm btn-primary mt-2" id="addLineBtn">
    <i class="fas fa-plus"></i> Ajouter une ligne
</button>

<template id="invoice-line-template">
    <tr>
        <td>
            <select name="items[__INDEX__][warehouse_id]" class="form-control warehouseSelect" required>
                <option value="">Sélectionnez un entrepôt</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                @endforeach
            </select>
        </td>

        <td>
            <select name="items[__INDEX__][product_id]" class="form-control productSelect" required>
                <option value="">Sélectionnez un produit</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-is-perishable="{{ $product->is_perishable }}">
                        {{ $product->name }}
                    </option>
                @endforeach
            </select>
        </td>

        <td><input type="number" name="items[__INDEX__][quantity]" class="form-control quantity" value="1" min="1" required></td>
        <td><input type="number" name="items[__INDEX__][unit_price]" class="form-control unit_price" value="0" min="0" required></td>
        <td><input type="number" name="items[__INDEX__][discount]" class="form-control discount" value="0" min="0"></td>
        __EXPIRATION_CELL__
        <td class="total_line">0</td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-danger removeLineBtn"><i class="fas fa-trash"></i></button></td>
    </tr>
</template>

<div class="mt-3 text-right">
    <strong>Total Réduction : </strong> <span id="invoiceDiscountTotal">0</span> FCFA<br>
    <strong>Total Facture : </strong> <span id="invoiceTotal">0</span> FCFA
</div>

@push('scripts')
    <script>
        const isSupplier = @json($isSupplier);
        const lineTemplate = document.getElementById('invoice-line-template');
        let lineIndex = document.querySelectorAll('#invoiceLinesTable tbody tr').length;

        function toNumber(v) {
            const n = Number(v);
            return isFinite(n) ? n : 0;
        }

        function updateLineTotal(row) {
            const qty = toNumber(row.querySelector('.quantity').value);
            const price = toNumber(row.querySelector('.unit_price').value);
            const discount = toNumber(row.querySelector('.discount').value);
            row.querySelector('.total_line').textContent = Math.max(0, qty * price - discount);
            updateInvoiceTotals();
        }

        function updateInvoiceTotals() {
            let total = 0,
                discountTotal = 0;
            document.querySelectorAll('#invoiceLinesTable tbody tr').forEach(row => {
                total += toNumber(row.querySelector('.total_line').textContent);
                discountTotal += toNumber(row.querySelector('.discount').value);
            });
            document.getElementById('invoiceTotal').textContent = total;
            document.getElementById('invoiceDiscountTotal').textContent = discountTotal;
        }

        function updateExpirationInput(row) {
            if (!isSupplier) return;
            const productSelect = row.querySelector('.productSelect');
            const expirationInput = row.querySelector('.expiration_date');
            if (!expirationInput) return;

            const isPerishable = productSelect.selectedOptions[0]?.dataset.isPerishable == "1";
            if (isPerishable) expirationInput.removeAttribute('disabled');
            else {
                expirationInput.value = "";
                expirationInput.setAttribute('disabled', true);
            }
        }

        function reindexRows() {
            document.querySelectorAll('#invoiceLinesTable tbody tr').forEach((row, i) => {
                row.querySelectorAll('select, input').forEach(input => {
                    const name = input.getAttribute('name');
                    if (name) input.setAttribute('name', name.replace(/\[\d+\]/, `[${i}]`));
                });
            });
            lineIndex = document.querySelectorAll('#invoiceLinesTable tbody tr').length;
        }

        document.getElementById('addLineBtn').addEventListener('click', () => {
            const tbody = document.querySelector('#invoiceLinesTable tbody');
            if (!tbody || !lineTemplate) return;

            const expirationCell = isSupplier
                ? `<td><input type="date" name="items[${lineIndex}][expiration_date]" class="form-control expiration_date" disabled></td>`
                : '';

            const wrapper = document.createElement('tbody');
            wrapper.innerHTML = lineTemplate.innerHTML
                .replaceAll('__INDEX__', String(lineIndex))
                .replace('__EXPIRATION_CELL__', expirationCell);

            const clone = wrapper.querySelector('tr');

            tbody.appendChild(clone);

            const newRow = tbody.querySelector('tr:last-child');
            updateLineTotal(newRow);
            updateExpirationInput(newRow);
            reindexRows();
            updateInvoiceTotals();
        });

        document.querySelector('#invoiceLinesTable tbody').addEventListener('change', function(e) {
            const row = e.target.closest('tr');
            if (e.target.classList.contains('productSelect')) {
                const price = e.target.selectedOptions[0]?.dataset.price || 0;
                row.querySelector('.unit_price').value = price;
                updateLineTotal(row);
                updateExpirationInput(row);
            }
        });

        document.querySelector('#invoiceLinesTable tbody').addEventListener('click', e => {
            if (e.target.closest('.removeLineBtn')) {
                e.target.closest('tr').remove();
                reindexRows();
                updateInvoiceTotals();
            }
        });

        document.querySelector('#invoiceLinesTable tbody').addEventListener('input', function(e) {
            if (e.target.classList.contains('quantity') || e.target.classList.contains('unit_price') || e.target
                .classList.contains('discount')) {
                updateLineTotal(e.target.closest('tr'));
            }
        });

        document.querySelectorAll('#invoiceLinesTable tbody tr').forEach(row => {
            updateLineTotal(row);
            updateExpirationInput(row);
        });
    </script>
@endpush
