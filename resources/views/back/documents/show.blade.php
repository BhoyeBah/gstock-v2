@extends('back.layouts.admin')

@section('content')
    @php
        $table = $record->getTable();
        $documentKind = $documentKind ?? (
            \Illuminate\Support\Str::contains($table, 'quote') ? 'quote' : (
                \Illuminate\Support\Str::contains($table, 'sale_order') ? 'sale_order' : (
                    \Illuminate\Support\Str::contains($table, 'delivery_note') ? 'delivery_note' : 'receipt'
                )
            )
        );
        $numberField = \Illuminate\Support\Str::contains($table, 'quote') ? 'quote_number' : (
            \Illuminate\Support\Str::contains($table, 'sale_order') ? 'order_number' : (
                \Illuminate\Support\Str::contains($table, 'purchase_order') ? 'purchase_number' : (
                    \Illuminate\Support\Str::contains($table, 'delivery_note') ? 'delivery_number' : 'receipt_number'
                )
            )
        );

        $badge = fn ($status) => match ($status) {
            'draft' => ['class' => 'neutral', 'label' => 'Brouillon', 'icon' => 'pen'],
            'sent' => ['class' => 'info', 'label' => 'Envoyé', 'icon' => 'paper-plane'],
            'accepted' => ['class' => 'success', 'label' => 'Accepté', 'icon' => 'check-circle'],
            'rejected' => ['class' => 'danger', 'label' => 'Rejeté', 'icon' => 'times-circle'],
            'converted' => ['class' => 'success', 'label' => 'Converti', 'icon' => 'exchange-alt'],
            'confirmed' => ['class' => 'info', 'label' => 'Confirmé', 'icon' => 'thumbs-up'],
            'delivered', 'validated', 'received' => ['class' => 'success', 'label' => ucfirst($status), 'icon' => 'circle-check'],
            'partially_delivered', 'partially_received' => ['class' => 'warning', 'label' => 'Partiel', 'icon' => 'hourglass-half'],
            'cancelled' => ['class' => 'danger', 'label' => 'Annulé', 'icon' => 'ban'],
            default => ['class' => 'info', 'label' => ucfirst((string) $status), 'icon' => 'circle-info'],
        };

        $status = $badge($record->status);
        $amount = number_format((int) ($record->total_ttc ?? $record->total_invoice ?? 0), 0, ',', ' ') . ' FCFA';
        $isQuote = $documentKind === 'quote';
        $isSaleOrder = $documentKind === 'sale_order';
        $isDelivery = $documentKind === 'delivery_note';
        $primaryActions = [];
        $secondaryActions = [];

        if ($isQuote) {
            $printUrl = route('quotes.print', $record);

            if (in_array($record->status, ['draft', 'sent', 'accepted'], true)) {
                $primaryActions[] = [
                    'type' => 'post',
                    'label' => 'Convertir en facture',
                    'icon' => 'file-invoice',
                    'url' => route('quotes.convert-to-invoice', $record),
                    'variant' => 'primary',
                ];
                $primaryActions[] = [
                    'type' => 'post',
                    'label' => 'Convertir en commande',
                    'icon' => 'file-contract',
                    'url' => route('quotes.convert-to-order', $record),
                    'variant' => 'outline-primary',
                ];
                $primaryActions[] = [
                    'type' => 'link',
                    'label' => 'Imprimer / Télécharger',
                    'icon' => 'print',
                    'url' => $printUrl,
                    'variant' => 'outline-secondary',
                ];

                if (in_array($record->status, ['draft', 'sent'], true)) {
                    $secondaryActions[] = [
                        'type' => 'link',
                        'label' => 'Modifier',
                        'icon' => 'pen',
                        'url' => route('quotes.edit', $record),
                        'danger' => false,
                    ];
                    if ($record->status === 'draft') {
                        $secondaryActions[] = [
                            'type' => 'post',
                            'label' => 'Marquer comme envoyé',
                            'icon' => 'paper-plane',
                            'url' => route('quotes.send', $record),
                            'danger' => false,
                        ];
                    }

                    $secondaryActions[] = [
                        'type' => 'post',
                        'label' => 'Accepter',
                        'icon' => 'check-circle',
                        'url' => route('quotes.accept', $record),
                        'danger' => false,
                    ];

                    $secondaryActions[] = [
                        'type' => 'post',
                        'label' => 'Rejeter',
                        'icon' => 'times-circle',
                        'url' => route('quotes.reject', $record),
                        'danger' => true,
                        'confirm' => 'Confirmer le rejet du devis ?',
                    ];
                }

                $secondaryActions[] = [
                    'type' => 'post',
                    'label' => 'Annuler',
                    'icon' => 'ban',
                    'url' => route('quotes.cancel', $record),
                    'danger' => true,
                    'confirm' => 'Confirmer l\'annulation du devis ?',
                ];
            } elseif ($record->status === 'converted') {
                $convertedTarget = $record->invoice
                    ? [
                        'label' => 'Voir la facture créée',
                        'url' => route('invoices.show', ['type' => 'clients', 'invoice' => $record->invoice->id]),
                    ]
                    : ($record->saleOrder
                        ? [
                            'label' => 'Voir la commande créée',
                            'url' => route('sale-orders.show', $record->saleOrder),
                        ]
                        : null);

                if ($convertedTarget) {
                    $primaryActions[] = [
                        'type' => 'link',
                        'label' => $convertedTarget['label'],
                        'icon' => 'link',
                        'url' => $convertedTarget['url'],
                        'variant' => 'primary',
                    ];
                }

                $primaryActions[] = [
                    'type' => 'link',
                    'label' => 'Imprimer / Télécharger',
                    'icon' => 'print',
                    'url' => $printUrl,
                    'variant' => 'outline-secondary',
                ];
            } elseif (in_array($record->status, ['rejected', 'cancelled'], true)) {
                $primaryActions[] = [
                    'type' => 'link',
                    'label' => 'Imprimer / Télécharger',
                    'icon' => 'print',
                    'url' => $printUrl,
                    'variant' => 'primary',
                ];
            } else {
                $primaryActions[] = [
                    'type' => 'link',
                    'label' => 'Imprimer / Télécharger',
                    'icon' => 'print',
                    'url' => $printUrl,
                    'variant' => 'primary',
                ];
            }
        } elseif ($isSaleOrder) {
            $printUrl = route('sale-orders.print', $record);

            if ($record->status === 'draft') {
                $primaryActions = [
                    [
                        'type' => 'post',
                        'label' => 'Confirmer',
                        'icon' => 'check',
                        'url' => route('sale-orders.confirm', $record),
                        'variant' => 'primary',
                    ],
                    [
                        'type' => 'post',
                        'label' => 'Créer facture',
                        'icon' => 'file-invoice',
                        'url' => route('sale-orders.create-invoice', $record),
                        'variant' => 'outline-primary',
                    ],
                    [
                        'type' => 'post',
                        'label' => 'Créer bon de livraison',
                        'icon' => 'truck',
                        'url' => route('sale-orders.create-delivery', $record),
                        'variant' => 'outline-secondary',
                    ],
                ];
                $secondaryActions = [
                    [
                        'type' => 'link',
                        'label' => 'Modifier',
                        'icon' => 'pen',
                        'url' => route('sale-orders.edit', $record),
                        'danger' => false,
                    ],
                    [
                        'type' => 'link',
                        'label' => 'Imprimer',
                        'icon' => 'print',
                        'url' => $printUrl,
                        'danger' => false,
                    ],
                    [
                        'type' => 'post',
                        'label' => 'Annuler',
                        'icon' => 'ban',
                        'url' => route('sale-orders.cancel', $record),
                        'danger' => true,
                        'confirm' => 'Confirmer l\'annulation de la commande ?',
                    ],
                ];
            } elseif ($record->status === 'confirmed') {
                $primaryActions = [
                    [
                        'type' => 'post',
                        'label' => 'Créer bon de livraison',
                        'icon' => 'truck',
                        'url' => route('sale-orders.create-delivery', $record),
                        'variant' => 'primary',
                    ],
                    [
                        'type' => 'post',
                        'label' => 'Créer facture',
                        'icon' => 'file-invoice',
                        'url' => route('sale-orders.create-invoice', $record),
                        'variant' => 'outline-primary',
                    ],
                    [
                        'type' => 'link',
                        'label' => 'Imprimer',
                        'icon' => 'print',
                        'url' => $printUrl,
                        'variant' => 'outline-secondary',
                    ],
                ];
                $secondaryActions = [
                    [
                        'type' => 'post',
                        'label' => 'Annuler',
                        'icon' => 'ban',
                        'url' => route('sale-orders.cancel', $record),
                        'danger' => true,
                    ],
                ];
            } elseif ($record->status === 'partially_delivered') {
                $primaryActions = [
                    [
                        'type' => 'post',
                        'label' => 'Créer livraison restante',
                        'icon' => 'truck-loading',
                        'url' => route('sale-orders.create-delivery', $record),
                        'variant' => 'primary',
                    ],
                    [
                        'type' => 'post',
                        'label' => 'Créer facture',
                        'icon' => 'file-invoice',
                        'url' => route('sale-orders.create-invoice', $record),
                        'variant' => 'outline-primary',
                    ],
                    [
                        'type' => 'link',
                        'label' => 'Imprimer',
                        'icon' => 'print',
                        'url' => $printUrl,
                        'variant' => 'outline-secondary',
                    ],
                ];
            } elseif ($record->status === 'delivered') {
                $primaryActions = [
                    [
                        'type' => 'post',
                        'label' => 'Créer facture',
                        'icon' => 'file-invoice',
                        'url' => route('sale-orders.create-invoice', $record),
                        'variant' => 'primary',
                    ],
                    [
                        'type' => 'link',
                        'label' => 'Imprimer',
                        'icon' => 'print',
                        'url' => $printUrl,
                        'variant' => 'outline-secondary',
                    ],
                ];
            } elseif ($record->status === 'invoiced' && $record->invoice) {
                $primaryActions = [
                    [
                        'type' => 'link',
                        'label' => 'Voir facture',
                        'icon' => 'link',
                        'url' => route('invoices.show', ['type' => 'clients', 'invoice' => $record->invoice->id]),
                        'variant' => 'primary',
                    ],
                    [
                        'type' => 'link',
                        'label' => 'Imprimer',
                        'icon' => 'print',
                        'url' => $printUrl,
                        'variant' => 'outline-secondary',
                    ],
                ];
            } else {
                $primaryActions = [
                    [
                        'type' => 'link',
                        'label' => 'Imprimer',
                        'icon' => 'print',
                        'url' => $printUrl,
                        'variant' => 'primary',
                    ],
                ];
            }
        } elseif ($isDelivery) {
            $printUrl = route('delivery-notes.print', $record);

            if ($record->status === 'draft') {
                $primaryActions = [
                    [
                        'type' => 'post',
                        'label' => 'Valider livraison',
                        'icon' => 'check',
                        'url' => route('delivery-notes.validate', $record),
                        'variant' => 'primary',
                    ],
                    [
                        'type' => 'link',
                        'label' => 'Imprimer',
                        'icon' => 'print',
                        'url' => $printUrl,
                        'variant' => 'outline-secondary',
                    ],
                    [
                        'type' => 'link',
                        'label' => 'Retour commande',
                        'icon' => 'arrow-left',
                        'url' => $record->saleOrder ? route('sale-orders.show', $record->saleOrder) : url()->previous(),
                        'variant' => 'outline-primary',
                    ],
                ];
                $secondaryActions = [
                    [
                        'type' => 'post',
                        'label' => 'Annuler',
                        'icon' => 'ban',
                        'url' => route('delivery-notes.cancel', $record),
                        'danger' => true,
                        'confirm' => 'Confirmer l\'annulation du bon de livraison ?',
                    ],
                ];
            } elseif (in_array($record->status, ['validated', 'delivered'], true)) {
                $primaryActions = [
                    [
                        'type' => 'link',
                        'label' => 'Voir commande',
                        'icon' => 'link',
                        'url' => $record->saleOrder ? route('sale-orders.show', $record->saleOrder) : url()->previous(),
                        'variant' => 'primary',
                    ],
                    [
                        'type' => 'link',
                        'label' => 'Imprimer',
                        'icon' => 'print',
                        'url' => $printUrl,
                        'variant' => 'outline-secondary',
                    ],
                    [
                        'type' => 'link',
                        'label' => 'Voir mouvements de stock',
                        'icon' => 'exchange-alt',
                        'url' => route('movements.index', ['movement_type' => 'delivery_out']),
                        'variant' => 'outline-primary',
                    ],
                ];
            } else {
                $primaryActions = [
                    [
                        'type' => 'link',
                        'label' => 'Voir commande',
                        'icon' => 'link',
                        'url' => $record->saleOrder ? route('sale-orders.show', $record->saleOrder) : url()->previous(),
                        'variant' => 'primary',
                    ],
                    [
                        'type' => 'link',
                        'label' => 'Imprimer',
                        'icon' => 'print',
                        'url' => $printUrl,
                        'variant' => 'outline-secondary',
                    ],
                ];
            }
        }
    @endphp

    <div class="container-fluid">
        <div class="page-hero page-hero--accent mb-4">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                <div>
                    <div class="page-hero__eyebrow mb-2">Document métier</div>
                    <h1 class="page-hero__title">{{ $title }}</h1>
                    <p class="page-hero__subtitle">{{ $record->{$numberField} }} · {{ $amount }}</p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @if (request()->headers->get('referer'))
                        <a href="{{ url()->previous() }}" class="btn btn-light">
                            <i class="fas fa-arrow-left mr-1"></i> Retour
                        </a>
                    @endif
                    <span class="status-pill status-pill--{{ $status['class'] }}">
                        <i class="fas fa-{{ $status['icon'] }}"></i>{{ $status['label'] }}
                    </span>
                </div>
            </div>
        </div>

        <style>
            .document-action-bar {
                position: sticky;
                top: 1rem;
                z-index: 25;
                background: rgba(255, 255, 255, .94);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(15, 23, 42, .08);
                border-radius: 20px;
                box-shadow: 0 14px 28px rgba(15, 23, 42, .08);
                padding: .9rem 1rem;
                margin-bottom: 1rem;
            }

            .document-action-bar__title {
                margin: 0;
                font-size: .78rem;
                text-transform: uppercase;
                letter-spacing: .08em;
                font-weight: 800;
                color: #64748b;
            }

            .document-action-bar__subtitle {
                margin: .2rem 0 0;
                font-size: .92rem;
                color: #0f172a;
                font-weight: 700;
            }

            .document-action-bar__primary .btn,
            .document-action-bar__primary form {
                margin-bottom: 0 !important;
            }

            .document-action-bar__primary {
                display: flex;
                flex-wrap: wrap;
                gap: .55rem;
            }

            .document-action-bar__summary {
                display: flex;
                align-items: center;
                gap: .55rem;
                flex-wrap: wrap;
            }

            .document-action-bar__summary .status-pill {
                margin: 0;
            }

            @media (max-width: 991.98px) {
                .document-action-bar {
                    position: static;
                }
            }
        </style>

        <div class="document-action-bar">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="document-action-bar__title">Actions du document</div>
                    <div class="document-action-bar__subtitle">
                        {{ $isQuote ? 'Convertir, imprimer ou ouvrir le menu secondaire sans descendre dans la page.' : 'Actions principales accessibles immédiatement.' }}
                    </div>
                </div>

                <div class="document-action-bar__summary">
                    @if (request()->headers->get('referer'))
                        <a href="{{ url()->previous() }}" class="btn btn-light">
                            <i class="fas fa-arrow-left mr-1"></i> Retour
                        </a>
                    @endif
                    <span class="status-pill status-pill--{{ $status['class'] }}">
                        <i class="fas fa-{{ $status['icon'] }}"></i>{{ $status['label'] }}
                    </span>
                </div>
            </div>

            <div class="mt-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="document-action-bar__primary">
                    @foreach($primaryActions as $action)
                        @if($action['type'] === 'post')
                            <form method="POST" action="{{ $action['url'] }}" class="d-inline-block">
                                @csrf
                                <button
                                    class="btn btn-{{ $action['variant'] }}"
                                    @if(!empty($action['confirm'])) onclick="return confirm('{{ $action['confirm'] }}')" @endif
                                >
                                    <i class="fas fa-{{ $action['icon'] }} mr-1"></i>{{ $action['label'] }}
                                </button>
                            </form>
                        @else
                            <a href="{{ $action['url'] }}" class="btn btn-{{ $action['variant'] }}">
                                <i class="fas fa-{{ $action['icon'] }} mr-1"></i>{{ $action['label'] }}
                            </a>
                        @endif
                    @endforeach
                </div>

                @if(count($secondaryActions))
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Plus
                        </button>
                        <div class="dropdown-menu dropdown-menu-right shadow-sm">
                            @foreach($secondaryActions as $action)
                                @if($action['type'] === 'link')
                                    <a href="{{ $action['url'] }}" class="dropdown-item {{ !empty($action['danger']) ? 'text-danger' : '' }}">
                                        <i class="fas fa-{{ $action['icon'] }} mr-1"></i>{{ $action['label'] }}
                                    </a>
                                @else
                                    <form method="POST" action="{{ $action['url'] }}" class="m-0">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="dropdown-item {{ !empty($action['danger']) ? 'text-danger' : '' }}"
                                            @if(!empty($action['confirm'])) onclick="return confirm(@js($action['confirm']))" @endif
                                        >
                                            <i class="fas fa-{{ $action['icon'] }} mr-1"></i>{{ $action['label'] }}
                                        </button>
                                    </form>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="document-shell p-4">
                    <div class="section-title">
                        <div>
                            <h3 class="mb-1">Lignes du document</h3>
                            <p>Produits, quantités, prix et total documentaire.</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table data-table">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th class="text-right">Qté</th>
                                    <th class="text-right">Prix</th>
                                    <th class="text-right">Remise</th>
                                    <th class="text-right">TVA</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                    <tr>
                                        <td>
                                            <div class="font-weight-bold">{{ $item->product?->name ?? 'Produit' }}</div>
                                            <div class="text-muted small">Article rattaché au document</div>
                                        </td>
                                        <td class="text-right">{{ $item->quantity ?? $item->quantity_ordered ?? $item->quantity_received ?? 0 }}</td>
                                        <td class="text-right">{{ number_format((int) ($item->unit_price_ht ?? $item->unit_cost_ht ?? $item->unit_price ?? 0), 0, ',', ' ') }}</td>
                                        <td class="text-right">{{ number_format((int) ($item->discount_amount ?? $item->discount ?? 0), 0, ',', ' ') }}</td>
                                        <td class="text-right">{{ number_format((int) ($item->tax_amount ?? 0), 0, ',', ' ') }}</td>
                                        <td class="text-right font-weight-bold">{{ number_format((int) ($item->total_ttc ?? $item->total_line ?? 0), 0, ',', ' ') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="empty-state my-4">
                                                <div class="empty-state__icon"><i class="fas fa-box-open"></i></div>
                                                <div class="font-weight-bold mb-1">Aucune ligne</div>
                                                <div>Ce document n’a pas encore de contenu détaillé.</div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="panel-card p-4 mb-4">
                    <div class="section-title">
                        <div>
                            <h4 class="mb-1">Résumé</h4>
                            <p>Informations essentielles du document.</p>
                        </div>
                    </div>

                    <div class="info-row">
                        <div>
                            <div class="info-row__label">Numéro</div>
                            <div class="info-row__value">{{ $record->{$numberField} }}</div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div>
                            <div class="info-row__label">Partenaire</div>
                            <div class="info-row__value">{{ $record->contact?->fullname ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div>
                            <div class="info-row__label">Date</div>
                            <div class="info-row__value">
                                {{ optional($record->{str_replace('number', 'date', $numberField)} ?? null)->format('d/m/Y') ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div>
                            <div class="info-row__label">Statut</div>
                            <div class="info-row__value">
                                <span class="status-pill status-pill--{{ $status['class'] }}">
                                    <i class="fas fa-{{ $status['icon'] }}"></i>{{ $status['label'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @if($isQuote)
                        <div class="info-row">
                            <div>
                                <div class="info-row__label">Validité</div>
                                <div class="info-row__value">{{ optional($record->valid_until)->format('d/m/Y') ?? 'N/A' }}</div>
                            </div>
                        </div>
                    @endif
                    <div class="info-row">
                        <div>
                            <div class="info-row__label">Total TTC</div>
                            <div class="info-row__value">{{ $amount }}</div>
                        </div>
                    </div>
                    @if(in_array($documentKind, ['quote', 'sale_order'], true))
                        <div class="info-row">
                            <div>
                                <div class="info-row__label">Total HT</div>
                                <div class="info-row__value">{{ number_format((int) ($record->total_ht ?? 0), 0, ',', ' ') }} FCFA</div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div>
                                <div class="info-row__label">Remise</div>
                                <div class="info-row__value">{{ number_format((int) ($record->total_discount ?? 0), 0, ',', ' ') }} FCFA</div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div>
                                <div class="info-row__label">TVA</div>
                                <div class="info-row__value">{{ number_format((int) ($record->tax_amount ?? 0), 0, ',', ' ') }} FCFA</div>
                            </div>
                        </div>
                    @endif
                    @if($isQuote && $record->saleOrder)
                        <div class="info-row">
                            <div>
                                <div class="info-row__label">Commande liée</div>
                                <div class="info-row__value">
                                    <a href="{{ route('sale-orders.show', $record->saleOrder) }}">#{{ $record->saleOrder->order_number }}</a>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if($isQuote && $record->invoice)
                        <div class="info-row">
                            <div>
                                <div class="info-row__label">Facture liée</div>
                                <div class="info-row__value">
                                    <a href="{{ route('invoices.show', ['type' => 'clients', 'invoice' => $record->invoice->id]) }}">#{{ $record->invoice->invoice_number }}</a>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if($isSaleOrder && $record->invoice)
                        <div class="info-row">
                            <div>
                                <div class="info-row__label">Facture liée</div>
                                <div class="info-row__value">
                                    <a href="{{ route('invoices.show', ['type' => 'clients', 'invoice' => $record->invoice->id]) }}">#{{ $record->invoice->invoice_number }}</a>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if($isDelivery && $record->saleOrder)
                        <div class="info-row">
                            <div>
                                <div class="info-row__label">Commande source</div>
                                <div class="info-row__value">
                                    <a href="{{ route('sale-orders.show', $record->saleOrder) }}">#{{ $record->saleOrder->order_number }}</a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
@endsection
