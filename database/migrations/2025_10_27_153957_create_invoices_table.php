<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Table invoices
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('contact_id')->index();
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date');
            $table->date('due_date');
            $table->enum('type', ['client', 'supplier'])->index();
            $table->integer('total_invoice');
            $table->integer('balance');
            $table->enum('status', ['draft', 'validated', 'partial', 'paid', 'cancelled'])
                ->default('draft')->index();
            $table->timestamps();

            // Foreign keys
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('restrict');

            $table->unique(['tenant_id', 'type', 'invoice_number']);
        });

        // Table invoice_items
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('invoice_id')->index();
            $table->uuid('warehouse_id')->index();
            $table->uuid('product_id')->index();
            $table->integer('quantity');
            $table->enum('type', ['in', 'out'])->index();
            $table->integer('unit_price');
            $table->integer('discount')->default(0);
            $table->integer('total_line');
            $table->date("expiration_date")->nullable()->index();
            $table->timestamps();

            // Foreign keys
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
