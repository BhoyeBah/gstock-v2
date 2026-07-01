<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 2 (POS) : autoriser une vente comptoir anonyme (sans client) tant
 * qu'elle est soldée. Le crédit/dette continue d'exiger un client.
 * On rend donc contact_id nullable sur invoices et payments.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->mapEnumToString();

        Schema::table('invoices', function (Blueprint $table) {
            $table->uuid('contact_id')->nullable()->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->uuid('contact_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        $this->mapEnumToString();

        Schema::table('invoices', function (Blueprint $table) {
            $table->uuid('contact_id')->nullable(false)->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->uuid('contact_id')->nullable(false)->change();
        });
    }

    /**
     * doctrine/dbal ne connaît pas le type `enum` et échoue lors de
     * l'introspection des tables invoices/payments (colonnes type/status/
     * payment_source). On le mappe sur `string` le temps de la migration.
     */
    private function mapEnumToString(): void
    {
        $platform = DB::getDoctrineConnection()->getDatabasePlatform();

        if (! $platform->hasDoctrineTypeMappingFor('enum')) {
            $platform->registerDoctrineTypeMapping('enum', 'string');
        }
    }
};
