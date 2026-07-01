@extends('back.layouts.admin')

@push('styles')
    <style>
        .platform-hero {
            background:
                radial-gradient(circle at top left, rgba(59, 130, 246, 0.24), transparent 34%),
                linear-gradient(135deg, #0f172a 0%, #111827 55%, #1f2937 100%);
            color: #fff;
            border: 0;
            border-radius: 1.2rem;
            overflow: hidden;
            box-shadow: 0 18px 42px rgba(15, 23, 42, 0.16);
        }

        .platform-hero__eyebrow {
            letter-spacing: .16em;
            text-transform: uppercase;
            font-size: .72rem;
            color: rgba(255, 255, 255, .72);
        }

        .metric-card {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .08);
            overflow: hidden;
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 36px rgba(15, 23, 42, .12);
        }

        .metric-card .metric-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            flex: 0 0 auto;
        }

        .metric-card .metric-label {
            color: #6b7280;
            font-size: .78rem;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .metric-card .metric-value {
            font-size: 1.7rem;
            font-weight: 800;
            color: #111827;
            line-height: 1.1;
        }

        .metric-card .metric-subtitle {
            color: #6b7280;
            font-size: .88rem;
        }

        .section-card {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .08);
            overflow: hidden;
        }

        .section-card .card-header {
            background: #fff;
            border-bottom: 1px solid rgba(148, 163, 184, .16);
        }

        .soft-badge {
            background: rgba(59, 130, 246, .08);
            color: #1d4ed8;
            border: 1px solid rgba(59, 130, 246, .12);
            border-radius: 999px;
            padding: .35rem .7rem;
            font-size: .78rem;
            font-weight: 700;
        }

        .alert-tile {
            border-radius: .95rem;
            border: 1px solid rgba(148, 163, 184, .16);
            background: #fff;
            padding: .85rem 1rem;
        }

        .plan-row + .plan-row {
            margin-top: .85rem;
        }

        .plan-progress {
            height: .5rem;
            border-radius: 999px;
            background: #e5e7eb;
            overflow: hidden;
        }

        .plan-progress > span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #2563eb, #38bdf8);
        }
    </style>
@endpush

