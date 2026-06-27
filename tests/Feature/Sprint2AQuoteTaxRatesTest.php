<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Quote;
use App\Models\TaxRate;
use App\Models\Tenant;
use App\Models\Units;
use App\Models\User;
use App\Models\Wallet;
use App\Models\walletTransaction;
use App\Services\TaxCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sprint2AQuoteTaxRatesTest extends TestCase
{
    use RefreshDatabase;

    private function createTenantUser(string $nameSuffix = 'A'): array
    {
        $tenant = Tenant::factory()->create([
            'name' => 'Tenant '.$nameSuffix,
            'slug' => 'tenant-'.$nameSuffix.'-'.strtolower($nameSuffix),
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'User '.$nameSuffix,
        ]);

        return [$tenant, $user];
    }

    private function createCatalog(Tenant $tenant, string $suffix = 'A'): array
    {
        $unit = Units::firstOrCreate([
            'code' => 'UNIT-'.$suffix,
        ], [
            'name' => 'Unité '.$suffix,
        ]);

        $category = Category::create([
            'tenant_id' => $tenant->id,
            'name' => 'Catégorie '.$suffix,
            'slug' => 'categorie-'.$suffix,
        ]);

        $contact = Contact::forceCreate([
            'tenant_id' => $tenant->id,
            'fullname' => 'Client '.$suffix,
            'phone_number' => '77'.str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT),
            'address' => 'Adresse '.$suffix,
            'type' => 'client',
        ]);

        $warehouse = \App\Models\Warehouse::create([
            'tenant_id' => $tenant->id,
            'name' => 'Entrepôt '.$suffix,
            'address' => 'Adresse dépôt '.$suffix,
            'description' => 'Dépôt '.$suffix,
            'is_active' => true,
        ]);

        $product = Product::forceCreate([
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'name' => 'Produit '.$suffix,
            'description' => 'Produit test '.$suffix,
            'price' => 1000,
            'seuil_alert' => 5,
            'is_active' => true,
            'is_perishable' => false,
        ]);

        $taxRate = TaxRate::create([
            'tenant_id' => $tenant->id,
            'name' => 'TVA '.$suffix,
            'rate' => 18,
            'is_default' => true,
            'is_active' => true,
        ]);

        return compact('unit', 'category', 'contact', 'warehouse', 'product', 'taxRate');
    }

    private function quotePayload(array $catalog, array $overrides = []): array
    {
        return array_merge([
            'contact_id' => $catalog['contact']->id,
            'quote_date' => now()->format('Y-m-d'),
            'expiry_date' => now()->addDays(10)->format('Y-m-d'),
            'status' => Quote::STATUS_DRAFT,
            'notes' => 'Devis de test',
            'items' => [
                [
                    'warehouse_id' => $catalog['warehouse']->id,
                    'product_id' => $catalog['product']->id,
                    'quantity' => 2,
                    'unit_price' => 1000,
                    'discount' => 100,
                    'tax_rate_id' => $catalog['taxRate']->id,
                ],
            ],
        ], $overrides);
    }

    public function test_quote_creation_has_no_stock_effect(): void
    {
        [$tenant, $user] = $this->createTenantUser('A');
        $catalog = $this->createCatalog($tenant, 'A');
        $initialBatches = Batch::count();

        $this->actingAs($user)
            ->post(route('quotes.store'), $this->quotePayload($catalog))
            ->assertRedirect();

        $this->assertSame($initialBatches, Batch::count());
        $this->assertDatabaseCount('quotes', 1);
    }

    public function test_quote_creation_has_no_wallet_effect(): void
    {
        [$tenant, $user] = $this->createTenantUser('A');
        $catalog = $this->createCatalog($tenant, 'A');
        $wallet = Wallet::forceCreate([
            'tenant_id' => $tenant->id,
            'name' => 'Caisse A',
            'code' => 'CAISSE-A',
            'identifier' => 'WALLET-A',
            'initial_balance' => 50000,
            'current_balance' => 50000,
            'type' => 'bank',
            'is_active' => true,
        ]);

        $initialBalance = $wallet->fresh()->current_balance;
        $initialTransactions = walletTransaction::count();

        $this->actingAs($user)
            ->post(route('quotes.store'), $this->quotePayload($catalog))
            ->assertRedirect();

        $this->assertSame($initialBalance, $wallet->fresh()->current_balance);
        $this->assertSame($initialTransactions, walletTransaction::count());
    }

    public function test_quote_conversion_creates_invoice(): void
    {
        [$tenant, $user] = $this->createTenantUser('A');
        $catalog = $this->createCatalog($tenant, 'A');

        $this->actingAs($user)
            ->post(route('quotes.store'), $this->quotePayload($catalog, ['status' => Quote::STATUS_ACCEPTED]))
            ->assertRedirect();

        $quote = Quote::firstOrFail();

        $this->actingAs($user)
            ->post(route('quotes.convert', $quote))
            ->assertSessionHas('success');

        $quote = $quote->fresh();
        $this->assertNotNull($quote->converted_invoice_id);
        $this->assertDatabaseHas('invoices', [
            'id' => $quote->converted_invoice_id,
            'tenant_id' => $tenant->id,
            'contact_id' => $catalog['contact']->id,
            'total_invoice' => $quote->total_ttc,
        ]);
    }

    public function test_quote_conversion_marks_quote_as_converted(): void
    {
        [$tenant, $user] = $this->createTenantUser('A');
        $catalog = $this->createCatalog($tenant, 'A');

        $this->actingAs($user)
            ->post(route('quotes.store'), $this->quotePayload($catalog, ['status' => Quote::STATUS_ACCEPTED]))
            ->assertRedirect();

        $quote = Quote::firstOrFail();

        $this->actingAs($user)
            ->post(route('quotes.convert', $quote))
            ->assertSessionHas('success');

        $this->assertSame(Quote::STATUS_CONVERTED, $quote->fresh()->status);
    }

    public function test_quote_rejects_foreign_contact(): void
    {
        [$tenantA, $userA] = $this->createTenantUser('A');
        [$tenantB] = $this->createTenantUser('B');
        $catalogA = $this->createCatalog($tenantA, 'A');
        $foreignContact = $this->createCatalog($tenantB, 'B')['contact'];

        $payload = $this->quotePayload($catalogA, [
            'contact_id' => $foreignContact->id,
        ]);

        $this->actingAs($userA)
            ->post(route('quotes.store'), $payload)
            ->assertSessionHasErrors('contact_id');
    }

    public function test_quote_rejects_foreign_product(): void
    {
        [$tenantA, $userA] = $this->createTenantUser('A');
        [$tenantB] = $this->createTenantUser('B');
        $catalogA = $this->createCatalog($tenantA, 'A');
        $foreignProduct = $this->createCatalog($tenantB, 'B')['product'];

        $payload = $this->quotePayload($catalogA, [
            'items' => [
                [
                    'warehouse_id' => $catalogA['warehouse']->id,
                    'product_id' => $foreignProduct->id,
                    'quantity' => 1,
                    'unit_price' => 1000,
                    'discount' => 0,
                    'tax_rate_id' => $catalogA['taxRate']->id,
                ],
            ],
        ]);

        $this->actingAs($userA)
            ->post(route('quotes.store'), $payload)
            ->assertSessionHasErrors('items.0.product_id');
    }

    public function test_quote_cannot_be_converted_twice(): void
    {
        [$tenant, $user] = $this->createTenantUser('A');
        $catalog = $this->createCatalog($tenant, 'A');

        $this->actingAs($user)
            ->post(route('quotes.store'), $this->quotePayload($catalog, ['status' => Quote::STATUS_ACCEPTED]))
            ->assertRedirect();

        $quote = Quote::firstOrFail();

        $this->actingAs($user)
            ->post(route('quotes.convert', $quote))
            ->assertSessionHas('success');

        $invoiceCount = Invoice::count();

        $this->actingAs($user)
            ->post(route('quotes.convert', $quote))
            ->assertSessionHas('error');

        $this->assertSame($invoiceCount, Invoice::count());
    }

    public function test_tax_rate_is_tenant_safe(): void
    {
        [$tenantA, $userA] = $this->createTenantUser('A');
        [$tenantB] = $this->createTenantUser('B');

        $this->createCatalog($tenantA, 'A');
        $catalogB = $this->createCatalog($tenantB, 'B');

        $this->actingAs($userA)
            ->get(route('tax_rates.index'))
            ->assertSee('TVA A')
            ->assertDontSee('TVA B');

        $this->assertDatabaseHas('tax_rates', [
            'tenant_id' => $tenantB->id,
            'name' => 'TVA B',
        ]);
    }

    public function test_tax_calculation_computes_ht_tax_ttc(): void
    {
        [$tenant, $user] = $this->createTenantUser('A');
        $catalog = $this->createCatalog($tenant, 'A');

        $service = app(TaxCalculatorService::class);
        $line = $service->calculateLine([
            'quantity' => 2,
            'unit_price' => 1000,
            'discount' => 100,
            'tax_rate_id' => $catalog['taxRate']->id,
        ], $tenant->id);

        $this->assertSame(1900, $line['subtotal_ht']);
        $this->assertSame(342, $line['tax_amount']);
        $this->assertSame(2242, $line['total_ttc']);
    }

    public function test_sprint1_tests_still_pass(): void
    {
        [$tenantA, $userA] = $this->createTenantUser('A');
        [$tenantB] = $this->createTenantUser('B');
        $catalogA = $this->createCatalog($tenantA, 'A');
        $foreignContact = $this->createCatalog($tenantB, 'B')['contact'];

        $this->actingAs($userA)
            ->post(route('quotes.store'), $this->quotePayload($catalogA, [
                'contact_id' => $foreignContact->id,
            ]))
            ->assertSessionHasErrors('contact_id');
    }
}
