<?php

namespace Tests\Feature;

use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReturnProductController;
use App\Http\Controllers\SaleController;
use App\Models\Batch;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\Units;
use App\Models\User;
use App\Models\Wallet;
use App\Models\walletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class Sprint1SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            \App\Http\Middleware\CheckSubscriptionAndPermissions::class,
            \App\Http\Middleware\CheckActiveUser::class,
        ]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function createTenantUserWithCreateUsersPermission(): array
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();

        $permission = Permission::create([
            'name' => 'create_users',
            'guard_name' => 'web',
        ]);

        $role = Role::create([
            'name' => $tenant->slug.'_admin',
            'guard_name' => 'web',
            'tenant_id' => $tenant->id,
        ]);
        $role->permissions()->sync([$permission->id]);
        $user->assignRole($role);

        return [$tenant, $user, $role, $permission];
    }

    private function createTenantCatalog(Tenant $tenant, string $prefix = 'a'): array
    {
        $unit = Units::firstOrCreate(
            ['code' => strtoupper($prefix).'U'],
            ['name' => 'Unit '.strtoupper($prefix)]
        );

        $category = null;
        $product = null;
        $contactClient = null;
        $contactSupplier = null;
        $warehouse = null;

        $actor = User::factory()->forTenant($tenant)->create();
        $this->actingAs($actor);

        $category = Category::create([
            'name' => 'Category '.strtoupper($prefix),
            'slug' => Str::slug('category '.strtoupper($prefix)).'-'.Str::random(6),
        ]);

        $contactClient = Contact::create([
            'fullname' => 'Client '.strtoupper($prefix),
            'phone_number' => '77'.str_pad((string) random_int(1000000, 9999999), 7, '0', STR_PAD_LEFT),
            'address' => 'Address '.strtoupper($prefix),
            'type' => 'client',
        ]);

        $contactSupplier = Contact::create([
            'fullname' => 'Supplier '.strtoupper($prefix),
            'phone_number' => '78'.str_pad((string) random_int(1000000, 9999999), 7, '0', STR_PAD_LEFT),
            'address' => 'Address '.strtoupper($prefix),
            'type' => 'supplier',
        ]);

        $warehouse = \App\Models\Warehouse::create([
            'name' => 'Warehouse '.strtoupper($prefix),
            'address' => 'Warehouse address '.strtoupper($prefix),
            'description' => 'Warehouse desc '.strtoupper($prefix),
            'manager_id' => null,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'name' => 'Product '.Str::upper($prefix),
            'description' => 'Product desc',
            'price' => 1000,
            'seuil_alert' => 2,
            'is_active' => true,
            'is_perishable' => false,
        ]);

        return compact('tenant', 'unit', 'category', 'product', 'contactClient', 'contactSupplier', 'warehouse');
    }

    private function createInvoiceForTenant(Tenant $tenant, string $type = 'client', int $total = 10000, int $balance = 10000): Invoice
    {
        $actor = User::factory()->forTenant($tenant)->create();
        $this->actingAs($actor);

        $catalog = $this->createTenantCatalog($tenant, Str::random(1));
        $contact = $type === 'client' ? $catalog['contactClient'] : $catalog['contactSupplier'];

        return Invoice::create([
            'contact_id' => $contact->id,
            'invoice_number' => 'INV-'.Str::upper(Str::random(8)),
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'type' => $type,
            'total_invoice' => $total,
            'balance' => $balance,
            'status' => $balance === $total ? 'validated' : 'partial',
        ]);
    }

    public function test_user_cannot_assign_foreign_role(): void
    {
        [$tenantA, $userA] = $this->createTenantUserWithCreateUsersPermission();
        $tenantB = Tenant::factory()->create();

        $actorB = User::factory()->forTenant($tenantB)->create();
        $this->actingAs($actorB);
        $foreignRole = Role::create([
            'name' => $tenantB->slug.'_manager',
            'guard_name' => 'web',
            'tenant_id' => $tenantB->id,
        ]);

        $this->actingAs($userA);

        $response = $this->post(route('users.store'), [
            'name' => 'Test Foreign Role',
            'email' => 'foreign-role@example.com',
            'phone' => '771234567',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => $foreignRole->id,
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'foreign-role@example.com']);
    }

    public function test_user_cannot_assign_platform_role(): void
    {
        [$tenantA, $userA] = $this->createTenantUserWithCreateUsersPermission();
        $platformTenant = Tenant::factory()->platform()->create();

        $platformActor = User::factory()->forTenant($platformTenant)->create();
        $this->actingAs($platformActor);
        $platformRole = Role::create([
            'name' => 'platform_manager',
            'guard_name' => 'web',
            'tenant_id' => $platformTenant->id,
        ]);

        $this->actingAs($userA);

        $response = $this->post(route('users.store'), [
            'name' => 'Test Platform Role',
            'email' => 'platform-role@example.com',
            'phone' => '771234568',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => $platformRole->id,
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'platform-role@example.com']);
    }

    public function test_invoice_creation_rejects_foreign_refs(): void
    {
        [$tenantA, $userA] = $this->createTenantUserWithCreateUsersPermission();
        $tenantB = Tenant::factory()->create();

        $this->actingAs(User::factory()->forTenant($tenantA)->create());
        $catalogB = $this->createTenantCatalog($tenantB, 'b');

        $response = $this->actingAs($userA)->post(route('invoices.store', ['type' => 'clients']), [
            'contact_id' => $catalogB['contactClient']->id,
            'invoice_number' => 'INV-FOREIGN-001',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDay()->toDateString(),
            'type' => 'client',
            'items' => [
                [
                    'warehouse_id' => $catalogB['warehouse']->id,
                    'product_id' => $catalogB['product']->id,
                    'quantity' => 2,
                    'unit_price' => 1000,
                    'discount' => 0,
                ],
            ],
        ]);

        $response->assertSessionHasErrors([
            'contact_id',
            'items.0.warehouse_id',
            'items.0.product_id',
        ]);
    }

    public function test_payment_rejects_foreign_invoice(): void
    {
        [$tenantA, $userA] = $this->createTenantUserWithCreateUsersPermission();
        $tenantB = Tenant::factory()->create();

        $catalogA = $this->createTenantCatalog($tenantA, 'a');
        $catalogB = $this->createTenantCatalog($tenantB, 'b');

        $invoiceForeign = $this->createInvoiceForTenant($tenantB, 'client', 10000, 10000);
        $walletA = Wallet::create([
            'name' => 'Wallet A',
            'code' => 'WA',
            'identifier' => 'WA-001',
            'initial_balance' => 0,
            'current_balance' => 5000,
            'type' => 'bank',
            'is_active' => true,
        ]);

        $response = $this->actingAs($userA)->post(route('payments.store', ['type' => 'clients']), [
            'invoice_id' => $invoiceForeign->id,
            'wallet_id' => $walletA->id,
            'amount_paid' => 1000,
            'payment_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('invoice_id');
    }

    public function test_inventory_adjustment_rejects_foreign_warehouse(): void
    {
        [$tenantA, $userA] = $this->createTenantUserWithCreateUsersPermission();
        $tenantB = Tenant::factory()->create();

        $this->createTenantCatalog($tenantA, 'a');
        $catalogB = $this->createTenantCatalog($tenantB, 'b');

        $response = $this->actingAs($userA)->post(route('inventories.store'), [
            'warehouse_id' => $catalogB['warehouse']->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_inventory_validation_decreases_stock_when_real_below_theoretical(): void
    {
        $tenant = Tenant::factory()->create();
        $actor = User::factory()->forTenant($tenant)->create();
        $this->actingAs($actor);
        $catalog = $this->createTenantCatalog($tenant, 'a');
        $invoice = $this->createInvoiceForTenant($tenant, 'supplier', 10000, 10000);

        $batchOld = Batch::create([
            'invoice_id' => $invoice->id,
            'tenant_id' => $tenant->id,
            'warehouse_id' => $catalog['warehouse']->id,
            'product_id' => $catalog['product']->id,
            'unit_price' => 1000,
            'quantity' => 6,
            'remaining' => 6,
        ]);
        $batchNew = Batch::create([
            'invoice_id' => $invoice->id,
            'tenant_id' => $tenant->id,
            'warehouse_id' => $catalog['warehouse']->id,
            'product_id' => $catalog['product']->id,
            'unit_price' => 1000,
            'quantity' => 4,
            'remaining' => 4,
        ]);

        $inventory = Inventory::create([
            'warehouse_id' => $catalog['warehouse']->id,
            'inventory_number' => 'INV-'.Str::random(8),
            'total_products' => 10,
            'status' => 'pending',
        ]);

        $inventoryItem = InventoryItem::create([
            'id' => (string) Str::uuid(),
            'inventory_id' => $inventory->id,
            'product_id' => $catalog['product']->id,
            'theoretical_qty' => 10,
            'real_qty' => null,
            'variance' => 0,
            'validated' => false,
        ]);

        $response = $this->patch(route('inventories.validate', ['id' => $inventoryItem->id]), [
            'real_qty' => 7,
            'reason' => 'Stock physique',
        ]);

        $response->assertSessionHas('success');
        $this->assertSame(3, $batchOld->fresh()->remaining);
        $this->assertSame(4, $batchNew->fresh()->remaining);
        $this->assertDatabaseHas('inventory_movements', [
            'tenant_id' => $tenant->id,
            'inventory_id' => $inventory->id,
            'inventory_item_id' => $inventoryItem->id,
            'product_id' => $catalog['product']->id,
            'warehouse_id' => $catalog['warehouse']->id,
            'quantity_before' => 10,
            'quantity_after' => 7,
            'variance' => -3,
            'user_id' => auth()->id(),
            'movement_type' => 'inventory_adjustment_out',
            'quantity' => 3,
        ]);
    }

    public function test_inventory_validation_increases_stock_when_real_above_theoretical(): void
    {
        $tenant = Tenant::factory()->create();
        $actor = User::factory()->forTenant($tenant)->create();
        $this->actingAs($actor);
        $catalog = $this->createTenantCatalog($tenant, 'a');
        $invoice = $this->createInvoiceForTenant($tenant, 'supplier', 10000, 10000);

        $batch = Batch::create([
            'invoice_id' => $invoice->id,
            'tenant_id' => $tenant->id,
            'warehouse_id' => $catalog['warehouse']->id,
            'product_id' => $catalog['product']->id,
            'unit_price' => 1000,
            'quantity' => 5,
            'remaining' => 5,
        ]);

        $inventory = Inventory::create([
            'warehouse_id' => $catalog['warehouse']->id,
            'inventory_number' => 'INV-'.Str::random(8),
            'total_products' => 5,
            'status' => 'pending',
        ]);

        $inventoryItem = InventoryItem::create([
            'id' => (string) Str::uuid(),
            'inventory_id' => $inventory->id,
            'product_id' => $catalog['product']->id,
            'theoretical_qty' => 5,
            'real_qty' => null,
            'variance' => 0,
            'validated' => false,
        ]);

        $response = $this->patch(route('inventories.validate', ['id' => $inventoryItem->id]), [
            'real_qty' => 8,
            'reason' => 'Stock physique',
        ]);

        $response->assertSessionHas('success');
        $this->assertSame(8, $batch->fresh()->remaining);
        $this->assertSame(8, $batch->fresh()->quantity);
        $this->assertDatabaseHas('inventory_movements', [
            'tenant_id' => $tenant->id,
            'inventory_id' => $inventory->id,
            'inventory_item_id' => $inventoryItem->id,
            'product_id' => $catalog['product']->id,
            'warehouse_id' => $catalog['warehouse']->id,
            'quantity_before' => 5,
            'quantity_after' => 8,
            'variance' => 3,
            'user_id' => auth()->id(),
            'movement_type' => 'inventory_adjustment_in',
            'quantity' => 3,
        ]);
    }

    public function test_payment_reversal_restores_wallet_balance(): void
    {
        [$tenant, $user] = $this->createTenantUserWithCreateUsersPermission();
        $catalog = $this->createTenantCatalog($tenant, 'a');
        $wallet = Wallet::create([
            'name' => 'Wallet A',
            'code' => 'WA-1',
            'identifier' => 'WA-1',
            'initial_balance' => 0,
            'current_balance' => 60,
            'type' => 'bank',
            'is_active' => true,
        ]);

        $invoice = Invoice::create([
            'contact_id' => $catalog['contactClient']->id,
            'invoice_number' => 'INV-REV-001',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDay()->toDateString(),
            'type' => 'client',
            'total_invoice' => 100,
            'balance' => 40,
            'status' => 'partial',
        ]);

        $payment = Payment::create([
            'wallet_id' => $wallet->id,
            'invoice_id' => $invoice->id,
            'tenant_id' => $tenant->id,
            'contact_id' => $catalog['contactClient']->id,
            'amount_paid' => 60,
            'remaining_amount' => 40,
            'payment_date' => now()->toDateString(),
            'payment_type' => 'Wallet A',
            'payment_source' => 'client',
        ]);

        $response = $this->actingAs($user)->delete(route('payments.destroy', [
            'type' => 'clients',
            'payment' => $payment->id,
        ]));

        $response->assertSessionHas('success');
        $this->assertSame(0, $wallet->fresh()->current_balance);
        $this->assertDatabaseHas('wallet_transactions', [
            'tenant_id' => $tenant->id,
            'wallet_id' => $wallet->id,
            'payment_id' => $payment->id,
            'transaction_type' => 'payment_reversal',
            'amount' => 60,
            'balance_before' => 60,
            'balance_after' => 0,
        ]);
    }

    public function test_payment_reversal_recomputes_invoice_status(): void
    {
        [$tenant, $user] = $this->createTenantUserWithCreateUsersPermission();
        $catalog = $this->createTenantCatalog($tenant, 'a');
        $wallet = Wallet::create([
            'name' => 'Wallet A',
            'code' => 'WA-2',
            'identifier' => 'WA-2',
            'initial_balance' => 0,
            'current_balance' => 60,
            'type' => 'bank',
            'is_active' => true,
        ]);

        $invoice = Invoice::create([
            'contact_id' => $catalog['contactClient']->id,
            'invoice_number' => 'INV-REV-002',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDay()->toDateString(),
            'type' => 'client',
            'total_invoice' => 100,
            'balance' => 40,
            'status' => 'partial',
        ]);

        $payment = Payment::create([
            'wallet_id' => $wallet->id,
            'invoice_id' => $invoice->id,
            'tenant_id' => $tenant->id,
            'contact_id' => $catalog['contactClient']->id,
            'amount_paid' => 60,
            'remaining_amount' => 40,
            'payment_date' => now()->toDateString(),
            'payment_type' => 'Wallet A',
            'payment_source' => 'client',
        ]);

        $this->actingAs($user)->delete(route('payments.destroy', [
            'type' => 'clients',
            'payment' => $payment->id,
        ]));

        $invoice->refresh();
        $this->assertSame(100, $invoice->balance);
        $this->assertSame('validated', $invoice->status);
    }

    public function test_cross_tenant_route_access_is_blocked(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $userA = User::factory()->forTenant($tenantA)->create();
        $this->actingAs($userA);
        $catalogB = $this->createTenantCatalog($tenantB, 'b');
        $invoiceB = $this->createInvoiceForTenant($tenantB, 'client', 1000, 1000);

        $this->actingAs($userA);
        $this->get(route('warehouses.show', ['warehouse' => $catalogB['warehouse']->id]))->assertNotFound();
        $this->get(route('invoices.show', ['type' => 'clients', 'invoice' => $invoiceB->id]))->assertNotFound();
    }

    public function test_no_debug_dd_remains_in_active_routes(): void
    {
        $directory = new \RecursiveDirectoryIterator(app_path('Http/Controllers'));
        $iterator = new \RecursiveIteratorIterator($directory);

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            $contents = preg_replace('/^\s*\/\/.*$/m', '', $contents);
            $contents = preg_replace('/\/\*.*?\*\//s', '', $contents);

            $this->assertStringNotContainsString('dd(', $contents, $file->getFilename());
            $this->assertStringNotContainsString('dump(', $contents, $file->getFilename());
            $this->assertStringNotContainsString('var_dump(', $contents, $file->getFilename());
        }
    }
}
