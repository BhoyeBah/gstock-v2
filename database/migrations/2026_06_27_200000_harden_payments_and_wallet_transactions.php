<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'status')) {
                $table->string('status')->default('completed')->index();
            }

            if (! Schema::hasColumn('payments', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable();
            }

            if (! Schema::hasColumn('payments', 'cancelled_by')) {
                $table->uuid('cancelled_by')->nullable()->index();
            }

            if (! Schema::hasColumn('payments', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable();
            }
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('wallet_transactions', 'tenant_id')) {
                $table->uuid('tenant_id')->nullable()->index();
            }

            if (! Schema::hasColumn('wallet_transactions', 'payment_id')) {
                $table->uuid('payment_id')->nullable()->index();
            }

            if (! Schema::hasColumn('wallet_transactions', 'user_id')) {
                $table->uuid('user_id')->nullable()->index();
            }

            if (! Schema::hasColumn('wallet_transactions', 'transaction_type')) {
                $table->string('transaction_type')->nullable()->index();
            }

            if (! Schema::hasColumn('wallet_transactions', 'balance_before')) {
                $table->integer('balance_before')->default(0);
            }

            if (! Schema::hasColumn('wallet_transactions', 'balance_after')) {
                $table->integer('balance_after')->default(0);
            }

            if (! Schema::hasColumn('wallet_transactions', 'description')) {
                $table->text('description')->nullable();
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE invoices MODIFY status VARCHAR(32) NOT NULL DEFAULT 'draft'");
        } elseif (DB::getDriverName() === 'sqlite') {
            $this->rebuildSqliteInvoicesWithTextStatus();
        }
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            foreach (['tenant_id', 'payment_id', 'user_id', 'transaction_type', 'balance_before', 'balance_after', 'description'] as $column) {
                if (Schema::hasColumn('wallet_transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            foreach (['status', 'cancelled_at', 'cancelled_by', 'cancellation_reason'] as $column) {
                if (Schema::hasColumn('payments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE invoices MODIFY status ENUM('draft', 'validated', 'partial', 'paid', 'cancelled') NOT NULL DEFAULT 'draft'");
        }
    }

    private function rebuildSqliteInvoicesWithTextStatus(): void
    {
        DB::statement('PRAGMA foreign_keys=OFF');
        DB::statement('
            CREATE TABLE invoices_rebuild (
                id CHAR(36) NOT NULL PRIMARY KEY,
                tenant_id CHAR(36) NOT NULL,
                contact_id CHAR(36) NOT NULL,
                invoice_number VARCHAR(255) NULL,
                invoice_date DATE NOT NULL,
                due_date DATE NOT NULL,
                type VARCHAR(32) NOT NULL,
                total_invoice INTEGER NOT NULL,
                balance INTEGER NOT NULL,
                status VARCHAR(32) NOT NULL DEFAULT "draft",
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ');
        DB::statement('
            INSERT INTO invoices_rebuild (
                id, tenant_id, contact_id, invoice_number, invoice_date, due_date,
                type, total_invoice, balance, status, created_at, updated_at
            )
            SELECT
                id, tenant_id, contact_id, invoice_number, invoice_date, due_date,
                type, total_invoice, balance, status, created_at, updated_at
            FROM invoices
        ');
        DB::statement('DROP TABLE invoices');
        DB::statement('ALTER TABLE invoices_rebuild RENAME TO invoices');
        DB::statement('CREATE INDEX invoices_tenant_id_index ON invoices (tenant_id)');
        DB::statement('CREATE INDEX invoices_contact_id_index ON invoices (contact_id)');
        DB::statement('CREATE INDEX invoices_type_index ON invoices (type)');
        DB::statement('CREATE INDEX invoices_status_index ON invoices (status)');
        DB::statement('CREATE UNIQUE INDEX invoices_tenant_id_type_invoice_number_unique ON invoices (tenant_id, type, invoice_number)');
        DB::statement('PRAGMA foreign_keys=ON');
    }
};
