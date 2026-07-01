@php
    use Carbon\Carbon;
@endphp

<div class="search-section">
    <div class="card-header text-white">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-filter mr-2"></i> Recherche et filtres
        </h6>
    </div>
    <div class="card-body p-4">
        <form method="GET" action="{{ route('invoices.index', $type) }}">
            <div class="form-row">
                <div class="col-md-3 mb-3">
                    <label for="search_number">Numéro de facture</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                        </div>
                        <input type="text" name="search_number" id="search_number" class="form-control"
                            value="{{ request('search_number') }}" placeholder="Ex: FAC-2024-001">
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <label for="search_contact">
                        {{ $invoiceType === 'Clients' ? 'Client' : 'Fournisseur' }}
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                        </div>
                        <input type="text" name="search_contact" id="search_contact" class="form-control"
                            value="{{ request('search_contact') }}"
                            placeholder="Nom {{ strtolower($invoiceType === 'Clients' ? 'du client' : 'du fournisseur') }}">
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <label for="status">Statut</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-tags"></i></span>
                        </div>
                        <select name="status" id="status" class="form-control">
                            <option value="">Tous les statuts</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Brouillon
                            </option>
                            <option value="validated" {{ request('status') === 'validated' ? 'selected' : '' }}>
                                Validée
                            </option>
                            <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>
                                Partielle
                            </option>
                            <option value="credited" {{ request('status') === 'credited' ? 'selected' : '' }}>
                                Créditée
                            </option>
                            <option value="partially_credited" {{ request('status') === 'partially_credited' ? 'selected' : '' }}>
                                Partiellement payée
                            </option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Payée
                            </option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>
                                Annulée
                            </option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary mr-2 flex-fill">
                        <i class="fas fa-search mr-1"></i> Rechercher
                    </button>
                    <a href="{{ route('invoices.index', $type) }}" class="btn btn-secondary flex-fill">
                        <i class="fas fa-redo mr-1"></i> Réinitialiser
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="invoice-list-section">
    <div class="card-header text-white">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-list-ul mr-2"></i> Liste des factures {{ strtolower($invoiceType) }}
        </h6>
    </div>
    <div class="card-body p-0">
        @if ($invoices->count() > 0)
            <div class="table-responsive">
                <table class="table invoice-table">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Numéro</th>
                            <th>{{ $invoiceType === 'Clients' ? 'Client' : 'Fournisseur' }}</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th class="text-right">Total</th>
                            <th class="text-right">Balance</th>
                            <th class="text-center" width="250">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoices as $invoice)
                            <tr>
                                <td class="font-weight-bold text-muted">
                                    {{ $loop->iteration + ($invoices->currentPage() - 1) * $invoices->perPage() }}
                                </td>
                                <td>
                                    <span class="font-weight-bold text-primary">
                                        {{ $invoice->invoice_number ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    @php $contact = $invoice->contact; @endphp
                                    @if ($contact)
                                        <a href="{{ route("$type.show", $contact->id) }}" class="contact-link">
                                            <i class="fas fa-user-circle mr-1"></i>
                                            {{ $contact->fullname }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <i class="fas fa-calendar-alt text-muted mr-1"></i>
                                    {{ $invoice->invoice_date ? Carbon::parse($invoice->invoice_date)->format('d/m/Y') : '-' }}
                                </td>
                                <td>
                                    @php
                                        $statusConfig = [
                                            'draft' => ['color' => 'secondary', 'icon' => 'fa-file'],
                                            'validated' => ['color' => 'info', 'icon' => 'fa-check-circle'],
                                            'partial' => ['color' => 'warning', 'icon' => 'fa-clock'],
                                            'credited' => ['color' => 'success', 'icon' => 'fa-file-invoice-dollar'],
                                            'partially_credited' => ['color' => 'warning', 'icon' => 'fa-file-invoice-dollar'],
                                            'paid' => ['color' => 'success', 'icon' => 'fa-check-double'],
                                            'cancelled' => ['color' => 'danger', 'icon' => 'fa-times-circle'],
                                        ];
                                        $config = $statusConfig[$invoice->status] ?? [
                                            'color' => 'secondary',
                                            'icon' => 'fa-file',
                                        ];
                                    @endphp
                                    <span class="badge badge-{{ $config['color'] }}">
                                        <i class="fas {{ $config['icon'] }} mr-1"></i>
                                        {{ [
                                            'draft' => 'Brouillon',
                                            'validated' => 'Validée',
                                            'partial' => 'Partiellement payée',
                                            'credited' => 'Créditée',
                                            'partially_credited' => 'Partiellement payée',
                                            'paid' => 'Payée',
                                            'cancelled' => 'Annulée',
                                        ][$invoice->status] ?? ucfirst($invoice->status) }}
                                    </span>
                                </td>
                                <td class="text-right font-weight-bold">
                                    {{ number_format($invoice->total_invoice, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="text-right">
                                    <span class="badge badge-{{ $invoice->balance > 0 ? 'warning' : 'success' }}">
                                        {{ number_format($invoice->balance, 0, ',', ' ') }} FCFA
                                    </span>
                                </td>
                                <td class="text-center action-buttons">
                                    <!-- ✅ Bouton Payer (affiché uniquement si la facture est validée ou partiellement payée) -->
                                    @if (in_array($invoice->status, ['validated', 'partial', 'partially_credited']))
                                        <button type="button" class="btn btn-sm btn-primary" title="Payer"
                                            data-toggle="modal" data-target="#paymentModal{{ $invoice->id }}">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </button>
                                    @endif

                                    <!-- ✅ Bouton Voir -->

                                    <a href="{{ route('invoices.show', [$type, $invoice->id]) }}"
                                        class="btn btn-sm btn-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <!-- ✅ Bouton Imprimer (toujours visible) -->
                                    <!-- Bouton Imprimer -->
                                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal"
                                        data-target="#printChoiceModal{{ $invoice->id }}">
                                        <i class="fas fa-print"></i>
                                    </button>

                                    <!-- ✅ Actions spécifiques au statut "draft" -->
                                    @if ($invoice->status === 'draft')
                                        <!-- Bouton Valider -->
                                        <form action="{{ route('invoices.validate', [$type, $invoice->id]) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm('Confirmez-vous la validation de cette facture ?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-success" title="Valider">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>

                                        <!-- Bouton Modifier -->
                                        <a href="{{ route('invoices.edit', [$type, $invoice->id]) }}"
                                            class="btn btn-sm btn-warning" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <!-- Bouton Supprimer -->
                                        <form action="{{ route('invoices.destroy', [$type, $invoice->id]) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm('Confirmer la suppression de cette facture ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <!-- Bouton Supprimer -->
                                    @can('force_delete_invoice')
                                        <form action="{{ route('invoices.forceDestroy', [$type, $invoice->id]) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm('Confirmer la suppression de cette facture ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Annuler">
                                                <i class="fas fa-skull"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('invoices.cancel', [$type, $invoice->id]) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm('Confirmer la suppression de cette facture ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Annuler">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </td>

                            </tr>

                            <!-- Modal paiement -->
                            <div class="modal fade" id="paymentModal{{ $invoice->id }}" tabindex="-1"
                                role="dialog">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title">
                                                <i class="fas fa-money-bill-wave mr-2"></i>
                                                Paiement facture #{{ $invoice->invoice_number }}
                                            </h5>
                                            <button type="button" class="close text-white" data-dismiss="modal">
                                                <span>&times;</span>
                                            </button>
                                        </div>

                                        <form action="{{ route('invoices.pay', [$type, $invoice->id]) }}"
                                            method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <div class="modal-body">
                                                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

                                                <!-- Solde restant -->
                                                <div class="alert alert-info text-center mb-4">
                                                    <i class="fas fa-wallet fa-2x mb-2"></i>
                                                    <div class="small">Solde restant</div>
                                                    <h4 class="mb-0 font-weight-bold">
                                                        {{ number_format($invoice->balance, 0, ',', ' ') }} FCFA
                                                    </h4>
                                                </div>

                                                <!-- Montant payé -->
                                                <div class="form-group">
                                                    <label for="amount_paid_{{ $invoice->id }}">
                                                        <i class="fas fa-dollar-sign text-primary mr-1"></i>
                                                        Montant payé <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="number" class="form-control form-control-lg"
                                                        id="amount_paid_{{ $invoice->id }}" name="amount_paid"
                                                        placeholder="Entrez le montant" min="1"
                                                        max="{{ $invoice->balance }}" required>
                                                </div>

                                                <!-- Sélection du wallet -->
                                                <div class="form-group">
                                                    <label for="wallet_id_{{ $invoice->id }}">
                                                        <i class="fas fa-wallet text-primary mr-1"></i>
                                                        Choisir le wallet
                                                    </label>
                                                    <select class="form-control" id="wallet_id_{{ $invoice->id }}"
                                                        name="wallet_id" required>
                                                        <option value="">-- Sélectionnez un wallet --</option>
                                                        @foreach ($wallets as $wallet)
                                                            <option value="{{ $wallet->id }}">
                                                                {{ $wallet->name ?? 'Wallet ' . $wallet->id }}
                                                                ({{ number_format($wallet->current_balance, 0, ',', ' ') }}
                                                                FCFA)
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>


                                                <!-- Date du paiement -->
                                                <div class="form-group mb-0">
                                                    <label for="payment_date_{{ $invoice->id }}">
                                                        <i class="fas fa-calendar-alt text-primary mr-1"></i>
                                                        Date du paiement <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="date" class="form-control"
                                                        id="payment_date_{{ $invoice->id }}" name="payment_date"
                                                        value="{{ date('Y-m-d') }}" required>
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-dismiss="modal">
                                                    <i class="fas fa-times mr-1"></i> Annuler
                                                </button>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-check mr-1"></i> Confirmer le paiement
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>


                            <!-- Modal spécifique à cette facture -->
                            <div class="modal fade" id="printChoiceModal{{ $invoice->id }}" tabindex="-1"
                                role="dialog" aria-labelledby="printChoiceModalLabel{{ $invoice->id }}"
                                aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content shadow">

                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title" id="printChoiceModalLabel{{ $invoice->id }}">
                                                <i class="fas fa-print mr-2"></i> Choisir l'orientation
                                                d'impression
                                            </h5>
                                            <button type="button" class="close text-white" data-dismiss="modal"
                                                aria-label="Fermer">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>

                                        <div class="modal-body py-5">
                                            <p class="text-muted text-center mb-4">Sélectionnez le format :</p>
                                            <div class="row justify-content-center align-items-stretch">
                                                <div class="col-6 col-md-5">
                                                    <a href="{{ route('invoices.print', [$type, $invoice->id]) }}?orientation=portrait"
                                                        target="_blank"
                                                        class="btn btn-outline-primary btn-lg btn-block h-100 d-flex flex-column align-items-center justify-content-center py-4">
                                                        <i class="fas fa-file-alt mb-3" style="font-size: 3em;"></i>
                                                        <span class="font-weight-bold">Portrait</span>
                                                    </a>
                                                </div>
                                                <div class="col-6 col-md-5">
                                                    <a href="{{ route('invoices.print', [$type, $invoice->id]) }}?orientation=landscape"
                                                        target="_blank"
                                                        class="btn btn-outline-secondary btn-lg btn-block h-100 d-flex flex-column align-items-center justify-content-center py-4">
                                                        <i class="fas fa-file-alt mb-3"
                                                            style="font-size: 3em; transform: rotate(90deg);"></i>
                                                        <span class="font-weight-bold">Paysage</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer border-0 bg-light">
                                            <button type="button" class="btn btn-sm btn-secondary"
                                                data-dismiss="modal">Annuler</button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center p-4 border-top">
                <div class="text-muted small">
                    <i class="fas fa-info-circle mr-1"></i>
                    Affichage de <strong>{{ $invoices->firstItem() }}</strong> à
                    <strong>{{ $invoices->lastItem() }}</strong> sur
                    <strong>{{ $invoices->total() }}</strong> factures
                </div>
                <div>
                    {{ $invoices->appends(request()->query())->links() }}
                </div>
            </div>
        @else
            <div class="p-5 text-center">
                <div class="mb-4">
                    <i class="fas fa-inbox fa-4x text-muted"></i>
                </div>
                <h5 class="text-muted">Aucune facture {{ strtolower($invoiceType) }} trouvée</h5>
                <p class="text-muted mb-4">
                    Essayez de modifier vos filtres ou créez-en une nouvelle
                </p>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addInvoiceModal">
                    <i class="fas fa-plus-circle mr-2"></i> Créer une nouvelle facture
                </button>
            </div>
        @endif
    </div>
</div>
