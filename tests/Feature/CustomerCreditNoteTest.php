<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSubscriptionAndPermissions;
use App\Models\Batch;
use App\Models\Category;
use App\Models\Contact;
use App\Models\CustomerCreditNote;
use App\Models\CustomerReturn;
use App\Models\DeliveryNote;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\SaleOrder;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\Units;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerCreditNoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_return_on_unpaid_invoice_creates_credit_note_and_reduces_balance_without_wallet_impact(): void
    {
        $scenario = $this->scenario();
        $invoice = $this->createValidatedCustomerInvoice($scenario);

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->get(route('invoices.show', ['type' => 'clients', 'invoice' => $invoice->id]))
            ->assertOk()
            ->assertSee('Appliquer un avoir');

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('customer-returns.store'), [
                'invoice_id' => $invoice->id,
                'contact_id' => $scenario['client']->id,
                'warehouse_id' => $scenario['warehouse']->id,
                'return_date' => now()->toDateString(),
                'reason' => 'Produit non conforme',
                'items' => [
                    $invoice->items->first()->id => [
                        'quantity_returned' => 2,
                    ],
                ],
            ])
            ->assertRedirect();

        $return = CustomerReturn::query()->where('tenant_id', $scenario['tenant']->id)->firstOrFail();
        $walletBefore = $scenario['wallet']->fresh()->current_balance;

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('customer-returns.validate', $return))
            ->assertRedirect();

        $creditNote = CustomerCreditNote::query()->where('tenant_id', $scenario['tenant']->id)->firstOrFail();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->get(route('customer-credit-notes.index'))
            ->assertOk()
            ->assertSee($creditNote->credit_note_number)
            ->assertSee('Avoirs client');

        $this->assertSame(6600, (int) $invoice->fresh()->balance);
        $this->assertSame($walletBefore, (int) $scenario['wallet']->fresh()->current_balance);
        $this->assertSame('applied', $creditNote->status);
        $this->assertSame(2200, (int) $creditNote->applied_amount);
        $this->assertSame(0, (int) $creditNote->remaining_amount);
        $this->assertDatabaseHas('inventory_movements', [
            'tenant_id' => $scenario['tenant']->id,
            'movement_type' => 'customer_return_in',
            'quantity' => 2,
        ]);
    }

    public function test_customer_return_on_paid_invoice_creates_available_credit_and_keeps_wallet_unchanged(): void
    {
        $scenario = $this->scenario();
        $invoice = $this->createValidatedCustomerInvoice($scenario);
        $paymentAmount = (int) $invoice->fresh()->balance;

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('payments.store', ['type' => 'clients']), [
                'invoice_id' => $invoice->id,
                'wallet_id' => $scenario['wallet']->id,
                'amount_paid' => $paymentAmount,
                'payment_date' => now()->toDateString(),
            ])
            ->assertRedirect();

        $walletBefore = $scenario['wallet']->fresh()->current_balance;

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('customer-returns.store'), [
                'invoice_id' => $invoice->id,
                'contact_id' => $scenario['client']->id,
                'warehouse_id' => $scenario['warehouse']->id,
                'return_date' => now()->toDateString(),
                'reason' => 'Produit déjà payé',
                'items' => [
                    $invoice->items->first()->id => [
                        'quantity_returned' => 3,
                    ],
                ],
            ])
            ->assertRedirect();

        $return = CustomerReturn::query()->where('tenant_id', $scenario['tenant']->id)->firstOrFail();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('customer-returns.validate', $return))
            ->assertRedirect();

        $creditNote = CustomerCreditNote::query()->where('tenant_id', $scenario['tenant']->id)->firstOrFail();

        $this->assertSame($walletBefore, $scenario['wallet']->fresh()->current_balance);
        $this->assertSame(0, (int) $invoice->fresh()->balance);
        $this->assertSame('validated', $creditNote->status);
        $this->assertSame(3300, (int) $creditNote->remaining_amount);
    }

    public function test_customer_credit_note_refund_updates_wallet_when_recorded(): void
    {
        $scenario = $this->scenario();
        $invoice = $this->createValidatedCustomerInvoice($scenario);
        $paymentAmount = (int) $invoice->fresh()->balance;

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('payments.store', ['type' => 'clients']), [
                'invoice_id' => $invoice->id,
                'wallet_id' => $scenario['wallet']->id,
                'amount_paid' => $paymentAmount,
                'payment_date' => now()->toDateString(),
            ])
            ->assertRedirect();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('customer-returns.store'), [
                'invoice_id' => $invoice->id,
                'contact_id' => $scenario['client']->id,
                'warehouse_id' => $scenario['warehouse']->id,
                'return_date' => now()->toDateString(),
                'reason' => 'Retour avec remboursement',
                'items' => [
                    $invoice->items->first()->id => [
                        'quantity_returned' => 3,
                    ],
                ],
            ])
            ->assertRedirect();

        $return = CustomerReturn::query()->where('tenant_id', $scenario['tenant']->id)->firstOrFail();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('customer-returns.validate', $return))
            ->assertRedirect();

        $creditNote = CustomerCreditNote::query()->where('tenant_id', $scenario['tenant']->id)->firstOrFail();
        $walletBefore = $scenario['wallet']->fresh()->current_balance;

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('customer-credit-notes.refund', $creditNote), [
                'wallet_id' => $scenario['wallet']->id,
                'amount' => 3300,
                'note' => 'Remboursement client',
            ])
            ->assertRedirect();

        $this->assertSame($walletBefore - 3300, (int) $scenario['wallet']->fresh()->current_balance);
        $this->assertSame('refunded', $creditNote->fresh()->status);
    }

    public function test_customer_credit_note_can_be_applied_to_another_open_invoice(): void
    {
        $scenario = $this->scenario();
        $invoiceOne = $this->createValidatedCustomerInvoice($scenario);

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('payments.store', ['type' => 'clients']), [
                'invoice_id' => $invoiceOne->id,
                'wallet_id' => $scenario['wallet']->id,
                'amount_paid' => 5000,
                'payment_date' => now()->toDateString(),
            ])
            ->assertRedirect();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('customer-returns.store'), [
                'invoice_id' => $invoiceOne->id,
                'contact_id' => $scenario['client']->id,
                'warehouse_id' => $scenario['warehouse']->id,
                'return_date' => now()->toDateString(),
                'reason' => 'Avoir à réutiliser',
                'items' => [
                    $invoiceOne->items->first()->id => [
                        'quantity_returned' => 8,
                    ],
                ],
            ])
            ->assertRedirect();

        $return = CustomerReturn::query()->where('tenant_id', $scenario['tenant']->id)->firstOrFail();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('customer-returns.validate', $return))
            ->assertRedirect();

        $creditNote = CustomerCreditNote::query()->where('tenant_id', $scenario['tenant']->id)->firstOrFail();

        $this->assertSame(0, (int) $invoiceOne->fresh()->balance);
        $this->assertSame(5000, (int) $creditNote->fresh()->remaining_amount);
        $this->assertSame('partially_applied', $creditNote->fresh()->status);

        \App\Models\Batch::create([
            'warehouse_id' => $scenario['warehouse']->id,
            'product_id' => $scenario['product']->id,
            'unit_price' => 700,
            'quantity' => 2,
            'remaining' => 2,
            'benefit' => 0,
            'expiration_date' => null,
            'origin' => 'purchase',
        ]);

        $saleOrder = app(\App\Services\SaleOrderService::class)->create([
            'contact_id' => $scenario['client']->id,
            'order_date' => now()->toDateString(),
            'notes' => 'Facture à régler avec avoir',
            'items' => [
                [
                    'product_id' => $scenario['product']->id,
                    'warehouse_id' => $scenario['warehouse']->id,
                    'quantity_ordered' => 2,
                    'unit_price_ht' => 1000,
                    'discount_amount' => 0,
                ],
            ],
        ], $scenario['user']);

        $saleOrder = app(\App\Services\SaleOrderService::class)->confirm($saleOrder);
        $invoiceTwo = app(\App\Services\SaleOrderConversionService::class)->toInvoice($saleOrder);
        app(\App\Services\InvoiceService::class)->validateInvoice($invoiceTwo);

        $invoiceTwo = $invoiceTwo->fresh(['items', 'contact']);

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->get(route('invoices.show', ['type' => 'clients', 'invoice' => $invoiceTwo->id]))
            ->assertOk()
            ->assertSee('Appliquer un avoir');

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->patch(route('invoices.applyCreditNote', ['type' => 'clients', 'invoice' => $invoiceTwo]), [
                'credit_note_id' => $creditNote->id,
                'amount' => 2200,
                'note' => 'Règlement par avoir',
            ])
            ->assertRedirect();

        $this->assertSame(0, (int) $invoiceTwo->fresh()->balance);
        $this->assertSame('credited', $invoiceTwo->fresh()->status);
        $this->assertSame(2800, (int) $creditNote->fresh()->remaining_amount);
        $this->assertSame(6000, (int) $creditNote->fresh()->applied_amount);
    }

    private function scenario(): array
    {
        $slug = 'customer-credit-'.uniqid();
        $tenant = Tenant::create(['name' => $slug, 'slug' => $slug, 'is_active' => true]);
        $user = User::create([
            'name' => 'User '.$slug,
            'email' => $slug.'@test.com',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant->id,
            'is_active' => true,
            'is_owner' => true,
        ]);
        $this->actingAs($user);

        Setting::create(['currency' => 'FCFA', 'tva' => 10]);

        $category = Category::create(['name' => 'Cat '.uniqid(), 'slug' => 'cat-'.uniqid()]);
        $unit = Units::create(['name' => 'Piece '.uniqid(), 'code' => 'PC'.random_int(100, 999)]);
        $product = Product::create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'name' => 'Prod '.uniqid(),
            'description' => 'Produit test',
            'price' => 1000,
            'seuil_alert' => 2,
            'is_active' => true,
            'is_perishable' => false,
        ]);
        $warehouse = Warehouse::create(['name' => 'WH '.uniqid(), 'address' => 'Dakar', 'description' => '']);
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
            'initial_balance' => 20000,
            'current_balance' => 20000,
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

        return compact('tenant', 'user', 'product', 'warehouse', 'client', 'wallet', 'batchA', 'batchB');
    }

    private function createValidatedCustomerInvoice(array $scenario): Invoice
    {
        $saleOrder = app(\App\Services\SaleOrderService::class)->create([
            'contact_id' => $scenario['client']->id,
            'order_date' => now()->toDateString(),
            'notes' => 'Commande test',
            'items' => [
                [
                    'product_id' => $scenario['product']->id,
                    'warehouse_id' => $scenario['warehouse']->id,
                    'quantity_ordered' => 8,
                    'unit_price_ht' => 1000,
                    'discount_amount' => 0,
                ],
            ],
        ], $scenario['user']);

        $saleOrder = app(\App\Services\SaleOrderService::class)->confirm($saleOrder);
        $invoice = app(\App\Services\SaleOrderConversionService::class)->toInvoice($saleOrder);
        app(\App\Services\InvoiceService::class)->validateInvoice($invoice);

        return $invoice->fresh(['items.product', 'payments']);
    }
}
