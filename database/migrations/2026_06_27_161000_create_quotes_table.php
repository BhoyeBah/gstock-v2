<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('contact_id')->index();
            $table->uuid('created_by')->nullable()->index();
            $table->string('quote_number')->nullable();
            $table->date('quote_date');
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired', 'converted'])->default('draft')->index();
            $table->integer('subtotal_ht')->default(0);
            $table->integer('tax_total')->default(0);
            $table->integer('total_ttc')->default(0);
            $table->uuid('converted_invoice_id')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('converted_invoice_id')->references('id')->on('invoices')->nullOnDelete();
            $table->unique(['tenant_id', 'quote_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
