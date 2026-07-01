<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ModulePlaceholderController extends Controller
{
    private const MODULES = [
        'quotes' => [
            'title' => 'Devis / Proforma',
            'status' => 'Disponible',
            'description' => 'Le module devis / proforma est livré et disponible via son workflow dédié.',
            'permissions' => ['read_quotes', 'manage_client_invoices'],
        ],
        'sale-orders' => [
            'title' => 'Commandes clients',
            'status' => 'Disponible',
            'description' => 'Le workflow commande client est disponible avec validation, impression et conversion.',
            'permissions' => ['read_sale_orders', 'manage_client_invoices'],
        ],
        'deliveries' => [
            'title' => 'Bons de livraison',
            'status' => 'Disponible',
            'description' => 'Le module bon de livraison est livré avec validation et journal stock.',
            'permissions' => ['read_deliveries', 'manage_stock', 'manage_client_invoices'],
        ],
        'purchase-orders' => [
            'title' => 'Commandes fournisseurs',
            'status' => 'Disponible',
            'description' => 'Le workflow commande fournisseur est livré avec création des réceptions et factures associées.',
            'permissions' => ['read_purchase_orders', 'manage_supplier_invoices'],
        ],
        'receipts' => [
            'title' => 'Bons de réception',
            'status' => 'Disponible',
            'description' => 'Le module bon de réception est livré et alimente les lots et mouvements de stock.',
            'permissions' => ['read_receipts', 'manage_stock', 'manage_supplier_invoices'],
        ],
        'batches' => [
            'title' => 'Lots / Batches',
            'status' => 'Disponible',
            'description' => 'Les lots disposent désormais d’un écran dédié avec filtres, statistiques et listing tenant-safe.',
            'permissions' => ['manage_stock', 'manage_warehouses'],
        ],
        'movements' => [
            'title' => 'Mouvements de stock',
            'status' => 'Disponible',
            'description' => 'Les mouvements ont maintenant leur journal global avec filtres et pagination.',
            'permissions' => ['manage_stock', 'manage_inventories'],
        ],
        'transfers' => [
            'title' => 'Transferts internes',
            'status' => 'Disponible',
            'description' => 'Les transferts internes disposent d’un historique autonome et consultable.',
            'permissions' => ['manage_warehouses', 'manage_stock'],
        ],
        'returns' => [
            'title' => 'Retours clients / fournisseurs',
            'status' => 'Disponible',
            'description' => 'Les bons de retour client et fournisseur sont livrés dans leurs modules dédiés, avec impression et validations.',
            'permissions' => ['manage_client_invoices', 'manage_supplier_invoices'],
        ],
    ];

    public function show(Request $request, string $module)
    {
        $definition = self::MODULES[$module] ?? null;

        if (! $definition) {
            abort(404);
        }

        $user = $request->user();
        $authorized = collect($definition['permissions'])->contains(fn (string $permission) => $user->can($permission));

        if (! $authorized && ! $user->is_platform_user()) {
            abort(403);
        }

        return view('back.modules.placeholder', [
            'moduleKey' => $module,
            'module' => $definition,
        ]);
    }
}
