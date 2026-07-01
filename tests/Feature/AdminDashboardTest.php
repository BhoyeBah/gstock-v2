<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Permission;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Admin\SaasMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_platform_dashboard(): void
    {
        [$tenant, $user] = $this->createPlatformUser();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Plateforme')
            ->assertSee('Vue globale de l’activité SaaS')
            ->assertSee('Administration Plateforme')
            ->assertDontSee('Produits')
            ->assertDontSee('Clients')
            ->assertDontSee('Fournisseurs')
            ->assertDontSee('Taxes / TVA')
            ->assertDontSee('Séquences documents');
    }

    public function test_tenant_admin_cannot_access_platform_dashboard(): void
    {
        $tenant = Tenant::create([
            'name' => 'Tenant A',
            'slug' => 'tenant-a',
            'email' => 'tenant-a@example.com',
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Tenant Admin',
            'email' => 'tenant-admin@example.com',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant->id,
            'is_owner' => true,
            'is_active' => true,
        ]);

        $plan = Plan::create([
            'name' => 'Standard',
            'slug' => 'standard-test',
            'price' => 12000,
            'duration_days' => 30,
            'max_users' => 10,
            'max_storage_mb' => 1000,
            'is_active' => true,
        ]);

        Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'amount_paid' => 12000,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_saas_metrics_are_calculated_correctly(): void
    {
        [$activeTenant, ] = $this->createTenantWithSubscription('active', 'standard', 12000, 30, true, now()->addMonth(), 4);
        [$suspendedTenant, ] = $this->createTenantWithSubscription('suspended', 'standard2', 15000, 30, false, now()->addMonth(), 2, true);
        [$trialTenant, ] = $this->createTenantWithSubscription('trial', 'gratuit', 0, 30, true, now()->addWeek(), 1);
        [$expiredTenant, ] = $this->createTenantWithSubscription('expired', 'expired-plan', 18000, 30, true, now()->subDay(), 5);
        [$annualTenant, ] = $this->createTenantWithSubscription('annual', 'annual', 120000, 365, true, now()->addYear(), 3);

        $service = app(SaasMetricsService::class);
        $counts = $service->getTenantCounts();

        $this->assertSame(5, $counts['total']);
        $this->assertSame(2, $counts['active']);
        $this->assertSame(1, $counts['suspended']);
        $this->assertSame(1, $counts['trial']);
        $this->assertSame(1, $counts['expired']);

        $this->assertSame(22000, $service->calculateMRR());
        $this->assertSame(264000, $service->calculateARR());
        $this->assertSame(11000, $service->calculateARPU());
    }

    public function test_expiring_subscriptions_are_listed(): void
    {
        $plan = $this->createPlan('pro', 10000, 30);
        $tenant = Tenant::create([
            'name' => 'Soon expiring',
            'slug' => 'soon-expiring',
            'is_active' => true,
        ]);

        Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'amount_paid' => 10000,
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->addDays(7),
            'is_active' => true,
        ]);

        $service = app(SaasMetricsService::class);
        $this->assertCount(1, $service->getExpiringSubscriptions());
    }

    public function test_plan_distribution_is_grouped_by_current_plan(): void
    {
        $this->createTenantWithSubscription('dist-a', 'standard-a', 10000, 30, true, now()->addMonth(), 2);
        $this->createTenantWithSubscription('dist-b', 'standard-a', 10000, 30, true, now()->addMonth(), 1);
        $this->createTenantWithSubscription('dist-c', 'gratuit', 0, 30, true, now()->addWeek(), 1);

        $distribution = app(SaasMetricsService::class)->getPlanDistribution();

        $this->assertCount(2, $distribution);
        $this->assertSame('Standard A', $distribution->firstWhere('plan', 'Standard A')['plan']);
        $this->assertSame(2, $distribution->firstWhere('plan', 'Standard A')['count']);
    }

    public function test_super_admin_can_access_plan_permissions_and_platform_settings(): void
    {
        [, $user] = $this->createPlatformUser();
        $plan = $this->createPlan('pro', 15000, 30);
        $permission = Permission::create([
            'name' => 'read_products',
            'guard_name' => 'web',
            'description' => 'Voir les produits',
        ]);

        $this->actingAs($user)
            ->get(route('admin.plan-permissions.index'))
            ->assertOk()
            ->assertSee('Permissions par plan');

        $this->actingAs($user)
            ->put(route('admin.plan-permissions.update', $plan), [
                'permissions' => [$permission->id],
            ])
            ->assertRedirect(route('admin.plan-permissions.index'));

        $this->assertTrue($plan->fresh()->permissions->contains('id', $permission->id));

        $this->actingAs($user)
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('Paramètres plateforme');

        $this->actingAs($user)
            ->post(route('admin.settings.store'), [
                'currency' => 'FCFA',
                'tva' => 18,
            ])
            ->assertRedirect(route('admin.settings.index'));

        $platformTenant = Tenant::where('slug', 'platform')->firstOrFail();
        $this->assertDatabaseHas('settings', [
            'tenant_id' => $platformTenant->id,
            'currency' => 'FCFA',
        ]);
    }

    public function test_tenant_admin_cannot_access_platform_settings_pages(): void
    {
        $tenant = Tenant::create([
            'name' => 'Tenant B',
            'slug' => 'tenant-b',
            'email' => 'tenant-b@example.com',
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Tenant Admin B',
            'email' => 'tenant-admin-b@example.com',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant->id,
            'is_owner' => true,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('admin.plan-permissions.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.settings.index'))
            ->assertForbidden();
    }

    private function createPlatformUser(): array
    {
        $tenant = Tenant::create([
            'name' => 'Platform',
            'slug' => 'platform',
            'email' => 'platform@example.com',
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant->id,
            'is_owner' => true,
            'is_active' => true,
        ]);

        return [$tenant, $user];
    }

    private function createTenantWithSubscription(
        string $tenantSlug,
        string $planSlug,
        int $planPrice,
        int $durationDays,
        bool $tenantActive,
        $endsAt,
        int $usersCount = 0,
        bool $subscriptionActive = true,
    ): array {
        $tenant = Tenant::create([
            'name' => ucfirst($tenantSlug),
            'slug' => $tenantSlug,
            'email' => $tenantSlug . '@example.com',
            'is_active' => $tenantActive,
        ]);

        for ($i = 0; $i < $usersCount; $i++) {
            User::create([
                'name' => $tenantSlug . ' user ' . $i,
                'email' => $tenantSlug . '.user.' . $i . '@example.com',
                'password' => Hash::make('password'),
                'tenant_id' => $tenant->id,
                'is_owner' => $i === 0,
                'is_active' => true,
            ]);
        }

        $plan = $this->createPlan($planSlug, $planPrice, $durationDays);

        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'amount_paid' => $planPrice,
            'starts_at' => now()->subDays(5),
            'ends_at' => $endsAt,
            'is_active' => $subscriptionActive,
        ]);

        return [$tenant, $subscription];
    }

    private function createPlan(string $slug, int $price, int $durationDays): Plan
    {
        return Plan::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => ucwords(str_replace('-', ' ', $slug)),
                'slug' => $slug,
                'price' => $price,
                'duration_days' => $durationDays,
                'max_users' => 10,
                'max_storage_mb' => 1000,
                'is_active' => true,
            ]
        );
    }
}
