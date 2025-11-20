@extends('back.layouts.admin')

@section('content')
    <div class="container-fluid px-4 py-4">

        <!-- En-tête épuré et moderne -->
        <div class="page-header mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                <div class="mb-3 mb-md-0">
                    <div class="d-flex align-items-center mb-3">
                        <a href="{{ route('inventories.index') }}" class="btn btn-outline-light btn-back me-3 d-md-none">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <div class="header-icon me-3">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div>
                            <h1 class="mb-1">Inventaire #{{ $inventory->inventory_number }}</h1>
                            <p class="text-muted mb-0 small">Gestion et validation des stocks</p>
                        </div>
                    </div>

                    <div class="info-pills">
                        <span class="pill">
                            <i class="fas fa-warehouse"></i>
                            {{ $inventory->warehouse->name ?? 'N/A' }}
                        </span>
                        <span class="pill">
                            <i class="fas fa-calendar-alt"></i>
                            {{ $inventory->created_at->format('d/m/Y à H:i') }}
                        </span>
                        <span class="pill status-pill {{ $inventory->status === 'completed' ? 'completed' : 'pending' }}">
                            <i class="fas {{ $inventory->status === 'completed' ? 'fa-check-circle' : 'fa-clock' }}"></i>
                            {{ $inventory->status === 'completed' ? 'Clôturé' : 'En cours' }}
                        </span>
                    </div>
                </div>

                <!-- BOUTON DE RETOUR (visible sur desktop) -->
                <div class="flex-shrink-0 d-none d-md-block">
                    <a href="{{ route('inventories.index') }}" class="btn btn-outline-success btn-back">
                        <i class="fas fa-arrow-left me-2"></i>
                        Retour à la liste
                    </a>
                </div>
            </div>
        </div>

        <!-- Carte principale sobre -->
        <div class="main-card">
            <div class="card-header-clean">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-boxes me-2"></i>
                        Produits scannés
                    </h5>
                    <span class="count-badge">{{ count($items) }} produits</span>
                </div>
            </div>

            <div class="table-container">
                <table class="clean-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">
                                <input class="modern-checkbox" type="checkbox" id="selectAll">
                            </th>
                            <th>
                                <i class="fas fa-box me-2 opacity-50"></i>Produit
                            </th>
                            <th class="text-center">Qté Théorique</th>
                            <th class="text-center">Qté Réelle</th>
                            <th class="text-center">Écart</th>
                            <th class="text-center">État</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($items as $item)
                            <tr data-id="{{ $item->id }}"
                                class="table-row @if ($item->validated) validated-row @endif">
                                <td class="text-center">
                                    <input type="checkbox" class="modern-checkbox product-checkbox"
                                        value="{{ $item->id }}" {{ $item->validated ? 'checked disabled' : '' }}>
                                </td>

                                <td>
                                    <div class="product-name">{{ $item->product->name }}</div>
                                </td>

                                <td class="text-center">
                                    <span class="qty-badge theoretical">{{ $item->theoretical_qty }}</span>
                                </td>

                                <td class="text-center">
                                    <input type="number" class="qty-input real-input"
                                        value="{{ $item->real_qty ?? $item->theoretical_qty }}"
                                        {{ $item->validated ? 'disabled' : '' }}>
                                </td>

                                <td class="text-center ecart">
                                    <span class="ecart-badge neutral">0</span>
                                </td>

                                <td class="text-center status-label">
                                    @if ($item->validated)
                                        <span class="status-badge validated">
                                            <i class="fas fa-check-circle"></i> Validé
                                        </span>
                                    @else
                                        <span class="status-badge neutral">
                                            Équivalent
                                        </span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    @if (!$item->validated)
                                        <form action="{{ route('inventories.validate', $item->id) }}" method="POST"
                                            class="validate-form">
                                            @csrf
                                            @method('PATCH')

                                            <input type="hidden" name="real_qty" class="hidden-real-qty"
                                                value="{{ $item->real_qty ?? $item->theoretical_qty }}">

                                            <button type="submit" class="btn-validate">
                                                <i class="fas fa-check"></i> Valider
                                            </button>
                                        </form>
                                    @else
                                        <span class="validated-icon">
                                            <i class="fas fa-check-double"></i>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        /* Design fluide et sobre */
        body {
            background-color: #f8f9fb;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        /* En-tête épuré */
        .page-header {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .header-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
        }

        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        /* Pills d'information */
        .info-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #f1f5f9;
            border-radius: 20px;
            font-size: 0.875rem;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .pill i {
            font-size: 0.85rem;
            opacity: 0.7;
        }

        .status-pill {
            font-weight: 500;
        }

        .status-pill.completed {
            background: #dcfce7;
            color: #166534;
            border-color: #bbf7d0;
        }

        .status-pill.pending {
            background: #fef3c7;
            color: #92400e;
            border-color: #fde68a;
        }

        /* Carte principale */
        .main-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .card-header-clean {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .card-header-clean h5 {
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
        }

        .count-badge {
            background: #f1f5f9;
            color: #475569;
            padding: 0.375rem 0.875rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* Tableau épuré */
        .table-container {
            overflow-x: auto;
        }

        .clean-table {
            width: 100%;
            border-collapse: collapse;
        }

        .clean-table thead th {
            background: #f8fafc;
            padding: 1rem 1.25rem;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
        }

        .clean-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.15s ease;
        }

        .clean-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .clean-table tbody tr.validated-row {
            background-color: #f0fdf4;
        }

        .clean-table tbody tr.validated-row:hover {
            background-color: #dcfce7;
        }

        .clean-table tbody td {
            padding: 1rem 1.25rem;
            color: #334155;
        }

        /* Nom du produit */
        .product-name {
            font-weight: 500;
            color: #1e293b;
        }

        /* Badges de quantité */
        .qty-badge {
            display: inline-block;
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            background: #eff6ff;
            color: #1e40af;
        }

        /* Input de quantité */
        .qty-input {
            width: 90px;
            padding: 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            text-align: center;
            font-weight: 600;
            color: #1e293b;
            background: #f8fafc;
            transition: all 0.2s ease;
        }

        .qty-input:focus {
            outline: none;
            border-color: #6366f1;
            background: white;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .qty-input:disabled {
            background: #f1f5f9;
            color: #94a3b8;
            cursor: not-allowed;
        }

        /* Badge d'écart */
        .ecart-badge {
            display: inline-block;
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .ecart-badge.neutral {
            background: #f1f5f9;
            color: #64748b;
        }

        .ecart-badge.positive {
            background: #dcfce7;
            color: #166534;
        }

        .ecart-badge.negative {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Badge de statut */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.875rem;
            border-radius: 20px;
            font-size: 0.8125rem;
            font-weight: 500;
        }

        .status-badge.validated {
            background: #dcfce7;
            color: #166534;
        }

        .status-badge.neutral {
            background: #f1f5f9;
            color: #475569;
        }

        .status-badge.pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-badge.excess {
            background: #fef3c7;
            color: #92400e;
        }

        .status-badge.missing {
            background: #fef3c7;
            color: #92400e;
        }

        /* Bouton de validation */
        .btn-validate {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-validate:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        }

        .btn-validate:active {
            transform: translateY(0);
        }

        /* Icône validé */
        .validated-icon {
            color: #10b981;
            font-size: 1.25rem;
        }

        /* Checkbox moderne */
        .modern-checkbox {
            width: 18px;
            height: 18px;
            border: 2px solid #cbd5e1;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .modern-checkbox:hover {
            border-color: #6366f1;
        }

        .modern-checkbox:checked {
            background-color: #6366f1;
            border-color: #6366f1;
        }

        .modern-checkbox:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Animation douce */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .table-row {
            animation: fadeIn 0.3s ease-out;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .info-pills {
                flex-direction: column;
            }

            .pill {
                width: 100%;
            }

            .qty-input {
                width: 70px;
            }
        }

        /* Amélioration de la fluidité */
        * {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .clean-table tbody tr,
        .btn-validate,
        .qty-input,
        .pill,
        .status-badge {
            will-change: transform;
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            function updateRow(row) {
                let theoretical = parseFloat(row.querySelector(".theoretical").innerText);
                let real = parseFloat(row.querySelector(".real-input").value);
                let ecartCell = row.querySelector(".ecart");
                let statusLabel = row.querySelector(".status-label");

                if (isNaN(real)) real = 0;
                let ecart = real - theoretical;

                if (real === 0) {
                    ecartCell.innerHTML = `<span class="ecart-badge neutral">0</span>`;
                    statusLabel.innerHTML =
                        `<span class="status-badge pending"><i class="fas fa-hourglass-half"></i> En cours</span>`;
                } else if (ecart > 0) {
                    ecartCell.innerHTML = `<span class="ecart-badge positive">+${ecart}</span>`;
                    statusLabel.innerHTML =
                        `<span class="status-badge excess"><i class="fas fa-arrow-up"></i> Excédant</span>`;
                } else if (ecart < 0) {
                    ecartCell.innerHTML = `<span class="ecart-badge negative">${ecart}</span>`;
                    statusLabel.innerHTML =
                        `<span class="status-badge missing"><i class="fas fa-arrow-down"></i> Manquant</span>`;
                } else {
                    ecartCell.innerHTML = `<span class="ecart-badge neutral">0</span>`;
                    statusLabel.innerHTML =
                        `<span class="status-badge neutral"><i class="fas fa-equals"></i> Équivalent</span>`;
                }
            }

            // Mise à jour des écarts dynamiques
            document.querySelectorAll(".real-input").forEach(input => {
                input.addEventListener("input", function() {
                    updateRow(this.closest("tr"));
                });
                updateRow(input.closest("tr"));
            });

            // Met la valeur modifiée dans l'input hidden AVANT validation
            document.querySelectorAll(".validate-form").forEach(form => {
                form.addEventListener("submit", function() {
                    const row = form.closest("tr");
                    const realInput = row.querySelector(".real-input");
                    const hiddenInput = form.querySelector(".hidden-real-qty");

                    hiddenInput.value = realInput.value;
                });
            });

        });
    </script>
@endsection
