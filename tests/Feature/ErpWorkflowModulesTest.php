<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSubscriptionAndPermissions;
use App\Models\Batch;
use App\Models\Category;
use App\Models\Contact;
use App\Models\CustomerReturn;
use App\Models\DeliveryNote;
use App\Models\GoodsReceipt;
use App\Models\InventoryMovement;
use App\Models\DeliveryNoteItem;
use App\Models\Permission;
use App\Models\Plan;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Quote;
use App\Models\SaleOrder;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\SupplierReturn;
use App\Models\Units;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ErpWorkflowModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_quote_creation_is_tenant_safe_and_does_not_touch_stock_or_wallet(): void
    {
        $scenario = $this->createCommerceScenario();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('quotes.store'), [
                'contact_id' => $scenario['client']->id,
                'quote_date' => now()->toDateString(),
                'valid_until' => now()->addDays(15)->toDateString(),
                'notes' => 'Offre test',
                'items' => [
                    [
                        'product_id' => $scenario['product']->id,
                        'warehouse_id' => $scenario['warehouse']->id,
                        'quantity' => 2,
                        'unit_price_ht' => 1000,
                        'discount_amount' => 100,
                    ],
                ],
            ])
            ->assertRedirect();

        $quote = Quote::query()->where('tenant_id', $scenario['tenant']->id)->firstOrFail();

        $this->assertSame(1900, (int) $quote->total_ht);
        $this->assertSame(100, (int) $quote->total_discount);
        $this->assertSame(190, (int) $quote->tax_amount);
        $this->assertSame(2090, (int) $quote->total_ttc);
        $this->assertSame('draft', $quote->status);
        $this->assertDatabaseHas('quote_items', [
            'quote_id' => $quote->id,
            'product_id' => $scenario['product']->id,
            'quantity' => 2,
            'unit_price_ht' => 1000,
            'discount_amount' => 100,
            'total_ttc' => 2090,
        ]);

        $this->assertDatabaseHas('wallets', [
            'id' => $scenario['wallet']->id,
            'current_balance' => 10000,
        ]);
        $this->assertDatabaseCount('inventory_movements', 0);

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('quotes.convert-to-order', $quote))
            ->assertRedirect();

        $this->assertDatabaseHas('sale_orders', [
            'tenant_id' => $scenario['tenant']->id,
            'quote_id' => $quote->id,
            'status' => 'draft',
            'total_ttc' => 2090,
        ]);

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('quotes.convert-to-invoice', $quote))
            ->assertSessionHasErrors();
    }

    public function test_quote_show_actions_are_compact_and_status_based(): void
    {
        $scenario = $this->createCommerceScenario();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('quotes.store'), [
                'contact_id' => $scenario['client']->id,
                'quote_date' => now()->toDateString(),
                'valid_until' => now()->addDays(15)->toDateString(),
                'notes' => 'Offre test',
                'items' => [
                    [
                        'product_id' => $scenario['product']->id,
                        'warehouse_id' => $scenario['warehouse']->id,
                        'quantity' => 1,
                        'unit_price_ht' => 1000,
                        'discount_amount' => 0,
                    ],
                ],
            ]);

        $quote = Quote::query()->where('tenant_id', $scenario['tenant']->id)->firstOrFail();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->get(route('quotes.show', $quote))
            ->assertSee('Convertir en facture')
            ->assertSee('Convertir en commande')
            ->assertSee('Imprimer / Télécharger')
            ->assertSee('Plus')
            ->assertSee('Modifier')
            ->assertSee('Marquer comme envoyé')
            ->assertSee('Accepter')
            ->assertSee('Rejeter')
            ->assertSee('Annuler');

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('quotes.convert-to-order', $quote))
            ->assertRedirect();

        $quote->refresh();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->get(route('quotes.show', $quote))
            ->assertSee('Voir la commande créée')
            ->assertSee('Imprimer / Télécharger')
            ->assertDontSee('Convertir en facture')
            ->assertDontSee('Convertir en commande');
    }

    public function test_sale_order_and_delivery_actions_follow_status_rules(): void
    {
        $scenario = $this->createCommerceScenario(withSupplier: true);

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('sale-orders.store'), [
                'contact_id' => $scenario['client']->id,
                'order_date' => now()->toDateString(),
                'notes' => 'Commande test',
                'items' => [
                    [
                        'product_id' => $scenario['product']->id,
                        'warehouse_id' => $scenario['warehouse']->id,
                        'quantity_ordered' => 2,
                        'unit_price_ht' => 1000,
                        'discount_amount' => 0,
                    ],
                ],
            ]);

        $saleOrder = SaleOrder::query()->where('tenant_id', $scenario['tenant']->id)->firstOrFail();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->get(route('sale-orders.show', $saleOrder))
            ->assertSee('Confirmer')
            ->assertSee('Créer facture')
            ->assertSee('Créer bon de livraison')
            ->assertSee('Modifier')
            ->assertSee('Annuler')
            ->assertSee('Imprimer');

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('sale-orders.confirm', $saleOrder))
            ->assertRedirect();

        $saleOrder->refresh();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->get(route('sale-orders.show', $saleOrder))
            ->assertSee('Créer bon de livraison')
            ->assertSee('Créer facture')
            ->assertSee('Imprimer')
            ->assertSee('Annuler');

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('sale-orders.create-delivery', $saleOrder))
            ->assertRedirect();

        $delivery = DeliveryNote::query()->where('tenant_id', $scenario['tenant']->id)->firstOrFail();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->get(route('delivery-notes.show', $delivery))
            ->assertSee('Valider livraison')
            ->assertSee('Imprimer')
            ->assertSee('Retour commande')
            ->assertSee('Annuler');

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('delivery-notes.validate', $delivery))
            ->assertRedirect();

        $delivery->refresh();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->get(route('delivery-notes.show', $delivery))
            ->assertSee('Voir commande')
            ->assertSee('Voir mouvements de stock')
            ->assertSee('Imprimer')
            ->assertDontSee('Valider livraison');
    }

    public function test_sale_order_delivery_sorts_fifo_and_updates_stock(): void
    {
        $scenario = $this->createCommerceScenario(withSupplier: true);

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('sale-orders.store'), [
                'contact_id' => $scenario['client']->id,
                'order_date' => now()->toDateString(),
                'notes' => 'Commande directe',
                'items' => [
                    [
                        'product_id' => $scenario['product']->id,
                        'warehouse_id' => $scenario['warehouse']->id,
                        'quantity_ordered' => 6,
                        'unit_price_ht' => 1000,
                        'discount_amount' => 0,
                    ],
                ],
            ])
            ->assertRedirect();

        $saleOrder = SaleOrder::query()->where('tenant_id', $scenario['tenant']->id)->firstOrFail();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('sale-orders.confirm', $saleOrder))
            ->assertRedirect();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('sale-orders.create-delivery', $saleOrder))
            ->assertRedirect();

        $delivery = DeliveryNote::query()->where('tenant_id', $scenario['tenant']->id)->firstOrFail();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('delivery-notes.validate', $delivery))
            ->assertRedirect();

        $this->assertDatabaseHas('batches', [
            'id' => $scenario['batchA']->id,
            'remaining' => 0,
        ]);
        $this->assertDatabaseHas('batches', [
            'id' => $scenario['batchB']->id,
            'remaining' => 2,
        ]);
        $this->assertSame(2, InventoryMovement::query()->where('movement_type', 'delivery_out')->count());
        $this->assertDatabaseHas('sale_orders', [
            'id' => $saleOrder->id,
            'status' => 'delivered',
        ]);
    }

    public function test_purchase_order_receipt_creates_batch_and_inventory_movement(): void
    {
        $scenario = $this->createCommerceScenario(withSupplier: true);

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('purchase-orders.store'), [
                'contact_id' => $scenario['supplier']->id,
                'purchase_date' => now()->toDateString(),
                'notes' => 'Commande fournisseur',
                'items' => [
                    [
                        'product_id' => $scenario['product']->id,
                        'warehouse_id' => $scenario['warehouse']->id,
                        'quantity_ordered' => 4,
                        'unit_cost_ht' => 600,
                        'discount_amount' => 0,
                        'expiration_date' => now()->addMonth()->toDateString(),
                    ],
                ],
            ])
            ->assertRedirect();

        $purchaseOrder = PurchaseOrder::query()->where('tenant_id', $scenario['tenant']->id)->firstOrFail();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('purchase-orders.create-receipt', $purchaseOrder))
            ->assertRedirect();

        $receipt = GoodsReceipt::query()->where('tenant_id', $scenario['tenant']->id)->firstOrFail();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('goods-receipts.validate', $receipt))
            ->assertRedirect();

        $this->assertDatabaseHas('batches', [
            'tenant_id' => $scenario['tenant']->id,
            'product_id' => $scenario['product']->id,
            'quantity' => 4,
            'remaining' => 4,
            'unit_price' => 600,
        ]);
        $this->assertDatabaseHas('inventory_movements', [
            'tenant_id' => $scenario['tenant']->id,
            'product_id' => $scenario['product']->id,
            'movement_type' => 'receipt_in',
            'quantity' => 4,
        ]);
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $purchaseOrder->id,
            'status' => 'received',
        ]);
    }

    public function test_customer_return_validates_and_reinstates_stock(): void
    {
        $scenario = $this->createCommerceScenario();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('sale-orders.store'), [
                'contact_id' => $scenario['client']->id,
                'order_date' => now()->toDateString(),
                'notes' => 'Commande test retour client',
                'items' => [
                    [
                        'product_id' => $scenario['product']->id,
                        'warehouse_id' => $scenario['warehouse']->id,
                        'quantity_ordered' => 4,
                        'unit_price_ht' => 1000,
                        'discount_amount' => 0,
                    ],
                ],
            ])
            ->assertRedirect();

        $saleOrder = SaleOrder::query()->where('tenant_id', $scenario['tenant']->id)->firstOrFail();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('sale-orders.create-delivery', $saleOrder))
            ->assertRedirect();

        $delivery = DeliveryNote::query()->where('tenant_id', $scenario['tenant']->id)->with('items')->firstOrFail();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('delivery-notes.validate', $delivery))
            ->assertRedirect();

        $delivery->refresh()->load('items');

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('customer-returns.store'), [
                'delivery_note_id' => $delivery->id,
                'contact_id' => $scenario['client']->id,
                'warehouse_id' => $scenario['warehouse']->id,
                'return_date' => now()->toDateString(),
                'reason' => 'Produit abîmé',
                'items' => [
                    $delivery->items->first()->id => [
                        'quantity_returned' => 1,
                    ],
                ],
            ])
            ->assertRedirect();

        $customerReturn = CustomerReturn::query()->where('tenant_id', $scenario['tenant']->id)->with('items')->firstOrFail();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->get(route('customer-returns.show', $customerReturn))
            ->assertSee('Valider')
            ->assertSee('Imprimer / Télécharger')
            ->assertSee('Actions');

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('customer-returns.validate', $customerReturn))
            ->assertRedirect();

        $this->assertDatabaseHas('batches', [
            'id' => $scenario['batchB']->id,
            'remaining' => 3,
        ]);
        $this->assertDatabaseHas('customer_returns', [
            'id' => $customerReturn->id,
            'status' => 'validated',
        ]);

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('customer-returns.validate', $customerReturn))
            ->assertSessionHasErrors();
    }

    public function test_supplier_return_validates_and_removes_stock(): void
    {
        $scenario = $this->createCommerceScenario(withSupplier: true);

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('purchase-orders.store'), [
                'contact_id' => $scenario['supplier']->id,
                'purchase_date' => now()->toDateString(),
                'notes' => 'Commande fournisseur retour',
                'items' => [
                    [
                        'product_id' => $scenario['product']->id,
                        'warehouse_id' => $scenario['warehouse']->id,
                        'quantity_ordered' => 4,
                        'unit_cost_ht' => 600,
                        'discount_amount' => 0,
                        'expiration_date' => now()->addMonth()->toDateString(),
                    ],
                ],
            ])
            ->assertRedirect();

        $purchaseOrder = PurchaseOrder::query()->where('tenant_id', $scenario['tenant']->id)->firstOrFail();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('purchase-orders.create-receipt', $purchaseOrder))
            ->assertRedirect();

        $receipt = GoodsReceipt::query()->where('tenant_id', $scenario['tenant']->id)->with('items')->firstOrFail();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('goods-receipts.validate', $receipt))
            ->assertRedirect();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('supplier-returns.store'), [
                'goods_receipt_id' => $receipt->id,
                'contact_id' => $scenario['supplier']->id,
                'warehouse_id' => $scenario['warehouse']->id,
                'return_date' => now()->toDateString(),
                'reason' => 'Retour fournisseur',
                'items' => [
                    $receipt->items->first()->id => [
                        'quantity_returned' => 1,
                    ],
                ],
            ])
            ->assertRedirect();

        $supplierReturn = SupplierReturn::query()->where('tenant_id', $scenario['tenant']->id)->with('items')->firstOrFail();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->get(route('supplier-returns.show', $supplierReturn))
            ->assertSee('Valider')
            ->assertSee('Imprimer / Télécharger')
            ->assertSee('Actions');

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('supplier-returns.validate', $supplierReturn))
            ->assertRedirect();

        $this->assertDatabaseHas('batches', [
            'tenant_id' => $scenario['tenant']->id,
            'product_id' => $scenario['product']->id,
            'remaining' => 3,
        ]);
        $this->assertDatabaseHas('inventory_movements', [
            'tenant_id' => $scenario['tenant']->id,
            'movement_type' => 'supplier_return_out',
            'quantity' => 1,
        ]);

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('supplier-returns.validate', $supplierReturn))
            ->assertSessionHasErrors();
    }

    public function test_foreign_document_access_is_blocked(): void
    {
        $tenantA = $this->createTenant('tenant-a');
        $tenantB = $this->createTenant('tenant-b');
        $userA = $this->createUser($tenantA);
        $userB = $this->createUser($tenantB);
        $this->actingAs($userB);

        $client = Contact::create([
            'fullname' => 'Client B',
            'phone_number' => '221770000111',
            'address' => 'Dakar',
            'type' => 'client',
        ]);
        $quote = Quote::create([
            'contact_id' => $client->id,
            'quote_number' => 'QUO-1',
            'quote_date' => now(),
            'status' => 'draft',
            'total_ht' => 0,
            'total_discount' => 0,
            'tax_amount' => 0,
            'total_ttc' => 0,
        ]);

        $this->actingAs($userA)
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->get(route('quotes.show', $quote))
            ->assertNotFound();
    }

    public function test_user_without_quote_permission_is_blocked(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $tenant = $this->createTenant('tenant-permission');
        $user = $this->createUser($tenant, false);

        $plan = Plan::create([
            'name' => 'Plan test',
            'slug' => 'plan-test-'.uniqid(),
            'price' => 0,
            'duration_days' => 30,
            'max_users' => 5,
            'max_storage_mb' => 100,
            'is_active' => true,
            'description' => 'Plan test',
        ]);

        Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'amount_paid' => 0,
            'payment_method' => 'manual',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('quotes.index'))
            ->assertForbidden();
    }

    private function createCommerceScenario(bool $withSupplier = false): array
    {
        $tenant = $this->createTenant('tenant-'.uniqid());
        $user = $this->createUser($tenant);
        $this->actingAs($user);

        Setting::create([
            'currency' => 'FCFA',
            'tva' => 10,
        ]);

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
        $client = Contact::create([
            'fullname' => 'Client '.uniqid(),
            'phone_number' => '22177'.random_int(1000000, 9999999),
            'address' => 'Dakar',
            'type' => 'client',
        ]);
        $wallet = Wallet::create([
            'name' => 'Caisse '.uniqid(),
            'code' => 'WAL'.random_int(100, 999),
            'identifier' => 'WAL-'.uniqid(),
            'initial_balance' => 10000,
            'current_balance' => 10000,
            'type' => 'bank',
        ]);
        $batchA = Batch::create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'unit_price' => 600,
            'quantity' => 5,
            'remaining' => 5,
            'benefit' => 0,
            'expiration_date' => null,
            'origin' => 'purchase',
        ]);
        sleep(1);
        $batchB = Batch::create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'unit_price' => 700,
            'quantity' => 3,
            'remaining' => 3,
            'benefit' => 0,
            'expiration_date' => null,
            'origin' => 'purchase',
        ]);
        $supplier = null;
        if ($withSupplier) {
            $supplier = Contact::create([
                'fullname' => 'Fournisseur '.uniqid(),
                'phone_number' => '22178'.random_int(1000000, 9999999),
                'address' => 'Dakar',
                'type' => 'supplier',
            ]);
        }

        return compact('tenant', 'user', 'category', 'unit', 'product', 'warehouse', 'client', 'wallet', 'batchA', 'batchB', 'supplier');
    }

    private function createTenant(string $slug): Tenant
    {
        return Tenant::create([
            'name' => $slug,
            'slug' => $slug,
            'is_active' => true,
        ]);
    }

    private function createUser(Tenant $tenant, bool $owner = true): User
    {
        return User::create([
            'name' => 'User '.$tenant->slug,
            'email' => $tenant->slug.'@example.test',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant->id,
            'is_active' => true,
            'is_owner' => $owner,
        ]);
    }
}
