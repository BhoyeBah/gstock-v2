<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 2 (POS) - Fonctionnalité 10 : clôture journalière de caisse.
 * Une session de caisse réconcilie un wallet (le tiroir-caisse) sur une période :
 * fonds d'ouverture -> encaissements de la session -> montant compté à la fermeture.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('user_id')->index();
            $table->uuid('wallet_id')->nullable()->index();

            $table->enum('status', ['open', 'closed'])->default('open')->index();

            $table->integer('opening_amount')->default(0);
            $table->integer('expected_amount')->nullable();
            $table->integer('counted_amount')->nullable();
            $table->integer('difference')->nullable();

            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('set null');

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_sessions');
    }
};
