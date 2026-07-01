<?php

namespace App\Services\Admin;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SaasMetricsService
{
    private ?Collection $tenantsCache = null;

    private ?Collection $summariesCache = null;

    private ?Collection $subscriptionsCache = null;

    private ?Collection $plansCache = null;

    public function getDashboardMetrics(): array
    {
        $tenantCounts = $this->getTenantCounts();
        $mrr = $this->calculateMRR();

        return [
            'generatedAt' => now(),
            'tenantCounts' => $tenantCounts,
            'mrr' => $mrr,
            'arr' => $this->calculateARR($mrr),
            'arpu' => $this->calculateARPU($mrr, max((int) $tenantCounts['active_paid'], 0)),
            'monthlyRevenue' => $this->getMonthlyRevenue(),
            'subscriptionCounts' => [
                'active' => $this->countActiveSubscriptions(),
                'expiringSoon' => $this->getExpiringSubscriptions()->count(),
            ],
            'planDistribution' => $this->getPlanDistribution(),
            'expiringSubscriptions' => $this->getExpiringSubscriptions(),
            'recentTenants' => $this->getRecentTenants(),
            'newTenantsThisMonth' => $this->getNewTenantsThisMonth(),
            'alerts' => $this->getAlerts(),
        ];
    }

    public function calculateMRR(): int
    {
        return (int) round(
            $this->activePaidSubscriptions()
                ->sum(fn (Subscription $subscription) => $this->subscriptionMonthlyValue($subscription))
        );
    }

    public function calculateARR(?int $mrr = null): int
    {
        $mrr ??= $this->calculateMRR();

        return $mrr * 12;
    }

    public function calculateARPU(?int $mrr = null, ?int $payingTenants = null): int
    {
        $mrr ??= $this->calculateMRR();
        $payingTenants ??= max((int) $this->getTenantCounts()['active_paid'], 0);

        if ($payingTenants < 1) {
            return 0;
        }

        return (int) round($mrr / $payingTenants);
    }

    public function getTenantCounts(): array
    {
        $summaries = $this->tenantSummaries();

        return [
            'total' => $summaries->count(),
            'active' => $summaries->where('status', 'active_paid')->count(),
            'suspended' => $summaries->where('status', 'suspended')->count(),
            'trial' => $summaries->where('status', 'trial')->count(),
            'expired' => $summaries->where('status', 'expired')->count(),
            'without_subscription' => $summaries->where('status', 'without_subscription')->count(),
            'active_paid' => $summaries->where('status', 'active_paid')->count(),
        ];
    }

    public function getPlanDistribution(): Collection
    {
        return $this->tenantSummaries()
            ->whereIn('status', ['active_paid', 'trial'])
            ->groupBy('plan_label')
            ->map(function (Collection $items, string $planLabel) {
                $mrr = (int) round($items->sum('mrr'));

                return [
                    'plan' => $planLabel,
                    'count' => $items->count(),
                    'mrr' => $mrr,
                    'share' => $this->tenantSummaries()->whereIn('status', ['active_paid', 'trial'])->count() > 0
                        ? round(($items->count() / $this->tenantSummaries()->whereIn('status', ['active_paid', 'trial'])->count()) * 100, 1)
                        : 0,
                ];
            })
            ->sortByDesc('count')
            ->values();
    }

    public function getExpiringSubscriptions(int $days = 14): Collection
    {
        return $this->activePaidSubscriptions()
            ->filter(function (Subscription $subscription) use ($days) {
                $endsAt = $subscription->ends_at;

                return $endsAt instanceof Carbon
                    && $endsAt->isFuture()
                    && $endsAt->lte(now()->copy()->addDays($days));
            })
            ->sortBy('ends_at')
            ->values()
            ->map(fn (Subscription $subscription) => $this->subscriptionPayload($subscription));
    }

    public function getNewTenantsThisMonth(): int
    {
        return $this->tenants()
            ->filter(fn (Tenant $tenant) => $tenant->created_at instanceof Carbon
                && $tenant->created_at->between(now()->startOfMonth(), now()->endOfMonth()))
            ->count();
    }

