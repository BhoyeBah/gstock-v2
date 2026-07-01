<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Contact;
use App\Models\DocumentSequence;
use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\Permission;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Role;
use App\Models\Setting;
use App\Models\StockOut;
use App\Models\StockTransfert;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Units;
use App\Models\Wallet;
use App\Models\Warehouse;
use App\Services\DocumentNumberService;
use App\Services\InvoiceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::updateOrCreate(
            ['slug' => 'mamadou-bhoye'],
            [
                'name' => 'Mamadou Bhoye',
                'email' => 'contact@mamadou-bhoye.com',
                'phone' => '+221700000123',
                'address' => 'Dakar, Sénégal',
                'is_active' => true,
            ]
        );

        $user = User::updateOrCreate(
            ['email' => 'contact@mamadou-bhoye.com'],
            [
                'name' => 'Mamadou Bhoye',
                'password' => Hash::make('passer1234'),
                'phone' => '+221700000123',
                'tenant_id' => $tenant->id,
                'is_owner' => true,
                'is_active' => true,
                'is_superadmin' => false,
            ]
        );

        Auth::login($user);

        $allPermissionIds = Permission::query()->pluck('id')->all();
        $role = Role::firstOrNew([
            'name' => 'Administrateur',
            'guard_name' => 'web',
            'tenant_id' => $tenant->id,
        ]);
        $role->save();
        $role->syncPermissions($allPermissionIds);

        $user->syncRoles([$role]);
        $user->syncPermissions($allPermissionIds);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $plan = Plan::query()
            ->where('slug', 'admin')
            ->first()
            ?? Plan::query()->where('is_active', true)->orderByDesc('price')->first()
            ?? Plan::query()->first();

        if ($plan) {
            Subscription::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                ],
                [
                    'tenant_id' => $tenant->id,
                    'amount_paid' => $plan->price ?? 0,
                    'payment_method' => 'seed',
                    'starts_at' => now()->subDay(),
                    'ends_at' => now()->addMonths(12),
                    'is_active' => true,
                ]
            );
        }

        Setting::updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'currency' => 'FCFA',
                'tva' => 18.00,
            ]
        );

        $mainWarehouse = Warehouse::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'name' => 'Entrepôt principal',
            ],
            [
                'address' => 'Centre-ville, Dakar',
                'description' => 'Stock central du tenant de démonstration',
                'manager_id' => $user->id,
            ]
        );

        $secondaryWarehouse = Warehouse::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'name' => 'Entrepôt secondaire',
            ],
            [
                'address' => 'Almadies, Dakar',
                'description' => 'Entrepôt de réserve pour les transferts',
                'manager_id' => $user->id,
            ]
        );

        $foodCategory = Category::updateOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'alimentaire'],
            [
                'name' => 'Alimentaire',
            ]
        );

        $hygieneCategory = Category::updateOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'hygiene'],
            [
                'name' => 'Hygiène',
            ]
        );

        $pieceUnit = Units::firstOrCreate(
            ['code' => 'pcs'],
            ['name' => 'Piece']
        );

        $boxUnit = Units::firstOrCreate(
            ['code' => 'box'],
            ['name' => 'Box']
        );

        $productA = Product::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'name' => 'Savon liquide 1L',
            ],
            [
                'category_id' => $hygieneCategory->id,
                'unit_id' => $pieceUnit->id,
                'description' => 'Produit de test pour les ventes, les lots et les transferts.',
                'price' => 2500,
                'seuil_alert' => 10,
                'is_active' => true,
                'is_perishable' => false,
            ]
        );

        $productB = Product::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'name' => 'Riz parfumé 25kg',
            ],
            [
                'category_id' => $foodCategory->id,
                'unit_id' => $boxUnit->id,
                'description' => 'Deuxième produit de démonstration pour les écrans stock.',
                'price' => 18500,
                'seuil_alert' => 5,
                'is_active' => true,
                'is_perishable' => false,
            ]
        );

        $client = Contact::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'phone_number' => '+221760000001',
            ],
            [
                'fullname' => 'Client Démo',
                'address' => 'Plateau, Dakar',
                'type' => Contact::TYPE_CLIENT,
            ]
        );

        $supplier = Contact::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'phone_number' => '+221760000002',
            ],
            [
                'fullname' => 'Fournisseur Démo',
                'address' => 'Zone industrielle, Dakar',
                'type' => Contact::TYPE_SUPPLIER,
            ]
        );

        $cashWallet = Wallet::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'code' => 'CASH',
            ],
            [
                'name' => 'Caisse principale',
                'identifier' => 'Caisse principale',
                'initial_balance' => 500000,
                'current_balance' => 500000,
                'type' => 'other',
                'is_active' => true,
            ]
        );

        $bankWallet = Wallet::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'code' => 'BANK',
            ],
            [
                'name' => 'Compte bancaire',
                'identifier' => 'Compte bancaire',
                'initial_balance' => 250000,
                'current_balance' => 250000,
                'type' => 'bank',
                'is_active' => true,
            ]
        );

        $documentTypes = DocumentNumberService::DEFAULTS;
        foreach ($documentTypes as $documentType => $defaults) {
            DocumentSequence::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'document_type' => $documentType,
                    'period_key' => now()->format('Y'),
                ],
                [
                    'prefix' => $defaults['prefix'],
                    'current_number' => 0,
                    'padding' => $defaults['padding'],
                    'year' => (int) now()->format('Y'),
                    'reset_period' => $defaults['reset_period'],
                    'is_active' => true,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]
            );
        }

        $invoiceService = app(InvoiceService::class);

        $purchaseInvoice = \App\Models\Invoice::query()
            ->where('tenant_id', $tenant->id)
            ->where('type', 'supplier')
            ->where('invoice_number', 'FF-DEMO-001')
            ->first();

        if (! $purchaseInvoice) {
            $purchaseInvoice = $invoiceService->createInvoice([
                'type' => 'supplier',
                'contact_id' => $supplier->id,
                'invoice_number' => null,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'items' => [
                    [
                        'product_id' => $productA->id,
                        'warehouse_id' => $mainWarehouse->id,
                        'quantity' => 40,
                        'discount' => 0,
                        'unit_price' => 1800,
                        'expiration_date' => null,
                    ],
                    [
                        'product_id' => $productB->id,
                        'warehouse_id' => $mainWarehouse->id,
                        'quantity' => 12,
                        'discount' => 0,
                        'unit_price' => 15000,
                        'expiration_date' => null,
                    ],
                ],
            ]);

            $invoiceService->validateInvoice($purchaseInvoice);
            $purchaseInvoice->update(['invoice_number' => 'FF-DEMO-001']);
        }

        $saleInvoice = \App\Models\Invoice::query()
            ->where('tenant_id', $tenant->id)
            ->where('type', 'client')
            ->where('invoice_number', 'FAC-DEMO-001')
            ->first();

        if (! $saleInvoice) {
            $saleInvoice = $invoiceService->createInvoice([
                'type' => 'client',
                'contact_id' => $client->id,
                'invoice_number' => null,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(15)->toDateString(),
                'items' => [
                    [
                        'product_id' => $productA->id,
                        'warehouse_id' => $mainWarehouse->id,
                        'quantity' => 5,
                        'discount' => 0,
                        'unit_price' => 2500,
                        'expiration_date' => null,
                    ],
                    [
                        'product_id' => $productB->id,
                        'warehouse_id' => $mainWarehouse->id,
                        'quantity' => 1,
                        'discount' => 0,
                        'unit_price' => 18500,
                        'expiration_date' => null,
                    ],
                ],
            ]);

            $invoiceService->validateInvoice($saleInvoice);
            $saleInvoice->update([
                'invoice_number' => 'FAC-DEMO-001',
                'status' => 'partial',
                'balance' => 7500,
            ]);
        }

        DB::table('payments')->updateOrInsert(
            [
                'tenant_id' => $tenant->id,
                'invoice_id' => $saleInvoice->id,
                'contact_id' => $client->id,
                'payment_type' => 'Acompte test',
            ],
            [
                'id' => (string) Str::uuid(),
                'wallet_id' => $cashWallet->id,
                'amount_paid' => 22500,
                'remaining_amount' => 7500,
                'payment_date' => now()->toDateString(),
                'payment_source' => 'client',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $sourceBatch = Batch::query()
            ->where('tenant_id', $tenant->id)
            ->where('warehouse_id', $mainWarehouse->id)
            ->where('product_id', $productA->id)
            ->latest('created_at')
            ->first();

        if ($sourceBatch) {
            $targetBatch = Batch::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'warehouse_id' => $secondaryWarehouse->id,
                    'product_id' => $productA->id,
                    'invoice_id' => $purchaseInvoice->id,
                ],
                [
                    'unit_price' => $sourceBatch->unit_price,
                    'quantity' => 10,
                    'benefit' => 0,
                    'remaining' => 10,
                    'expiration_date' => null,
                    'source_type' => 'seed',
                    'source_id' => $saleInvoice->id,
                    'origin' => 'demo',
                ]
            );

            $sourceBatch->update([
                'remaining' => 22,
            ]);

            $targetBatch->update([
                'remaining' => 10,
            ]);

            DB::statement('PRAGMA foreign_keys = OFF');

            try {
                StockTransfert::updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'source_batch_id' => $sourceBatch->id,
                        'target_batch_id' => $targetBatch->id,
                        'product_id' => $productA->id,
                    ],
                    [
                        'transfer_number' => 'TRF-DEMO-001',
                        'source_warehouse_id' => $mainWarehouse->id,
                        'target_warehouse_id' => $secondaryWarehouse->id,
                        'quantity' => 10,
                    ]
                );
            } finally {
                DB::statement('PRAGMA foreign_keys = ON');
            }

            DB::statement('PRAGMA foreign_keys = OFF');

            try {
                StockOut::updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'batch_id' => $sourceBatch->id,
                        'reason' => 'Sortie test',
                    ],
                    [
                        'stock_out_number' => 'OUT-DEMO-001',
                        'quantity' => 3,
                    ]
                );
            } finally {
                DB::statement('PRAGMA foreign_keys = ON');
            }

            Inventory::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'warehouse_id' => $mainWarehouse->id,
                    'inventory_number' => 'INV-DEMO-001',
                ],
                [
                    'total_products' => 2,
                    'closed_at' => null,
                    'status' => 'pending',
                ]
            );

            $inventory = Inventory::query()
                ->where('tenant_id', $tenant->id)
                ->where('inventory_number', 'INV-DEMO-001')
                ->first();

            if ($inventory) {
                InventoryItem::updateOrCreate(
                    [
                        'inventory_id' => $inventory->id,
                        'product_id' => $productA->id,
                    ],
                    [
                        'theoretical_qty' => 22,
                        'real_qty' => 21,
                        'variance' => -1,
                        'validated' => true,
                        'status' => 'completed',
                        'validated_at' => now(),
                        'validated_by' => $user->id,
                        'reconciled_at' => now(),
                        'reconciled_by' => $user->id,
                        'reason' => 'Ajustement initial de démonstration',
                    ]
                );
            }
        }

        Auth::logout();
    }
}
