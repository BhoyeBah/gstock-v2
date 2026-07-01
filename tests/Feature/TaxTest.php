<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSubscriptionAndPermissions;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Setting;
use App\Models\Tax;
use App\Models\Tenant;
use App\Models\Units;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TaxTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────
    // Création tenant-safe
    // ──────────────────────────────────────────────

    public function test_tenant_can_create_tax(): void
    {
        [$tenant, $user] = $this->tenantUser();

        $this->actingAs($user)
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('taxes.store'), [
                'name' => 'TVA 20%',
                'rate' => 20,
                'is_active' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('taxes', [
            'tenant_id' => $tenant->id,
            'name' => 'TVA 20%',
            'rate' => 20,
            'is_active' => true,
        ]);
    }

    public function test_tax_name_must_be_unique_within_tenant(): void
    {
        [$tenant, $user] = $this->tenantUser();

        Tax::create(['tenant_id' => $tenant->id, 'name' => 'TVA 10%', 'rate' => 10, 'is_active' => true]);

        $this->actingAs($user)
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('taxes.store'), ['name' => 'TVA 10%', 'rate' => 10])
            ->assertSessionHasErrors('name');
    }

    public function test_tax_name_can_be_reused_across_tenants(): void
    {
        [$tenantA, $userA] = $this->tenantUser('tenant-a');
        [$tenantB, $userB] = $this->tenantUser('tenant-b');

        Tax::create(['tenant_id' => $tenantA->id, 'name' => 'TVA 20%', 'rate' => 20, 'is_active' => true]);

        $this->actingAs($userB)
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('taxes.store'), ['name' => 'TVA 20%', 'rate' => 20])
            ->assertRedirect();

        $this->assertDatabaseCount('taxes', 2);
    }

    // ──────────────────────────────────────────────
    // Isolation cross-tenant sur update/delete
    // ──────────────────────────────────────────────

    public function test_tenant_cannot_update_foreign_tax(): void
    {
        [$tenantA, $userA] = $this->tenantUser('tenant-a');
        [$tenantB, $userB] = $this->tenantUser('tenant-b');

        // Créer la taxe en tant que userB pour que HasTenant injecte le bon tenant_id
        $this->actingAs($userB);
        $taxB = Tax::create(['name' => 'TVA B', 'rate' => 5, 'is_active' => true]);
        $this->assertSame($tenantB->id, $taxB->tenant_id);

        // UserA tente de modifier la taxe de B — le model binding renvoie 404 (tenant scope)
        $this->actingAs($userA)
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->put(route('taxes.update', $taxB), ['name' => 'Hack', 'rate' => 99])
            ->assertNotFound();

        $this->assertDatabaseHas('taxes', ['id' => $taxB->id, 'name' => 'TVA B']);
    }

    public function test_tenant_cannot_delete_foreign_tax(): void
    {
        [$tenantA, $userA] = $this->tenantUser('tenant-a');
        [$tenantB, $userB] = $this->tenantUser('tenant-b');

        $this->actingAs($userB);
        $taxB = Tax::create(['name' => 'TVA B', 'rate' => 5, 'is_active' => true]);

        // UserA tente de supprimer la taxe de B — le model binding renvoie 404 (tenant scope)
        $this->actingAs($userA)
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->delete(route('taxes.destroy', $taxB))
            ->assertNotFound();

        $this->assertDatabaseHas('taxes', ['id' => $taxB->id]);
    }

    // ──────────────────────────────────────────────
    // Tax inactive refusée dans les Form Requests
    // ──────────────────────────────────────────────

    public function test_inactive_tax_is_rejected_on_quote(): void
    {
        [$tenant, $user] = $this->tenantUser();
        $scenario = $this->makeDocScenario($tenant);

        $inactiveTax = Tax::create(['tenant_id' => $tenant->id, 'name' => 'TVA désactivée', 'rate' => 5, 'is_active' => false]);

        $this->actingAs($user)
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('quotes.store'), [
                'contact_id' => $scenario['client']->id,
                'quote_date' => now()->toDateString(),
                'items' => [[
                    'product_id' => $scenario['product']->id,
                    'warehouse_id' => $scenario['warehouse']->id,
                    'quantity' => 1,
                    'unit_price_ht' => 1000,
                    'tax_id' => $inactiveTax->id,
                ]],
            ])
            ->assertSessionHasErrors('items.0.tax_id');
    }

    public function test_foreign_tax_is_rejected_on_quote(): void
    {
        [$tenantA, $userA] = $this->tenantUser('tenant-a');
        [$tenantB, $userB] = $this->tenantUser('tenant-b');

        $scenario = $this->makeDocScenario($tenantA);

        // Créer la taxe en tant que userB pour que HasTenant l'attribue au bon tenant
        $this->actingAs($userB);
        $foreignTax = Tax::create(['name' => 'TVA B', 'rate' => 10, 'is_active' => true]);
        $this->assertSame($tenantB->id, $foreignTax->tenant_id);

        $this->actingAs($userA)
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('quotes.store'), [
                'contact_id' => $scenario['client']->id,
                'quote_date' => now()->toDateString(),
                'items' => [[
                    'product_id' => $scenario['product']->id,
                    'warehouse_id' => $scenario['warehouse']->id,
                    'quantity' => 1,
                    'unit_price_ht' => 1000,
                    'tax_id' => $foreignTax->id,
                ]],
            ])
            ->assertSessionHasErrors('items.0.tax_id');
    }

    public function test_valid_active_tax_is_accepted_on_quote(): void
    {
        [$tenant, $user] = $this->tenantUser();
        $scenario = $this->makeDocScenario($tenant);
        $tax = Tax::create(['tenant_id' => $tenant->id, 'name' => 'TVA 10%', 'rate' => 10, 'is_active' => true]);

        $this->actingAs($user)
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('quotes.store'), [
                'contact_id' => $scenario['client']->id,
                'quote_date' => now()->toDateString(),
                'items' => [[
                    'product_id' => $scenario['product']->id,
                    'warehouse_id' => $scenario['warehouse']->id,
                    'quantity' => 1,
                    'unit_price_ht' => 1000,
                    'tax_id' => $tax->id,
                ]],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('quotes', ['tenant_id' => $tenant->id]);
    }

    public function test_null_tax_id_is_accepted_on_quote(): void
    {
        [$tenant, $user] = $this->tenantUser();
        $scenario = $this->makeDocScenario($tenant);

        $this->actingAs($user)
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('quotes.store'), [
                'contact_id' => $scenario['client']->id,
                'quote_date' => now()->toDateString(),
                'items' => [[
                    'product_id' => $scenario['product']->id,
                    'warehouse_id' => $scenario['warehouse']->id,
                    'quantity' => 1,
                    'unit_price_ht' => 1000,
                ]],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('quotes', ['tenant_id' => $tenant->id]);
    }

    public function test_deleted_tax_is_rejected_on_quote(): void
    {
        [$tenant, $user] = $this->tenantUser();
        $scenario = $this->makeDocScenario($tenant);
        $tax = Tax::create(['tenant_id' => $tenant->id, 'name' => 'TVA supprimée', 'rate' => 7, 'is_active' => true]);
        $tax->delete();

        $this->actingAs($user)
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('quotes.store'), [
                'contact_id' => $scenario['client']->id,
                'quote_date' => now()->toDateString(),
                'items' => [[
                    'product_id' => $scenario['product']->id,
                    'warehouse_id' => $scenario['warehouse']->id,
                    'quantity' => 1,
                    'unit_price_ht' => 1000,
                    'tax_id' => $tax->id,
                ]],
            ])
            ->assertSessionHasErrors('items.0.tax_id');
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    private function tenantUser(string $slug = null): array
    {
        $slug = $slug ?? 'tenant-' . uniqid();
        $tenant = Tenant::create(['name' => $slug, 'slug' => $slug, 'is_active' => true]);
        $user = User::create([
            'name' => 'User ' . $slug,
            'email' => $slug . '@test.com',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant->id,
            'is_active' => true,
            'is_owner' => true,
        ]);

        return [$tenant, $user];
    }

    private function makeDocScenario(Tenant $tenant): array
    {
        $this->actingAs(User::where('tenant_id', $tenant->id)->firstOrFail());

        Setting::create(['currency' => 'FCFA', 'tva' => 10]);

        $category = Category::create(['name' => 'Cat ' . uniqid(), 'slug' => 'cat-' . uniqid()]);
        $unit = Units::create(['name' => 'Pce ' . uniqid(), 'code' => 'U' . random_int(100, 999)]);
        $product = Product::create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'name' => 'Prod ' . uniqid(),
            'description' => '',
            'price' => 1000,
            'seuil_alert' => 0,
            'is_active' => true,
            'is_perishable' => false,
        ]);
        $warehouse = Warehouse::create(['name' => 'WH ' . uniqid(), 'address' => 'Dakar', 'description' => '']);
        $client = Contact::create([
            'fullname' => 'Client ' . uniqid(),
            'phone_number' => '221' . random_int(700000000, 799999999),
            'address' => 'Dakar',
            'type' => 'client',
        ]);

        return compact('product', 'warehouse', 'client');
    }
}
