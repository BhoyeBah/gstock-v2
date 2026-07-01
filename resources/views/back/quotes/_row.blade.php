@php
    $warehouseId = $row['warehouse_id'] ?? '';
    $productId = $row['product_id'] ?? '';
    $quantity = $row['quantity'] ?? 1;
    $unitPrice = $row['unit_price'] ?? 0;
    $discount = $row['discount'] ?? 0;
    $taxRateId = $row['tax_rate_id'] ?? '';
@endphp
<tr>
    <td>
        <select name="items[{{ $index }}][warehouse_id]" class="form-control" required>
            <option value="">Sélectionner</option>
            @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" @selected($warehouseId == $warehouse->id)>{{ $warehouse->name }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <select name="items[{{ $index }}][product_id]" class="form-control" required>
            <option value="">Sélectionner</option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}" @selected($productId == $product->id)>{{ $product->name }}</option>
            @endforeach
        </select>
    </td>
    <td><input type="number" name="items[{{ $index }}][quantity]" class="form-control" min="1" value="{{ $quantity }}" required></td>
    <td><input type="number" name="items[{{ $index }}][unit_price]" class="form-control" min="0" value="{{ $unitPrice }}" required></td>
    <td><input type="number" name="items[{{ $index }}][discount]" class="form-control" min="0" value="{{ $discount }}"></td>
    <td>
        <select name="items[{{ $index }}][tax_rate_id]" class="form-control">
            <option value="">TVA par défaut</option>
            @foreach ($taxRates as $taxRate)
                <option value="{{ $taxRate->id }}" @selected($taxRateId == $taxRate->id)>
                    {{ $taxRate->name }} ({{ $taxRate->rate }}%)
                </option>
            @endforeach
        </select>
    </td>
    <td class="text-center">
        <button type="button" class="btn btn-sm btn-outline-danger remove-quote-row">X</button>
    </td>
</tr>
