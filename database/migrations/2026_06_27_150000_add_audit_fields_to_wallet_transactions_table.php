<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->uuid('tenant_id')->nullable()->after('id')->index();
            $table->uuid('payment_id')->nullable()->after('wallet_id')->index();
            $table->uuid('user_id')->nullable()->after('payment_id')->index();
            $table->string('transaction_type')->nullable()->after('user_id');
            $table->integer('balance_before')->nullable()->after('amount');
            $table->integer('balance_after')->nullable()->after('balance_before');
            $table->string('description')->nullable()->after('source_id');

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->nullOnDelete();

            $table->foreign('payment_id')
                ->references('id')
                ->on('payments')
                ->nullOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropForeign(['payment_id']);
            $table->dropForeign(['user_id']);

            $table->dropColumn([
                'tenant_id',
                'payment_id',
                'user_id',
                'transaction_type',
                'balance_before',
                'balance_after',
                'description',
            ]);
        });
    }
};
