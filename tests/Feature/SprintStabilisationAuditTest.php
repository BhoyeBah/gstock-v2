<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SprintStabilisationAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_route_redirects_to_dashboard(): void
    {
        [, $user] = $this->createPlatformUser();

        $this->actingAs($user)
            ->get(route('sales.index'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_quotes_route_is_now_a_real_module(): void
    {
        [, $user] = $this->createPlatformUser();

        $this->actingAs($user)
            ->get(route('quotes.index'))
            ->assertOk()
            ->assertSee('Devis / Proforma')
            ->assertDontSee('Bientôt');
    }

    public function test_blocking_controllers_no_longer_contain_debug_helpers(): void
    {
        $returnController = file_get_contents(app_path('Http/Controllers/ReturnProductController.php'));

        $this->assertStringNotContainsString('dd(', $returnController);
    }

    private function createPlatformUser(): array
    {
        $tenant = Tenant::create([
            'name' => 'Platform',
            'slug' => 'platform',
            'email' => 'platform@example.com',
            'phone' => '221770000000',
            'address' => 'Dakar',
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
}
