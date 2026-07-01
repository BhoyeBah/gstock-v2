@extends('back.layouts.admin')

@section('content')
    @php
        $status = $record->status ?? 'draft';
        $statusMap = [
            'draft' => ['label' => 'Brouillon', 'class' => 'neutral', 'icon' => 'pen'],
            'validated' => ['label' => 'Validé', 'class' => 'info', 'icon' => 'check-circle'],
            'applied' => ['label' => 'Appliqué', 'class' => 'success', 'icon' => 'file-invoice-dollar'],
            'refunded' => ['label' => 'Remboursé', 'class' => 'success', 'icon' => 'money-bill-wave'],
            'cancelled' => ['label' => 'Annulé', 'class' => 'danger', 'icon' => 'ban'],
        ];
        $state = $statusMap[$status] ?? ['label' => ucfirst((string) $status), 'class' => 'info', 'icon' => 'circle-info'];
        $canRefund = $record->remaining_amount > 0;
    @endphp

    <div class="container-fluid">
        <div class="page-hero page-hero--accent mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="page-hero__eyebrow mb-2">Avoir fournisseur</div>
                    <h1 class="page-hero__title">{{ $record->credit_note_number }}</h1>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="status-pill status-pill--{{ $state['class'] }}"><i class="fas fa-{{ $state['icon'] }}"></i>{{ $state['label'] }}</span>
                        <span class="status-pill status-pill--info">{{ $record->contact?->fullname ?? 'N/A' }}</span>
                        <span class="status-pill status-pill--neutral">{{ optional($record->credit_date)->format('d/m/Y') }}</span>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('supplier-credit-notes.print', $record) }}" class="btn btn-outline-primary">
                        <i class="fas fa-print mr-1"></i> Imprimer
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card table-card">
                    <div class="card-body">
                        <h4 class="mb-3">Lignes de l’avoir</h4>
                        <div class="table-responsive">
                            <table class="table data-table">
                                <thead>
                                    <tr>
                                        <th>Produit</th>
                                        <th class="text-right">Qté</th>
                                        <th class="text-right">PU HT</th>
                                        <th class="text-right">TVA</th>
                                        <th class="text-right">Total TTC</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($record->items as $item)
                                        <tr>
                                            <td>{{ $item->product?->name ?? 'Produit' }}</td>
                                            <td class="text-right">{{ $item->quantity }}</td>
                                            <td class="text-right">{{ number_format($item->unit_cost_ht, 0, ',', ' ') }}</td>
                                            <td class="text-right">{{ number_format($item->tax_amount, 0, ',', ' ') }}</td>
                                            <td class="text-right font-weight-bold">{{ number_format($item->total_ttc, 0, ',', ' ') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Aucune ligne.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card table-card mb-4">
                    <div class="card-body">
                        <h4 class="mb-3">Résumé financier</h4>
                        <div class="info-row">
                            <div class="info-row__label">Facture liée</div>
                            <div class="info-row__value">{{ $record->invoice?->invoice_number ?? 'Aucune' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-row__label">Total TTC</div>
                            <div class="info-row__value">{{ number_format($record->total_ttc, 0, ',', ' ') }} FCFA</div>
                        </div>
                        <div class="info-row">
                            <div class="info-row__label">Montant appliqué</div>
                            <div class="info-row__value">{{ number_format($record->applied_amount, 0, ',', ' ') }} FCFA</div>
                        </div>
                        <div class="info-row">
                            <div class="info-row__label">Crédit restant</div>
                            <div class="info-row__value">{{ number_format($record->remaining_amount, 0, ',', ' ') }} FCFA</div>
                        </div>

                        @if($record->supplierReturn)
                            <div class="alert alert-light border mt-3 mb-0">
                                Retour source {{ $record->supplierReturn->return_number }}.
                                @if($record->invoice)
                                    <div class="mt-2">
                                        Nouveau solde facture : <strong>{{ number_format($record->invoice->balance, 0, ',', ' ') }} FCFA</strong>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card table-card">
                    <div class="card-body">
                        <h4 class="mb-3">Remboursement</h4>
                        @if($canRefund)
                            <form action="{{ route('supplier-credit-notes.refund', $record) }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label>Wallet</label>
                                    <select name="wallet_id" class="form-control" required>
                                        @foreach(\App\Models\Wallet::query()->where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get() as $wallet)
                                            <option value="{{ $wallet->id }}">{{ $wallet->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Montant</label>
                                    <input type="number" name="amount" class="form-control" min="1" max="{{ $record->remaining_amount }}" value="{{ $record->remaining_amount }}" required>
                                </div>
                                <div class="form-group">
                                    <label>Note</label>
                                    <input type="text" name="note" class="form-control" placeholder="Remboursement fournisseur">
                                </div>
                                <button class="btn btn-success btn-block" type="submit">
                                    <i class="fas fa-check mr-1"></i> Enregistrer remboursement
                                </button>
                            </form>
                        @else
                            <div class="alert alert-success mb-0">Aucun remboursement en attente.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
