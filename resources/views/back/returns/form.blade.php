@extends('back.layouts.admin')

@section('content')
    @php
        $isCustomer = ($module ?? 'customer') === 'customer';
        $isCreate = empty($record->id);
        $status = $record->status ?? 'draft';
        $statusMap = [
            'draft' => ['label' => 'Brouillon', 'class' => 'neutral', 'icon' => 'pen'],
            'validated' => ['label' => 'Validé', 'class' => 'success', 'icon' => 'check-circle'],
            'cancelled' => ['label' => 'Annulé', 'class' => 'danger', 'icon' => 'ban'],
        ];
        $state = $statusMap[$status] ?? ['label' => ucfirst((string) $status), 'class' => 'info', 'icon' => 'circle-info'];
        $sourceTitle = $isCustomer ? 'Choisir la facture ou le BL source' : 'Choisir la facture ou la réception source';
        $sourceDocs = $isCustomer ? ($invoices ?? collect()) : ($supplierInvoices ?? collect());
        $sourceAltDocs = $isCustomer ? ($deliveryNotes ?? collect()) : ($goodsReceipts ?? collect());
        $selectedSource = $sourceContext['source'] ?? null;
        $selectedSourceType = $sourceContext['type'] ?? null;
        $items = $selectedSource?->items ?? collect();
        if (! $isCreate) {
            $items = $record->items ?? collect();
        }
        $sourceLabel = $selectedSource
            ? ($isCustomer
                ? ($selectedSourceType === 'invoice' ? $selectedSource->invoice_number : $selectedSource->delivery_number)
                : ($selectedSourceType === 'invoice' ? $selectedSource->invoice_number : $selectedSource->receipt_number))
            : 'Aucune source sélectionnée';
        $sourcePartner = $selectedSource?->contact?->fullname ?? 'N/A';
    @endphp

    <style>
        .return-form__panel,
        .return-form__summary {
            border: 0;
            border-radius: 22px;
            box-shadow: 0 16px 36px rgba(15, 23, 42, .08);
            overflow: hidden;
        }

        .return-form__summary {
            position: sticky;
            top: 1rem;
        }

        .return-form__section-title {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: .85rem;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #1d4ed8;
            font-weight: 800;
        }

        .return-form__input {
            border-radius: 12px;
            min-height: 44px;
            border: 1px solid rgba(15, 23, 42, .12);
            box-shadow: none;
        }

        .return-form__input:focus {
            border-color: rgba(29, 78, 216, .45);
            box-shadow: 0 0 0 .18rem rgba(29, 78, 216, .12);
        }

        .return-form__field-help {
            margin-top: .35rem;
            color: #64748b;
            font-size: .82rem;
        }

        .return-form__meta {
            display: flex;
            flex-wrap: wrap;
            gap: .55rem;
            margin-top: .75rem;
        }

        .return-form__pill {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .38rem .75rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, .12);
            color: #fff;
            font-weight: 700;
            font-size: .8rem;
        }

        .return-form__status-card {
            border-radius: 18px;
            padding: .95rem 1rem;
            margin-top: 1rem;
            border: 1px solid rgba(15, 23, 42, .08);
        }

        .return-form__status-card--neutral {
            background: rgba(100, 116, 139, .08);
        }

        .return-form__status-card--success {
            background: rgba(22, 163, 74, .08);
        }

        .return-form__status-card--danger {
            background: rgba(220, 38, 38, .08);
        }
    </style>

    <div class="container-fluid">
        <div class="page-hero page-hero--accent mb-4">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                <div>
                    <div class="page-hero__eyebrow mb-2">Retour ERP</div>
                    <h1 class="page-hero__title">{{ $title }}</h1>
                    <p class="page-hero__subtitle">
                        {{ $isCustomer ? 'Réintégrer des produits vendus à partir d’une facture ou d’un BL.' : 'Sortir des produits retournés au fournisseur depuis une facture ou une réception.' }}
                    </p>
                    <div class="return-form__meta">
                        <span class="return-form__pill"><i class="fas fa-{{ $state['icon'] }}"></i>{{ $state['label'] }}</span>
                        <span class="return-form__pill"><i class="fas fa-hashtag"></i>{{ $record->return_number ?? 'Auto' }}</span>
                        <span class="return-form__pill"><i class="fas fa-user"></i>{{ $sourcePartner }}</span>
                    </div>
                    <div class="return-form__status-card return-form__status-card--{{ $state['class'] }}">
                        <div class="font-weight-bold mb-1">
                            {{ $state['label'] === 'Brouillon' ? 'Document en cours de préparation' : ($state['label'] === 'Validé' ? 'Document déjà traité' : 'Document annulé') }}
                        </div>
                        <div class="text-muted small mb-0">
                            {{ $state['label'] === 'Brouillon' ? 'Vous pouvez encore charger une source et ajuster les quantités.' : ($state['label'] === 'Validé' ? 'Les quantités ont été intégrées dans les mouvements de stock.' : 'Ce document est clos et ne doit plus être modifié.') }}
                        </div>
                    </div>
                </div>
                <span class="status-pill status-pill--info">
                    <i class="fas fa-shield-alt"></i> Tenant safe
                </span>
            </div>
        </div>

        <form method="GET" action="{{ route($isCustomer ? 'customer-returns.create' : 'supplier-returns.create') }}" class="mb-4">
            <div class="card return-form__panel">
                <div class="card-body">
                    <div class="return-form__section-title">
                        <i class="fas fa-link"></i> {{ $sourceTitle }}
                    </div>
                    <div class="alert alert-light border mb-3">
                        <strong>Document source :</strong> {{ $sourceLabel }}
                        <div class="small text-muted mt-1">
                            Sélectionnez le type puis chargez le document pour afficher les lignes exploitables.
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Type de source</label>
                            <select name="source_type" class="form-control return-form__input">
                                <option value="">-- Sélectionner --</option>
                                @if($isCustomer)
                                    <option value="invoice" @selected($selectedSourceType === 'invoice')>Facture client</option>
                                    <option value="delivery_note" @selected($selectedSourceType === 'delivery_note')>Bon de livraison</option>
                                @else
                                    <option value="invoice" @selected($selectedSourceType === 'invoice')>Facture fournisseur</option>
                                    <option value="goods_receipt" @selected($selectedSourceType === 'goods_receipt')>Bon de réception</option>
                                @endif
                            </select>
                            <div class="return-form__field-help">Choisissez le document qui servira de base au retour.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Document de référence</label>
                            <select name="source_id" class="form-control return-form__input">
                                <option value="">-- Sélectionner --</option>
                                @foreach($sourceDocs as $doc)
                                    <option value="{{ $doc->id }}" @selected($selectedSource?->id === $doc->id)>
                                        {{ $isCustomer ? $doc->invoice_number : $doc->invoice_number }} - {{ $doc->contact?->fullname ?? 'N/A' }}
                                    </option>
                                @endforeach
                                @foreach($sourceAltDocs as $doc)
                                    <option value="{{ $doc->id }}" @selected($selectedSource?->id === $doc->id)>
                                        {{ $isCustomer ? $doc->delivery_number : $doc->receipt_number }} - {{ $doc->contact?->fullname ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="return-form__field-help">Les lignes exploitables apparaissent après chargement du document.</div>
                        </div>
                        <div class="col-md-2 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search mr-1"></i> Charger
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <form method="POST" action="{{ $storeRoute ? route($storeRoute) : route($updateRoute, $record) }}">
            @csrf
            @if($updateRoute)
                @method('PUT')
            @endif

            <input type="hidden" name="source_type" value="{{ $selectedSourceType }}">
            <input type="hidden" name="invoice_id" value="{{ $isCustomer && $selectedSourceType === 'invoice' ? $selectedSource?->id : ($record->invoice_id ?? '') }}">
            <input type="hidden" name="delivery_note_id" value="{{ $isCustomer && $selectedSourceType === 'delivery_note' ? $selectedSource?->id : ($record->delivery_note_id ?? '') }}">
            <input type="hidden" name="supplier_invoice_id" value="{{ ! $isCustomer && $selectedSourceType === 'invoice' ? $selectedSource?->id : ($record->supplier_invoice_id ?? '') }}">
            <input type="hidden" name="goods_receipt_id" value="{{ ! $isCustomer && $selectedSourceType === 'goods_receipt' ? $selectedSource?->id : ($record->goods_receipt_id ?? '') }}">

            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card return-form__panel">
                        <div class="card-body">
                            <div class="return-form__section-title">
                                <i class="fas fa-file-alt"></i> Informations du retour
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="font-weight-bold">{{ $isCustomer ? 'Client' : 'Fournisseur' }}</label>
                                    <select name="contact_id" class="form-control return-form__input" required>
                                        @foreach(($isCustomer ? ($invoices ?? collect()) : ($supplierInvoices ?? collect())) as $doc)
                                            <option value="{{ $doc->contact_id }}" @selected(old('contact_id', $record->contact_id ?? ($selectedSource?->contact_id ?? null)) == $doc->contact_id)>
                                                {{ $doc->contact?->fullname ?? 'N/A' }}
                                            </option>
                                        @endforeach
                                        @foreach(($isCustomer ? ($deliveryNotes ?? collect()) : ($goodsReceipts ?? collect())) as $doc)
                                            <option value="{{ $doc->contact_id }}" @selected(old('contact_id', $record->contact_id ?? ($selectedSource?->contact_id ?? null)) == $doc->contact_id)>
                                                {{ $doc->contact?->fullname ?? 'N/A' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="return-form__field-help">Le partenaire est repris depuis la source quand elle est chargée.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="font-weight-bold">Entrepôt cible</label>
                                    <select name="warehouse_id" class="form-control return-form__input" required>
                                        <option value="">-- Sélectionner --</option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" @selected(old('warehouse_id', $record->warehouse_id ?? ($selectedSource?->warehouse_id ?? null)) == $warehouse->id)>
                                                {{ $warehouse->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="return-form__field-help">
                                        {{ $isCustomer ? 'Le stock sera réintégré dans cet entrepôt.' : 'Le stock sera sorti de cet entrepôt.' }}
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="font-weight-bold">Date</label>
                                    <input type="date" name="return_date" class="form-control return-form__input" value="{{ old('return_date', optional($record->return_date ?? now())->format('Y-m-d')) }}" required>
                                    <div class="return-form__field-help">Date de création du document de retour.</div>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="font-weight-bold">Motif principal</label>
                                    <input type="text" name="reason" class="form-control return-form__input" value="{{ old('reason', $record->reason ?? '') }}" placeholder="Ex: article défectueux, erreur de livraison">
                                    <div class="return-form__field-help">Explique brièvement pourquoi le retour est effectué.</div>
                                </div>
                                <div class="col-12">
                                    <label class="font-weight-bold">Notes</label>
                                    <textarea name="notes" rows="3" class="form-control return-form__input" placeholder="Commentaires ou consignes">{{ old('notes', $record->notes ?? '') }}</textarea>
                                    <div class="return-form__field-help">Informations internes, instructions de contrôle ou remarques de service.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card return-form__panel mt-4">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                                <div>
                                    <h4 class="mb-1">Lignes du retour</h4>
                                    <p class="mb-0 text-muted">Indiquez la quantité à retourner sur chaque ligne.</p>
                                </div>
                            </div>

                            @if($items->count() > 0)
                                <div class="table-responsive">
                                    <table class="table data-table">
                                        <thead>
                                            <tr>
                                                <th>Produit</th>
                                                <th class="text-right">{{ $isCustomer ? 'Vendu' : 'Reçu' }}</th>
                                                <th class="text-right">À retourner</th>
                                                <th class="text-right">PU HT</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($items as $item)
                                                @php
                                                    $lineId = $item->id;
                                                    $quantityBase = $isCustomer ? ($item->quantity ?? $item->quantity_ordered ?? $item->quantity_received ?? 0) : ($item->quantity ?? $item->quantity_received ?? $item->quantity_ordered ?? 0);
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <div class="font-weight-bold">{{ $item->product?->name ?? 'Produit' }}</div>
                                                        <div class="text-muted small">Ligne source</div>
                                                    </td>
                                                    <td class="text-right">{{ $quantityBase }}</td>
                                                    <td class="text-right" style="max-width: 140px;">
                                                        <input type="number" min="0" max="{{ $quantityBase }}" class="form-control text-right return-form__input"
                                                            name="items[{{ $lineId }}][quantity_returned]"
                                                            value="{{ old('items.'.$lineId.'.quantity_returned', $isCreate ? 0 : ($item->quantity_returned ?? 0)) }}">
                                                    </td>
                                                    <td class="text-right">{{ number_format((int) ($item->unit_price_ht ?? $item->unit_cost_ht ?? $item->unit_price ?? 0), 0, ',', ' ') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="empty-state py-5">
                                    <div class="empty-state__icon"><i class="fas fa-box-open"></i></div>
                                    <div class="font-weight-bold mb-1">Aucune source chargée</div>
                                    <div>Choisissez un document source pour afficher les lignes à retourner.</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="card return-form__summary p-4">
                        <div class="section-title">
                            <div>
                                <h4 class="mb-1">Résumé</h4>
                                <p>Contrôle rapide avant validation.</p>
                            </div>
                        </div>

                        <div class="info-row">
                            <div>
                                <div class="info-row__label">Document source</div>
                                <div class="info-row__value">{{ $sourceLabel }}</div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div>
                                <div class="info-row__label">Partenaire</div>
                                <div class="info-row__value">{{ $sourcePartner }}</div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div>
                                <div class="info-row__label">Type</div>
                                <div class="info-row__value">{{ $isCustomer ? 'Retour client' : 'Retour fournisseur' }}</div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div>
                                <div class="info-row__label">Numéro</div>
                                <div class="info-row__value">{{ $record->return_number ?? 'Auto' }}</div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div>
                                <div class="info-row__label">Statut</div>
                                <div class="info-row__value">
                                    <span class="status-pill status-pill--{{ $state['class'] }}">
                                        <i class="fas fa-{{ $state['icon'] }}"></i>{{ $state['label'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div>
                                <div class="info-row__label">Lignes chargées</div>
                                <div class="info-row__value">{{ $items->count() }}</div>
                            </div>
                        </div>

                        <div class="alert alert-info border-0 mt-4 mb-0">
                            <i class="fas fa-info-circle mr-1"></i>
                            La validation réintègre ou sort le stock et crée les mouvements liés.
                        </div>

                        <div class="d-flex flex-column gap-2 mt-4">
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-save mr-1"></i> {{ $updateRoute ? 'Mettre à jour' : 'Créer' }}
                            </button>
                            @if (! $isCreate)
                                <a href="{{ route($isCustomer ? 'customer-returns.print' : 'supplier-returns.print', $record) }}" class="btn btn-outline-primary btn-block">
                                    <i class="fas fa-print mr-1"></i> Imprimer
                                </a>
                            @endif
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-block">
                                <i class="fas fa-arrow-left mr-1"></i> Retour
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
