@extends('back.layouts.admin')

@section('content')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
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

        /* Filter Card */
        .filter-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
            border: none;
            margin-bottom: 2rem;
        }

        .filter-card label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-card .form-control {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            /* padding: 0.75rem 1rem; */
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .filter-card .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .btn-generate {
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-generate:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 1.75rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            border: none;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--card-gradient);
        }

        .stat-card.primary::before { background: var(--primary-gradient); }
        .stat-card.success::before { background: var(--success-gradient); }
        .stat-card.warning::before { background: var(--warning-gradient); }
        .stat-card.danger::before { background: var(--danger-gradient); }
        .stat-card.info::before { background: var(--info-gradient); }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            background: var(--card-gradient);
            color: white;
        }

        .stat-card.primary .stat-icon { background: var(--primary-gradient); }
        .stat-card.success .stat-icon { background: var(--success-gradient); }
        .stat-card.warning .stat-icon { background: var(--warning-gradient); }
        .stat-card.danger .stat-icon { background: var(--danger-gradient); }
        .stat-card.info .stat-icon { background: var(--info-gradient); }

        .stat-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
        }

        /* Table Card */
        .table-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
            border: none;
            margin-bottom: 2rem;
        }

        .table-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f3f4f6;
        }

        .table-card-title {
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

        .modern-table tbody td a {
            color: #667eea;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .modern-table tbody td a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .badge-modern {
            padding: 0.4rem 0.9rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-modern.success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }

        .badge-modern.warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
        }

        .badge-modern.danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
        }

        .badge-modern.secondary {
            background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
            color: #374151;
        }

        /* Chart Card */
        .chart-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
            border: none;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .chart-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .chart-container {
            position: relative;
            height: 350px;
        }

        /* Pagination */
        .pagination {
            gap: 0.5rem;
        }

        .page-item .page-link {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            color: #667eea;
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
            border-color: #667eea;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .page-header-modern h1 {
                font-size: 1.5rem;
            }

            .table-responsive {
                border-radius: 12px;
            }
        }
    </style>

    <!-- Page Header -->
    <div class="page-header-modern">
        <h1>
            <i class="fas fa-chart-line"></i>
            Rapports des Factures
        </h1>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('reports.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label>📅 Date de début</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>

                <div class="col-md-3">
                    <label>📅 Date de fin</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>

                <div class="col-md-3">
                    <label>🏷️ Type</label>
                    <select class="form-control" name="type">
                        <option value="">Tous les types</option>
                        <option value="client" {{ request('type') == 'client' ? 'selected' : '' }}>Client</option>
                        <option value="supplier" {{ request('type') == 'supplier' ? 'selected' : '' }}>Fournisseur</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-generate w-100">
                        <i class="fas fa-search"></i> Générer le rapport
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Statistics Grid -->
    <div class="stats-grid">
        @php
            $statsConfig = [
                ['title' => 'Total Factures', 'value' => $stats->total_factures, 'icon' => 'fa-file-invoice', 'color' => 'primary'],
                ['title' => 'Total Payé', 'value' => $stats->total_paye, 'icon' => 'fa-check-circle', 'color' => 'success'],
                ['title' => 'En Attente', 'value' => $stats->total_attente, 'icon' => 'fa-clock', 'color' => 'warning'],
                ['title' => 'Annulées', 'value' => $stats->total_annule, 'icon' => 'fa-times-circle', 'color' => 'danger'],
                ['title' => 'Bénéfices', 'value' => $stats->benefice, 'icon' => 'fa-coins', 'color' => 'success'],
                ['title' => 'Dépenses', 'value' => $stats->depenses, 'icon' => 'fa-money-bill-wave', 'color' => 'info'],
            ];
        @endphp

        @foreach ($statsConfig as $stat)
            <div class="stat-card {{ $stat['color'] }}">
                <div class="stat-icon">
                    <i class="fas {{ $stat['icon'] }}"></i>
                </div>
                <div class="stat-label">{{ $stat['title'] }}</div>
                <div class="stat-value">{{ number_format($stat['value'] ?? 0, 0, ',', ' ') }} FCFA</div>
            </div>
        @endforeach
    </div>

    <!-- Invoices Table -->
    <div class="table-card">
        <div class="table-card-header">
            <h6 class="table-card-title">
                <i class="fas fa-list"></i>
                Détail des factures
            </h6>
        </div>

        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>N° Facture</th>
                        <th>Client / Fournisseur</th>
                        <th>Date</th>
                        <th>Balance</th>
                        <th>Total</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoicesList as $invoice)
                        @php
                            $contactRoute = $invoice->type === 'client' ? 'clients.show' : 'suppliers.show';
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('invoices.show', [$invoice->type . 's', $invoice->id]) }}">
                                    <strong>{{ $invoice->invoice_number }}</strong>
                                </a>
                            </td>
                            <td>
                                <a href="{{ route($contactRoute, $invoice->contact->id) }}">
                                    {{ $invoice->contact->fullname ?? '-' }}
                                </a>
                            </td>
                            <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                            <td><strong>{{ number_format($invoice->balance, 0, ',', ' ') }} FCFA</strong></td>
                            <td>{{ number_format($invoice->total_invoice, 0, ',', ' ') }} FCFA</td>
                            <td>
                                @php
                                    $statusLabel = match ($invoice->status) {
                                        'paid' => 'Payé',
                                        'partial' => 'Partiel',
                                        'cancelled' => 'Annulée',
                                        default => ucfirst($invoice->status),
                                    };
                                    $statusColor = match ($invoice->status) {
                                        'paid' => 'success',
                                        'partial' => 'warning',
                                        'cancelled' => 'danger',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge-modern {{ $statusColor }}">{{ $statusLabel }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                Aucune facture trouvée
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $invoicesList->links() }}
            </div>
        </div>
    </div>

    <!-- Sales Chart -->
    <div class="chart-card">
        <div class="chart-header">
            <h6 class="chart-title">
                <i class="fas fa-chart-area"></i>
                Évolution des ventes
            </h6>
        </div>
        <div class="chart-container">
            <canvas id="salesChart"></canvas>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const labels = @json($chartData->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M')));
        const values = @json($chartData->pluck('total'));

        const ctx = document.getElementById('salesChart');
        const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 350);
        gradient.addColorStop(0, 'rgba(102, 126, 234, 0.3)');
        gradient.addColorStop(1, 'rgba(102, 126, 234, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: "Ventes (FCFA)",
                    data: values,
                    fill: true,
                    backgroundColor: gradient,
                    borderColor: '#667eea',
                    borderWidth: 3,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 13 },
                        borderColor: '#667eea',
                        borderWidth: 1
                    },
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: { size: 12, weight: '600' },
                            padding: 15,
                            usePointStyle: true
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
                            },
                            font: { size: 11 }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: { size: 11 }
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    </script>
@endpush
