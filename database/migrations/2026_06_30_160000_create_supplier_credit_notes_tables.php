<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_credit_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('credit_note_number')->unique();
            $table->uuid('supplier_return_id')->unique();
            $table->uuid('supplier_invoice_id')->nullable()->index();
            $table->uuid('contact_id')->index();
            $table->string('status')->default('draft');
            $table->date('credit_date');
            $table->integer('total_ht')->default(0);
            $table->integer('total_discount')->default(0);
            $table->integer('tax_amount')->default(0);
            $table->integer('total_ttc')->default(0);
            $table->integer('applied_amount')->default(0);
            $table->integer('remaining_amount')->default(0);
            $table->uuid('created_by')->nullable()->index();
            $table->timestamp('validated_at')->nullable();
            $table->uuid('validated_by')->nullable()->index();
            $table->timestamp('cancelled_at')->nullable();
            $table->uuid('cancelled_by')->nullable()->index();
            $table->timestamp('refunded_at')->nullable();
            $table->uuid('refunded_by')->nullable()->index();
            $table->uuid('wallet_id')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('supplier_return_id')->references('id')->on('supplier_returns')->onDelete('cascade');
            $table->foreign('supplier_invoice_id')->references('id')->on('invoices')->nullOnDelete();
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('validated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('cancelled_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('refunded_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('wallet_id')->references('id')->on('wallets')->nullOnDelete();
        });

        Schema::create('supplier_credit_note_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('supplier_credit_note_id')->index();
            $table->uuid('supplier_return_item_id')->nullable()->index();
            $table->uuid('product_id')->index();
            $table->integer('quantity');
            $table->integer('unit_cost_ht');
            $table->integer('discount_amount')->default(0);
            $table->integer('subtotal_ht');
            $table->uuid('tax_id')->nullable()->index();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->integer('tax_amount')->default(0);
            $table->integer('total_ttc')->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('supplier_credit_note_id')->references('id')->on('supplier_credit_notes')->onDelete('cascade');
            $table->foreign('supplier_return_item_id')->references('id')->on('supplier_return_items')->nullOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
            $table->foreign('tax_id')->references('id')->on('taxes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_credit_note_items');
        Schema::dropIfExists('supplier_credit_notes');
    }
};
