<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('sale_orders', 'invoice_id')) {
                $table->uuid('invoice_id')->nullable()->after('quote_id')->index();
                $table->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete();
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'quote_id')) {
                $table->uuid('quote_id')->nullable()->after('contact_id')->index();
                $table->foreign('quote_id')->references('id')->on('quotes')->nullOnDelete();
            }

            if (! Schema::hasColumn('invoices', 'sale_order_id')) {
                $table->uuid('sale_order_id')->nullable()->after('quote_id')->index();
                $table->foreign('sale_order_id')->references('id')->on('sale_orders')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'sale_order_id')) {
                $table->dropForeign(['sale_order_id']);
                $table->dropColumn('sale_order_id');
            }

            if (Schema::hasColumn('invoices', 'quote_id')) {
                $table->dropForeign(['quote_id']);
                $table->dropColumn('quote_id');
            }
        });

        Schema::table('sale_orders', function (Blueprint $table) {
            if (Schema::hasColumn('sale_orders', 'invoice_id')) {
                $table->dropForeign(['invoice_id']);
                $table->dropColumn('invoice_id');
            }
        });
    }
};
