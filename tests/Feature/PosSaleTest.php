<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSubscriptionAndPermissions;
use App\Models\Batch;
use App\Models\Category;
use App\Models\Contact;
use App\Models\InventoryMovement;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\Units;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PosSaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_sale_creates_invoice_payment_and_stock_movement(): void
    {
        $s = $this->scenario();

        $this->actingAs($s['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('sales.store'), [
                'warehouse_id' => $s['warehouse']->id,
                'wallet_id'    => $s['wallet']->id,
                'amount_paid'  => 3000,
                'items' => [[
                    'product_id' => $s['product']->id,
                    'quantity'   => 3,
                    'unit_price' => 1000,
                    'discount'   => 0,
                ]],
            ])
            ->assertRedirect();

        $invoice = Invoice::where('tenant_id', $s['tenant']->id)->firstOrFail();

        $this->assertSame('client', $invoice->type);
        $this->assertSame(3000, (int) $invoice->total_invoice);
        $this->assertSame(0, (int) $invoice->balance);
        $this->assertSame('paid', $invoice->status);

        // Wallet crédité
        $this->assertDatabaseHas('wallets', [
            'id' => $s['wallet']->id,
            'current_balance' => 10000 + 3000,
        ]);

        // Stock sorti en FIFO (batch A a 5 unités, on sort 3 → batch A passe à 2)
        $this->assertDatabaseHas('batches', ['id' => $s['batchA']->id, 'remaining' => 2]);
        $this->assertDatabaseHas('batches', ['id' => $s['batchB']->id, 'remaining' => 3]);

        // Mouvement inventaire créé
        $this->assertDatabaseHas('inventory_movements', [
            'tenant_id'     => $s['tenant']->id,
            'movement_type' => 'sale_out',
            'product_id'    => $s['product']->id,
            'quantity'      => 3,
        ]);
    }

    public function test_pos_sale_partial_payment_creates_partial_invoice(): void
    {
        $s = $this->scenario();

        $this->actingAs($s['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('sales.store'), [
                'warehouse_id' => $s['warehouse']->id,
                'wallet_id'    => $s['wallet']->id,
                'amount_paid'  => 500,
                'items' => [[
                    'product_id' => $s['product']->id,
                    'quantity'   => 1,
                    'unit_price' => 1000,
                    'discount'   => 0,
                ]],
            ])
            ->assertRedirect();

        $invoice = Invoice::where('tenant_id', $s['tenant']->id)->firstOrFail();
        $this->assertSame('partial', $invoice->status);
        $this->assertSame(500, (int) $invoice->balance);
    }

    public function test_pos_sale_rejects_insufficient_stock(): void
    {
        $s = $this->scenario();

        $this->actingAs($s['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('sales.store'), [
                'warehouse_id' => $s['warehouse']->id,
                'wallet_id'    => $s['wallet']->id,
                'amount_paid'  => 99000,
                'items' => [[
                    'product_id' => $s['product']->id,
                    'quantity'   => 100, // stock total = 8
                    'unit_price' => 1000,
                ]],
            ])
            ->assertSessionHasErrors('items');

        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_pos_sale_rejects_foreign_wallet(): void
    {
        $s = $this->scenario();
        [$tenantB, $userB] = $this->otherTenant();

        $this->actingAs($userB);
        $foreignWallet = Wallet::create([
            'name' => 'Caisse B', 'code' => 'WBB', 'identifier' => 'WBB-1',
            'initial_balance' => 0, 'current_balance' => 0, 'type' => 'bank',
        ]);

        $this->actingAs($s['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('sales.store'), [
                'warehouse_id' => $s['warehouse']->id,
                'wallet_id'    => $foreignWallet->id,
                'amount_paid'  => 1000,
                'items' => [[
                    'product_id' => $s['product']->id,
                    'quantity'   => 1,
                    'unit_price' => 1000,
                ]],
            ])
            ->assertSessionHasErrors('wallet_id');
    }

    public function test_pos_receipt_page_is_tenant_safe(): void
    {
        $s = $this->scenario();
        [$tenantB, $userB] = $this->otherTenant();

        $this->actingAs($s['user']);
        Invoice::create([
            'contact_id'     => $s['client']->id,
            'invoice_number' => 'INV-POS-TEST',
            'invoice_date'   => now()->toDateString(),
            'due_date'       => now()->toDateString(),
            'type'           => 'client',
            'total_invoice'  => 1000,
            'total_ht'       => 1000,
            'tax_amount'     => 0,
            'discount_amount'=> 0,
            'balance'        => 0,
            'status'         => 'paid',
        ]);
        $invoice = Invoice::where('tenant_id', $s['tenant']->id)->firstOrFail();

        // UserB ne doit pas accéder au reçu de A (HasTenant global scope → 404)
        $this->actingAs($userB)
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->get(route('sales.receipt', $invoice))
            ->assertNotFound();
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function scenario(): array
    {
        $slug = 'pos-tenant-' . uniqid();
        $tenant = Tenant::create(['name' => $slug, 'slug' => $slug, 'is_active' => true]);
        $user = User::create([
            'name' => 'POS User', 'email' => $slug . '@test.com',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant->id, 'is_active' => true, 'is_owner' => true,
        ]);
        $this->actingAs($user);

        Setting::create(['currency' => 'FCFA', 'tva' => 0]);

        $cat = Category::create(['name' => 'Cat ' . uniqid(), 'slug' => 'cat-' . uniqid()]);
        $unit = Units::create(['name' => 'U ' . uniqid(), 'code' => 'U' . random_int(100, 999)]);
        $product = Product::create([
            'category_id' => $cat->id, 'unit_id' => $unit->id,
            'name' => 'Prod ' . uniqid(), 'description' => '', 'price' => 1000,
            'seuil_alert' => 2, 'is_active' => true, 'is_perishable' => false,
        ]);
        $warehouse = Warehouse::create(['name' => 'WH ' . uniqid(), 'address' => 'Dakar', 'description' => '']);
        $client = Contact::create([
            'fullname' => 'Client ' . uniqid(), 'phone_number' => '221' . random_int(700000000, 799999999),
            'address' => 'Dakar', 'type' => 'client',
        ]);
        $wallet = Wallet::create([
            'name' => 'Caisse ' . uniqid(), 'code' => 'CA' . random_int(100, 999),
            'identifier' => 'CA-' . uniqid(),
            'initial_balance' => 10000, 'current_balance' => 10000, 'type' => 'bank',
        ]);
        $batchA = Batch::create([
            'warehouse_id' => $warehouse->id, 'product_id' => $product->id,
            'unit_price' => 600, 'quantity' => 5, 'remaining' => 5,
            'benefit' => 0, 'expiration_date' => null, 'origin' => 'purchase',
        ]);
        sleep(1);
        $batchB = Batch::create([
            'warehouse_id' => $warehouse->id, 'product_id' => $product->id,
            'unit_price' => 700, 'quantity' => 3, 'remaining' => 3,
            'benefit' => 0, 'expiration_date' => null, 'origin' => 'purchase',
        ]);

        return compact('tenant', 'user', 'product', 'warehouse', 'client', 'wallet', 'batchA', 'batchB');
    }

    private function otherTenant(): array
    {
        $slug = 'pos-other-' . uniqid();
        $tenant = Tenant::create(['name' => $slug, 'slug' => $slug, 'is_active' => true]);
        $user = User::create([
            'name' => 'Other', 'email' => $slug . '@test.com',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant->id, 'is_active' => true, 'is_owner' => true,
        ]);

        return [$tenant, $user];
    }
}
