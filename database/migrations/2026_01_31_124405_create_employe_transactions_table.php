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
        Schema::create('employe_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employe_id')->index();
            $table->uuid('wallet_id')->nullable()->index();
            $table->integer('amount');
            $table->enum('type', [
                'salary_payment',
                'advance',
                'advance_repayment',
                'bonus',
                'deduction',
            ]);
            $table->date('date');
            $table->string('reference')->nullable()->unique();
            $table->text('note')->nullable();
            $table->timestamps();

            // (optionnel) FK si tes tables existent
            $table->foreign('employe_id')->references('id')->on('employes')->cascadeOnDelete();
            $table->foreign('wallet_id')->references('id')->on('wallets')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employe_transactions');
    }
};
