<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            if (! Schema::hasColumn('quotes', 'valid_until')) {
                $table->date('valid_until')->nullable()->after('quote_date');
            }

            if (! Schema::hasColumn('quotes', 'total_ht')) {
                $table->integer('total_ht')->default(0)->after('status');
            }

            if (! Schema::hasColumn('quotes', 'total_discount')) {
                $table->integer('total_discount')->default(0)->after('total_ht');
            }

            if (! Schema::hasColumn('quotes', 'tax_amount')) {
                $table->integer('tax_amount')->default(0)->after('total_discount');
            }

            if (! Schema::hasColumn('quotes', 'converted_to_sale_order_id')) {
                $table->uuid('converted_to_sale_order_id')->nullable()->index()->after('total_ttc');
            }

            if (! Schema::hasColumn('quotes', 'converted_to_invoice_id')) {
                $table->uuid('converted_to_invoice_id')->nullable()->index()->after('converted_to_sale_order_id');
            }

            if (! Schema::hasColumn('quotes', 'converted_at')) {
                $table->timestamp('converted_at')->nullable()->after('converted_to_invoice_id');
            }

            if (! Schema::hasColumn('quotes', 'notes')) {
                $table->text('notes')->nullable()->after('converted_at');
            }
        });

        Schema::table('quote_items', function (Blueprint $table) {
            if (! Schema::hasColumn('quote_items', 'unit_price_ht')) {
                $table->integer('unit_price_ht')->default(0)->after('quantity');
            }

            if (! Schema::hasColumn('quote_items', 'discount_amount')) {
                $table->integer('discount_amount')->default(0)->after('unit_price_ht');
            }

            if (! Schema::hasColumn('quote_items', 'tax_id')) {
                $table->uuid('tax_id')->nullable()->index()->after('subtotal_ht');
            }

            if (! Schema::hasColumn('quote_items', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0)->after('tax_id');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_items');
        Schema::dropIfExists('quotes');
    }
};
