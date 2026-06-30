<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Dashboard
            ['name' => 'access_dashboard', 'description' => 'Accéder au tableau de bord'],

            // Users & Roles
            ['name' => 'manage_users', 'description' => 'Gérer les utilisateurs'],
            ['name' => 'create_users', 'description' => 'Créer des utilisateurs'],
            ['name' => 'delete_users', 'description' => 'Supprimer des utilisateurs du tenant'],
            ['name' => 'delete_any_users', 'description' => 'Supprimer des utilisateurs de n’importe quel tenant'],
            ['name' => 'manage_roles', 'description' => 'Gérer les rôles'],

            // Products
            ['name' => 'read_products', 'description' => 'Voir les produits'],
            ['name' => 'create_products', 'description' => 'Créer des produits'],
            ['name' => 'toggle_products', 'description' => 'Activer / Désactiver des produits'],
            ['name' => 'toggle_product', 'description' => 'Activer / Désactiver un produit'],
            ['name' => 'delete_product', 'description' => 'Supprimer un produit'],

            // Clients
            ['name' => 'read_clients', 'description' => 'Voir la liste des clients'],
            ['name' => 'read_client', 'description' => 'Voir un client'],
            ['name' => 'create_clients', 'description' => 'Créer des clients'],
            ['name' => 'update_clients', 'description' => 'Modifier des clients'],
            ['name' => 'delete_clients', 'description' => 'Supprimer des clients'],
            ['name' => 'toggle_clients', 'description' => 'Activer / Désactiver des clients'],

            // Suppliers
            ['name' => 'read_suppliers', 'description' => 'Voir la liste des fournisseurs'],
            ['name' => 'read_supplier', 'description' => 'Voir un fournisseur'],
            ['name' => 'create_suppliers', 'description' => 'Créer des fournisseurs'],
            ['name' => 'update_suppliers', 'description' => 'Modifier des fournisseurs'],
            ['name' => 'delete_suppliers', 'description' => 'Supprimer des fournisseurs'],
            ['name' => 'toggle_suppliers', 'description' => 'Activer / Désactiver des fournisseurs'],

            // Invoices
            ['name' => 'manage_invoices', 'description' => 'Gérer les factures'],
            ['name' => 'manage_client_invoices', 'description' => 'Gérer les factures clients'],
            ['name' => 'manage_supplier_invoices', 'description' => 'Gérer les factures fournisseurs'],
            ['name' => 'read_invoices', 'description' => 'Voir les factures'],
            ['name' => 'create_invoices', 'description' => 'Créer des factures'],
            ['name' => 'update_invoices', 'description' => 'Modifier des factures'],
            ['name' => 'validate_invoices', 'description' => 'Valider les factures'],
            ['name' => 'cancel_invoices', 'description' => 'Annuler les factures'],
            ['name' => 'pay_invoices', 'description' => 'Effectuer le paiement des factures'],
            ['name' => 'make_payment', 'description' => 'Enregistrer un paiement depuis une facture'],
            ['name' => 'force_delete_invoice', 'description' => 'Supprimer définitivement une facture'],

            // Quotes / Proformas
            ['name' => 'read_quotes', 'description' => 'Voir les devis / proformas'],
            ['name' => 'create_quotes', 'description' => 'Créer des devis / proformas'],
            ['name' => 'update_quotes', 'description' => 'Modifier des devis / proformas'],
            ['name' => 'delete_quotes', 'description' => 'Supprimer des devis / proformas'],
            ['name' => 'convert_quotes', 'description' => 'Convertir un devis en facture'],

            // Sale orders
            ['name' => 'read_sale_orders', 'description' => 'Voir les commandes clients'],
            ['name' => 'create_sale_orders', 'description' => 'Créer des commandes clients'],
            ['name' => 'update_sale_orders', 'description' => 'Modifier des commandes clients'],
            ['name' => 'confirm_sale_orders', 'description' => 'Confirmer des commandes clients'],
            ['name' => 'cancel_sale_orders', 'description' => 'Annuler des commandes clients'],

            // Deliveries
            ['name' => 'read_deliveries', 'description' => 'Voir les bons de livraison'],
            ['name' => 'create_deliveries', 'description' => 'Créer des bons de livraison'],
            ['name' => 'validate_deliveries', 'description' => 'Valider des bons de livraison'],
            ['name' => 'cancel_deliveries', 'description' => 'Annuler des bons de livraison'],

            // Customer returns
            ['name' => 'read_customer_returns', 'description' => 'Voir les bons de retour client'],
            ['name' => 'create_customer_returns', 'description' => 'Créer des bons de retour client'],
            ['name' => 'update_customer_returns', 'description' => 'Modifier des bons de retour client'],
            ['name' => 'validate_customer_returns', 'description' => 'Valider des bons de retour client'],
            ['name' => 'cancel_customer_returns', 'description' => 'Annuler des bons de retour client'],

            // Purchase orders
            ['name' => 'read_purchase_orders', 'description' => 'Voir les commandes fournisseurs'],
            ['name' => 'create_purchase_orders', 'description' => 'Créer des commandes fournisseurs'],
            ['name' => 'update_purchase_orders', 'description' => 'Modifier des commandes fournisseurs'],
            ['name' => 'confirm_purchase_orders', 'description' => 'Confirmer des commandes fournisseurs'],
            ['name' => 'cancel_purchase_orders', 'description' => 'Annuler des commandes fournisseurs'],

            // Receipts
            ['name' => 'read_receipts', 'description' => 'Voir les bons de réception'],
            ['name' => 'create_receipts', 'description' => 'Créer des bons de réception'],
            ['name' => 'validate_receipts', 'description' => 'Valider des bons de réception'],
            ['name' => 'cancel_receipts', 'description' => 'Annuler des bons de réception'],

            // Supplier returns
            ['name' => 'read_supplier_returns', 'description' => 'Voir les bons de retour fournisseur'],
            ['name' => 'create_supplier_returns', 'description' => 'Créer des bons de retour fournisseur'],
            ['name' => 'update_supplier_returns', 'description' => 'Modifier des bons de retour fournisseur'],
            ['name' => 'validate_supplier_returns', 'description' => 'Valider des bons de retour fournisseur'],
            ['name' => 'cancel_supplier_returns', 'description' => 'Annuler des bons de retour fournisseur'],

            // Expenses
            ['name' => 'manage_expenses', 'description' => 'Gérer les dépenses'],
            ['name' => 'delete_expenses', 'description' => 'Supprimer des dépenses'],

            // Warehouses
            ['name' => 'manage_warehouses', 'description' => 'Gérer les entrepôts'],
            ['name' => 'toggle_warehouses', 'description' => 'Activer / Désactiver un entrepôt'],

            // Categories & Units
            ['name' => 'manage_categories', 'description' => 'Gérer les catégories'],
            ['name' => 'manage_units', 'description' => 'Gérer les unités'],

            // Subscriptions & Plans & Tenants
            ['name' => 'manage_subscriptions', 'description' => 'Gérer les abonnements'],
            ['name' => 'view_subscriptions', 'description' => 'Voir ses abonnements'],
            ['name' => 'manage_plans', 'description' => 'Gérer les plans'],
            ['name' => 'manage_tenants', 'description' => 'Gérer les entreprises (tenants)'],

            // Settings
            ['name' => 'manage_settings', 'description' => 'Gérer les paramètres'],

            // Notifications
            ['name' => 'manage_notifications', 'description' => 'Gérer les notifications'],

            // Reports
            ['name' => 'read_reports', 'description' => 'Voir les rapports'],
            ['name' => 'manage_reports', 'description' => 'Gérer et consulter les rapports'],
            ['name' => 'view_report', 'description' => 'Voir le menu et les écrans de rapports'],
            ['name' => 'read_document_sequences', 'description' => 'Voir les séquences de documents'],
            ['name' => 'manage_document_sequences', 'description' => 'Gérer les séquences de documents'],

            // Activities
            ['name' => 'read_activities', 'description' => 'Voir les activités'],
            ['name' => 'read_all_activities', 'description' => 'Voir toutes les activités'],
            // Pour Inventaires (Stock & Logistique)
            ['name' => 'manage_inventory', 'description' => 'Gérer les inventaires'],
            ['name' => 'manage_inventories', 'description' => 'Gérer les inventaires'],
            ['name' => 'read_inventories', 'description' => 'Voir les inventaires'],
            ['name' => 'create_inventories', 'description' => 'Créer des inventaires'],
            ['name' => 'validate_inventories', 'description' => 'Valider les inventaires'],
            ['name' => 'reconcile_inventory', 'description' => 'Réconcilier les lignes d’inventaire'],
            ['name' => 'manage_stock', 'description' => 'Gérer le menu et les opérations de stock'],
            ['name' => 'manage_stock_out', 'description' => 'Gérer les sorties de stock'],

            // Pour Paiements Clients / Fournisseurs
            ['name' => 'manage_payments', 'description' => 'Gérer les paiements'],
            ['name' => 'read_payments', 'description' => 'Voir les paiements'],
            ['name' => 'create_payments', 'description' => 'Créer des paiements'],
            ['name' => 'read_client_payments', 'description' => 'Voir les paiements clients'],
            ['name' => 'read_supplier_payments', 'description' => 'Voir les paiements fournisseurs'],
            ['name' => 'cancel_payments', 'description' => 'Annuler les paiements'],
            ['name' => 'read_recouvrement', 'description' => 'Voir les écrans de recouvrement'],
            ['name' => 'manage_wallets', 'description' => 'Gérer les wallets et transferts'],

            // Taxes
            ['name' => 'read_taxes', 'description' => 'Voir les taxes et la TVA'],
            ['name' => 'manage_taxes', 'description' => 'Gérer les taxes et la TVA'],

            // Employés
            ['name' => 'manage_employee', 'description' => 'Gérer les employés'],

            // Pour l’Administration Plateforme - Permissions Globales
            ['name' => 'manage_permissions', 'description' => 'Gérer les permissions globales'],

        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm['name'], 'guard_name' => 'web'],
                [
                    'id' => (string) Str::uuid(),
                    'description' => $perm['description'],
                ]
            );
        }
    }
}