    public function getRecentTenants(int $limit = 6): Collection
    {
        return $this->tenantSummaries()
            ->sortByDesc(fn (array $item) => $item['created_at']?->timestamp ?? 0)
            ->take($limit)
            ->values()
            ->map(function (array $item) {
                return [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                    'status' => $item['status'],
                    'status_label' => $this->statusLabel($item['status']),
                    'plan' => $item['plan_label'] ?? 'Sans abonnement',
                    'users_count' => $item['users_count'],
                    'max_users' => $item['max_users'],
                    'created_at' => $item['created_at'],
                ];
            });
    }

    public function getMonthlyRevenue(): int
    {
        return (int) round(
            $this->activePaidSubscriptions()
                ->filter(function (Subscription $subscription) {
                    $createdAt = $subscription->created_at;

                    return $createdAt instanceof Carbon
                        && $createdAt->between(now()->startOfMonth(), now()->endOfMonth());
                })
                ->sum(fn (Subscription $subscription) => (int) $subscription->amount_paid)
        );
    }

    public function getAlerts(): array
    {
        return [
            'without_subscription' => $this->tenantSummaries()
                ->where('status', 'without_subscription')
                ->values()
                ->take(5)
                ->map(fn (array $item) => $this->alertTenantPayload($item))
                ->all(),
            'expired_subscriptions' => $this->subscriptions()
                ->filter(fn (Subscription $subscription) => $subscription->ends_at instanceof Carbon && $subscription->ends_at->isPast())
                ->sortByDesc('ends_at')
                ->take(5)
                ->map(fn (Subscription $subscription) => $this->subscriptionPayload($subscription))
                ->values()
                ->all(),
            'near_user_limit' => $this->tenantSummaries()
                ->filter(fn (array $item) => $item['status'] === 'active_paid' && $item['max_users'] && $item['users_count'] !== null)
                ->filter(function (array $item) {
                    $maxUsers = (int) $item['max_users'];

                    if ($maxUsers < 1) {
                        return false;
                    }

                    return $item['users_count'] >= (int) ceil($maxUsers * 0.8);
                })
                ->sortByDesc(fn (array $item) => $item['users_count'] / max((int) $item['max_users'], 1))
                ->take(5)
                ->values()
                ->map(function (array $item) {
                    return [
                        'id' => $item['id'],
                        'name' => $item['name'],
                        'slug' => $item['slug'],
                        'users_count' => $item['users_count'],
                        'max_users' => $item['max_users'],
                        'usage_percent' => $item['max_users'] ? round(($item['users_count'] / $item['max_users']) * 100, 1) : null,
                    ];
                })
                ->all(),
            'plans_without_price' => $this->plans()
                ->filter(fn (Plan $plan) => (int) $plan->price <= 0 && ! in_array($plan->slug, ['gratuit', 'admin'], true))
                ->values()
                ->map(fn (Plan $plan) => [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                ])
                ->all(),
            'suspended_tenants' => $this->tenantSummaries()
                ->where('status', 'suspended')
                ->values()
                ->take(5)
                ->map(fn (array $item) => $this->alertTenantPayload($item))
                ->all(),
        ];
    }

    public function countActiveSubscriptions(): int
    {
        return $this->activePaidSubscriptions()->count();
    }

    private function tenants(): Collection
    {
        if ($this->tenantsCache instanceof Collection) {
            return $this->tenantsCache;
        }

        $this->tenantsCache = Tenant::query()
            ->where('slug', '!=', 'platform')
            ->with(['subscriptions.plan'])
            ->withCount('users')
            ->orderByDesc('created_at')
            ->get();

        return $this->tenantsCache;
    }

    private function subscriptions(): Collection
    {
        if ($this->subscriptionsCache instanceof Collection) {
            return $this->subscriptionsCache;
        }

        $this->subscriptionsCache = Subscription::query()
            ->with(['tenant', 'plan'])
            ->whereHas('tenant', fn ($query) => $query->where('slug', '!=', 'platform'))
            ->orderByDesc('created_at')
            ->get();

        return $this->subscriptionsCache;
    }

    private function plans(): Collection
    {
        if ($this->plansCache instanceof Collection) {
            return $this->plansCache;
        }

        $this->plansCache = Plan::query()
            ->orderBy('price')
            ->get();

        return $this->plansCache;
    }

