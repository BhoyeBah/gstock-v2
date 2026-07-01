<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuoteService
{
    public function __construct(private readonly DocumentNumberService $documentNumberService)
    {
    }

    public function create(array $data, $user): Quote
    {
        return DB::transaction(function () use ($data, $user) {
            $tenantId = $user->tenant_id;
            $contact = $this->resolveContact($tenantId, $data['contact_id']);
            $taxRate = (float) (Setting::query()->where('tenant_id', $tenantId)->value('tva') ?? 0);
            $items = $this->normalizeItems($data['items'], $tenantId, $taxRate);

            $quote = Quote::create([
                'contact_id' => $contact->id,
                'quote_number' => $this->documentNumberService->generate('quote', $user->tenant),
                'quote_date' => $data['quote_date'],
                'valid_until' => $data['valid_until'] ?? null,
                'expiry_date' => $data['valid_until'] ?? null,
                'status' => $data['status'] ?? Quote::STATUS_DRAFT,
                'total_ht' => collect($items)->sum('subtotal_ht'),
                'subtotal_ht' => collect($items)->sum('subtotal_ht'),
                'total_discount' => collect($items)->sum('discount_amount'),
                'tax_amount' => collect($items)->sum('tax_amount'),
                'tax_total' => collect($items)->sum('tax_amount'),
                'total_ttc' => collect($items)->sum('total_ttc'),
                'created_by' => $user->id,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $item['quote_id'] = $quote->id;
                QuoteItem::create($item);
            }

            return $quote->fresh(['items.product', 'contact']);
        });
    }

    public function update(Quote $quote, array $data, $user): Quote
    {
        return DB::transaction(function () use ($quote, $data, $user) {
            if ($quote->status === Quote::STATUS_CONVERTED) {
                throw ValidationException::withMessages([
                    'quote' => 'Un devis converti ne peut plus être modifié.',
                ]);
            }

            $taxRate = (float) (Setting::query()->where('tenant_id', $user->tenant_id)->value('tva') ?? 0);
            $items = $this->normalizeItems($data['items'], $user->tenant_id, $taxRate);

            $quote->update([
                'contact_id' => $data['contact_id'],
                'quote_date' => $data['quote_date'],
                'valid_until' => $data['valid_until'] ?? null,
                'expiry_date' => $data['valid_until'] ?? null,
                'status' => $data['status'] ?? $quote->status,
                'total_ht' => collect($items)->sum('subtotal_ht'),
                'subtotal_ht' => collect($items)->sum('subtotal_ht'),
                'total_discount' => collect($items)->sum('discount_amount'),
                'tax_amount' => collect($items)->sum('tax_amount'),
                'tax_total' => collect($items)->sum('tax_amount'),
                'total_ttc' => collect($items)->sum('total_ttc'),
                'notes' => $data['notes'] ?? null,
            ]);

            $quote->items()->delete();

            foreach ($items as $item) {
                $quote->items()->create($item);
            }

            return $quote->fresh(['items.product', 'contact']);
        });
    }

    public function send(Quote $quote): Quote
    {
        $this->ensureCanTransition($quote, ['draft']);
        $quote->update(['status' => Quote::STATUS_SENT]);
        return $quote;
    }

    public function accept(Quote $quote): Quote
    {
        $this->ensureCanTransition($quote, ['draft', 'sent']);
        $quote->update(['status' => Quote::STATUS_ACCEPTED]);
        return $quote;
    }

    public function reject(Quote $quote): Quote
    {
        $this->ensureCanTransition($quote, ['draft', 'sent', 'accepted']);
        $quote->update(['status' => Quote::STATUS_REJECTED]);
        return $quote;
    }

    public function cancel(Quote $quote): Quote
    {
        $this->ensureCanTransition($quote, ['draft', 'sent', 'accepted']);
        $quote->update(['status' => Quote::STATUS_CANCELLED]);
        return $quote;
    }

    private function ensureCanTransition(Quote $quote, array $allowedStatuses): void
    {
        if ($quote->tenant_id !== auth()->user()?->tenant_id) {
            throw ValidationException::withMessages([
                'quote' => 'Ce devis appartient à un autre tenant.',
            ]);
        }

        if (! in_array($quote->status, $allowedStatuses, true)) {
            throw ValidationException::withMessages([
                'quote' => 'Cette action n’est pas autorisée dans l’état actuel du devis.',
            ]);
        }
    }

    private function resolveContact(string $tenantId, string $contactId): Contact
    {
        return Contact::query()
            ->where('tenant_id', $tenantId)
            ->where('type', 'client')
            ->whereKey($contactId)
            ->firstOrFail();
    }

    private function normalizeItems(array $items, string $tenantId, float $taxRate): array
    {
        $rows = [];

        foreach ($items as $item) {
            $product = Product::query()
                ->where('tenant_id', $tenantId)
                ->whereKey($item['product_id'])
                ->firstOrFail();

            $quantity = (int) $item['quantity'];
            $unitPrice = (int) ($item['unit_price_ht'] ?? $item['unit_price'] ?? 0);
            $discount = max(0, (int) ($item['discount_amount'] ?? $item['discount'] ?? 0));
            $subtotal = max(($unitPrice * $quantity) - $discount, 0);
            $taxAmount = (int) round($subtotal * $taxRate / 100);

            $rows[] = [
                'product_id' => $product->id,
                'warehouse_id' => $item['warehouse_id'] ?? null,
                'quantity' => $quantity,
                'unit_price_ht' => $unitPrice,
                'unit_price' => $unitPrice,
                'discount_amount' => $discount,
                'discount' => $discount,
                'subtotal_ht' => $subtotal,
                'tax_id' => $item['tax_id'] ?? null,
                'tax_rate_id' => $item['tax_rate_id'] ?? null,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_ttc' => $subtotal + $taxAmount,
            ];
        }

        if (empty($rows)) {
            throw ValidationException::withMessages(['items' => 'Ajoute au moins une ligne.']);
        }

        return $rows;
    }
}
