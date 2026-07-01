<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rattache un encaissement à une session de caisse ouverte (POS) afin de
 * calculer le total journalier réel du tiroir lors de la clôture.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->uuid('cash_session_id')->nullable()->index()->after('wallet_id');

            $table->foreign('cash_session_id')
                ->references('id')->on('cash_sessions')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['cash_session_id']);
            $table->dropColumn('cash_session_id');
        });
    }
};
