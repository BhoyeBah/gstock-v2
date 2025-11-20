@extends('back.layouts.admin')

@section('content')
    @push('styles')
        <style>
            body {
                background: #f8f9fc;
                font-family: 'Inter', 'Segoe UI', sans-serif;
            }

            .page-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 40px 0;
                margin-bottom: 30px;
                box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
                border-radius: 0 0 20px 20px;
            }

            .page-header h1 {
                color: white;
                font-weight: 600;
                font-size: 2.2rem;
                margin-bottom: 0;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .page-header h1 i {
                background: rgba(255, 255, 255, 0.2);
                padding: 12px;
                border-radius: 12px;
                margin-right: 15px;
            }

            .header-controls {
                display: flex;
                align-items: center;
                gap: 15px;
            }

            .header-controls select {
                min-width: 250px;
                border: 2px solid rgba(255, 255, 255, 0.3);
                background: rgba(255, 255, 255, 0.95);
                color: #2c3e50;
                border-radius: 12px;
                /* padding: 12px 16px; */
                font-weight: 500;
                transition: all 0.3s ease;
            }

            .header-controls select:focus {
                border-color: white;
                background: white;
                box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.2);
            }

            .card {
                border: none;
                border-radius: 16px;
                box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
                margin-bottom: 30px;
                overflow: hidden;
                transition: all 0.3s ease;
            }

            .card:hover {
                box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
                transform: translateY(-2px);
            }

            .card-header {
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                border-bottom: 3px solid #667eea;
                font-weight: 600;
                color: #2c3e50;
                padding: 20px 24px;
                font-size: 1.1rem;
            }

            .card-header i {
                color: #667eea;
                margin-right: 8px;
            }

            .btn-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border: none;
                border-radius: 12px;
                padding: 12px 28px;
                font-weight: 600;
                transition: all 0.3s ease;
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            }

            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            }

            .btn-success {
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                border: none;
                border-radius: 10px;
                padding: 8px 16px;
                font-weight: 500;
                transition: all 0.2s ease;
            }

            .btn-success:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            }

            .btn-info {
                background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                border: none;
                border-radius: 10px;
                padding: 8px 16px;
                font-weight: 500;
                transition: all 0.2s ease;
            }

            .btn-info:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            }

            .badge {
                font-weight: 500;
                padding: 8px 16px;
                border-radius: 8px;
                font-size: 0.85rem;
                letter-spacing: 0.3px;
            }

            .badge-info {
                background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
                color: #1e40af;
            }

            .badge-success {
                background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
                color: #065f46;
            }

            .badge-danger {
                background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
                color: #991b1b;
            }

            .table {
                margin-bottom: 0;
            }

            .table thead {
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            }

            .table thead th {
                border: none;
                color: #495057;
                font-weight: 600;
                text-transform: uppercase;
                font-size: 0.75rem;
                letter-spacing: 0.5px;
                padding: 16px 20px;
            }

            .table tbody tr {
                transition: all 0.2s ease;
                border-bottom: 1px solid #f1f3f5;
            }

            .table tbody tr:hover {
                background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
                transform: scale(1.01);
                box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
            }

            .table tbody td {
                padding: 18px 20px;
                vertical-align: middle;
                color: #2c3e50;
            }

            .inventory-stats {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }

            .stat-card {
                background: white;
                padding: 24px;
                border-radius: 16px;
                border: 2px solid #f1f3f5;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
                text-align: center;
                transition: all 0.3s ease;
            }

            .stat-card:hover {
                border-color: #667eea;
                transform: translateY(-4px);
                box-shadow: 0 8px 20px rgba(102, 126, 234, 0.15);
            }

            .stat-card .stat-icon {
                width: 60px;
                height: 60px;
                margin: 0 auto 16px;
                border-radius: 16px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.8rem;
            }

            .stat-card .stat-value {
                font-size: 2rem;
                font-weight: 700;
                color: #2c3e50;
                margin-bottom: 8px;
            }

            .stat-card .stat-label {
                color: #6c757d;
                font-size: 0.9rem;
                font-weight: 500;
            }

            .stat-card.stat-total .stat-icon {
                background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
                color: #1e40af;
            }

            .stat-card.stat-validated .stat-icon {
                background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
                color: #065f46;
            }

            .stat-card.stat-pending .stat-icon {
                background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
                color: #92400e;
            }

            .progress {
                height: 10px;
                border-radius: 6px;
                background-color: #f1f3f5;
                margin-top: 12px;
                overflow: hidden;
            }

            .progress-bar {
                background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
                transition: width 0.6s ease;
            }

            .select-wrapper {
                position: relative;
            }

            .select-wrapper::after {
                content: '\f078';
                font-family: 'Font Awesome 5 Free';
                font-weight: 900;
                position: absolute;
                right: 16px;
                top: 50%;
                transform: translateY(-50%);
                pointer-events: none;
                color: #667eea;
            }


            .table-actions {
                display: flex;
                gap: 8px;
                justify-content: center;
            }

            /* Animation pour les nouvelles lignes */
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateX(-20px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }

            .table tbody tr {
                animation: slideIn 0.3s ease-out;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .page-header h1 {
                    font-size: 1.5rem;
                }

                .header-controls {
                    flex-direction: column;
                    width: 100%;
                }

                .header-controls select {
                    width: 100%;
                }

                .stat-card {
                    margin-bottom: 15px;
                }
            }
        </style>
    @endpush


    <!-- PAGE HEADER -->
    <div class="page-header">
        <div class="container-fluid">
            <div class="row align-items-center">

                <!-- TITRE -->
                <div class="col-md-6 mb-3 mb-md-0">
                    <h1 class="mb-0">
                        <i class="fas fa-clipboard-list"></i> Gestion des Inventaires
                    </h1>
                </div>

                <!-- FORMULAIRE: SELECT + BOUTON -->
                <div class="col-md-6">
                    <form action="{{ route('inventories.store') }}" method="POST" class="header-controls justify-content-md-end">
                        @csrf

                        <!-- SELECT ENTREPÔT -->
                        <div class="select-wrapper flex-grow-1 flex-md-grow-0">
                            <select class="form-control" name="warehouse_id" required>
                                <option value="">-- Choisir un entrepôt --</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- BOUTON -->
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Générer
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- STATISTIQUES (Optionnel) -->
    <div class="container-fluid mb-4">
        <div class="inventory-stats">
            <div class="stat-card stat-total">
                <div class="stat-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stat-value">{{ $inventories->count() }}</div>
                <div class="stat-label">Total Inventaires</div>
            </div>

            <div class="stat-card stat-validated">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value">{{ $inventories->where('status', 'completed')->count() }}</div>
                <div class="stat-label">Clôturés</div>
            </div>

            <div class="stat-card stat-pending">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value">{{ $inventories->where('status', '!=', 'completed')->count() }}</div>
                <div class="stat-label">En cours</div>
            </div>
        </div>
    </div>

    <!-- PAGE LISTE INVENTAIRES -->
    <div class="container-fluid" id="listPage">

        <div class="card">
            <div class="card-header">
                <i class="fas fa-list"></i> Historique des Inventaires
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">

                    <table class="table table-hover mb-0" id="inventoryTable">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag me-2"></i>N° Inventaire</th>
                                <th><i class="fas fa-warehouse me-2"></i>Entrepôt</th>
                                <th><i class="fas fa-calendar me-2"></i>Date</th>
                                <th class="text-center"><i class="fas fa-box me-2"></i>Total Produits</th>
                                <th class="text-center"><i class="fas fa-check me-2"></i>Validés</th>
                                <th class="text-center"><i class="fas fa-exclamation-triangle me-2"></i>Écarts</th>
                                <th class="text-center"><i class="fas fa-flag me-2"></i>Statut</th>
                                <th class="text-center"><i class="fas fa-cog me-2"></i>Actions</th>
                            </tr>
                        </thead>

                        <tbody id="inventoryTableBody">
                            @foreach ($inventories as $inventory)
                                <tr>
                                    <td>
                                        <strong style="color: #667eea; font-size: 1.05rem;">
                                            #{{ $inventory->inventory_number }}
                                        </strong>
                                    </td>
                                    <td>
                                        <i class="fas fa-building text-muted me-2"></i>
                                        {{ $inventory->warehouse->name }}
                                    </td>
                                    <td>
                                        <i class="far fa-clock text-muted me-2"></i>
                                        {{ $inventory->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info">
                                            {{ $inventory->total_products }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <strong style="color: #10b981;">
                                            {{ $inventory->total_products - $inventory->ecart_sum }}
                                        </strong>
                                        <span class="text-muted">/{{ $inventory->total_products }}</span>
                                        @if($inventory->total_products > 0)
                                            <div class="progress mt-2" style="height: 6px;">
                                                <div class="progress-bar"
                                                     style="width: {{ (($inventory->total_products - $inventory->ecart_sum) / $inventory->total_products) * 100 }}%">
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($inventory->ecart_sum > 0)
                                            <span class="badge badge-danger">
                                                <i class="fas fa-exclamation-circle me-1"></i>
                                                {{ $inventory->ecart_sum }}
                                            </span>
                                        @else
                                            <span class="text-success">
                                                <i class="fas fa-check-circle"></i> 0
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $inventory->status == 'completed' ? 'badge-success' : 'badge-info' }}">
                                            <i class="fas {{ $inventory->status == 'completed' ? 'fa-check-double' : 'fa-hourglass-half' }} me-1"></i>
                                            {{ $inventory->status == 'completed' ? 'Clôturé' : 'En cours' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="{{ route('inventories.show', $inventory->id) }}"
                                               class="btn btn-sm btn-success"
                                               title="Voir les détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route("inventories.print", $inventory->id) }}" class="btn btn-sm btn-info" title="Imprimer">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @if($loop->index == 0)
                                    <style>
                                        .table tbody tr:nth-child(1) {
                                            animation-delay: 0.1s;
                                        }
                                    </style>
                                @endif
                            @endforeach
                        </tbody>
                    </table>

                </div>
            </div>

        </div>
    </div>

@endsection
