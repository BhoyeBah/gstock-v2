<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Contact;
use App\Models\DeliveryNote;
use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SaleOrder;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\Units;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\DeliveryService;
use App\Services\ReceiptService;
use App\Services\SaleOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ServiceTenantDefenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_order_confirm_rejects_foreign_tenant(): void
    {
        [$tenantA, $userA] = $this->tenantUser('a');
        [$tenantB, $userB] = $this->tenantUser('b');

        $this->actingAs($userA);
        Setting::create(['currency' => 'FCFA', 'tva' => 0]);
        $saleOrder = $this->makeSaleOrder($tenantA, $userA);

        // UserB essaie de confirmer la commande de A via le service directement
        $this->actingAs($userB);

        $this->expectException(ValidationException::class);

        app(SaleOrderService::class)->confirm($saleOrder);
    }

    public function test_sale_order_cancel_rejects_foreign_tenant(): void
    {
        [$tenantA, $userA] = $this->tenantUser('a2');
        [$tenantB, $userB] = $this->tenantUser('b2');

        $this->actingAs($userA);
        Setting::create(['currency' => 'FCFA', 'tva' => 0]);
        $saleOrder = $this->makeSaleOrder($tenantA, $userA);

        $this->actingAs($userB);

        $this->expectException(ValidationException::class);

        app(SaleOrderService::class)->cancel($saleOrder);
    }

    public function test_delivery_cancel_rejects_foreign_tenant(): void
    {
        [$tenantA, $userA] = $this->tenantUser('a3');
        [$tenantB, $userB] = $this->tenantUser('b3');

        $this->actingAs($userA);
        $delivery = $this->makeDelivery($tenantA, $userA);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        app(DeliveryService::class)->cancel($delivery, $userB);
    }

    public function test_receipt_cancel_rejects_foreign_tenant(): void
    {
        [$tenantA, $userA] = $this->tenantUser('a4');
        [$tenantB, $userB] = $this->tenantUser('b4');

        $this->actingAs($userA);
        Setting::create(['currency' => 'FCFA', 'tva' => 0]);
        $receipt = $this->makeReceipt($tenantA, $userA);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        app(ReceiptService::class)->cancel($receipt, $userB);
    }

    // ─── Helpers ───────────────────────────────────────────

    private function tenantUser(string $suffix): array
    {
        $slug = 'tenant-def-' . $suffix;
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

    private function makeSaleOrder(Tenant $tenant, User $user): SaleOrder
    {
        $cat = Category::create(['name' => 'Cat ' . uniqid(), 'slug' => 'cat-' . uniqid()]);
        $unit = Units::create(['name' => 'U ' . uniqid(), 'code' => 'U' . random_int(100, 999)]);
        $product = Product::create([
            'category_id' => $cat->id, 'unit_id' => $unit->id,
            'name' => 'P ' . uniqid(), 'description' => '', 'price' => 100,
            'seuil_alert' => 0, 'is_active' => true, 'is_perishable' => false,
        ]);
        $warehouse = Warehouse::create(['name' => 'WH ' . uniqid(), 'address' => 'Dakar', 'description' => '']);
        $client = Contact::create([
            'fullname' => 'Cl ' . uniqid(), 'phone_number' => '221' . random_int(700000000, 799999999),
            'address' => 'Dakar', 'type' => 'client',
        ]);

        return SaleOrder::create([
            'contact_id' => $client->id,
            'order_number' => 'SO-' . uniqid(),
            'order_date' => now()->toDateString(),
            'status' => SaleOrder::STATUS_DRAFT,
            'total_ht' => 100,
            'total_discount' => 0,
            'tax_amount' => 0,
            'total_ttc' => 100,
            'created_by' => $user->id,
        ]);
    }

    private function makeDelivery(Tenant $tenant, User $user): DeliveryNote
    {
        $warehouse = Warehouse::create(['name' => 'WH ' . uniqid(), 'address' => 'Dakar', 'description' => '']);
        $client = Contact::create([
            'fullname' => 'Cl ' . uniqid(), 'phone_number' => '221' . random_int(700000000, 799999999),
            'address' => 'Dakar', 'type' => 'client',
        ]);

        $cat = Category::create(['name' => 'Cat ' . uniqid(), 'slug' => 'cat-' . uniqid()]);
        $unit = Units::create(['name' => 'U ' . uniqid(), 'code' => 'U' . random_int(100, 999)]);
        $product = Product::create([
            'category_id' => $cat->id, 'unit_id' => $unit->id,
            'name' => 'P ' . uniqid(), 'description' => '', 'price' => 100,
            'seuil_alert' => 0, 'is_active' => true, 'is_perishable' => false,
        ]);

        $saleOrder = SaleOrder::create([
            'contact_id' => $client->id,
            'order_number' => 'SO-' . uniqid(),
            'order_date' => now()->toDateString(),
            'status' => SaleOrder::STATUS_CONFIRMED,
            'total_ht' => 100,
            'total_discount' => 0,
            'tax_amount' => 0,
            'total_ttc' => 100,
            'created_by' => $user->id,
        ]);

        return DeliveryNote::create([
            'sale_order_id' => $saleOrder->id,
            'contact_id' => $client->id,
            'warehouse_id' => $warehouse->id,
            'delivery_number' => 'DN-' . uniqid(),
            'status' => 'draft',
            'delivery_date' => now()->toDateString(),
        ]);
    }

    private function makeReceipt(Tenant $tenant, User $user): GoodsReceipt
    {
        $warehouse = Warehouse::create(['name' => 'WH ' . uniqid(), 'address' => 'Dakar', 'description' => '']);
        $supplier = Contact::create([
            'fullname' => 'Sup ' . uniqid(), 'phone_number' => '221' . random_int(700000000, 799999999),
            'address' => 'Dakar', 'type' => 'supplier',
        ]);

        $purchaseOrder = PurchaseOrder::create([
            'contact_id' => $supplier->id,
            'purchase_number' => 'PO-' . uniqid(),
            'purchase_date' => now()->toDateString(),
            'status' => 'draft',
            'total_ht' => 100,
            'total_discount' => 0,
            'tax_amount' => 0,
            'total_ttc' => 100,
            'created_by' => $user->id,
        ]);

        return GoodsReceipt::create([
            'purchase_order_id' => $purchaseOrder->id,
            'contact_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'receipt_number' => 'GR-' . uniqid(),
            'status' => 'draft',
            'receipt_date' => now()->toDateString(),
        ]);
    }
}
