<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSubscriptionAndPermissions;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PaymentWalletCancellationTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_cancellation_keeps_payment_and_reverses_wallet(): void
    {
        $data = $this->createTenantPaymentScenario(total: 1000, walletBalance: 1000, amount: 1000);

        $this->actingAs($data['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->delete(route('payments.destroy', ['clients', $data['payment']]), [
                'cancellation_reason' => 'Erreur de saisie',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'id' => $data['payment']->id,
            'tenant_id' => $data['tenant']->id,
            'status' => 'cancelled',
            'cancellation_reason' => 'Erreur de saisie',
        ]);

        $this->assertDatabaseHas('wallets', [
            'id' => $data['wallet']->id,
            'current_balance' => 0,
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'tenant_id' => $data['tenant']->id,
            'wallet_id' => $data['wallet']->id,
            'payment_id' => $data['payment']->id,
            'transaction_type' => 'payment_cancel_reverse',
            'amount' => 1000,
            'balance_before' => 1000,
            'balance_after' => 0,
        ]);
    }

    public function test_payment_cancellation_recomputes_invoice_to_partial_and_unpaid(): void
    {
        $data = $this->createTenantPaymentScenario(total: 1000, walletBalance: 700, amount: 300, otherPaidAmount: 400);

        $this->actingAs($data['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->delete(route('payments.destroy', ['clients', $data['payment']]))
            ->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'id' => $data['invoice']->id,
            'balance' => 600,
            'status' => 'partial',
        ]);

        $otherPayment = Payment::where('invoice_id', $data['invoice']->id)
            ->where('status', 'completed')
            ->first();

        $this->actingAs($data['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->delete(route('payments.destroy', ['clients', $otherPayment]))
            ->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'id' => $data['invoice']->id,
            'balance' => 1000,
            'status' => 'unpaid',
        ]);
    }

    public function test_payment_cannot_be_cancelled_twice(): void
    {
        $data = $this->createTenantPaymentScenario(total: 1000, walletBalance: 1000, amount: 1000);

        $this->actingAs($data['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->delete(route('payments.destroy', ['clients', $data['payment']]))
            ->assertRedirect();

        $this->actingAs($data['user'])
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->delete(route('payments.destroy', ['clients', $data['payment']]))
            ->assertSessionHasErrors();

        $this->assertDatabaseCount('wallet_transactions', 1);
    }

    public function test_payment_creation_rejects_foreign_invoice_and_wallet(): void
    {
        $tenantA = $this->createTenant('tenant-a');
        $tenantB = $this->createTenant('tenant-b');
        $userA = $this->createUser($tenantA);
        $userB = $this->createUser($tenantB);

        $this->actingAs($userB);
        $contactB = Contact::create([
            'fullname' => 'Client B',
            'phone_number' => '221770000002',
            'address' => 'Dakar',
            'type' => 'client',
        ]);
        $invoiceB = Invoice::create([
            'contact_id' => $contactB->id,
            'invoice_number' => 'FAC-B',
            'invoice_date' => now(),
            'due_date' => now()->addDay(),
            'type' => 'client',
            'total_invoice' => 1000,
            'balance' => 1000,
            'status' => 'validated',
        ]);
        $walletB = Wallet::create([
            'name' => 'Caisse B',
            'code' => 'CB',
            'identifier' => 'CB-1',
            'initial_balance' => 0,
            'current_balance' => 0,
            'type' => 'bank',
        ]);

        $this->actingAs($userA)
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('payments.store', ['clients']), [
                'invoice_id' => $invoiceB->id,
                'wallet_id' => $walletB->id,
                'amount_paid' => 100,
                'payment_date' => now()->format('Y-m-d'),
            ])
            ->assertSessionHasErrors(['invoice_id', 'wallet_id']);
    }

    public function test_wallet_transfer_creates_audited_transactions(): void
    {
        $tenant = $this->createTenant('tenant-transfer');
        $user = $this->createUser($tenant);
        $this->actingAs($user);

        $fromWallet = Wallet::create([
            'name' => 'Caisse',
            'code' => 'CASH-T',
            'identifier' => 'CASH',
            'initial_balance' => 1000,
            'current_balance' => 1000,
            'type' => 'bank',
        ]);
        $toWallet = Wallet::create([
            'name' => 'Banque',
            'code' => 'BANK-T',
            'identifier' => 'BANK',
            'initial_balance' => 100,
            'current_balance' => 100,
            'type' => 'bank',
        ]);

        $this->actingAs($user)
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('wallet.transfert'), [
                'from_wallet_id' => $fromWallet->id,
                'to_wallet_id' => $toWallet->id,
                'amount' => 250,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('wallets', [
            'id' => $fromWallet->id,
            'current_balance' => 750,
        ]);
        $this->assertDatabaseHas('wallets', [
            'id' => $toWallet->id,
            'current_balance' => 350,
        ]);
        $this->assertDatabaseHas('wallet_transactions', [
            'tenant_id' => $tenant->id,
            'wallet_id' => $fromWallet->id,
            'user_id' => $user->id,
            'transaction_type' => 'wallet_transfer_out',
            'amount' => 250,
            'balance_before' => 1000,
            'balance_after' => 750,
        ]);
        $this->assertDatabaseHas('wallet_transactions', [
            'tenant_id' => $tenant->id,
            'wallet_id' => $toWallet->id,
            'user_id' => $user->id,
            'transaction_type' => 'wallet_transfer_in',
            'amount' => 250,
            'balance_before' => 100,
            'balance_after' => 350,
        ]);
    }

    public function test_wallet_transfer_rejects_foreign_wallet(): void
    {
        $tenantA = $this->createTenant('tenant-wallet-a');
        $tenantB = $this->createTenant('tenant-wallet-b');
        $userA = $this->createUser($tenantA);
        $userB = $this->createUser($tenantB);

        $this->actingAs($userA);
        $fromWallet = Wallet::create([
            'name' => 'Caisse A',
            'code' => 'CASH-A',
            'identifier' => 'A',
            'initial_balance' => 1000,
            'current_balance' => 1000,
            'type' => 'bank',
        ]);

        $this->actingAs($userB);
        $foreignWallet = Wallet::create([
            'name' => 'Caisse B',
            'code' => 'CASH-B',
            'identifier' => 'B',
            'initial_balance' => 0,
            'current_balance' => 0,
            'type' => 'bank',
        ]);

        $this->actingAs($userA)
            ->withoutMiddleware(CheckSubscriptionAndPermissions::class)
            ->post(route('wallet.transfert'), [
                'from_wallet_id' => $fromWallet->id,
                'to_wallet_id' => $foreignWallet->id,
                'amount' => 100,
            ])
            ->assertSessionHasErrors(['to_wallet_id']);
    }

    private function createTenantPaymentScenario(int $total, int $walletBalance, int $amount, int $otherPaidAmount = 0): array
    {
        $tenant = $this->createTenant('tenant-main-'.uniqid());
        $user = $this->createUser($tenant);

        $this->actingAs($user);

        $contact = Contact::create([
            'fullname' => 'Client Test',
            'phone_number' => '22177'.random_int(1000000, 9999999),
            'address' => 'Dakar',
            'type' => 'client',
        ]);

        $invoice = Invoice::create([
            'contact_id' => $contact->id,
            'invoice_number' => 'FAC-'.uniqid(),
            'invoice_date' => now(),
            'due_date' => now()->addDay(),
            'type' => 'client',
            'total_invoice' => $total,
            'balance' => max($total - $amount - $otherPaidAmount, 0),
            'status' => ($amount + $otherPaidAmount) >= $total ? 'paid' : 'partial',
        ]);

        $wallet = Wallet::create([
            'name' => 'Caisse',
            'code' => 'CAISSE'.random_int(1000, 9999),
            'identifier' => 'CASH',
            'initial_balance' => $walletBalance,
            'current_balance' => $walletBalance,
            'type' => 'bank',
        ]);

        if ($otherPaidAmount > 0) {
            Payment::create([
                'wallet_id' => $wallet->id,
                'invoice_id' => $invoice->id,
                'tenant_id' => $tenant->id,
                'contact_id' => $contact->id,
                'amount_paid' => $otherPaidAmount,
                'remaining_amount' => $total - $otherPaidAmount,
                'payment_date' => now(),
                'payment_type' => $wallet->name,
                'payment_source' => 'client',
                'status' => 'completed',
            ]);
        }

        $payment = Payment::create([
            'wallet_id' => $wallet->id,
            'invoice_id' => $invoice->id,
            'tenant_id' => $tenant->id,
            'contact_id' => $contact->id,
            'amount_paid' => $amount,
            'remaining_amount' => $invoice->balance,
            'payment_date' => now(),
            'payment_type' => $wallet->name,
            'payment_source' => 'client',
            'status' => 'completed',
        ]);

        return compact('tenant', 'user', 'contact', 'invoice', 'wallet', 'payment');
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
