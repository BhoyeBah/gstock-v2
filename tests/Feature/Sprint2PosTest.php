<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\CashSession;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\Units;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Warehouse;
use App\Services\CashSessionService;
use App\Services\InvoiceService;
use App\Services\PosService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class Sprint2PosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            \App\Http\Middleware\CheckSubscriptionAndPermissions::class,
            \App\Http\Middleware\CheckActiveUser::class,
        ]);
    }

    /**
     * Crée un tenant avec un utilisateur connecté et un catalogue de base.
     */
    private function makeContext(string $prefix = 'a'): array
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();
        $this->actingAs($user);

        $unit = Units::firstOrCreate(
            ['code' => strtoupper($prefix).Str::upper(Str::random(3))],
            ['name' => 'Unit '.$prefix]
        );

        $category = Category::create([
            'name' => 'Cat '.$prefix,
            'slug' => Str::slug('cat '.$prefix).'-'.Str::random(6),
        ]);

        $warehouse = Warehouse::create([
            'name' => 'WH '.$prefix,
            'address' => 'addr',
            'description' => 'desc',
            'manager_id' => null,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'name' => 'Prod '.strtoupper($prefix),
            'description' => 'd',
            'price' => 1000,
            'seuil_alert' => 1,
            'is_active' => true,
            'is_perishable' => false,
        ]);

        $client = Contact::create([
            'fullname' => 'Client '.$prefix,
            'phone_number' => '77'.random_int(1000000, 9999999),
            'address' => 'a',
            'type' => 'client',
        ]);

        $supplier = Contact::create([
            'fullname' => 'Supplier '.$prefix,
            'phone_number' => '78'.random_int(1000000, 9999999),
            'address' => 'a',
            'type' => 'supplier',
        ]);

        $wallet = Wallet::create([
            'name' => 'Caisse '.$prefix,
            'code' => 'C'.strtoupper(Str::random(5)),
            'identifier' => 'ID'.Str::random(5),
            'initial_balance' => 0,
            'current_balance' => 0,
            'type' => 'other',
            'is_active' => true,
        ]);

        return compact('tenant', 'user', 'warehouse', 'product', 'client', 'supplier', 'wallet');
    }

    /**
     * Injecte du stock réel via le flux fournisseur (crée les lots/batches).
     */
    private function seedStock(array $ctx, int $qty, int $unitPrice = 500): void
    {
        $service = app(InvoiceService::class);
        $invoice = $service->createInvoice([
            'contact_id' => $ctx['supplier']->id,
            'invoice_number' => null,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->toDateString(),
            'type' => 'supplier',
            'items' => [[
                'product_id' => $ctx['product']->id,
                'warehouse_id' => $ctx['warehouse']->id,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'discount' => 0,
            ]],
        ]);
        $invoice->load('items');
        $service->validateInvoice($invoice);
    }

    private function availableStock(array $ctx): int
    {
        return (int) Batch::where('product_id', $ctx['product']->id)
            ->where('warehouse_id', $ctx['warehouse']->id)
            ->sum('remaining');
    }

    public function test_pos_sale_creates_invoice_decrements_stock_and_records_full_payment(): void
    {
        $ctx = $this->makeContext();
        $this->seedStock($ctx, 10);

        $response = $this->post(route('pos.store'), [
            'warehouse_id' => $ctx['warehouse']->id,
            'contact_id' => $ctx['client']->id,
            'items' => [[
                'product_id' => $ctx['product']->id,
                'quantity' => 3,
                'unit_price' => 1000,
                'discount' => 0,
            ]],
            'payments' => [[
                'wallet_id' => $ctx['wallet']->id,
                'amount' => 3000,
            ]],
        ]);

        $response->assertRedirect();

        $invoice = Invoice::where('type', 'client')->first();
        $this->assertNotNull($invoice);
        $this->assertSame('paid', $invoice->status);
        $this->assertSame(3000, (int) $invoice->total_invoice);
        $this->assertSame(0, (int) $invoice->balance);

        // Stock décrémenté (10 - 3 = 7).
        $this->assertSame(7, $this->availableStock($ctx));

        // Encaissement enregistré + wallet crédité.
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount_paid' => 3000,
        ]);
        $this->assertSame(3000, (int) $ctx['wallet']->fresh()->current_balance);

        // Mouvement de stock "vente".
        $this->assertDatabaseHas('inventory_movements', [
            'invoice_id' => $invoice->id,
            'reason' => 'vente',
        ]);
    }

    public function test_pos_partial_payment_leaves_debt_for_client(): void
    {
        $ctx = $this->makeContext();
        $this->seedStock($ctx, 10);

        $this->post(route('pos.store'), [
            'warehouse_id' => $ctx['warehouse']->id,
            'contact_id' => $ctx['client']->id,
            'items' => [[
                'product_id' => $ctx['product']->id,
                'quantity' => 2,
                'unit_price' => 1000,
                'discount' => 0,
            ]],
            'payments' => [[
                'wallet_id' => $ctx['wallet']->id,
                'amount' => 500,
            ]],
        ]);

        $invoice = Invoice::where('type', 'client')->first();
        $this->assertNotNull($invoice);
        $this->assertSame('partial', $invoice->status);
        $this->assertSame(1500, (int) $invoice->balance);
    }

    public function test_pos_anonymous_partial_sale_is_rejected(): void
    {
        $ctx = $this->makeContext();
        $this->seedStock($ctx, 10);

        $response = $this->post(route('pos.store'), [
            'warehouse_id' => $ctx['warehouse']->id,
            'contact_id' => null,
            'items' => [[
                'product_id' => $ctx['product']->id,
                'quantity' => 2,
                'unit_price' => 1000,
                'discount' => 0,
            ]],
            'payments' => [[
                'wallet_id' => $ctx['wallet']->id,
                'amount' => 500,
            ]],
        ]);

        $response->assertSessionHas('error');
        $this->assertSame(0, Invoice::where('type', 'client')->count());
        // Stock inchangé (transaction annulée).
        $this->assertSame(10, $this->availableStock($ctx));
    }

    public function test_pos_anonymous_full_payment_is_allowed(): void
    {
        $ctx = $this->makeContext();
        $this->seedStock($ctx, 10);

        $this->post(route('pos.store'), [
            'warehouse_id' => $ctx['warehouse']->id,
            'contact_id' => null,
            'items' => [[
                'product_id' => $ctx['product']->id,
                'quantity' => 2,
                'unit_price' => 1000,
                'discount' => 0,
            ]],
            'payments' => [[
                'wallet_id' => $ctx['wallet']->id,
                'amount' => 2000,
            ]],
        ]);

        $invoice = Invoice::where('type', 'client')->first();
        $this->assertNotNull($invoice);
        $this->assertNull($invoice->contact_id);
        $this->assertSame('paid', $invoice->status);
    }

    public function test_pos_quantity_exceeding_stock_is_rejected(): void
    {
        $ctx = $this->makeContext();
        $this->seedStock($ctx, 1);

        $response = $this->post(route('pos.store'), [
            'warehouse_id' => $ctx['warehouse']->id,
            'contact_id' => $ctx['client']->id,
            'items' => [[
                'product_id' => $ctx['product']->id,
                'quantity' => 5,
                'unit_price' => 1000,
                'discount' => 0,
            ]],
            'payments' => [[
                'wallet_id' => $ctx['wallet']->id,
                'amount' => 5000,
            ]],
        ]);

        $response->assertSessionHas('error');
        $this->assertSame(0, Invoice::where('type', 'client')->count());
        $this->assertSame(1, $this->availableStock($ctx));
    }

    public function test_pos_product_search_is_tenant_scoped(): void
    {
        $ctxA = $this->makeContext('a');
        $this->seedStock($ctxA, 5);

        // Tenant B avec son propre produit.
        $ctxB = $this->makeContext('b');
        $this->seedStock($ctxB, 5);

        // On se reconnecte en tant qu'utilisateur du tenant A.
        $this->actingAs($ctxA['user']);

        $response = $this->getJson(route('pos.products', ['warehouse_id' => $ctxA['warehouse']->id]));
        $response->assertOk();

        $ids = collect($response->json())->pluck('id')->all();
        $this->assertContains($ctxA['product']->id, $ids);
        $this->assertNotContains($ctxB['product']->id, $ids);
    }

    public function test_cash_session_close_computes_expected_and_difference(): void
    {
        $ctx = $this->makeContext();
        $this->seedStock($ctx, 10);

        // Ouvre une caisse sur le wallet avec 1000 de fonds.
        $cashService = app(CashSessionService::class);
        $session = $cashService->open($ctx['wallet']->id, 1000);

        // Vente encaissée sur ce wallet -> rattachée à la session ouverte.
        app(PosService::class)->createSale([
            'warehouse_id' => $ctx['warehouse']->id,
            'contact_id' => $ctx['client']->id,
            'items' => [[
                'product_id' => $ctx['product']->id,
                'quantity' => 2,
                'unit_price' => 1000,
                'discount' => 0,
            ]],
            'payments' => [[
                'wallet_id' => $ctx['wallet']->id,
                'amount' => 2000,
            ]],
        ]);

        $session->refresh();
        $this->assertSame(2000, $session->collectedAmount());

        // Clôture : compté = 3000 (fonds 1000 + 2000). Écart 0.
        $cashService->close($session, 3000);
        $session->refresh();

        $this->assertSame(CashSession::STATUS_CLOSED, $session->status);
        $this->assertSame(3000, (int) $session->expected_amount);
        $this->assertSame(3000, (int) $session->counted_amount);
        $this->assertSame(0, (int) $session->difference);

        // Session sans encaissement : attendu 0, écart négatif si manquant.
        $session2 = $cashService->open($ctx['wallet']->id, 0);
        $cashService->close($session2, 0);
        $session2->refresh();
        $this->assertSame(0, (int) $session2->expected_amount);
        $this->assertSame(0, (int) $session2->difference);
    }

    public function test_daily_sales_report_shows_totals(): void
    {
        $ctx = $this->makeContext();
        $this->seedStock($ctx, 10);

        app(PosService::class)->createSale([
            'warehouse_id' => $ctx['warehouse']->id,
            'contact_id' => $ctx['client']->id,
            'items' => [[
                'product_id' => $ctx['product']->id,
                'quantity' => 2,
                'unit_price' => 1000,
                'discount' => 0,
            ]],
            'payments' => [[
                'wallet_id' => $ctx['wallet']->id,
                'amount' => 1500,
            ]],
        ]);

        $response = $this->get(route('reports.daily-sales', ['date' => now()->toDateString()]));
        $response->assertOk();
        $response->assertViewHas('totalSales', 2000);
        $response->assertViewHas('totalCollected', 1500);
        $response->assertViewHas('totalOutstanding', 500);
    }
}
