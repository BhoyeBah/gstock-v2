<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('document_sequences')) {
            Schema::create('document_sequences', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->index();
                $table->string('document_type');
                $table->string('prefix', 10);
                $table->unsignedBigInteger('current_number')->default(0);
                $table->unsignedInteger('padding')->default(4);
                $table->unsignedInteger('year')->nullable();
                $table->string('period_key')->default('global');
                $table->enum('reset_period', ['never', 'yearly', 'monthly'])->default('yearly');
                $table->boolean('is_active')->default(true);
                $table->uuid('created_by')->nullable();
                $table->uuid('updated_by')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'document_type', 'period_key'], 'document_sequences_unique_period');
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }

        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'payment_number')) {
                $table->string('payment_number')->nullable()->after('id');
                $table->unique(['tenant_id', 'payment_number'], 'payments_tenant_payment_number_unique');
            }
        });

        Schema::table('stock_transferts', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_transferts', 'transfer_number')) {
                $table->string('transfer_number')->nullable()->after('id');
                $table->unique(['tenant_id', 'transfer_number'], 'stock_transferts_tenant_number_unique');
            }
        });

        Schema::table('stock_outs', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_outs', 'stock_out_number')) {
                $table->string('stock_out_number')->nullable()->after('id');
                $table->unique(['tenant_id', 'stock_out_number'], 'stock_outs_tenant_number_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_outs', function (Blueprint $table) {
            if (Schema::hasColumn('stock_outs', 'stock_out_number')) {
                $table->dropUnique('stock_outs_tenant_number_unique');
                $table->dropColumn('stock_out_number');
            }
        });

        Schema::table('stock_transferts', function (Blueprint $table) {
            if (Schema::hasColumn('stock_transferts', 'transfer_number')) {
                $table->dropUnique('stock_transferts_tenant_number_unique');
                $table->dropColumn('transfer_number');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'payment_number')) {
                $table->dropUnique('payments_tenant_payment_number_unique');
                $table->dropColumn('payment_number');
            }
        });

        Schema::dropIfExists('document_sequences');
    }
};
