<?php

namespace App\Services;

use App\Models\DocumentSequence;
use App\Models\Tenant;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DocumentNumberService
{
    public const DEFAULTS = [
        'quote' => ['prefix' => 'DEV', 'reset_period' => 'yearly', 'padding' => 4],
        'sale_order' => ['prefix' => 'SO', 'reset_period' => 'yearly', 'padding' => 4],
        'delivery_note' => ['prefix' => 'BL', 'reset_period' => 'yearly', 'padding' => 4],
        'customer_invoice' => ['prefix' => 'FAC', 'reset_period' => 'yearly', 'padding' => 4],
        'purchase_order' => ['prefix' => 'PO', 'reset_period' => 'yearly', 'padding' => 4],
        'goods_receipt' => ['prefix' => 'BR', 'reset_period' => 'yearly', 'padding' => 4],
        'supplier_invoice' => ['prefix' => 'FF', 'reset_period' => 'yearly', 'padding' => 4],
        'customer_return' => ['prefix' => 'RCL', 'reset_period' => 'yearly', 'padding' => 4],
        'supplier_return' => ['prefix' => 'RFL', 'reset_period' => 'yearly', 'padding' => 4],
        'payment' => ['prefix' => 'PAY', 'reset_period' => 'yearly', 'padding' => 4],
        'inventory' => ['prefix' => 'INV', 'reset_period' => 'yearly', 'padding' => 4],
        'transfer' => ['prefix' => 'TRF', 'reset_period' => 'yearly', 'padding' => 4],
        'stock_out' => ['prefix' => 'OUT', 'reset_period' => 'yearly', 'padding' => 4],
    ];

    public function generate(string $documentType, ?Tenant $tenant = null): string
    {
        $tenant = $this->resolveTenant($tenant);

        return DB::transaction(function () use ($documentType, $tenant) {
            $sequence = $this->lockSequence($documentType, $tenant);
            $sequence->current_number++;
            $sequence->updated_by = Auth::id();
            $sequence->save();

            return $this->formatNumber($sequence, $sequence->current_number);
        });
    }

    public function preview(string $documentType, ?Tenant $tenant = null): string
    {
        $tenant = $this->resolveTenant($tenant);
        $sequence = $this->ensureSequenceExists($documentType, $tenant);

        return $this->formatNumber($sequence, $sequence->current_number + 1);
    }

    public function ensureSequenceExists(string $documentType, Tenant $tenant): DocumentSequence
    {
        $defaults = $this->defaultsFor($documentType);
        $baseSequence = DocumentSequence::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('document_type', $documentType)
            ->latest('updated_at')
            ->first();

        $resetPeriod = $baseSequence?->reset_period ?? $defaults['reset_period'];
        $period = $this->resolvePeriod($resetPeriod);

        $sequence = DocumentSequence::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('document_type', $documentType)
            ->where('period_key', $period['period_key'])
            ->first();

        if ($sequence) {
            return $sequence;
        }

        return DocumentSequence::create([
            'tenant_id' => $tenant->id,
            'document_type' => $documentType,
            'prefix' => $baseSequence?->prefix ?? $defaults['prefix'],
            'current_number' => 0,
            'padding' => $baseSequence?->padding ?? $defaults['padding'],
            'year' => $period['year'],
            'period_key' => $period['period_key'],
            'reset_period' => $resetPeriod,
            'is_active' => $baseSequence?->is_active ?? true,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);
    }

    public function supportedDocumentTypes(): array
    {
        return array_keys(self::DEFAULTS);
    }

    private function lockSequence(string $documentType, Tenant $tenant): DocumentSequence
    {
        $defaults = $this->defaultsFor($documentType);
        $baseSequence = DocumentSequence::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('document_type', $documentType)
            ->latest('updated_at')
            ->first();

        $resetPeriod = $baseSequence?->reset_period ?? $defaults['reset_period'];
        $period = $this->resolvePeriod($resetPeriod);

        $sequence = DocumentSequence::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('document_type', $documentType)
            ->where('period_key', $period['period_key'])
            ->lockForUpdate()
            ->first();

        if ($sequence) {
            return $sequence;
        }

        try {
            DocumentSequence::create([
                'tenant_id' => $tenant->id,
                'document_type' => $documentType,
                'prefix' => $baseSequence?->prefix ?? $defaults['prefix'],
                'current_number' => 0,
                'padding' => $baseSequence?->padding ?? $defaults['padding'],
                'year' => $period['year'],
                'period_key' => $period['period_key'],
                'reset_period' => $resetPeriod,
                'is_active' => $baseSequence?->is_active ?? true,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
        } catch (QueryException $exception) {
            if (! $this->isUniqueConstraintViolation($exception)) {
                throw $exception;
            }
        }

        return DocumentSequence::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('document_type', $documentType)
            ->where('period_key', $period['period_key'])
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function resolveTenant(?Tenant $tenant = null): Tenant
    {
        if ($tenant) {
            return $tenant;
        }

        $tenant = Auth::user()?->tenant;

        if (! $tenant) {
            throw new RuntimeException('Aucun tenant disponible pour générer un numéro de document.');
        }

        return $tenant;
    }

    private function resolvePeriod(string $resetPeriod): array
    {
        return match ($resetPeriod) {
            'monthly' => [
                'period_key' => now()->format('Y-m'),
                'year' => (int) now()->format('Y'),
            ],
            'never' => [
                'period_key' => 'global',
                'year' => null,
            ],
            default => [
                'period_key' => now()->format('Y'),
                'year' => (int) now()->format('Y'),
            ],
        };
    }

    private function formatNumber(DocumentSequence $sequence, int $number): string
    {
        return sprintf(
            '%s/%s/%s',
            $sequence->prefix,
            now()->format('Y'),
            str_pad((string) $number, $sequence->padding, '0', STR_PAD_LEFT)
        );
    }

    private function defaultsFor(string $documentType): array
    {
        if (! isset(self::DEFAULTS[$documentType])) {
            throw new RuntimeException("Type de document non supporte: {$documentType}");
        }

        return self::DEFAULTS[$documentType];
    }

    private function isUniqueConstraintViolation(QueryException $exception): bool
    {
        $message = $exception->getMessage();

        return str_contains($message, 'UNIQUE constraint failed')
            || str_contains($message, 'Integrity constraint violation')
            || str_contains($message, 'Duplicate entry');
    }
}