@section('content')
    @php
        $formatMoney = function ($amount) {
            return number_format((int) $amount, 0, ',', ' ') . ' FCFA';
        };

        $tenantCounts = $metrics['tenantCounts'] ?? [];
        $subscriptionCounts = $metrics['subscriptionCounts'] ?? [];
        $planDistribution = collect($metrics['planDistribution'] ?? []);
        $expiringSubscriptions = collect($metrics['expiringSubscriptions'] ?? []);
        $recentTenants = collect($metrics['recentTenants'] ?? []);
        $alerts = $metrics['alerts'] ?? [];
    @endphp

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4">
        <div class="mb-3 mb-md-0">
            <div class="platform-hero__eyebrow mb-2">Super-admin / Admin plateforme</div>
            <h1 class="h3 mb-1 text-gray-900 font-weight-bold">Dashboard Plateforme</h1>
            <p class="mb-0 text-muted">Vue globale de l’activité SaaS, de l’adoption et de la santé des abonnements.</p>
        </div>

        <div class="d-flex flex-wrap align-items-center">
            <span class="soft-badge mr-2 mb-2 mb-md-0">
                <i class="fas fa-calendar-day mr-1"></i>
                {{ now()->isoFormat('DD/MM/YYYY') }}
            </span>
            <span class="soft-badge mb-2 mb-md-0">
                <i class="fas fa-clock mr-1"></i>
                {{ optional($metrics['generatedAt'] ?? now())->format('H:i') }}
            </span>
        </div>
    </div>

    <div class="card platform-hero mb-4">
        <div class="card-body p-4 p-lg-5">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="platform-hero__eyebrow mb-2">Plateforme SaaS</div>
                    <h2 class="h2 font-weight-bold mb-3">Pilote de bord des abonnements, des tenants et du revenu récurrent</h2>
                    <p class="mb-4 text-white-50" style="max-width: 52rem;">
                        Ce tableau de bord consolide uniquement les données plateforme. Il ne mélange pas les métriques
                        d’un tenant avec celles du SaaS global.
                    </p>
                    <div class="d-flex flex-wrap">
                        <a href="{{ route('admin.tenants.index') }}" class="btn btn-light mr-2 mb-2">
                            <i class="fas fa-city mr-1"></i> Tenants
                        </a>
                        <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-light mr-2 mb-2">
                            <i class="fas fa-receipt mr-1"></i> Abonnements
                        </a>
                        <a href="{{ route('admin.plans.index') }}" class="btn btn-outline-light mb-2">
                            <i class="fas fa-gem mr-1"></i> Plans
                        </a>
                    </div>
                    <div class="d-flex flex-wrap mt-2">
                        <a href="{{ route('admin.plan-permissions.index') }}" class="btn btn-sm btn-outline-light mr-2 mb-2">
                            <i class="fas fa-list-check mr-1"></i> Permissions par plan
                        </a>
                        <a href="{{ route('admin.settings.index') }}" class="btn btn-sm btn-outline-light mb-2">
                            <i class="fas fa-cogs mr-1"></i> Paramètres plateforme
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 mt-4 mt-lg-0">
                    <div class="alert alert-info mb-0 border-0 shadow-sm">
                        <div class="d-flex align-items-start">
                            <div class="mr-3">
                                <i class="fas fa-shield-alt fa-2x"></i>
                            </div>
                            <div>
                                <div class="font-weight-bold mb-1">Accès sécurisé</div>
                                <div class="small">
                                    Seuls le super-admin et l’administration plateforme voient ce dashboard et ses métriques SaaS.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="metric-label mb-2">Total tenants</div>
                            <div class="metric-value">{{ number_format((int) ($tenantCounts['total'] ?? 0), 0, ',', ' ') }}</div>
                            <div class="metric-subtitle mt-2">Entreprises actives et inactives hors plateforme.</div>
                        </div>
                        <div class="metric-icon bg-primary">
                            <i class="fas fa-city"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="metric-label mb-2">Tenants actifs</div>
                            <div class="metric-value">{{ number_format((int) ($tenantCounts['active'] ?? 0), 0, ',', ' ') }}</div>
                            <div class="metric-subtitle mt-2">Comptes en paiement actif et opérationnels.</div>
                        </div>
                        <div class="metric-icon bg-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="metric-label mb-2">MRR</div>
                            <div class="metric-value">{{ $formatMoney($metrics['mrr'] ?? 0) }}</div>
                            <div class="metric-subtitle mt-2">Revenu récurrent mensuel estimé.</div>
                        </div>
                        <div class="metric-icon bg-info">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="metric-label mb-2">ARR</div>
                            <div class="metric-value">{{ $formatMoney($metrics['arr'] ?? 0) }}</div>
                            <div class="metric-subtitle mt-2">Projection annuelle basée sur le MRR.</div>
                        </div>
                        <div class="metric-icon bg-dark">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="metric-label mb-2">En essai</div>
                            <div class="metric-value">{{ number_format((int) ($tenantCounts['trial'] ?? 0), 0, ',', ' ') }}</div>
                            <div class="metric-subtitle mt-2">Tenants sur plan gratuit ou trial.</div>
                        </div>
                        <div class="metric-icon bg-warning">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="metric-label mb-2">Expirés</div>
                            <div class="metric-value">{{ number_format((int) ($tenantCounts['expired'] ?? 0), 0, ',', ' ') }}</div>
                            <div class="metric-subtitle mt-2">Abonnements échus à relancer.</div>
                        </div>
                        <div class="metric-icon bg-secondary">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="metric-label mb-2">Suspendus</div>
                            <div class="metric-value">{{ number_format((int) ($tenantCounts['suspended'] ?? 0), 0, ',', ' ') }}</div>
                            <div class="metric-subtitle mt-2">Comptes désactivés côté plateforme.</div>
                        </div>
                        <div class="metric-icon bg-danger">
                            <i class="fas fa-ban"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="metric-label mb-2">Nouveaux ce mois</div>
                            <div class="metric-value">{{ number_format((int) ($metrics['newTenantsThisMonth'] ?? 0), 0, ',', ' ') }}</div>
                            <div class="metric-subtitle mt-2">Nouveaux tenants créés ce mois-ci.</div>
                        </div>
                        <div class="metric-icon bg-primary">
                            <i class="fas fa-user-plus"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6 mb-4">
            <div class="card section-card h-100">
                <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                    <div>
                        <h6 class="m-0 font-weight-bold text-gray-900">Répartition par plan</h6>
                        <small class="text-muted">Tenants actifs et en essai regroupés par plan courant.</small>
                    </div>
                    <a href="{{ route('admin.plans.index') }}" class="btn btn-sm btn-outline-primary mt-3 mt-md-0">
                        Gérer les plans
                    </a>
                </div>
                <div class="card-body">
                    @forelse ($planDistribution as $planRow)
                        <div class="plan-row">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div>
                                    <div class="font-weight-bold text-gray-900">{{ $planRow['plan'] }}</div>
                                    <small class="text-muted">{{ $planRow['count'] }} tenant(s)</small>
                                </div>
                                <div class="text-right">
                                    <div class="font-weight-bold">{{ $formatMoney($planRow['mrr'] ?? 0) }}</div>
                                    <small class="text-muted">{{ $planRow['share'] ?? 0 }} % du portefeuille</small>
                                </div>
                            </div>
                            <div class="plan-progress">
                                <span style="width: {{ min(100, (float) ($planRow['share'] ?? 0)) }}%;"></span>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-light mb-0">Aucune répartition disponible pour le moment.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-6 mb-4">
            <div class="card section-card h-100">
                <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                    <div>
                        <h6 class="m-0 font-weight-bold text-gray-900">Abonnements expirant bientôt</h6>
                        <small class="text-muted">Fenêtre de 14 jours sur les abonnements payants actifs.</small>
                    </div>
                    <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-sm btn-outline-primary mt-3 mt-md-0">
                        Voir tous les abonnements
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Entreprise</th>
                                    <th>Plan</th>
                                    <th>Expiration</th>
                                    <th class="text-right">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($expiringSubscriptions as $subscription)
                                    <tr>
                                        <td>
                                            <div class="font-weight-bold text-gray-900">{{ $subscription['tenant'] }}</div>
                                            <small class="text-muted">MRR: {{ $formatMoney($subscription['amount_paid']) }}</small>
                                        </td>
                                        <td>{{ $subscription['plan'] }}</td>
                                        <td>{{ optional($subscription['ends_at'])->format('d/m/Y') }}</td>
                                        <td class="text-right">
                                            <span class="badge badge-warning">{{ $subscription['status_label'] }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            Aucun abonnement n’expire dans les 14 prochains jours.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-7 mb-4">
            <div class="card section-card h-100">
                <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                    <div>
                        <h6 class="m-0 font-weight-bold text-gray-900">Derniers tenants inscrits</h6>
                        <small class="text-muted">Vue récente des comptes créés sur la plateforme.</small>
                    </div>
                    <a href="{{ route('admin.tenants.index') }}" class="btn btn-sm btn-outline-primary mt-3 mt-md-0">
                        Voir les tenants
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Tenant</th>
                                    <th>Plan</th>
                                    <th>Utilisateurs</th>
                                    <th>Créé le</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentTenants as $tenant)
                                    <tr>
                                        <td>
                                            <div class="font-weight-bold text-gray-900">{{ $tenant['name'] }}</div>
                                            <small class="text-muted">{{ $tenant['slug'] }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary">{{ $tenant['plan'] }}</span>
                                            <span class="badge badge-light ml-1">{{ $tenant['status_label'] }}</span>
                                        </td>
                                        <td>
                                            {{ number_format((int) $tenant['users_count'], 0, ',', ' ') }}
                                            @if ($tenant['max_users'])
                                                <small class="text-muted">/ {{ (int) $tenant['max_users'] }}</small>
                                            @endif
                                        </td>
                                        <td>{{ optional($tenant['created_at'])->format('d/m/Y') }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('admin.tenants.edit', $tenant['id']) }}" class="btn btn-sm btn-outline-secondary" title="Éditer">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Aucun tenant disponible.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5 mb-4">
            <div class="card section-card h-100">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-gray-900">Alertes plateforme</h6>
                    <small class="text-muted">Points de vigilance à traiter en priorité.</small>
                </div>
                <div class="card-body">
                    @php
                        $alertSections = [
                            ['title' => 'Tenants sans abonnement', 'items' => collect($alerts['without_subscription'] ?? []), 'icon' => 'fas fa-exclamation-circle', 'color' => 'warning'],
                            ['title' => 'Abonnements expirés', 'items' => collect($alerts['expired_subscriptions'] ?? []), 'icon' => 'fas fa-calendar-times', 'color' => 'danger'],
                            ['title' => 'Limite utilisateurs proche', 'items' => collect($alerts['near_user_limit'] ?? []), 'icon' => 'fas fa-users', 'color' => 'info'],
                            ['title' => 'Plans sans prix', 'items' => collect($alerts['plans_without_price'] ?? []), 'icon' => 'fas fa-gem', 'color' => 'secondary'],
                            ['title' => 'Tenants suspendus', 'items' => collect($alerts['suspended_tenants'] ?? []), 'icon' => 'fas fa-ban', 'color' => 'dark'],
                        ];
                    @endphp

                    @foreach ($alertSections as $section)
                        <div class="alert-tile mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="font-weight-bold text-gray-900">
                                    <i class="{{ $section['icon'] }} text-{{ $section['color'] }} mr-1"></i>
                                    {{ $section['title'] }}
                                </div>
                                <span class="badge badge-{{ $section['color'] }}">{{ $section['items']->count() }}</span>
                            </div>

                            @if ($section['items']->isNotEmpty())
                                <div class="small text-muted">
                                    {{ $section['items']->take(3)->pluck('name')->implode(', ') }}
                                </div>
                            @else
                                <div class="small text-muted">Aucune alerte à signaler.</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="metric-label mb-2">Revenus du mois</div>
                    <div class="metric-value">{{ $formatMoney($metrics['monthlyRevenue'] ?? 0) }}</div>
                    <div class="metric-subtitle mt-2">Basé sur les abonnements créés ce mois-ci.</div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="metric-label mb-2">Abonnements actifs</div>
                    <div class="metric-value">{{ number_format((int) ($subscriptionCounts['active'] ?? 0), 0, ',', ' ') }}</div>
                    <div class="metric-subtitle mt-2">Abonnements payants actuellement valides.</div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="metric-label mb-2">ARPU</div>
                    <div class="metric-value">{{ $formatMoney($metrics['arpu'] ?? 0) }}</div>
                    <div class="metric-subtitle mt-2">Revenu moyen par tenant payant actif.</div>
                </div>
            </div>
        </div>
    </div>
@endsection