    private function tenantSummaries(): Collection
    {
        if ($this->summariesCache instanceof Collection) {
            return $this->summariesCache;
        }

        $this->summariesCache = $this->tenants()->map(function (Tenant $tenant) {
            $subscriptions = $tenant->subscriptions->sortByDesc(function (Subscription $subscription) {
                return $subscription->starts_at?->timestamp
                    ?? $subscription->created_at?->timestamp
                    ?? 0;
            })->values();

            $latest = $subscriptions->first();
            $activeSubscriptions = $subscriptions->filter(fn (Subscription $subscription) => $this->isActiveSubscription($subscription));
            $latestActive = $activeSubscriptions->first();
            $latestPlan = $latestActive?->plan ?? $latest?->plan;
            $status = $this->tenantStatus($tenant, $latest, $latestActive);

            return [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'is_active' => (bool) $tenant->is_active,
                'created_at' => $tenant->created_at,
                'users_count' => (int) ($tenant->users_count ?? 0),
                'max_users' => $latestPlan?->max_users,
                'status' => $status,
                'plan_label' => $latestPlan?->name,
                'plan_slug' => $latestPlan?->slug,
                'plan_price' => $latestPlan?->price,
                'latest_subscription' => $latestActive ?? $latest,
                'mrr' => $latestActive ? $this->subscriptionMonthlyValue($latestActive) : 0,
            ];
        });

        return $this->summariesCache;
    }

    private function activePaidSubscriptions(): Collection
    {
        return $this->subscriptions()->filter(function (Subscription $subscription) {
            return $this->isActiveSubscription($subscription)
                && (bool) $subscription->tenant?->is_active
                && ! $this->isFreePlan($subscription->plan);
        });
    }

    private function isActiveSubscription(Subscription $subscription): bool
    {
        if (! $subscription->is_active) {
            return false;
        }

        $startsAt = $subscription->starts_at;
        $endsAt = $subscription->ends_at;

        if ($startsAt instanceof Carbon && $startsAt->isFuture()) {
            return false;
        }

        if ($endsAt instanceof Carbon && $endsAt->isPast()) {
            return false;
        }

        return (bool) $subscription->plan;
    }

    private function subscriptionMonthlyValue(Subscription $subscription): int
    {
        if ($this->isFreePlan($subscription->plan)) {
            return 0;
        }

        $plan = $subscription->plan;
        $price = (int) ($plan?->price ?? 0);
        $durationDays = (int) ($plan?->duration_days ?? 30);

        if ($durationDays >= 365) {
            return (int) round($price / 12);
        }

        return $price;
    }

    private function isFreePlan(?Plan $plan): bool
    {
        if (! $plan) {
            return true;
        }

        return (int) $plan->price <= 0;
    }

    private function tenantStatus(Tenant $tenant, ?Subscription $latest, ?Subscription $latestActive): string
    {
        if (! $tenant->is_active) {
            return 'suspended';
        }

        if (! $latest) {
            return 'without_subscription';
        }

        if ($latestActive && $this->isFreePlan($latestActive->plan)) {
            return 'trial';
        }

        if ($latestActive && ! $this->isFreePlan($latestActive->plan)) {
            return 'active_paid';
        }

        if ($latest->ends_at instanceof Carbon && $latest->ends_at->isPast()) {
            return 'expired';
        }

        return 'without_subscription';
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'active_paid' => 'Actif',
            'trial' => 'Essai',
            'expired' => 'Expiré',
            'suspended' => 'Suspendu',
            default => 'Sans abonnement',
        };
    }

    private function subscriptionPayload(Subscription $subscription): array
    {
        return [
            'id' => $subscription->id,
            'tenant' => $subscription->tenant?->name ?? 'Tenant inconnu',
            'plan' => $subscription->plan?->name ?? 'Plan inconnu',
            'amount_paid' => (int) $subscription->amount_paid,
            'ends_at' => $subscription->ends_at,
            'status_label' => $this->subscriptionStatusLabel($subscription),
        ];
    }

    private function alertTenantPayload(array $item): array
    {
        return [
            'id' => $item['id'],
            'name' => $item['name'],
            'slug' => $item['slug'],
            'status_label' => $this->statusLabel($item['status']),
        ];
    }

    private function subscriptionStatusLabel(Subscription $subscription): string
    {
        if ($subscription->ends_at instanceof Carbon && $subscription->ends_at->isPast()) {
            return 'Expiré';
        }

        return $this->isFreePlan($subscription->plan) ? 'Essai' : 'Actif';
    }
}
