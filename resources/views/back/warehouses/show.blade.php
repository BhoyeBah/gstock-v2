@extends('back.layouts.admin')

@section('content')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #fee140 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        body {
            background: #f8f9fc;
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
        }

        /* Header Section */
        .warehouse-header {
            background: var(--primary-gradient);
            border-radius: 24px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .warehouse-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
            animation: pulse 8s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1) rotate(0deg); opacity: 0.5; }
            50% { transform: scale(1.1) rotate(180deg); opacity: 0.8; }
        }

        .warehouse-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .warehouse-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .warehouse-title i {
            font-size: 2rem;
        }

        .btn-back {
            background: white;
            color: #4f46e5;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            color: #4f46e5;
        }

        /* Info Card */
        .info-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            border: none;
        }

        .info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--primary-gradient);
        }

        .info-card-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f3f4f6;
        }

        .info-card-header i {
            width: 40px;
            height: 40px;
            background: var(--primary-gradient);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .info-card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .info-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-value {
            font-size: 1rem;
            color: #1f2937;
            font-weight: 500;
        }

        .info-value a {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .info-value a:hover {
            color: #7c3aed;
            text-decoration: underline;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-badge.active {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }

        .status-badge.inactive {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
        }

        /* Table Cards */
        .table-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            border: none;
        }

        .table-card.info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--info-gradient);
        }

        .table-card.warning::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--warning-gradient);
        }

        .table-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f3f4f6;
        }

        .table-card-header-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .table-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .table-card.info .table-icon {
            background: var(--info-gradient);
        }

        .table-card.warning .table-icon {
            background: var(--warning-gradient);
        }

        .table-card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        /* Modern Table */
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
            text-align: center;
            white-space: nowrap;
        }

        .modern-table tbody tr {
            transition: all 0.2s ease;
        }

        .modern-table tbody tr:hover {
            background: #f9fafb;
            transform: scale(1.005);
        }

        .modern-table tbody td {
            padding: 1rem;
            border-bottom: 1px solid #f3f4f6;
            color: #4b5563;
            vertical-align: middle;
        }

        .modern-table tbody td a {
            color: #4f46e5;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .modern-table tbody td a:hover {
            color: #7c3aed;
            text-decoration: underline;
        }

        /* Badge for quantities */
        .qty-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .qty-badge.initial {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
        }

        .qty-badge.remaining {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }

        /* Movement type badges */
        .movement-badge {
            display: inline-block;
            padding: 0.4rem 0.9rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .movement-badge.in {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }

        .movement-badge.out {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }

        .empty-state p {
            margin: 0;
            font-size: 1rem;
        }

        /* Pagination */
        .pagination {
            gap: 0.5rem;
            margin-top: 1.5rem;
        }

        .page-item .page-link {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            color: #4f46e5;
            font-weight: 600;
            padding: 0.5rem 1rem;
            margin: 0 0.25rem;
            transition: all 0.3s ease;
        }

        .page-item.active .page-link {
            background: var(--primary-gradient);
            border-color: transparent;
        }

        .page-item .page-link:hover {
            background: #f3f4f6;
            border-color: #4f46e5;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .warehouse-header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .warehouse-title {
                font-size: 1.25rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .table-responsive {
                border-radius: 12px;
            }
        }
    </style>

    <div class="container-fluid">
        <!-- Warehouse Header -->
        <div class="warehouse-header">
            <div class="warehouse-header-content">
                <h1 class="warehouse-title">
                    <i class="fas fa-warehouse"></i>
                    <span>{{ $warehouse->name }}</span>
                </h1>
                <a href="{{ route('warehouses.index') }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>

        <!-- General Information -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-info-circle"></i>
                <h6 class="info-card-title">Informations générales</h6>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">
                        📌 Nom de l'entrepôt
                    </div>
                    <div class="info-value">{{ $warehouse->name }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">
                        📍 Adresse
                    </div>
                    <div class="info-value">{{ $warehouse->address ?? '-' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">
                        👤 Responsable
                    </div>
                    <div class="info-value">
                        @if($warehouse->manager)
                            <a href="{{ route('users.edit', $warehouse->manager->id) }}">
                                {{ $warehouse->manager->name }}
                            </a>
                        @else
                            -
                        @endif
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label">
                        📝 Description
                    </div>
                    <div class="info-value">{{ $warehouse->description ?? '-' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">
                        ⚙️ Statut
                    </div>
                    <div class="info-value">
                        @if ($warehouse->is_active)
                            <span class="status-badge active">
                                <i class="fas fa-check-circle"></i> Activé
                            </span>
                        @else
                            <span class="status-badge inactive">
                                <i class="fas fa-times-circle"></i> Désactivé
                            </span>
                        @endif
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label">
                        📅 Date de création
                    </div>
                    <div class="info-value">{{ $warehouse->created_at->format('d/m/Y à H:i') }}</div>
                </div>
            </div>
        </div>

        <!-- Batches Table -->
        <div class="table-card info">
            <div class="table-card-header">
                <div class="table-card-header-left">
                    <div class="table-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <h6 class="table-card-title">Lots disponibles</h6>
                </div>
            </div>

            @if ($batches->count())
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Produit</th>
                                <th>Facture</th>
                                <th>Prix unitaire</th>
                                <th>Qté initiale</th>
                                <th>Qté restante</th>
                                <th>Date d'expiration</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($batches as $batch)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>
                                        <a href="{{ route('products.show', $batch->product->id ?? '#') }}">
                                            {{ $batch->product->name ?? '-' }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('invoices.show', [$batch->invoice->type . 's', $batch->invoice->id ?? '#']) }}">
                                            <strong>{{ $batch->invoice->invoice_number ?? '-' }}</strong>
                                        </a>
                                    </td>
                                    <td class="text-right">
                                        <strong>{{ number_format($batch->unit_price, 0, ',', ' ') }} FCFA</strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="qty-badge initial">{{ $batch->quantity }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="qty-badge remaining">{{ $batch->remaining }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if ($batch->expiration_date)
                                            {{ $batch->expiration_date->format('d/m/Y') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center">
                    {{ $batches->links() }}
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <p>Aucun lot disponible pour cet entrepôt.</p>
                </div>
            @endif
        </div>

        <!-- Stock Movements Table -->
        <div class="table-card warning">
            <div class="table-card-header">
                <div class="table-card-header-left">
                    <div class="table-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <h6 class="table-card-title">Mouvements de stock</h6>
                </div>
            </div>

            @if ($movements->count())
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Produit</th>
                                <th>Type de mouvement</th>
                                <th>Quantité</th>
                                <th>Facture</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($movements as $movement)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>
                                        <a href="{{ route('products.show', $movement->product->id ?? '#') }}">
                                            {{ $movement->product->name ?? '-' }}
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <span class="movement-badge {{ in_array(strtolower($movement->reason), ['entrée', 'in', 'achat']) ? 'in' : 'out' }}">
                                            {{ $movement->reason }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <strong>{{ $movement->quantity }}</strong>
                                    </td>
                                    <td>
                                        @if($movement->invoice)
                                            <a href="#">
                                                <strong>{{ $movement->invoice->invoice_number }}</strong>
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $movement->created_at->format('d/m/Y à H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center">
                    {{ $movements->links() }}
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <p>Aucun mouvement de stock enregistré.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
