@extends('back.layouts.admin')

@section('content')
    @php
        $isCustomer = ($module ?? 'customer') === 'customer';
        $status = $record->status ?? 'draft';
        $statusMap = [
            'draft' => ['label' => 'Brouillon', 'class' => 'neutral', 'icon' => 'pen'],
            'validated' => ['label' => 'Validé', 'class' => 'success', 'icon' => 'check-circle'],
            'cancelled' => ['label' => 'Annulé', 'class' => 'danger', 'icon' => 'ban'],
        ];
        $state = $statusMap[$status] ?? ['label' => ucfirst((string) $status), 'class' => 'info', 'icon' => 'circle-info'];
        $itemsCollection = collect($items ?? []);
        $totalLines = $itemsCollection->count();
        $totalReturned = $itemsCollection->sum('quantity_returned');
        $documentSource = $isCustomer
            ? ($record->invoice?->invoice_number ?? $record->deliveryNote?->delivery_number ?? 'N/A')
            : ($record->supplierInvoice?->invoice_number ?? $record->goodsReceipt?->receipt_number ?? 'N/A');
        $documentKind = $isCustomer ? 'Bon de retour client' : 'Bon de retour fournisseur';
    @endphp

    <style>
        .print-document {
            background: #fff;
            color: #0f172a;
        }

        .print-brand {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .print-brand__mark {
            width: 56px;
            height: 56px;
            border-radius: 18px;
            background: linear-gradient(135deg, #1d4ed8, #0f172a);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            box-shadow: 0 14px 30px rgba(15, 23, 42, .12);
        }

        .print-brand__title {
            font-size: 1.45rem;
            font-weight: 900;
            line-height: 1;
        }

        .print-brand__subtitle {
            color: #64748b;
            font-size: .92rem;
        }

        .print-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .print-meta__card {
            border: 1px solid rgba(15, 23, 42, .08);
            border-radius: 16px;
            padding: .95rem 1rem;
            background: #f8fafc;
        }

        .print-meta__label {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #64748b;
            font-weight: 800;
        }

        .print-meta__value {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
            margin-top: .2rem;
        }

        .print-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .35rem .75rem;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .print-badge--success { background: rgba(22, 163, 74, .1); color: #166534; }
        .print-badge--neutral { background: rgba(100, 116, 139, .12); color: #334155; }
        .print-badge--danger { background: rgba(220, 38, 38, .1); color: #991b1b; }

        .print-section {
            border: 1px solid rgba(15, 23, 42, .08);
            border-radius: 18px;
            overflow: hidden;
        }

        .print-section__header {
            background: #f8fafc;
            padding: .9rem 1rem;
            border-bottom: 1px solid rgba(15, 23, 42, .08);
            font-weight: 800;
        }

        .print-signatures {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .print-signatures__box {
            min-height: 120px;
            border: 1px dashed rgba(15, 23, 42, .2);
            border-radius: 18px;
            padding: 1rem;
        }

        @media print {
            .no-print { display: none !important; }
            .print-card { box-shadow: none !important; border: 0 !important; }
            body { background: #fff !important; }
            .print-section, .print-meta__card { break-inside: avoid; }
        }
    </style>

    <div class="container-fluid print-document">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <div>
                <h1 class="h3 mb-1">Bon de retour {{ $isCustomer ? 'client' : 'fournisseur' }}</h1>
                <p class="mb-0 text-muted">{{ $record->return_number }}</p>
            </div>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print mr-1"></i> Imprimer
            </button>
        </div>

        <div class="card print-card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex justify-content-between flex-wrap align-items-start gap-3 mb-4">
                    <div class="print-brand">
                        <div class="print-brand__mark">
                            <i class="fas fa-undo"></i>
                        </div>
                        <div>
                            <div class="print-brand__title">{{ $documentKind }}</div>
                            <div class="print-brand__subtitle">StockPro · Document métier officiel</div>
                            <div class="mt-2">
                                <span class="print-badge print-badge--{{ $state['class'] }}">
                                    <i class="fas fa-{{ $state['icon'] }}"></i>{{ $state['label'] }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="text-right">
                        <div class="text-uppercase small font-weight-bold text-muted">Référence</div>
                        <div class="h4 mb-1">{{ $record->return_number }}</div>
                        <div class="text-muted">{{ optional($record->return_date)->format('d/m/Y') }}</div>
                    </div>
                </div>

                <div class="print-meta mb-4">
                    <div class="print-meta__card">
                        <div class="print-meta__label">Partenaire</div>
                        <div class="print-meta__value">{{ $record->contact?->fullname ?? 'N/A' }}</div>
                    </div>
                    <div class="print-meta__card">
                        <div class="print-meta__label">Document source</div>
                        <div class="print-meta__value">{{ $documentSource }}</div>
                    </div>
                    <div class="print-meta__card">
                        <div class="print-meta__label">Entrepôt</div>
                        <div class="print-meta__value">{{ $record->warehouse?->name ?? 'N/A' }}</div>
                    </div>
                    <div class="print-meta__card">
                        <div class="print-meta__label">Lignes / Quantité</div>
                        <div class="print-meta__value">{{ $totalLines }} ligne(s) · {{ number_format($totalReturned) }} unité(s)</div>
                    </div>
                </div>

                <div class="print-section mb-4">
                    <div class="print-section__header">Détail des lignes</div>
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Produit</th>
                                    <th>Source</th>
                                    <th class="text-right">Qté vendue / reçue</th>
                                    <th class="text-right">Qté retournée</th>
                                    <th class="text-right">PU HT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($itemsCollection as $item)
                                    <tr>
                                        <td>{{ $item->product?->name ?? 'Produit' }}</td>
                                        <td>{{ $item->invoiceItem?->invoice?->invoice_number ?? $item->deliveryNoteItem?->deliveryNote?->delivery_number ?? $item->goodsReceiptItem?->goodsReceipt?->receipt_number ?? 'N/A' }}</td>
                                        <td class="text-right">{{ $isCustomer ? $item->quantity_sold : $item->quantity_received }}</td>
                                        <td class="text-right font-weight-bold">{{ $item->quantity_returned }}</td>
                                        <td class="text-right">{{ number_format((int) ($item->unit_price_ht ?? $item->unit_cost_ht ?? 0), 0, ',', ' ') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Aucune ligne disponible</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="border rounded p-3">
                            <div class="text-muted text-uppercase small font-weight-bold mb-1">Motif</div>
                            <div>{{ $record->reason ?? 'Non renseigné' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3">
                            <div class="text-muted text-uppercase small font-weight-bold mb-1">Notes</div>
                            <div>{{ $record->notes ?? 'Aucune note' }}</div>
                        </div>
                    </div>
                </div>

                <div class="print-signatures">
                    <div class="print-signatures__box">
                        <div class="text-uppercase small font-weight-bold text-muted mb-2">Établi par</div>
                        <div class="text-muted">Signature et cachet</div>
                    </div>
                    <div class="print-signatures__box">
                        <div class="text-uppercase small font-weight-bold text-muted mb-2">Réception / Contrôle</div>
                        <div class="text-muted">Signature et validation interne</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
