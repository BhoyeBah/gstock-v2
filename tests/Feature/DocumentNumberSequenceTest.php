<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSubscriptionAndPermissions;
use App\Models\Batch;
use App\Models\Category;
use App\Models\Contact;
use App\Models\DocumentSequence;
use App\Models\Inventory;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\Units;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Warehouse;
use App\Services\DocumentNumberService;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DocumentNumberSequenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_invoice_number_is_generated(): void
    {
        $scenario = $this->createInvoiceScenario();

        app(InvoiceService::class)->validateInvoice($scenario['invoice']->fresh('items.product', 'tenant'));

        $this->assertSame('FAC/'.now()->format('Y').'/0001', $scenario['invoice']->fresh()->invoice_number);
    }

    public function test_payment_number_is_generated(): void
    {
        $scenario = $this->createPaymentScenario();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('payments.store', ['clients']), [
                'invoice_id' => $scenario['invoice']->id,
                'wallet_id' => $scenario['wallet']->id,
                'amount_paid' => 200,
                'payment_date' => now()->format('Y-m-d'),
            ])
            ->assertRedirect();

        $payment = Payment::where('invoice_id', $scenario['invoice']->id)
            ->where('amount_paid', 200)
            ->latest()
            ->firstOrFail();

        $this->assertSame('PAY/'.now()->format('Y').'/0001', $payment->payment_number);
    }

    public function test_inventory_number_is_generated(): void
    {
        $scenario = $this->createInventoryScenario();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('inventories.store'), [
                'warehouse_id' => $scenario['warehouse']->id,
            ])
            ->assertRedirect();

        $inventory = Inventory::latest()->firstOrFail();

        $this->assertSame('INV/'.now()->format('Y').'/0001', $inventory->inventory_number);
    }

    public function test_two_successive_generations_produce_different_numbers(): void
    {
        $scenario = $this->createBaseScenario();
        $this->actingAs($scenario['user']);

        $service = app(DocumentNumberService::class);

        $first = $service->generate('payment', $scenario['tenant']);
        $second = $service->generate('payment', $scenario['tenant']);

        $this->assertSame('PAY/'.now()->format('Y').'/0001', $first);
        $this->assertSame('PAY/'.now()->format('Y').'/0002', $second);
    }

    public function test_padding_and_prefix_work_correctly(): void
    {
        $scenario = $this->createBaseScenario();
        $this->actingAs($scenario['user']);

        $service = app(DocumentNumberService::class);
        $service->ensureSequenceExists('inventory', $scenario['tenant']);

        $sequence = DocumentSequence::where('tenant_id', $scenario['tenant']->id)
            ->where('document_type', 'inventory')
            ->firstOrFail();
        $sequence->update([
            'prefix' => 'INVX',
            'padding' => 6,
        ]);

        $number = $service->generate('inventory', $scenario['tenant']);

        $this->assertSame('INVX/'.now()->format('Y').'/000001', $number);
    }

    public function test_annual_reset_creates_new_sequence_for_new_year(): void
    {
        $scenario = $this->createBaseScenario();
        $this->actingAs($scenario['user']);

        DocumentSequence::create([
            'tenant_id' => $scenario['tenant']->id,
            'document_type' => 'payment',
            'prefix' => 'PAY',
            'current_number' => 42,
            'padding' => 4,
            'year' => (int) now()->subYear()->format('Y'),
            'period_key' => now()->subYear()->format('Y'),
            'reset_period' => 'yearly',
            'is_active' => true,
        ]);

        $number = app(DocumentNumberService::class)->generate('payment', $scenario['tenant']);

        $this->assertSame('PAY/'.now()->format('Y').'/0001', $number);
        $this->assertSame(2, DocumentSequence::where('tenant_id', $scenario['tenant']->id)->where('document_type', 'payment')->count());
    }

    public function test_each_tenant_has_its_own_sequence(): void
    {
        $tenantA = $this->createTenant('tenant-a');
        $userA = $this->createUser($tenantA);
        $tenantB = $this->createTenant('tenant-b');
        $userB = $this->createUser($tenantB);

        $this->actingAs($userA);
        $first = app(DocumentNumberService::class)->generate('inventory', $tenantA);

        $this->actingAs($userB);
        $second = app(DocumentNumberService::class)->generate('inventory', $tenantB);

        $this->assertSame('INV/'.now()->format('Y').'/0001', $first);
        $this->assertSame('INV/'.now()->format('Y').'/0001', $second);
    }

    public function test_cross_tenant_sequence_management_is_blocked(): void
    {
        [$tenantA, $userA] = $this->createTenantUserWithPermission('tenant-seq-a', 'manage_document_sequences');
        [$tenantB, $userB] = $this->createTenantUserWithPermission('tenant-seq-b', 'manage_document_sequences');
        $readPermission = Permission::firstOrCreate([
            'name' => 'read_document_sequences',
            'guard_name' => 'web',
        ], [
            'description' => 'read_document_sequences',
        ]);
        $userA->givePermissionTo($readPermission);

        $this->actingAs($userB);
        $foreignSequence = app(DocumentNumberService::class)->ensureSequenceExists('payment', $tenantB);

        $this->actingAs($userA)
            ->put(route('document-sequences.update', $foreignSequence->id), [
                'prefix' => 'X',
                'padding' => 4,
                'reset_period' => 'yearly',
                'is_active' => 1,
            ])
            ->assertNotFound();
    }

    public function test_document_number_service_uses_transaction_and_locking(): void
    {
        $serviceSource = file_get_contents(app_path('Services/DocumentNumberService.php'));

        $this->assertStringContainsString('DB::transaction', $serviceSource);
        $this->assertStringContainsString('lockForUpdate', $serviceSource);
    }

    public function test_existing_document_number_is_not_replaced(): void
    {
        $scenario = $this->createInvoiceScenario('MANUAL/2026/0099');

        app(InvoiceService::class)->validateInvoice($scenario['invoice']->fresh('items.product', 'tenant'));

        $this->assertSame('MANUAL/2026/0099', $scenario['invoice']->fresh()->invoice_number);
    }

    public function test_user_without_permission_cannot_manage_document_sequences(): void
    {
        $scenario = $this->createBaseScenario();
        $this->actingAs($scenario['user']);
        $sequence = app(DocumentNumberService::class)->ensureSequenceExists('payment', $scenario['tenant']);

        $this->put(route('document-sequences.update', $sequence->id), [
            'prefix' => 'NEW',
            'padding' => 4,
            'reset_period' => 'yearly',
            'is_active' => 1,
        ])->assertForbidden();
    }

    private function createPaymentScenario(): array
    {
        $scenario = $this->createBaseScenario();
        $this->actingAs($scenario['user']);

        $contact = Contact::create([
            'fullname' => 'Client Sequence',
            'phone_number' => '221770000321',
            'address' => 'Dakar',
            'type' => 'client',
        ]);

        $invoice = Invoice::create([
            'contact_id' => $contact->id,
            'invoice_number' => 'FAC-OLD',
            'invoice_date' => now(),
            'due_date' => now()->addDay(),
            'type' => 'client',
            'total_invoice' => 1000,
            'balance' => 1000,
            'status' => 'validated',
        ]);

        $wallet = Wallet::create([
            'name' => 'Caisse sequence',
            'code' => 'SEQ-001',
            'identifier' => 'SEQ-WALLET',
            'initial_balance' => 0,
            'current_balance' => 0,
            'type' => 'bank',
        ]);

        return $scenario + compact('contact', 'invoice', 'wallet');
    }

    private function createInventoryScenario(): array
    {
        $scenario = $this->createBaseScenario();
        $this->actingAs($scenario['user']);

        Batch::create([
            'tenant_id' => $scenario['tenant']->id,
            'invoice_id' => null,
            'warehouse_id' => $scenario['warehouse']->id,
            'product_id' => $scenario['product']->id,
            'unit_price' => 500,
            'quantity' => 10,
            'benefit' => 0,
            'remaining' => 10,
            'expiration_date' => null,
            'origin' => 'seed',
        ]);

        return $scenario;
    }

    private function createInvoiceScenario(?string $invoiceNumber = null): array
    {
        $scenario = $this->createBaseScenario();
        $this->actingAs($scenario['user']);

        $contact = Contact::create([
            'fullname' => 'Client Test',
            'phone_number' => '221770000123',
            'address' => 'Dakar',
            'type' => 'client',
        ]);

        Batch::create([
            'tenant_id' => $scenario['tenant']->id,
            'invoice_id' => null,
            'warehouse_id' => $scenario['warehouse']->id,
            'product_id' => $scenario['product']->id,
            'unit_price' => 700,
            'quantity' => 10,
            'benefit' => 0,
            'remaining' => 10,
            'expiration_date' => null,
            'origin' => 'seed',
        ]);

        $invoice = app(InvoiceService::class)->createInvoice([
            'contact_id' => $contact->id,
            'invoice_number' => $invoiceNumber,
            'due_date' => now()->addDay(),
            'invoice_date' => now(),
            'type' => 'client',
            'items' => [[
                'warehouse_id' => $scenario['warehouse']->id,
                'product_id' => $scenario['product']->id,
                'quantity' => 2,
                'unit_price' => 1000,
                'discount' => 0,
                'expiration_date' => null,
            ]],
        ]);

        return $scenario + compact('contact', 'invoice');
    }

    private function createBaseScenario(): array
    {
        $tenant = $this->createTenant('tenant-'.uniqid());
        $user = $this->createUser($tenant);
        $this->actingAs($user);

        $category = Category::create([
            'name' => 'Categorie '.uniqid(),
            'slug' => 'categorie-'.uniqid(),
        ]);
        $unit = Units::create([
            'name' => 'Piece '.uniqid(),
            'code' => 'PC'.random_int(100, 999),
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'name' => 'Produit '.uniqid(),
            'description' => 'Produit test',
            'price' => 1000,
            'seuil_alert' => 2,
            'is_active' => true,
            'is_perishable' => false,
        ]);
        $warehouse = Warehouse::create([
            'name' => 'Entrepot '.uniqid(),
            'address' => 'Dakar',
            'description' => 'Entrepot test',
        ]);

        return compact('tenant', 'user', 'category', 'unit', 'product', 'warehouse');
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

    private function createTenantUserWithPermission(string $slug, string $permissionName): array
    {
        $tenant = $this->createTenant($slug);
        $user = $this->createUser($tenant);
        $permission = Permission::firstOrCreate([
            'name' => $permissionName,
            'guard_name' => 'web',
        ], [
            'description' => $permissionName,
        ]);
        $user->givePermissionTo($permission);

        return [$tenant, $user];
    }
}
