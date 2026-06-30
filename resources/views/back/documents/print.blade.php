@extends('back.layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h1 class="h4 mb-3">{{ $title }}</h1>
            <p class="text-muted">{{ $record->status }}</p>

            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th class="text-right">Qté</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        <tr>
                            <td>{{ $item->product?->name }}</td>
                            <td class="text-right">{{ $item->quantity ?? $item->quantity_ordered ?? $item->quantity_received }}</td>
                            <td class="text-right">{{ number_format((int) ($item->total_ttc ?? $item->total_line ?? 0), 0, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
