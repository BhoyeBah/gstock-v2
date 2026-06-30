<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ModulePlaceholderController extends Controller
{
    private const MODULES = [
        'quotes' => [
            'title' => 'Devis / Proforma',
            'status' => 'Absent',
            'description' => 'Le module devis / proforma n’est pas encore implémente dans cette base. La sidebar l’expose pour préparer le parcours ERP sans créer de lien mort.',
            'permissions' => ['read_quotes', 'manage_client_invoices'],
        ],
        'sale-orders' => [
            'title' => 'Commandes clients',
            'status' => 'Absent',
            'description' => 'Le workflow commande client n’est pas encore codé. Il devra être livré dans un sprint dédié.',
            'permissions' => ['read_sale_orders', 'manage_client_invoices'],
        ],
        'deliveries' => [
            'title' => 'Bons de livraison',
            'status' => 'Absent',
            'description' => 'Le module bon de livraison n’est pas encore disponible dans les routes, contrôleurs et vues de cette base.',
            'permissions' => ['read_deliveries', 'manage_stock', 'manage_client_invoices'],
        ],
        'purchase-orders' => [
            'title' => 'Commandes fournisseurs',
            'status' => 'Absent',
            'description' => 'Le workflow commande fournisseur n’est pas encore implémente.',
            'permissions' => ['read_purchase_orders', 'manage_supplier_invoices'],
        ],
        'receipts' => [
            'title' => 'Bons de réception',
            'status' => 'Absent',
            'description' => 'Le module bon de réception n’est pas encore disponible dans les routes, contrôleurs et vues de cette base.',
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
            'status' => 'En préparation',
            'description' => 'Le retour est déjà intégré dans certaines factures, mais le module autonome reste à cadrer.',
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
