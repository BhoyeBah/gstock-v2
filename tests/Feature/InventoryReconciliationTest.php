<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSubscriptionAndPermissions;
use App\Models\Batch;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Permission;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\Units;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InventoryReconciliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_real_lower_decrements_batches_fifo(): void
    {
        $scenario = $this->createInventoryScenario([5, 7]);

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->patch(route('inventories.validate', $scenario['item']->id), [
                'real_qty' => 4,
                'reason' => 'Casse constatée',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('batches', [
            'id' => $scenario['batches'][0]->id,
            'remaining' => 0,
        ]);
        $this->assertDatabaseHas('batches', [
            'id' => $scenario['batches'][1]->id,
            'remaining' => 4,
        ]);
        $this->assertDatabaseHas('inventory_items', [
            'id' => $scenario['item']->id,
            'real_qty' => 4,
            'variance' => -8,
            'validated' => true,
            'status' => 'reconciled',
        ]);
    }

    public function test_real_lower_across_multiple_lots_creates_multiple_movements(): void
    {
        $scenario = $this->createInventoryScenario([5, 7]);

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->patch(route('inventories.validate', $scenario['item']->id), [
                'real_qty' => 4,
                'reason' => 'Écart inventaire',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('inventory_movements', [
            'inventory_item_id' => $scenario['item']->id,
            'batch_id' => $scenario['batches'][0]->id,
            'quantity' => 5,
            'quantity_before' => 5,
            'quantity_after' => 0,
            'variance' => -5,
            'movement_type' => 'inventory_adjustment_out',
        ]);
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_item_id' => $scenario['item']->id,
            'batch_id' => $scenario['batches'][1]->id,
            'quantity' => 3,
            'quantity_before' => 7,
            'quantity_after' => 4,
            'variance' => -3,
            'movement_type' => 'inventory_adjustment_out',
        ]);
        $this->assertSame(2, InventoryMovement::where('inventory_item_id', $scenario['item']->id)->count());
    }

    public function test_real_higher_creates_new_inventory_gain_batch(): void
    {
        $scenario = $this->createInventoryScenario([5]);

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->patch(route('inventories.validate', $scenario['item']->id), [
                'real_qty' => 9,
                'reason' => 'Stock retrouvé',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('batches', [
            'tenant_id' => $scenario['tenant']->id,
            'warehouse_id' => $scenario['warehouse']->id,
            'product_id' => $scenario['product']->id,
            'quantity' => 4,
            'remaining' => 4,
            'origin' => 'inventory_gain',
            'source_type' => InventoryItem::class,
            'source_id' => $scenario['item']->id,
        ]);
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_item_id' => $scenario['item']->id,
            'quantity' => 4,
            'quantity_before' => 0,
            'quantity_after' => 4,
            'variance' => 4,
            'movement_type' => 'inventory_adjustment_in',
        ]);
    }

    public function test_no_variance_creates_no_movement(): void
    {
        $scenario = $this->createInventoryScenario([6]);

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->patch(route('inventories.validate', $scenario['item']->id), [
                'real_qty' => 6,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('inventory_items', [
            'id' => $scenario['item']->id,
            'real_qty' => 6,
            'variance' => 0,
            'status' => 'reconciled',
        ]);
        $this->assertSame(0, InventoryMovement::where('inventory_item_id', $scenario['item']->id)->count());
    }

    public function test_double_reconciliation_is_blocked(): void
    {
        $scenario = $this->createInventoryScenario([6]);

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->patch(route('inventories.validate', $scenario['item']->id), [
                'real_qty' => 5,
            ])
            ->assertRedirect();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->patch(route('inventories.validate', $scenario['item']->id), [
                'real_qty' => 4,
            ])
            ->assertSessionHasErrors(['inventory_item']);

        $this->assertSame(1, InventoryMovement::where('inventory_item_id', $scenario['item']->id)->count());
    }

    public function test_negative_stock_is_impossible(): void
    {
        $scenario = $this->createInventoryScenario([3], theoreticalQuantity: 10);

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->patch(route('inventories.validate', $scenario['item']->id), [
                'real_qty' => 0,
            ])
            ->assertSessionHasErrors(['real_qty']);

        $this->assertDatabaseHas('batches', [
            'id' => $scenario['batches'][0]->id,
            'remaining' => 3,
        ]);
        $this->assertDatabaseHas('inventory_items', [
            'id' => $scenario['item']->id,
            'validated' => false,
            'status' => 'pending',
        ]);
    }

    public function test_cross_tenant_inventory_item_is_blocked(): void
    {
        $tenantA = $this->createTenant('tenant-a');
        $userA = $this->createUser($tenantA);
        $tenantB = $this->createTenant('tenant-b');
        $userB = $this->createUser($tenantB);

        $this->actingAs($userB);
        $foreignScenario = $this->createInventoryScenario([5], tenant: $tenantB, user: $userB);

        $this->actingAs($userA)
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->patch(route('inventories.validate', $foreignScenario['item']->id), [
                'real_qty' => 4,
            ])
            ->assertNotFound();
    }

    public function test_user_without_inventory_permission_is_blocked(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $scenario = $this->createInventoryScenario([5]);
        $permission = Permission::firstOrCreate([
            'name' => 'manage_inventories',
            'guard_name' => 'web',
        ], [
            'description' => 'Gérer les inventaires',
        ]);
        $plan = Plan::create([
            'name' => 'Plan inventaire',
            'slug' => 'inventory-plan',
            'price' => 0,
            'duration_days' => 30,
            'max_users' => 10,
            'max_storage_mb' => 100,
            'is_active' => true,
            'description' => 'Plan test',
        ]);
        $plan->permissions()->attach($permission->id);

        Subscription::create([
            'tenant_id' => $scenario['tenant']->id,
            'plan_id' => $plan->id,
            'amount_paid' => 0,
            'payment_method' => 'test',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        $this->actingAs($scenario['user'])
            ->patch(route('inventories.validate', $scenario['item']->id), [
                'real_qty' => 4,
            ])
            ->assertForbidden();
    }

    public function test_movements_are_created_with_required_traceability_fields(): void
    {
        $scenario = $this->createInventoryScenario([5]);

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->patch(route('inventories.validate', $scenario['item']->id), [
                'real_qty' => 3,
                'reason' => 'Perte vérifiée',
            ])
            ->assertRedirect();

        $movement = InventoryMovement::where('inventory_item_id', $scenario['item']->id)->firstOrFail();

        $this->assertSame($scenario['tenant']->id, $movement->tenant_id);
        $this->assertSame($scenario['inventory']->id, $movement->inventory_id);
        $this->assertSame($scenario['item']->id, $movement->inventory_item_id);
        $this->assertSame($scenario['product']->id, $movement->product_id);
        $this->assertSame($scenario['warehouse']->id, $movement->warehouse_id);
        $this->assertSame($scenario['batches'][0]->id, $movement->batch_id);
        $this->assertSame($scenario['user']->id, $movement->user_id);
        $this->assertSame(5, $movement->quantity_before);
        $this->assertSame(3, $movement->quantity_after);
        $this->assertSame(-2, $movement->variance);
        $this->assertSame('Perte vérifiée', $movement->reason);
        $this->assertNotNull($movement->movement_date);
    }

    private function createInventoryScenario(array $batchQuantities, ?int $theoreticalQuantity = null, ?Tenant $tenant = null, ?User $user = null): array
    {
        $tenant ??= $this->createTenant('tenant-'.uniqid());
        $user ??= $this->createUser($tenant);

        $this->actingAs($user);

        $category = Category::create([
            'name' => 'Catégorie '.uniqid(),
            'slug' => 'categorie-'.uniqid(),
        ]);
        $unit = Units::create([
            'name' => 'Pièce '.uniqid(),
            'code' => 'P'.random_int(1000, 9999),
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'name' => 'Produit '.uniqid(),
            'description' => 'Produit test',
            'price' => 1000,
            'seuil_alert' => 5,
            'is_active' => true,
            'is_perishable' => false,
        ]);
        $warehouse = Warehouse::create([
            'name' => 'Entrepôt '.uniqid(),
            'address' => 'Dakar',
            'description' => 'Entrepôt test',
        ]);
        $inventory = Inventory::create([
            'warehouse_id' => $warehouse->id,
            'inventory_number' => 'INV-TEST-'.uniqid(),
            'total_products' => array_sum($batchQuantities),
            'status' => 'pending',
        ]);
        $batches = [];

        foreach ($batchQuantities as $index => $quantity) {
            $batches[] = Batch::create([
                'tenant_id' => $tenant->id,
                'invoice_id' => null,
                'warehouse_id' => $warehouse->id,
                'product_id' => $product->id,
                'unit_price' => 500 + $index,
                'quantity' => $quantity,
                'benefit' => 0,
                'remaining' => $quantity,
                'expiration_date' => now()->addDays($index + 1),
                'origin' => 'test_seed',
            ]);
        }

        $item = InventoryItem::create([
            'inventory_id' => $inventory->id,
            'product_id' => $product->id,
            'theoretical_qty' => $theoreticalQuantity ?? array_sum($batchQuantities),
            'real_qty' => null,
            'variance' => 0,
            'validated' => false,
            'status' => 'pending',
        ]);

        return compact('tenant', 'user', 'category', 'unit', 'product', 'warehouse', 'inventory', 'batches', 'item');
    }

    private function createTenant(string $slug): Tenant
    {
        return Tenant::create([
            'name' => $slug,
            'slug' => $slug,
            'is_active' => true,
        ]);
    }

    private function createUser(Tenant $tenant): User
    {
        return User::create([
            'name' => 'User '.$tenant->slug,
            'email' => $tenant->slug.'@example.test',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant->id,
            'is_active' => true,
            'is_owner' => true,
        ]);
    }
}
