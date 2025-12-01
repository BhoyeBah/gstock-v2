@extends('back.layouts.admin')

@section('content')
    @php
        // Les calculs sont plus efficaces s'ils sont faits dans le contrôleur,
// mais pour garder la logique ici comme dans votre exemple :
$invoices = $contact->invoices()->latest()->paginate(10); // Paginer pour de meilleures performances

// Stats globales (sur toutes les factures, pas seulement la page actuelle)
$statsInvoices = $contact->invoices(); // Nouvelle requête pour les stats
$total_invoices = (clone $statsInvoices)->count();
$total_invoice_amount = (clone $statsInvoices)->sum('total_invoice');
$total_balance = (clone $statsInvoices)->sum('balance');
        $total_paid = $total_invoice_amount - $total_balance;
    @endphp

    @push('styles')
        <style>
            /* Design inspiré de la page de détails du produit pour la cohérence */
            .contact-show-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 15px;
                padding: 2rem;
                margin-bottom: 2rem;
                box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            }

            .contact-show-header h1 {
                color: #fff;
                font-weight: 700;
                margin: 0;
                font-size: 1.75rem;
            }

            .contact-show-header h1 .contact-name {
                font-weight: 400;
                opacity: 0.9;
            }

            .contact-show-header .btn {
                background: rgba(255, 255, 255, 0.2);
                color: #fff;
                border: 2px solid rgba(255, 255, 255, 0.4);
                font-weight: 600;
                padding: 0.6rem 1.5rem;
                border-radius: 10px;
                transition: all 0.3s ease;
            }

            .contact-show-header .btn:hover {
                background: #fff;
                color: #764ba2;
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            }

            .stat-card {
                background: #fff;
                border-radius: 12px;
                padding: .5rem;
                margin-bottom: 1.5rem;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
                border-left: 4px solid;
                transition: all 0.3s ease;
                height: 100%;
            }

            .stat-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            }

            .stat-card.border-primary {
                border-left-color: #4e73df;
            }

            .stat-card.border-success {
                border-left-color: #1cc88a;
            }

            .stat-card.border-danger {
                border-left-color: #e74a3b;
            }

            .stat-card.border-info {
                border-left-color: #36b9cc;
            }

            .stat-card .stat-icon {
                font-size: 2.5rem;
                opacity: 0.8;
            }

            .stat-card .stat-label {
                font-size: 0.75rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 0.5rem;
            }

            .stat-card .stat-value {
                font-size: 1.5rem;
                font-weight: 700;
                color: #5a5c69;
            }

            .section-card {
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
                margin-bottom: 2rem;
                overflow: hidden;
            }

            .section-card .card-header {
                background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
                color: #fff;
                padding: 1.25rem 1.5rem;
                border: none;
            }

            .section-card .card-header.bg-info {
                background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
            }

            .section-card .card-header h6 {
                margin: 0;
                font-weight: 700;
                font-size: 1.1rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .contact-info-table {
                margin: 0;
            }

            .contact-info-table th {
                background: #f8f9fc;
                font-weight: 600;
                color: #5a5c69;
                width: 200px;
                padding: 1rem;
                border: 1px solid #e3e6f0;
            }

            .contact-info-table td {
                padding: 1rem;
                color: #858796;
                border: 1px solid #e3e6f0;
            }

            .action-buttons {
                display: flex;
                gap: 0.75rem;
                margin-top: 1.5rem;
            }

            .modern-table {
                margin: 0;
            }

            .modern-table thead th {
                background: #f8f9fc;
                color: #5a5c69;
                font-weight: 600;
                border: none;
                border-bottom: 1px solid #e3e6f0;
                padding: 1rem;
                font-size: 0.8rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .modern-table tbody td {
                padding: 1rem;
                border-bottom: 1px solid #e3e6f0;
                color: #858796;
                vertical-align: middle;
            }

            .modern-table tbody tr:last-child td {
                border-bottom: none;
            }

            .modern-table tbody tr:hover {
                background: #f8f9fc;
            }

            .empty-state {
                padding: 3rem 1rem;
                text-align: center;
            }
        </style>
    @endpush

    <div class="container-fluid">
        <!-- Header -->
        <div class="contact-show-header">
            <div class="d-flex justify-content-between align-items:center flex-wrap">
                <h1>
                    <i class="fas fa-user-tie"></i>
                    Détails <span class="contact-name d-none d-sm-inline">- {{ $contact->fullname }}</span>
                </h1>
                <a href="{{ route($type . '.index') }}" class="btn mt-2 mt-md-0">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="row m-3">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card border-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label text-primary">Total Factures</div>
                            <div class="stat-value">{{ $total_invoices }}</div>
                        </div>
                        <i class="fas fa-file-invoice stat-icon text-primary"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card border-success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label text-success">Total à Payé</div>
                            <div class="stat-value">{{ number_format($total_paid, 0, ',', ' ') }} <small>CFA</small></div>
                        </div>
                        <i class="fas fa-hand-holding-usd stat-icon text-success"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card border-danger">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label text-danger">Solde Dû</div>
                            <div class="stat-value">{{ number_format($total_balance, 0, ',', ' ') }} <small>CFA</small>
                            </div>
                        </div>
                        <i class="fas fa-balance-scale-right stat-icon text-danger"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card border-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label text-info">Chiffre d'Affaires</div>
                            <div class="stat-value">{{ number_format($total_invoice_amount, 0, ',', ' ') }}
                                <small>CFA</small>
                            </div>
                        </div>
                        <i class="fas fa-receipt stat-icon text-info"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations du contact -->
        <div class="section-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-info-circle"></i> Informations sur le
                    {{ $type === 'clients' ? 'client' : 'fournisseur' }}</h6>
                <span
                    class="badge {{ $contact->is_active ? 'badge-success' : 'badge-danger' }}">{{ $contact->is_active ? 'Activé' : 'Désactivé' }}</span>
            </div>
            <div class="card-body">
                <table class="table contact-info-table">
                    <tbody>
                        <tr>
                            <th>Nom complet</th>
                            <td><strong>{{ $contact->fullname }}</strong></td>
                        </tr>
                        <tr>
                            <th>Téléphone</th>
                            <td>{{ $contact->phone_number }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $contact->email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Adresse</th>
                            <td>{{ $contact->address ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Créé le</th>
                            <td>{{ $contact->created_at->format('d/m/Y à H:i') }}</td>
                        </tr>
                    </tbody>
                </table>
                <div class="action-buttons">
                    <a href="{{ route("$type.edit", $contact->id) }}" class="btn btn-warning"><i
                            class="fas fa-edit mr-2"></i> Modifier</a>
                    <form action="{{ route("$type.destroy", $contact->id) }}" method="POST"
                        onsubmit="return confirm('Confirmer la suppression ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt mr-2"></i>
                            Supprimer</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Factures -->
        <div class="section-card">
            <div class="card-header bg-info">
                <h6><i class="fas fa-file-invoice"></i> Historique des Factures ({{ $total_invoices }})</h6>
            </div>
            <div class="card-body p-0">
                @if ($invoices->count() > 0)
                    <div class="table-responsive">
                        <table class="table modern-table mb-0">
                            <thead>
                                <tr>
                                    <th>Numéro</th>
                                    <th>Date</th>
                                    <th>Échéance</th>
                                    <th class="text-right">Montant Total</th>
                                    <th class="text-right">Solde Dû</th>
                                    <th class="text-center">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoices as $invoice)
                                    <tr>
                                        <td>
                                            <a href="{{ route('invoices.show', [$invoice->type . 's', $invoice->id]) }}">
                                                <strong>{{ $invoice->invoice_number }}</strong>
                                            </a>
                                        </td>
                                        <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                                        <td>{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}</td>
                                        <td class="text-right">{{ number_format($invoice->total_invoice, 0, ',', ' ') }}
                                            CFA</td>
                                        <td
                                            class="text-right font-weight-bold {{ $invoice->balance > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format($invoice->balance, 0, ',', ' ') }} CFA
                                        </td>
                                        <td class="text-center">
                                            @if ($invoice->balance <= 0)
                                                <span class="badge badge-success">Payée</span>
                                            @elseif($invoice->due_date < now())
                                                <span class="badge badge-danger">En retard</span>
                                            @else
                                                <span class="badge badge-warning">En attente</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination -->
                    @if ($invoices->hasPages())
                        <div class="p-3 border-top">
                            {{ $invoices->links() }}
                        </div>
                    @endif
                @else
                    <div class="empty-state">
                        <i class="fas fa-folder-open fa-3x mb-3 text-muted"></i>
                        <p class="text-muted">Aucune facture n'a été trouvée pour ce contact.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Paiements -->
        <div class="section-card">
            <div class="card-header bg-primary">
                <h6><i class="fas fa-file-invoice"></i> Historique des paiements ({{ $total_invoices }})</h6>
            </div>

            <div class="card-body p-0">
                @if ($invoices->count() > 0)
                    <div class="table-responsive">
                        <table class="table modern-table mb-0">
                            <thead>
                                <tr>
                                    <th>Numéro</th>
                                    <th>Date</th>
                                    <th class="text-right">Montant Total</th>
                                    <th class="text-right">Solde Dû</th>
                                    <th class="text-center">Statut</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($invoices as $invoice)
                                    <tr>
                                        <td>
                                            <a href="{{ route('invoices.show', [$invoice->type . 's', $invoice->id]) }}">
                                                <strong>{{ $invoice->invoice_number }}</strong>
                                            </a>
                                        </td>

                                        <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>

                                        <td class="text-right">
                                            {{ number_format($invoice->total_invoice, 0, ',', ' ') }} CFA
                                        </td>

                                        <td
                                            class="text-right font-weight-bold {{ $invoice->balance > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format($invoice->balance, 0, ',', ' ') }} CFA
                                        </td>

                                        <td class="text-center">
                                            @if ($invoice->balance <= 0)
                                                <span class="badge badge-success">Payée</span>
                                            @elseif ($invoice->due_date < now())
                                                <span class="badge badge-danger">En retard</span>
                                            @else
                                                <span class="badge badge-warning">En attente</span>
                                            @endif
                                        </td>
                                    </tr>

                                    {{-- Paiements --}}
                                    @if ($invoice->payments->count() > 0)
                                        <tr>
                                            <td colspan="5" class="bg-light p-0">
                                                <table class="table mb-0">
                                                    <thead class="small text-muted">
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Raison</th>
                                                            <th class="text-right">Montant payé</th>
                                                            <th class="text-right">Restant</th>
                                                        </tr>
                                                    </thead>

                                                    <tbody>
                                                        @foreach ($invoice->payments as $payment)
                                                            <tr>
                                                                <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}
                                                                </td>
                                                                <td>{{ ucfirst($payment->payment_type) }}</td>
                                                                <td class="text-right">
                                                                    {{ number_format($payment->amount_paid, 0, ',', ' ') }}
                                                                    CFA
                                                                </td>
                                                                <td class="text-right">
                                                                    {{ number_format($payment->remaining_amount, 0, ',', ' ') }}
                                                                    CFA
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if ($invoices->hasPages())
                        <div class="p-3 border-top">
                            {{ $invoices->links() }}
                        </div>
                    @endif
                @else
                    <div class="empty-state">
                        <i class="fas fa-folder-open fa-3x mb-3 text-muted"></i>
                        <p class="text-muted">Aucune facture n'a été trouvée pour ce contact.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
@endsection
