<?php

namespace App\Http\Requests;

use App\Models\Contact;
use App\Models\GoodsReceipt;
use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = $this->user()?->tenant_id;

        return [
            'supplier_invoice_id' => [
                'nullable',
                'uuid',
                Rule::exists('invoices', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)->where('type', Invoice::TYPE_SUPPLIER)),
            ],
            'goods_receipt_id' => [
                'nullable',
                'uuid',
                Rule::exists('goods_receipts', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'contact_id' => [
                'required',
                'uuid',
                Rule::exists('contacts', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)->where('type', Contact::TYPE_SUPPLIER)),
            ],
            'warehouse_id' => [
                'nullable',
                'uuid',
                Rule::exists('warehouses', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'return_date' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.quantity_returned' => ['required', 'integer', 'min:0'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                $data = $this->all();

                if (empty($data['supplier_invoice_id']) && empty($data['goods_receipt_id'])) {
                    $validator->errors()->add('source', 'Veuillez sélectionner une facture fournisseur ou un bon de réception source.');
                }

                $items = $data['items'] ?? [];
                $hasQuantity = false;

                foreach ($items as $item) {
                    if ((int) ($item['quantity_returned'] ?? 0) > 0) {
                        $hasQuantity = true;
                        break;
                    }
                }

                if (! $hasQuantity) {
                    $validator->errors()->add('items', 'Ajoutez au moins une ligne avec une quantité retournée supérieure à zéro.');
                }
            },
        ];
    }
}
