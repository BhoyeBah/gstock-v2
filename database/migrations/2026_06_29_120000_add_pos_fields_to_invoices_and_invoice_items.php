<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'total_ht')) {
                $table->integer('total_ht')->default(0)->after('total_invoice');
            }

            if (! Schema::hasColumn('invoices', 'tax_amount')) {
                $table->integer('tax_amount')->default(0)->after('total_ht');
            }

            if (! Schema::hasColumn('invoices', 'discount_amount')) {
                $table->integer('discount_amount')->default(0)->after('tax_amount');
            }
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            if (! Schema::hasColumn('invoice_items', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0)->after('discount');
            }

            if (! Schema::hasColumn('invoice_items', 'tax_amount')) {
                $table->integer('tax_amount')->default(0)->after('tax_rate');
            }

            if (! Schema::hasColumn('invoice_items', 'total_ht')) {
                $table->integer('total_ht')->default(0)->after('tax_amount');
            }

            if (! Schema::hasColumn('invoice_items', 'total_ttc')) {
                $table->integer('total_ttc')->default(0)->after('total_ht');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            foreach (['tax_rate', 'tax_amount', 'total_ht', 'total_ttc'] as $column) {
                if (Schema::hasColumn('invoice_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            foreach (['total_ht', 'tax_amount', 'discount_amount'] as $column) {
                if (Schema::hasColumn('invoices', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
