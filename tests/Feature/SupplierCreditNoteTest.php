<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSubscriptionAndPermissions;
use App\Models\Batch;
use App\Models\Category;
use App\Models\Contact;
use App\Models\GoodsReceipt;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Setting;
use App\Models\SupplierCreditNote;
use App\Models\SupplierReturn;
use App\Models\Tenant;
use App\Models\Units;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SupplierCreditNoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplier_return_on_unpaid_invoice_creates_credit_note_and_reduces_balance_without_wallet_impact(): void
    {
        $scenario = $this->scenario();
        $invoice = $this->createValidatedSupplierInvoice($scenario, 100000);

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('supplier-returns.store'), [
                'supplier_invoice_id' => $invoice->id,
                'contact_id' => $scenario['supplier']->id,
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

        $return = SupplierReturn::query()->where('tenant_id', $scenario['tenant']->id)->firstOrFail();

        $walletBefore = $scenario['wallet']->fresh()->current_balance;

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('supplier-returns.validate', $return))
            ->assertRedirect();

        $creditNote = SupplierCreditNote::query()->where('tenant_id', $scenario['tenant']->id)->firstOrFail();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->get(route('supplier-credit-notes.index'))
            ->assertOk()
            ->assertSee($creditNote->credit_note_number)
            ->assertSee('Avoirs fournisseur');

        $this->assertSame(8800, (int) $invoice->fresh()->balance);
        $this->assertSame($walletBefore, (int) $scenario['wallet']->fresh()->current_balance);
        $this->assertSame('applied', $creditNote->status);
        $this->assertSame(2200, (int) $creditNote->applied_amount);
        $this->assertSame(0, (int) $creditNote->remaining_amount);
        $this->assertDatabaseHas('inventory_movements', [
            'tenant_id' => $scenario['tenant']->id,
            'movement_type' => 'supplier_return_out',
            'quantity' => 2,
        ]);
    }

    public function test_supplier_return_on_paid_invoice_creates_available_credit_and_keeps_wallet_unchanged(): void
    {
        $scenario = $this->scenario();
        $invoice = $this->createValidatedSupplierInvoice($scenario, 100000);
        $paymentAmount = (int) $invoice->fresh()->balance;

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('payments.store', ['type' => 'suppliers']), [
                'invoice_id' => $invoice->id,
                'wallet_id' => $scenario['wallet']->id,
                'amount_paid' => $paymentAmount,
                'payment_date' => now()->toDateString(),
            ])
            ->assertRedirect();

        $walletBefore = $scenario['wallet']->fresh()->current_balance;

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('supplier-returns.store'), [
                'supplier_invoice_id' => $invoice->id,
                'contact_id' => $scenario['supplier']->id,
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

        $return = SupplierReturn::query()->where('tenant_id', $scenario['tenant']->id)->firstOrFail();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('supplier-returns.validate', $return))
            ->assertRedirect();

        $creditNote = SupplierCreditNote::query()->where('tenant_id', $scenario['tenant']->id)->firstOrFail();

        $this->assertSame($walletBefore, $scenario['wallet']->fresh()->current_balance);
        $this->assertSame(0, (int) $invoice->fresh()->balance);
        $this->assertSame('validated', $creditNote->status);
        $this->assertSame(3300, (int) $creditNote->remaining_amount);
    }

    public function test_supplier_return_without_invoice_does_not_touch_finance(): void
    {
        $scenario = $this->scenario();
        $receipt = $this->createValidatedGoodsReceipt($scenario);

        $walletBefore = $scenario['wallet']->fresh()->current_balance;

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('supplier-returns.store'), [
                'goods_receipt_id' => $receipt->id,
                'contact_id' => $scenario['supplier']->id,
                'warehouse_id' => $scenario['warehouse']->id,
                'return_date' => now()->toDateString(),
                'reason' => 'Retour sans facture',
                'items' => [
                    $receipt->items->first()->id => [
                        'quantity_returned' => 1,
                    ],
                ],
            ])
            ->assertRedirect();

        $return = SupplierReturn::query()->where('tenant_id', $scenario['tenant']->id)->firstOrFail();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('supplier-returns.validate', $return))
            ->assertRedirect();

        $this->assertDatabaseCount('supplier_credit_notes', 0);
        $this->assertSame($walletBefore, $scenario['wallet']->fresh()->current_balance);
    }

    public function test_supplier_credit_note_refund_updates_wallet_when_recorded(): void
    {
        $scenario = $this->scenario();
        $invoice = $this->createValidatedSupplierInvoice($scenario, 100000);
        $paymentAmount = (int) $invoice->fresh()->balance;

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('payments.store', ['type' => 'suppliers']), [
                'invoice_id' => $invoice->id,
                'wallet_id' => $scenario['wallet']->id,
                'amount_paid' => $paymentAmount,
                'payment_date' => now()->toDateString(),
            ])
            ->assertRedirect();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('supplier-returns.store'), [
                'supplier_invoice_id' => $invoice->id,
                'contact_id' => $scenario['supplier']->id,
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

        $return = SupplierReturn::query()->where('tenant_id', $scenario['tenant']->id)->firstOrFail();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('supplier-returns.validate', $return))
            ->assertRedirect();

        $creditNote = SupplierCreditNote::query()->where('tenant_id', $scenario['tenant']->id)->firstOrFail();
        $walletBefore = $scenario['wallet']->fresh()->current_balance;

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('supplier-credit-notes.refund', $creditNote), [
                'wallet_id' => $scenario['wallet']->id,
                'amount' => 3300,
                'note' => 'Remboursement fournisseur',
            ])
            ->assertRedirect();

        $this->assertSame($walletBefore + 3300, (int) $scenario['wallet']->fresh()->current_balance);
        $this->assertSame('refunded', $creditNote->fresh()->status);
    }

    private function scenario(): array
    {
        $slug = 'supplier-credit-'.uniqid();
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
        $supplier = Contact::create([
            'fullname' => 'Supplier '.uniqid(),
            'phone_number' => '22178'.random_int(1000000, 9999999),
            'address' => 'Dakar',
            'type' => 'supplier',
        ]);
        $wallet = Wallet::create([
            'name' => 'Caisse '.uniqid(),
            'code' => 'WAL'.random_int(100, 999),
            'identifier' => 'WAL-'.uniqid(),
            'initial_balance' => 20000,
            'current_balance' => 20000,
            'type' => 'bank',
        ]);

        return compact('tenant', 'user', 'product', 'warehouse', 'supplier', 'wallet');
    }

    private function createValidatedGoodsReceipt(array $scenario): GoodsReceipt
    {
        $purchaseOrder = PurchaseOrder::create([
            'contact_id' => $scenario['supplier']->id,
            'purchase_number' => 'PO-'.uniqid(),
            'purchase_date' => now()->toDateString(),
            'status' => 'draft',
            'total_ht' => 10000,
            'total_discount' => 0,
            'tax_amount' => 1000,
            'total_ttc' => 11000,
            'created_by' => $scenario['user']->id,
        ]);

        $purchaseOrder->items()->create([
            'product_id' => $scenario['product']->id,
            'warehouse_id' => $scenario['warehouse']->id,
            'quantity_ordered' => 10,
            'quantity_received' => 0,
            'quantity_remaining' => 10,
            'unit_cost_ht' => 1000,
            'discount_amount' => 0,
            'tax_rate' => 10,
            'tax_amount' => 1000,
            'subtotal_ht' => 10000,
            'total_ttc' => 11000,
            'expiration_date' => now()->addMonth()->toDateString(),
        ]);

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('purchase-orders.create-receipt', $purchaseOrder))
            ->assertRedirect();

        $receipt = GoodsReceipt::query()->where('tenant_id', $scenario['tenant']->id)->firstOrFail();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('goods-receipts.validate', $receipt))
            ->assertRedirect();

        return $receipt->fresh(['items']);
    }

    private function createValidatedSupplierInvoice(array $scenario, int $amount): Invoice
    {
        $receipt = $this->createValidatedGoodsReceipt($scenario);

        $purchaseOrder = $receipt->purchaseOrder()->firstOrFail();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('purchase-orders.create-supplier-invoice', $purchaseOrder))
            ->assertRedirect();

        $invoice = Invoice::query()->where('tenant_id', $scenario['tenant']->id)->where('type', 'supplier')->firstOrFail();

        $this->actingAs($scenario['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->patch(route('invoices.validate', ['type' => 'suppliers', 'invoice' => $invoice]))
            ->assertRedirect();

        return $invoice->fresh(['items.product', 'payments']);
    }
}
