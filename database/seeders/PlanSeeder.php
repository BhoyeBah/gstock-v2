<?php



namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Plan Gratuit
        $free = Plan::updateOrCreate(
            ['slug' => 'gratuit'],
            [
                'name' => 'Gratuit',
                'price' => 0,
                'duration_days' => 30,
                'max_users' => 3,
                'max_storage_mb' => 100,
                'is_active' => true,
                'description' => 'Plan gratuit avec 3 utilisateurs max.',
            ]
        );

        // Plan Standard
        $standard = Plan::updateOrCreate(
            ['slug' => 'standard'],
            [
                'name' => 'Standard',
                'price' => 10000,
                'duration_days' => 30,
                'max_users' => 10,
                'max_storage_mb' => 1000,
                'is_active' => true,
                'description' => 'Plan intermédiaire pour les PME.',
            ]
        );

        // Plan Premium
        $premium = Plan::updateOrCreate(
            ['slug' => 'premium'],
            [
                'name' => 'Premium',
                'price' => 25000,
                'duration_days' => 30,
                'max_users' => 30,
                'max_storage_mb' => 5000,
                'is_active' => true,
                'description' => 'Accès complet avec stockage et utilisateurs élargis.',
            ]
        );

        // Plan Admin (non visible pour les autres)
        $admin = Plan::updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
                'price' => 0,
                'duration_days' => 36500, // ~100 ans
                'max_users' => 9999,
                'max_storage_mb' => 999999,
                'is_active' => false, // désactivé pour l'affichage public
                'description' => 'Plan réservé au propriétaire du SaaS.',
            ]
        );

        $workflowPermissions = [
            'read_quotes',
            'create_quotes',
            'update_quotes',
            'delete_quotes',
            'convert_quotes',
            'create_pos_sales',
            'read_sale_orders',
            'create_sale_orders',
            'update_sale_orders',
            'confirm_sale_orders',
            'cancel_sale_orders',
            'read_deliveries',
            'create_deliveries',
            'validate_deliveries',
            'cancel_deliveries',
            'read_purchase_orders',
            'create_purchase_orders',
            'update_purchase_orders',
            'confirm_purchase_orders',
            'cancel_purchase_orders',
            'read_receipts',
            'create_receipts',
            'validate_receipts',
            'cancel_receipts',
            'manage_reports',
            'read_products',
            'read_clients',
            'read_suppliers',
            'manage_client_invoices',
            'manage_supplier_invoices',
            'read_client_payments',
            'read_supplier_payments',
            'manage_employee',
        ];

        $this->syncPlanPermissions($free, $workflowPermissions);

        $this->syncPlanPermissions($standard, array_merge($workflowPermissions, [
            'manage_warehouses',
            'manage_inventories',
        ]));

        $this->syncPlanPermissions($premium, array_merge($workflowPermissions, [
            'manage_warehouses',
            'manage_inventories',
            'manage_wallets',
            'manage_expenses',
            'manage_employee',
        ]));

        $this->syncPlanPermissions($admin, Permission::query()->pluck('name')->all());
    }

    private function syncPlanPermissions(Plan $plan, array $permissions): void
    {
        $permissionIds = Permission::query()
            ->whereIn('name', $permissions)
            ->pluck('id')
            ->all();

        $plan->permissions()->sync($permissionIds);
    }
}
