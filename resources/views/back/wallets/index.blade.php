@extends('back.layouts.admin')
@php
    $walletGroups = $wallets->groupBy(fn($wallet) => $wallet->type ?? 'other');

    $typeLabels = [
        'wave' => 'Wave',
        'orange' => 'Orange Money',
        'bank' => 'Banque',
        'other' => 'Autre',
    ];

    $typeIcons = [
        'wave' => 'fas fa-wallet',
        'orange' => 'fas fa-mobile-alt',
        'bank' => 'fas fa-university',
        'other' => 'fas fa-wallet',
    ];
@endphp

@section('content')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --wave-gradient: linear-gradient(135deg, #00D4FF 0%, #0099CC 100%);
            --orange-gradient: linear-gradient(135deg, #FF7900 0%, #FF5500 100%);
            --bank-gradient: linear-gradient(135deg, #1E40AF 0%, #1E3A8A 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --wallet-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        body {
            background: #f8f9fc;
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
        }

        /* Header Section */
        .page-header-modern {
            background: var(--primary-gradient);
            border-radius: 24px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .page-header-modern::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }

        .page-header-modern h1 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn-add-wallet {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .btn-add-wallet:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
            color: white;
        }

        /* Wallet Cards */
        .wallet-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .wallet-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            border: none;
        }

        .wallet-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
        }

        .wallet-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--wallet-gradient);
        }

        .wallet-card.wave::before {
            background: var(--wave-gradient);
        }

        .wallet-card.orange::before {
            background: var(--orange-gradient);
        }

        .wallet-card.bank::before {
            background: var(--bank-gradient);
        }

        .wallet-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .wallet-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 1rem;
            background: var(--wallet-gradient);
            color: white;
            flex-shrink: 0;
        }

        .wallet-card.wave .wallet-icon {
            background: var(--wave-gradient);
        }

        .wallet-card.orange .wallet-icon {
            background: var(--orange-gradient);
        }

        .wallet-card.bank .wallet-icon {
            background: var(--bank-gradient);
        }

        .wallet-info {
            flex: 1;
            min-width: 0;
        }

        .wallet-info h5 {
            margin: 0 0 0.25rem 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: #1f2937;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .wallet-info small {
            color: #6b7280;
            font-size: 0.875rem;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .wallet-balance {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #f3f4f6;
        }

        .wallet-balance-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .wallet-balance-amount {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1f2937;
            display: flex;
            align-items: baseline;
            gap: 0.5rem;
        }

        .wallet-balance-amount small {
            font-size: 1rem;
            color: #6b7280;
            font-weight: 600;
        }

        /* History Table */
        .history-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
            border: none;
        }

        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f3f4f6;
        }

        .history-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .modern-table thead th {
            background: #f9fafb;
            color: #374151;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem;
            border-bottom: 2px solid #e5e7eb;
            white-space: nowrap;
        }

        .modern-table tbody tr {
            transition: all 0.2s ease;
        }

        .modern-table tbody tr:hover {
            background: #f9fafb;
        }

        .modern-table tbody td {
            padding: 1rem;
            border-bottom: 1px solid #f3f4f6;
            color: #4b5563;
            vertical-align: middle;
        }

        .badge-modern {
            padding: 0.4rem 0.9rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            white-space: nowrap;
        }

        .badge-modern.success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }

        .badge-modern.danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
        }

        .badge-modern.warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
        }

        .wallet-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .wallet-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .wallet-dot.wave {
            background: var(--wave-gradient);
        }

        .wallet-dot.orange {
            background: var(--orange-gradient);
        }

        .wallet-dot.bank {
            background: var(--bank-gradient);
        }

        .wallet-dot.other {
            background: var(--wallet-gradient);
        }

        .status-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .status-badge.validated {
            background: #d1fae5;
            color: #065f46;
        }

        .status-badge.pending {
            background: #fef3c7;
            color: #92400e;
        }

        /* Modal Styles */
        .modal-content {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background: var(--primary-gradient);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 1.5rem 2rem;
            border: none;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .modal-header .close {
            color: white;
            opacity: 1;
            text-shadow: none;
            font-size: 1.5rem;
            font-weight: 300;
        }

        .modal-header .close:hover {
            opacity: 0.8;
        }

        .modal-title {
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal-body {
            padding: 2rem;
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .form-control,
        .form-select {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            /* padding: 0.75rem 1rem; */
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .btn-submit {
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-cancel {
            background: #f3f4f6;
            border: none;
            border-radius: 12px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            color: #374151;
            transition: all 0.3s ease;
        }

        .btn-cancel:hover {
            background: #e5e7eb;
            color: #374151;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Fix pour gap dans Bootstrap 4 */
        .d-flex.gap-2>* {
            margin-right: 0.5rem;
        }

        .d-flex.gap-2>*:last-child {
            margin-right: 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .wallet-grid {
                grid-template-columns: 1fr;
            }

            .page-header-modern h1 {
                font-size: 1.5rem;
            }

            .page-header-modern {
                padding: 1.5rem;
            }

            .table-responsive {
                overflow-x: auto;
            }

            .modern-table {
                font-size: 0.875rem;
            }

            .modern-table thead th,
            .modern-table tbody td {
                padding: 0.75rem 0.5rem;
            }

            .wallet-balance-amount {
                font-size: 1.5rem;
            }
        }
    </style>

    <!-- Page Header -->
    <div class="page-header-modern">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <h1>
                <i class="fas fa-wallet"></i>
                Mes Wallets
            </h1>
            <div class="d-flex gap-2 mt-3 mt-md-0">
                <button class="btn btn-add-wallet" data-toggle="modal" data-target="#transferWalletModal">
                    <i class="fas fa-exchange-alt mr-2"></i>Transférer
                </button>
                <button class="btn btn-add-wallet" data-toggle="modal" data-target="#addWalletModal">
                    <i class="fas fa-plus mr-2"></i>Ajouter
                </button>
            </div>
        </div>
    </div>

    <!-- Wallets Grid -->
    @if ($wallets->count() > 0)
        <div class="wallet-grid">
            @foreach ($walletGroups as $type => $group)
                @foreach ($group as $wallet)
                    <div class="wallet-card {{ $type }}">
                        <div class="wallet-header">
                            <div class="wallet-icon">
                                <i class="{{ $typeIcons[$type] ?? 'fas fa-wallet' }}"></i>
                            </div>
                            <div class="wallet-info">
                                <h5 title="{{ $wallet->name }}">{{ $wallet->name }}</h5>
                                <small title="{{ $wallet->identifier }}">{{ $wallet->identifier ?? 'N/A' }}</small>
                            </div>
                        </div>
                        <div class="wallet-balance">
                            <div class="wallet-balance-label">Solde disponible</div>
                            <div class="wallet-balance-amount">
                                {{ number_format($wallet->current_balance ?? 0, 0, ',', ' ') }}
                                <small>FCFA</small>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    @else
        <div class="history-card">
            <div class="empty-state">
                <i class="fas fa-wallet"></i>
                <h5>Aucun wallet disponible</h5>
                <p>Commencez par ajouter votre premier wallet</p>
                <button class="btn btn-submit mt-3" data-toggle="modal" data-target="#addWalletModal">
                    <i class="fas fa-plus mr-2"></i>Ajouter un wallet
                </button>
            </div>
        </div>
    @endif

    <!-- Transaction History -->
    <div class="history-card mt-4">
        <div class="history-header">
            <h6 class="history-title">
                <i class="fas fa-history"></i>
                Historique récent
            </h6>
        </div>

        <div class="table-responsive">
            @php
                $allTransactions = $wallets
                    ->flatMap(function ($wallet) {
                        return $wallet->transactions->map(function ($tx) use ($wallet) {
                            $tx->wallet_data = $wallet;
                            return $tx;
                        });
                    })
                    ->sortByDesc('created_at');
            @endphp

            @if ($allTransactions->count() > 0)
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Wallet</th>
                            <th>Description</th>
                            <th class="text-end">Montant</th>
                            <th class="text-center">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($allTransactions as $tx)
                            <tr>
                                <td style="white-space: nowrap;">
                                    {{ $tx->created_at->format('d M Y') }}<br>
                                    <small class="text-muted">{{ $tx->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    @if ($tx->type === 'in')
                                        <span class="badge-modern success">
                                            <i class="fas fa-arrow-down"></i> Reçu
                                        </span>
                                    @elseif($tx->type === 'out')
                                        <span class="badge-modern danger">
                                            <i class="fas fa-arrow-up"></i> Envoyé
                                        </span>
                                    @else
                                        <span class="badge-modern warning">
                                            <i class="fas fa-exchange-alt"></i> {{ ucfirst($tx->type) }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="wallet-indicator">
                                        <div class="wallet-dot {{ $tx->wallet_data->type ?? 'other' }}"></div>
                                        {{ $tx->wallet_data->name ?? 'N/A' }}
                                    </div>
                                </td>
                                <td>{{ $tx->note ?? 'Aucune description' }}</td>
                                <td class="text-end"
                                    style="white-space: nowrap; font-weight: 700; color: {{ $tx->type === 'in' ? '#065f46' : '#991b1b' }}">
                                    {{ $tx->type === 'in' ? '+' : '-' }}{{ number_format($tx->amount ?? 0, 0, ',', ' ') }}
                                    FCFA
                                </td>
                                <td class="text-center">
                                    <span class="status-badge validated">Validé</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <h5>Aucune transaction</h5>
                    <p>Vos transactions apparaîtront ici</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Add Wallet Modal -->
    <div class="modal fade" id="addWalletModal" tabindex="-1" role="dialog" aria-labelledby="addWalletModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addWalletModalLabel">
                        <i class="fas fa-plus-circle"></i>
                        Ajouter un nouveau wallet
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('wallet.store') }}" method="POST">
                        @csrf
                        <div class="form-group mb-4">
                            <label for="wallet_type" class="form-label">
                                <i class="fas fa-layer-group text-primary mr-2"></i>
                                Type de wallet
                            </label>
                            <select class="form-control" id="wallet_type" name="type" required>
                                <option value="" disabled selected>Choisir un type</option>
                                <option value="wave">Wave</option>
                                <option value="orange">Orange Money</option>
                                <option value="bank">Banque</option>
                                <option value="other">Autre</option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="wallet_name" class="form-label">Nom du wallet</label>
                            <input type="text" class="form-control" id="wallet_name" name="name"
                                placeholder="Ex: Mon compte Wave principal" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="wallet_code" class="form-label">Code du wallet</label>
                            <input type="text" class="form-control" id="wallet_code" name="code"
                                placeholder="Ex: OM79" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="wallet_number" class="form-label">Numéro / Identifiant</label>
                            <input type="text" class="form-control" id="wallet_number" name="identifier"
                                placeholder="Ex: +221 77 123 45 67">
                            <small class="form-text text-muted">Numéro de téléphone, numéro de compte, etc.</small>
                        </div>

                        <div class="form-group mb-4">
                            <label for="initial_balance" class="form-label">Solde initial</label>
                            <input type="number" class="form-control" id="initial_balance" name="initial_balance"
                                placeholder="0" step="0.01" min="0" value="0">
                            <small class="form-text text-muted">Montant en FCFA</small>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-cancel" data-dismiss="modal">
                                Annuler
                            </button>
                            <button type="submit" class="btn btn-submit">
                                <i class="fas fa-check mr-2"></i>Créer le wallet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Transfer Between Wallets Modal -->
    <div class="modal fade" id="transferWalletModal" tabindex="-1" role="dialog"
        aria-labelledby="transferWalletModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="transferWalletModalLabel">
                        <i class="fas fa-exchange-alt"></i>
                        Transférer entre wallets
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('wallet.transfert') }}" method="POST" id="transferForm">
                        @csrf
                        <div class="form-group mb-4">
                            <label for="from_wallet" class="form-label">
                                <i class="fas fa-arrow-up text-danger mr-2"></i>
                                Depuis le wallet
                            </label>
                            <select class="form-control" id="from_wallet" name="from_wallet_id" required>
                                <option value="" disabled selected>Sélectionner le wallet source</option>
                                @foreach ($wallets as $wallet)
                                    <option value="{{ $wallet->id }}"
                                        data-balance="{{ $wallet->current_balance ?? 0 }}">
                                        {{ $wallet->name }} -
                                        {{ number_format($wallet->current_balance ?? 0, 0, ',', ' ') }} FCFA
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="text-center mb-4">
                            <i class="fas fa-arrow-down text-primary" style="font-size: 1.5rem;"></i>
                        </div>

                        <div class="form-group mb-4">
                            <label for="to_wallet" class="form-label">
                                <i class="fas fa-arrow-down text-success mr-2"></i>
                                Vers le wallet
                            </label>
                            <select class="form-control" id="to_wallet" name="to_wallet_id" required>
                                <option value="" disabled selected>Sélectionner le wallet destination</option>
                                @foreach ($wallets as $wallet)
                                    <option value="{{ $wallet->id }}">
                                        {{ $wallet->name }} -
                                        {{ number_format($wallet->current_balance ?? 0, 0, ',', ' ') }} FCFA
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="transfer_amount" class="form-label">
                                <i class="fas fa-coins text-warning mr-2"></i>
                                Montant à transférer
                            </label>
                            <input type="number" class="form-control" id="transfer_amount" name="amount"
                                placeholder="Ex: 50000" step="0.01" min="0" required>
                            <small class="form-text text-muted">Montant en FCFA</small>
                        </div>

                        <div class="alert alert-info"
                            style="background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); border: none; border-radius: 12px;">
                            <i class="fas fa-info-circle mr-2"></i>
                            <small>Ce transfert sera enregistré dans l'historique des deux wallets.</small>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-cancel" data-dismiss="modal">
                                Annuler
                            </button>
                            <button type="submit" class="btn btn-submit">
                                <i class="fas fa-paper-plane mr-2"></i>Effectuer le transfert
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Validation pour empêcher le transfert vers le même wallet
        document.addEventListener('DOMContentLoaded', function() {
            const transferForm = document.getElementById('transferForm');

            if (transferForm) {
                transferForm.addEventListener('submit', function(e) {
                    const fromWallet = document.getElementById('from_wallet').value;
                    const toWallet = document.getElementById('to_wallet').value;
                    const amount = parseFloat(document.getElementById('transfer_amount').value);

                    if (fromWallet === toWallet) {
                        e.preventDefault();
                        alert('Vous ne pouvez pas transférer vers le même wallet !');
                        return false;
                    }

                    // Vérifier le solde disponible
                    const fromWalletOption = document.querySelector('#from_wallet option:checked');
                    const balance = parseFloat(fromWalletOption?.getAttribute('data-balance') || 0);

                    if (amount > balance) {
                        e.preventDefault();
                        alert('Solde insuffisant dans le wallet source !');
                        return false;
                    }
                });
            }

            // Désactiver le wallet source dans la liste destination
            const fromWalletSelect = document.getElementById('from_wallet');
            if (fromWalletSelect) {
                fromWalletSelect.addEventListener('change', function() {
                    const toWalletSelect = document.getElementById('to_wallet');
                    const selectedValue = this.value;

                    // Réactiver toutes les options
                    toWalletSelect.querySelectorAll('option').forEach(option => {
                        option.disabled = false;
                    });

                    // Désactiver l'option correspondante
                    const optionToDisable = toWalletSelect.querySelector(
                        `option[value="${selectedValue}"]`);
                    if (optionToDisable) {
                        optionToDisable.disabled = true;
                    }

                    // Si l'option désactivée est sélectionnée, réinitialiser
                    if (toWalletSelect.value === selectedValue) {
                        toWalletSelect.value = '';
                    }
                });
            }
        });
    </script>
@endsection
