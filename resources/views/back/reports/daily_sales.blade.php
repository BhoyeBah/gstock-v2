@extends('back.layouts.admin')

@section('content')
@php
    $fmt = fn ($n) => number_format((int) $n, 0, ',', ' ');
@endphp
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-calendar-day mr-2"></i>Ventes du jour</h1>
        <form method="GET" action="{{ route('reports.daily-sales') }}" class="form-inline">
            <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm mr-2">
            <button class="btn btn-sm btn-primary">Afficher</button>
        </form>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Ventes</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $salesCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Chiffre d'affaires</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $fmt($totalSales) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Encaissé</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $fmt($totalCollected) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Restant dû (crédit)</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $fmt($totalOutstanding) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Encaissements par moyen</h6></div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            @forelse ($collectedByWallet as $walletName => $amount)
                                <tr><td>{{ $walletName }}</td><td class="text-right">{{ $fmt($amount) }}</td></tr>
                            @empty
                                <tr><td class="text-muted">Aucun encaissement.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Factures du {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</h6></div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>N°</th><th>Client</th><th>Statut</th>
                                <th class="text-right">Total</th><th class="text-right">Reste</th><th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($invoices as $invoice)
                                <tr>
                                    <td>{{ $invoice->invoice_number }}</td>
                                    <td>{{ optional($invoice->contact)->fullname ?? 'Passage' }}</td>
                                    <td><span class="badge badge-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'partial' ? 'warning' : 'info') }}">{{ $invoice->status }}</span></td>
                                    <td class="text-right">{{ $fmt($invoice->total_invoice) }}</td>
                                    <td class="text-right">{{ $fmt($invoice->balance) }}</td>
                                    <td><a href="{{ route('pos.receipt', $invoice->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fas fa-receipt"></i></a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">Aucune vente ce jour.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
