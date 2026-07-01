<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            if (! Schema::hasColumn('inventory_items', 'status')) {
                $table->string('status')->default('pending')->after('validated');
            }

            if (! Schema::hasColumn('inventory_items', 'validated_at')) {
                $table->timestamp('validated_at')->nullable()->after('status');
            }

            if (! Schema::hasColumn('inventory_items', 'validated_by')) {
                $table->uuid('validated_by')->nullable()->after('validated_at');
            }

            if (! Schema::hasColumn('inventory_items', 'reconciled_at')) {
                $table->timestamp('reconciled_at')->nullable()->after('validated_by');
            }

            if (! Schema::hasColumn('inventory_items', 'reconciled_by')) {
                $table->uuid('reconciled_by')->nullable()->after('reconciled_at');
            }

            if (! Schema::hasColumn('inventory_items', 'reason')) {
                $table->text('reason')->nullable()->after('reconciled_by');
            }
        });

        if (DB::getDriverName() === 'sqlite') {
            $this->rebuildBatchesForSqlite();
            $this->rebuildInventoryMovementsForSqlite();
            $this->rebuildReturnProductsForSqlite();

            return;
        }

        Schema::table('batches', function (Blueprint $table) {
            if (! Schema::hasColumn('batches', 'source_type')) {
                $table->string('source_type')->nullable()->after('expiration_date');
            }

            if (! Schema::hasColumn('batches', 'source_id')) {
                $table->uuid('source_id')->nullable()->after('source_type');
            }

            if (! Schema::hasColumn('batches', 'origin')) {
                $table->string('origin')->nullable()->after('source_id');
            }
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            if (! Schema::hasColumn('inventory_movements', 'tenant_id')) {
                $table->uuid('tenant_id')->nullable()->index()->after('id');
            }

            if (! Schema::hasColumn('inventory_movements', 'inventory_id')) {
                $table->uuid('inventory_id')->nullable()->index()->after('tenant_id');
            }

            if (! Schema::hasColumn('inventory_movements', 'inventory_item_id')) {
                $table->uuid('inventory_item_id')->nullable()->index()->after('inventory_id');
            }

            if (! Schema::hasColumn('inventory_movements', 'warehouse_id')) {
                $table->uuid('warehouse_id')->nullable()->index()->after('product_id');
            }

            if (! Schema::hasColumn('inventory_movements', 'quantity_before')) {
                $table->integer('quantity_before')->default(0)->after('quantity');
            }

            if (! Schema::hasColumn('inventory_movements', 'quantity_after')) {
                $table->integer('quantity_after')->default(0)->after('quantity_before');
            }

            if (! Schema::hasColumn('inventory_movements', 'variance')) {
                $table->integer('variance')->default(0)->after('quantity_after');
            }

            if (! Schema::hasColumn('inventory_movements', 'movement_type')) {
                $table->string('movement_type')->nullable()->index()->after('variance');
            }

            if (! Schema::hasColumn('inventory_movements', 'source_type')) {
                $table->string('source_type')->nullable()->after('movement_type');
            }

            if (! Schema::hasColumn('inventory_movements', 'source_id')) {
                $table->uuid('source_id')->nullable()->after('source_type');
            }

            if (! Schema::hasColumn('inventory_movements', 'user_id')) {
                $table->uuid('user_id')->nullable()->index()->after('source_id');
            }

            if (! Schema::hasColumn('inventory_movements', 'movement_date')) {
                $table->timestamp('movement_date')->nullable()->after('user_id');
            }
        });

        DB::statement('ALTER TABLE batches MODIFY invoice_id CHAR(36) NULL');
        DB::statement('ALTER TABLE inventory_movements MODIFY invoice_item_id CHAR(36) NULL');
        DB::statement('ALTER TABLE inventory_movements MODIFY invoice_id CHAR(36) NULL');
        DB::statement('ALTER TABLE inventory_movements MODIFY batch_id CHAR(36) NULL');

        DB::statement("
            UPDATE inventory_movements im
            LEFT JOIN invoices i ON i.id = im.invoice_id
            LEFT JOIN batches b ON b.id = im.batch_id
            SET im.tenant_id = COALESCE(im.tenant_id, i.tenant_id, b.tenant_id),
                im.warehouse_id = COALESCE(im.warehouse_id, b.warehouse_id)
            WHERE im.invoice_id IS NOT NULL OR im.batch_id IS NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            foreach (['status', 'validated_at', 'validated_by', 'reconciled_at', 'reconciled_by', 'reason'] as $column) {
                if (Schema::hasColumn('inventory_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('batches', function (Blueprint $table) {
            foreach (['source_type', 'source_id', 'origin'] as $column) {
                if (Schema::hasColumn('batches', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            foreach ([
                'tenant_id',
                'inventory_id',
                'inventory_item_id',
                'warehouse_id',
                'quantity_before',
                'quantity_after',
                'variance',
                'movement_type',
                'source_type',
                'source_id',
                'user_id',
                'movement_date',
            ] as $column) {
                if (Schema::hasColumn('inventory_movements', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function rebuildBatchesForSqlite(): void
    {
        DB::statement('PRAGMA foreign_keys=OFF');
        Schema::rename('batches', 'batches_old_inventory_reconciliation');

        Schema::create('batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('invoice_id')->nullable();
            $table->uuid('warehouse_id');
            $table->uuid('tenant_id');
            $table->uuid('product_id');
            $table->integer('unit_price');
            $table->integer('quantity');
            $table->integer('benefit')->default(0);
            $table->integer('remaining');
            $table->date('expiration_date')->nullable();
            $table->string('source_type')->nullable();
            $table->uuid('source_id')->nullable();
            $table->string('origin')->nullable();
            $table->timestamps();
        });

        DB::statement("
            INSERT INTO batches (
                id, invoice_id, warehouse_id, tenant_id, product_id, unit_price, quantity, benefit, remaining,
                expiration_date, source_type, source_id, origin, created_at, updated_at
            )
            SELECT
                id, invoice_id, warehouse_id, tenant_id, product_id, unit_price, quantity, benefit, remaining,
                expiration_date, NULL, NULL, NULL, created_at, updated_at
            FROM batches_old_inventory_reconciliation
        ");

        Schema::drop('batches_old_inventory_reconciliation');
        DB::statement('PRAGMA foreign_keys=ON');
    }

    private function rebuildInventoryMovementsForSqlite(): void
    {
        DB::statement('PRAGMA foreign_keys=OFF');
        Schema::rename('inventory_movements', 'inventory_movements_old_reconciliation');

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable();
            $table->uuid('inventory_id')->nullable();
            $table->uuid('inventory_item_id')->nullable();
            $table->uuid('invoice_item_id')->nullable();
            $table->uuid('invoice_id')->nullable();
            $table->uuid('batch_id')->nullable();
            $table->uuid('product_id');
            $table->uuid('warehouse_id')->nullable();
            $table->integer('quantity');
            $table->integer('quantity_before')->default(0);
            $table->integer('quantity_after')->default(0);
            $table->integer('variance')->default(0);
            $table->integer('profit')->default(0);
            $table->string('movement_type')->nullable();
            $table->string('source_type')->nullable();
            $table->uuid('source_id')->nullable();
            $table->uuid('user_id')->nullable();
            $table->timestamp('movement_date')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        DB::statement("
            INSERT INTO inventory_movements (
                id, tenant_id, inventory_id, inventory_item_id, invoice_item_id, invoice_id, batch_id, product_id,
                warehouse_id, quantity, quantity_before, quantity_after, variance, profit, movement_type,
                source_type, source_id, user_id, movement_date, reason, created_at, updated_at
            )
            SELECT
                id,
                COALESCE(
                    (SELECT tenant_id FROM invoices WHERE invoices.id = inventory_movements_old_reconciliation.invoice_id),
                    (SELECT tenant_id FROM batches WHERE batches.id = inventory_movements_old_reconciliation.batch_id)
                ),
                NULL,
                NULL,
                invoice_item_id,
                invoice_id,
                batch_id,
                product_id,
                (SELECT warehouse_id FROM batches WHERE batches.id = inventory_movements_old_reconciliation.batch_id),
                quantity, 0, 0, 0, profit, NULL,
                NULL, NULL, NULL, NULL, reason, created_at, updated_at
            FROM inventory_movements_old_reconciliation
        ");

        Schema::drop('inventory_movements_old_reconciliation');
        DB::statement('PRAGMA foreign_keys=ON');
    }

    private function rebuildReturnProductsForSqlite(): void
    {
        DB::statement('PRAGMA foreign_keys=OFF');
        Schema::rename('return_products', 'return_products_old_inventory_reconciliation');

        Schema::create('return_products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('invoice_item_id');
            $table->uuid('inventory_movement_id')->nullable();
            $table->integer('quantity');
            $table->string('motif');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('invoice_item_id')->references('id')->on('invoice_items')->onDelete('cascade');
            $table->foreign('inventory_movement_id')->references('id')->on('inventory_movements')->onDelete('set null');
        });

        DB::statement("
            INSERT INTO return_products (
                id, tenant_id, invoice_item_id, inventory_movement_id, quantity, motif, created_at, updated_at
            )
            SELECT
                id, tenant_id, invoice_item_id, inventory_movement_id, quantity, motif, created_at, updated_at
            FROM return_products_old_inventory_reconciliation
        ");

        Schema::drop('return_products_old_inventory_reconciliation');
        DB::statement('PRAGMA foreign_keys=ON');
    }
};
