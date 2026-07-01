@extends('back.layouts.admin')

@section('content')
    @php
        $isQuote = $mode === 'quote';
        $isSaleOrder = $mode === 'sale_order';
        $isPurchaseOrder = $mode === 'purchase_order';
        $isDelivery = $mode === 'delivery';
        $isReceipt = $mode === 'receipt';
        $itemQtyField = $isPurchaseOrder || $isSaleOrder ? 'quantity_ordered' : 'quantity';
        $itemUnitField = $isPurchaseOrder ? 'unit_cost_ht' : 'unit_price_ht';
        $dateField = $isQuote ? 'quote_date' : ($isSaleOrder ? 'order_date' : ($isPurchaseOrder ? 'purchase_date' : 'receipt_date'));
        $lines = old('items', $record->items?->toArray() ?? [0 => []]);
    @endphp

    <style>
        .quote-form-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%);
            border: 0;
            border-radius: 26px;
            color: #fff;
            box-shadow: 0 18px 38px rgba(15, 23, 42, .12);
            overflow: hidden;
        }

        .quote-form-hero .page-hero__eyebrow,
        .quote-form-hero .page-hero__subtitle {
            color: rgba(255, 255, 255, .82);
        }

        .quote-form-hero__chips {
            display: flex;
            flex-wrap: wrap;
            gap: .55rem;
            margin-top: 1rem;
        }

        .quote-form-hero__chip {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .5rem .85rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .16);
            font-weight: 700;
            font-size: .88rem;
        }

        .quote-form-card,
        .quote-form-table,
        .quote-form-summary {
            border: 0;
            border-radius: 22px;
            box-shadow: 0 16px 36px rgba(15, 23, 42, .08);
            overflow: hidden;
        }

        .quote-form-card {
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }

        .quote-form-summary {
            position: sticky;
            top: 1rem;
        }

        .quote-form-table .table thead th {
            text-transform: uppercase;
            font-size: .74rem;
            letter-spacing: .08em;
            color: #64748b;
            border-top: 0;
            border-bottom: 0;
        }

        .quote-form-table .table tbody tr:hover {
            background: rgba(29, 78, 216, .03);
        }

        .quote-form-lines-title {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
        }

        .quote-form-lines-subtitle {
            color: #64748b;
            margin: 0;
        }

        .quote-form-add-row {
            border-radius: 12px;
            padding: .58rem .9rem;
            font-weight: 700;
        }

        @media (max-width: 991.98px) {
            .quote-form-summary {
                position: static;
            }
        }
    </style>

    <div class="container-fluid">
        <div class="page-hero page-hero--accent mb-4 {{ $isQuote ? 'quote-form-hero' : '' }}">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="page-hero__eyebrow mb-2">Documents commerciaux</div>
                    <h1 class="page-hero__title">{{ $title }}</h1>
                    <p class="page-hero__subtitle">
                        {{ $isQuote ? 'Construisez un devis clair, compact et prêt à convertir en facture ou commande.' : 'Formulaire simplifié, tenant-safe et conçu pour aller vite.' }}
                    </p>
                    @if($isQuote)
                        <div class="quote-form-hero__chips">
                            <span class="quote-form-hero__chip"><i class="fas fa-file-signature"></i> Devis / Proforma</span>
                            <span class="quote-form-hero__chip"><i class="fas fa-exchange-alt"></i> Conversion rapide</span>
                            <span class="quote-form-hero__chip"><i class="fas fa-shield-alt"></i> Pas d'impact stock</span>
                        </div>
                    @endif
                </div>
                <span class="status-pill status-pill--info">
                    <i class="fas fa-shield-alt"></i> Tenant safe
                </span>
            </div>
        </div>

        <form method="POST" action="{{ $updateRoute ? route($updateRoute, $record) : route($storeRoute) }}">
            @csrf
            @if($updateRoute)
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-xl-8 mb-4">
                    <div class="panel-card p-4 quote-form-card">
                        <div class="section-title">
                            <div>
                                <h3 class="mb-1">{{ $isQuote ? 'Informations du devis' : 'Informations principales' }}</h3>
                                <p>{{ $isQuote ? 'Client, validité, remarques et lignes du devis.' : 'Les champs de base du document.' }}</p>
                            </div>
                        </div>

                        <div class="row">
                            @if(!($isDelivery || $isReceipt))
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Contact</label>
                                        <select name="contact_id" class="form-control" required>
                                            @foreach($contacts as $contact)
                                                <option value="{{ $contact->id }}" @selected(old('contact_id', $record->contact_id ?? null) == $contact->id)>
                                                    {{ $contact->fullname }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Date</label>
                                    <input type="date" name="{{ $dateField }}" class="form-control"
                                           value="{{ old($dateField, optional($record->{$dateField} ?? null)->format('Y-m-d') ?? now()->toDateString()) }}" required>
                                </div>
                            </div>

                            @if($isQuote)
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Validité</label>
                                        <input type="date" name="valid_until" class="form-control"
                                               value="{{ old('valid_until', optional($record->valid_until ?? null)->format('Y-m-d')) }}">
                                    </div>
                                </div>
                            @endif

                            @if($isDelivery || $isReceipt)
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">{{ $isDelivery ? 'Commande client' : 'Commande fournisseur' }}</label>
                                        <select name="{{ $isDelivery ? 'sale_order_id' : 'purchase_order_id' }}" class="form-control" required>
                                            @foreach(($isDelivery ? $saleOrders : $purchaseOrders) as $source)
                                                <option value="{{ $source->id }}">
                                                    {{ $source->{$isDelivery ? 'order_number' : 'purchase_number'} }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Entrepôt</label>
                                        <select name="warehouse_id" class="form-control" required>
                                            @foreach($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif

                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <label class="font-weight-bold">Notes</label>
                                    <textarea name="notes" rows="{{ $isQuote ? 3 : 4 }}" class="form-control" placeholder="Notes, commentaires ou instructions de traitement.">{{ old('notes', $record->notes ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(!($isDelivery || $isReceipt))
                        <div class="table-card mt-4 quote-form-table">
                            <div class="card-body p-0">
                                <div class="d-flex flex-wrap align-items-center justify-content-between px-4 pt-4 pb-3">
                                    <div>
                                        <h4 class="mb-1 quote-form-lines-title">{{ $isQuote ? 'Lignes du devis' : 'Lignes du document' }}</h4>
                                        <p class="mb-0 quote-form-lines-subtitle">Ajoute les produits, les prix et les remises.</p>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm quote-form-add-row" id="add-row">
                                        <i class="fas fa-plus mr-1"></i> Ajouter une ligne
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table data-table">
                                        <thead>
                                            <tr>
                                                <th>Produit</th>
                                                <th>Entrepôt</th>
                                                <th class="text-right">Quantité</th>
                                                <th class="text-right">Prix</th>
                                                <th class="text-right">Remise</th>
                                                <th class="text-right"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="rows">
                                            @foreach($lines as $i => $item)
                                                <tr>
                                                    <td>
                                                        <select name="items[{{ $i }}][product_id]" class="form-control" required>
                                                            @foreach($products as $product)
                                                                <option value="{{ $product->id }}" @selected(($item['product_id'] ?? null) == $product->id)>
                                                                    {{ $product->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select name="items[{{ $i }}][warehouse_id]" class="form-control">
                                                            <option value="">--</option>
                                                            @foreach($warehouses as $warehouse)
                                                                <option value="{{ $warehouse->id }}" @selected(($item['warehouse_id'] ?? null) == $warehouse->id)>
                                                                    {{ $warehouse->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input name="items[{{ $i }}][{{ $itemQtyField }}]" class="form-control text-right" type="number" min="1" value="{{ $item[$itemQtyField] ?? 1 }}">
                                                    </td>
                                                    <td>
                                                        <input name="items[{{ $i }}][{{ $itemUnitField }}]" class="form-control text-right" type="number" min="0" value="{{ $item[$itemUnitField] ?? 0 }}">
                                                    </td>
                                                    <td>
                                                        <input name="items[{{ $i }}][discount_amount]" class="form-control text-right" type="number" min="0" value="{{ $item['discount_amount'] ?? 0 }}">
                                                    </td>
                                                    <td class="text-right">
                                                        <button type="button" class="btn btn-outline-danger btn-sm remove-row">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <template id="document-line-template">
                            <tr>
                                <td>
                                    <select name="items[__INDEX__][product_id]" class="form-control" required>
                                        <option value="">Sélectionnez un produit</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="items[__INDEX__][warehouse_id]" class="form-control">
                                        <option value="">--</option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input name="items[__INDEX__][{{ $itemQtyField }}]" class="form-control text-right" type="number" min="1" value="1">
                                </td>
                                <td>
                                    <input name="items[__INDEX__][{{ $itemUnitField }}]" class="form-control text-right" type="number" min="0" value="0">
                                </td>
                                <td>
                                    <input name="items[__INDEX__][discount_amount]" class="form-control text-right" type="number" min="0" value="0">
                                </td>
                                <td class="text-right">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    @endif
                </div>

                <div class="col-xl-4 mb-4">
                    <div class="panel-card p-4 quote-form-summary">
                        <div class="section-title">
                            <div>
                                <h4 class="mb-1">Résumé</h4>
                                <p>Contrôle rapide avant validation.</p>
                            </div>
                        </div>

                        <div class="info-row">
                            <div>
                                <div class="info-row__label">Type</div>
                                <div class="info-row__value">{{ $title }}</div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div>
                                <div class="info-row__label">Mode</div>
                                <div class="info-row__value">
                                    @if($isQuote) Devis / Proforma
                                    @elseif($isSaleOrder) Commande client
                                    @elseif($isDelivery) Bon de livraison
                                    @elseif($isPurchaseOrder) Commande fournisseur
                                    @else Bon de réception
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div>
                                <div class="info-row__label">Lignes</div>
                                <div class="info-row__value">{{ $isDelivery || $isReceipt ? 'Traitement automatique' : count($lines) }}</div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div>
                                <div class="info-row__label">Statut initial</div>
                                <div class="info-row__value">Brouillon</div>
                            </div>
                            <span class="status-pill status-pill--neutral">Draft</span>
                        </div>

                        <div class="alert alert-info border-0 mt-4 mb-0">
                            <i class="fas fa-info-circle mr-1"></i>
                            Les documents restent tenant-safe et les séquences seront générées automatiquement à l’enregistrement.
                        </div>

                        <div class="d-flex flex-column gap-2 mt-4">
                            <button class="btn btn-success btn-block">
                                <i class="fas fa-save mr-1"></i> {{ $updateRoute ? 'Mettre à jour' : 'Créer' }}
                            </button>
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-block">
                                <i class="fas fa-arrow-left mr-1"></i> Retour
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @if(!($isDelivery || $isReceipt))
        @push('scripts')
            <script>
                (function () {
                    const rows = document.getElementById('rows');
                    const addRow = document.getElementById('add-row');
                    const template = document.getElementById('document-line-template');
                    if (!rows || !addRow) return;

                    let index = rows.querySelectorAll('tr').length;

                    function reindexRows() {
                        rows.querySelectorAll('tr').forEach((row, rowIndex) => {
                            row.querySelectorAll('select,input').forEach((el) => {
                                if (el.name) {
                                    el.name = el.name.replace(/\[\d+\]/, '[' + rowIndex + ']');
                                }
                            });
                        });
                        index = rows.querySelectorAll('tr').length;
                    }

                    function attachRowBehavior(row) {
                        const productSelect = row.querySelector('select[name*="[product_id]"]');
                        const priceInput = row.querySelector('input[name*="[{{ $itemUnitField }}]"]');

                        if (productSelect && priceInput) {
                            productSelect.addEventListener('change', function () {
                                const option = this.selectedOptions[0];
                                const price = option ? parseInt(option.dataset.price || '0', 10) : 0;
                                priceInput.value = Number.isFinite(price) ? price : 0;
                            });
                        }
                    }

                    rows.querySelectorAll('tr').forEach(attachRowBehavior);

                    addRow.addEventListener('click', function () {
                        if (!template) return;

                        const clone = template.content.firstElementChild.cloneNode(true);
                        clone.querySelectorAll('[name]').forEach((el) => {
                            el.name = el.name.replaceAll('__INDEX__', String(index));
                        });

                        clone.querySelectorAll('select').forEach((select) => {
                            select.selectedIndex = 0;
                        });

                        clone.querySelectorAll('input[type="number"]').forEach((input) => {
                            input.value = input.name.includes('{{ $itemQtyField }}') ? 1 : 0;
                        });

                        rows.appendChild(clone);
                        attachRowBehavior(clone);
                        index++;
                    });

                    rows.addEventListener('click', function (e) {
                        const button = e.target.closest('.remove-row');
                        if (button && rows.children.length > 1) {
                            button.closest('tr').remove();
                            reindexRows();
                        }
                    });

                    reindexRows();
                })();
            </script>
        @endpush
    @endif
@endsection
