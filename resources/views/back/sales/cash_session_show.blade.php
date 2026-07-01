@extends('back.layouts.admin')

@section('content')
@php
    $collected = $cashSession->collectedAmount();
    $expectedLive = $cashSession->expected_amount ?? ($cashSession->opening_amount + $collected);
@endphp
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-cash-register mr-2"></i>Caisse — {{ optional($cashSession->wallet)->name ?? '—' }}</h1>
        <a href="{{ route('cash-sessions.index') }}" class="btn btn-sm btn-secondary">Retour</a>
    </div>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Détails</h6></div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><th>Statut</th><td>{!! $cashSession->isOpen() ? '<span class="badge badge-success">Ouverte</span>' : '<span class="badge badge-secondary">Clôturée</span>' !!}</td></tr>
                        <tr><th>Ouverte le</th><td>{{ $cashSession->opened_at->format('d/m/Y H:i') }}</td></tr>
                        <tr><th>Ouverte par</th><td>{{ optional($cashSession->user)->name ?? '—' }}</td></tr>
                        <tr><th>Fonds d'ouverture</th><td>{{ number_format($cashSession->opening_amount, 0, ',', ' ') }}</td></tr>
                        <tr><th>Encaissements</th><td>{{ number_format($collected, 0, ',', ' ') }}</td></tr>
                        <tr><th>Attendu en caisse</th><td><strong>{{ number_format($expectedLive, 0, ',', ' ') }}</strong></td></tr>
                        @unless ($cashSession->isOpen())
                            <tr><th>Compté</th><td>{{ number_format($cashSession->counted_amount, 0, ',', ' ') }}</td></tr>
                            <tr><th>Écart</th><td class="{{ $cashSession->difference < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($cashSession->difference, 0, ',', ' ') }}</td></tr>
                            <tr><th>Fermée le</th><td>{{ optional($cashSession->closed_at)->format('d/m/Y H:i') }}</td></tr>
                        @endunless
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            @if ($cashSession->isOpen())
                <div class="card shadow mb-4">
                    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-danger">Clôturer la caisse</h6></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('cash-sessions.close', $cashSession->id) }}">
                            @csrf
                            <div class="form-group">
                                <label>Montant physiquement compté</label>
                                <input type="number" name="counted_amount" class="form-control" value="{{ $expectedLive }}" min="0" required>
                                <small class="text-muted">Attendu : {{ number_format($expectedLive, 0, ',', ' ') }}</small>
                            </div>
                            <div class="form-group">
                                <label>Note (optionnel)</label>
                                <input type="text" name="note" class="form-control" maxlength="500">
                            </div>
                            <button class="btn btn-danger btn-block"><i class="fas fa-lock mr-1"></i> Clôturer</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Encaissements de la session</h6></div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-sm">
                <thead><tr><th>Date</th><th>Facture</th><th class="text-right">Montant</th></tr></thead>
                <tbody>
                    @forelse ($cashSession->payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_date->format('d/m/Y H:i') }}</td>
                            <td>{{ optional($payment->invoice)->invoice_number ?? '—' }}</td>
                            <td class="text-right">{{ number_format($payment->amount_paid, 0, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted">Aucun encaissement.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
