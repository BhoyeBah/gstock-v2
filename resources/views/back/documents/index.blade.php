@extends('back.layouts.admin')

@section('content')
    @php
        $firstRecord = method_exists($records, 'getCollection') ? $records->getCollection()->first() : $records->first();
        $table = $firstRecord?->getTable() ?? '';
        $documentKind = $documentKind ?? (\Illuminate\Support\Str::contains($table, 'quote') ? 'quote' : (\Illuminate\Support\Str::contains($table, 'sale_order') ? 'sale_order' : (\Illuminate\Support\Str::contains($table, 'delivery_note') ? 'delivery_note' : 'receipt')));
        $numberField = \Illuminate\Support\Str::contains($table, 'quote') ? 'quote_number' : (
            \Illuminate\Support\Str::contains($table, 'sale_order') ? 'order_number' : (
                \Illuminate\Support\Str::contains($table, 'purchase_order') ? 'purchase_number' : (
                    \Illuminate\Support\Str::contains($table, 'delivery_note') ? 'delivery_number' : 'receipt_number'
                )
            )
        );
        $dateField = str_replace('number', 'date', $numberField);

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
    @endphp

    <div class="container-fluid">
        <div class="page-hero page-hero--accent mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="page-hero__eyebrow mb-2">Documents commerciaux</div>
                    <h1 class="page-hero__title">{{ $title }}</h1>
                    <p class="page-hero__subtitle">{{ $subtitle ?? 'Liste tenant-safe, statuts lisibles et accès rapide aux actions principales.' }}</p>
                </div>

                @if (!empty($createRoute))
                    <a href="{{ route($createRoute) }}" class="btn btn-light px-4">
                        <i class="fas fa-plus mr-1"></i> Nouveau document
                    </a>
                @endif
            </div>
        </div>

        <div class="table-card">
            <div class="card-body p-0">
                <div class="d-flex flex-wrap align-items-center justify-content-between px-4 pt-4 pb-3">
                    <div>
                        <h4 class="mb-1">{{ $title }}</h4>
                        <p class="mb-0 text-muted">{{ $emptyMessage ?? 'Aucun document pour le moment.' }}</p>
                    </div>
                    @if(!empty($status))
                        <span class="status-pill status-pill--info">
                            <i class="fas fa-filter"></i>{{ $status }}
                        </span>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table data-table">
                        <thead>
                            <tr>
                                <th>Numéro</th>
                                <th>Client / Fournisseur</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th class="text-right">Total TTC</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($records as $record)
                                @php $state = $badge($record->status); @endphp
                                <tr>
                                    <td class="font-weight-bold text-primary">{{ $record->{$numberField} }}</td>
                                    <td>{{ $record->contact?->fullname ?? 'N/A' }}</td>
                                    <td>{{ optional($record->{$dateField})->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="status-pill status-pill--{{ $state['class'] }}">
                                            <i class="fas fa-{{ $state['icon'] }}"></i>{{ $state['label'] }}
                                        </span>
                                    </td>
                                    <td class="text-right font-weight-bold">
                                        {{ number_format((int) ($record->total_ttc ?? $record->total_invoice ?? 0), 0, ',', ' ') }} FCFA
                                    </td>
                                    <td class="text-right">
                                        @if ($documentKind === 'quote')
                                            <div class="d-inline-flex align-items-center flex-wrap justify-content-end" style="gap:.5rem;">
                                                <a href="{{ route($showRoute, $record) }}" class="btn btn-sm btn-outline-primary">
                                                    Voir
                                                </a>

                                                @if (in_array($record->status, ['draft', 'sent'], true))
                                                    <a href="{{ route('quotes.edit', $record) }}" class="btn btn-sm btn-outline-secondary">
                                                        Modifier
                                                    </a>
                                                @endif

                                                @if (!in_array($record->status, ['converted', 'rejected', 'cancelled'], true))
                                                    <a href="{{ route('quotes.print', $record) }}" class="btn btn-sm btn-outline-secondary">
                                                        Imprimer
                                                    </a>
                                                @endif

                                                @if (in_array($record->status, ['draft', 'sent', 'accepted'], true))
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-outline-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            Plus
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                                            @if ($record->status === 'draft')
                                                                <form method="POST" action="{{ route('quotes.send', $record) }}" class="m-0">
                                                                    @csrf
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="fas fa-paper-plane mr-1"></i> Marquer comme envoyé
                                                                    </button>
                                                                </form>
                                                            @endif

                                                            @if (in_array($record->status, ['draft', 'sent'], true))
                                                                <form method="POST" action="{{ route('quotes.accept', $record) }}" class="m-0">
                                                                    @csrf
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="fas fa-check-circle mr-1"></i> Accepter
                                                                    </button>
                                                                </form>
                                                                <form method="POST" action="{{ route('quotes.reject', $record) }}" class="m-0">
                                                                    @csrf
                                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Confirmer le rejet du devis ?')">
                                                                        <i class="fas fa-times-circle mr-1"></i> Rejeter
                                                                    </button>
                                                                </form>
                                                                <div class="dropdown-divider"></div>
                                                            @endif

                                                            <form method="POST" action="{{ route('quotes.cancel', $record) }}" class="m-0">
                                                                @csrf
                                                                <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Confirmer l\'annulation du devis ?')">
                                                                    <i class="fas fa-ban mr-1"></i> Annuler
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @elseif (!empty($showRoute))
                                            <a href="{{ route($showRoute, $record) }}" class="btn btn-sm btn-outline-primary">
                                                Voir
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state my-4">
                                            <div class="empty-state__icon"><i class="fas fa-folder-open"></i></div>
                                            <div class="font-weight-bold mb-1">{{ $emptyMessage ?? 'Aucune donnée.' }}</div>
                                            <div>Utilisez le bouton de création pour enregistrer un premier document.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3">
            {{ $records->links() }}
        </div>
    </div>
@endsection
