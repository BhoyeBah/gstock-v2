@php
    $current_user = auth()->user();
    $routeType = (string) request()->route('type');
    $currentModule = (string) request()->route('module');

    $isActive = function ($patterns) {
        foreach ((array) $patterns as $pattern) {
            if (request()->routeIs($pattern) || request()->is($pattern)) {
                return true;
            }
        }

        return false;
    };

    $hasAnyPermission = function (array $permissions) use ($current_user) {
        if (! $current_user) {
            return false;
        }

        foreach ($permissions as $permission) {
            if ($current_user->can($permission)) {
                return true;
            }
        }

        return false;
    };

    $isModuleActive = fn(string $module) => request()->routeIs('modules.show') && $currentModule === $module;

    $active_subscription = null;
    if (isset($current_user->tenant) && isset($current_user->tenant->subscriptions)) {
        $active_subscription = $current_user->tenant->subscriptions
            ->where('is_active', true)
            ->where('ends_at', '>=', now())
            ->sortByDesc('ends_at')
            ->first();
    }

    $isClientInvoiceRoute = request()->routeIs('invoices.*') && in_array($routeType, ['client', 'clients'], true);
    $isSupplierInvoiceRoute = request()->routeIs('invoices.*') && in_array($routeType, ['supplier', 'suppliers'], true);
    $isClientPaymentRoute = request()->routeIs('payments.*') && in_array($routeType, ['client', 'clients'], true);
    $isSupplierPaymentRoute = request()->routeIs('payments.*') && in_array($routeType, ['supplier', 'suppliers'], true);
    $isCustomerReturnRoute = request()->routeIs('customer-returns.*');
    $isSupplierReturnRoute = request()->routeIs('supplier-returns.*');
    $isCustomerCreditNoteRoute = request()->routeIs('customer-credit-notes.*');
    $isSupplierCreditNoteRoute = request()->routeIs('supplier-credit-notes.*');
    $isReturnsDashboardRoute = request()->routeIs('returns.*');

    $isSalesActive = request()->routeIs('quotes.*')
        || request()->routeIs('sale-orders.*')
        || request()->routeIs('delivery-notes.*')
        || $isCustomerReturnRoute
        || $isCustomerCreditNoteRoute
        || $isReturnsDashboardRoute
        || $isClientInvoiceRoute
        || $isClientPaymentRoute;

    $isPurchasesActive = request()->routeIs('purchase-orders.*')
        || request()->routeIs('goods-receipts.*')
        || $isSupplierReturnRoute
        || $isSupplierCreditNoteRoute
        || $isReturnsDashboardRoute
        || $isSupplierInvoiceRoute
        || $isSupplierPaymentRoute;

    $isStockActive = $isActive(['warehouses.*', 'inventories.*', 'stockout.*', 'batches.*', 'movements.*', 'transfers.*'])
        || $isModuleActive('batches')
        || $isModuleActive('movements')
        || $isModuleActive('transfers');

    $isReportsActive = $isActive(['reports.*']);
    $isAdministrationActive = $isActive([
        'settings.*',
        'document-sequences.*',
        'roles.*',
        'users.*',
        'tenant.subscriptions.*',
        'employes.*',
    ]);
@endphp

<style>
    .sidebar {
        background:
            radial-gradient(circle at top, rgba(59, 130, 246, 0.18), transparent 22%),
            linear-gradient(180deg, #0f172a 0%, #111827 100%) !important;
        color: #ffffff;
        box-shadow: 16px 0 40px rgba(15, 23, 42, 0.18);
    }

    .sidebar .nav-item .nav-link {
        padding: 0.9rem 1rem;
        font-size: 0.95rem;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.85);
        border-radius: 14px;
        margin: 0 0.5rem;
        transition: all .16s ease;
    }

    .sidebar .nav-item .nav-link:hover {
        color: #ffffff;
        background-color: rgba(255, 255, 255, 0.10);
    }

    .sidebar .nav-item .nav-link i {
        font-size: 1.1rem;
        margin-right: 0.75rem;
        color: #ffffff;
    }

    .sidebar .nav-item.active .nav-link {
        font-weight: 700;
        color: #ffffff;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.38), rgba(59, 130, 246, 0.18));
        border-left: 4px solid #93c5fd;
    }

    .sidebar .nav-item.active .nav-link i {
        color: #dbeafe;
    }

    .sidebar .sidebar-heading {
        padding: 1rem 1.2rem 0.45rem;
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.62);
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .sidebar .sidebar-brand-text {
        color: #ffffff;
    }

    .sidebar .collapse .collapse-inner {
        padding: 0.35rem 0.25rem 0.35rem 0.5rem;
        background-color: rgba(255, 255, 255, 0.06);
        color: #ffffff;
        border-radius: 16px;
        margin: 0 0.5rem 0.5rem;
    }

    .sidebar .collapse .collapse-inner a.collapse-item {
        padding: 0.65rem 1rem;
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.84);
        border-radius: 12px;
        margin: 0.15rem 0;
    }

    .sidebar .collapse .collapse-inner a.collapse-item:hover {
        background-color: rgba(255, 255, 255, 0.08);
        color: #ffffff;
    }

    .sidebar .collapse .collapse-inner a.collapse-item.active {
        color: #ffffff;
        background: rgba(59, 130, 246, 0.18);
        font-weight: 600;
    }

    .sidebar .collapse .collapse-inner a.collapse-item.collapse-item--customer-return {
        border-left: 3px solid rgba(56, 189, 248, 0.5);
    }

    .sidebar .collapse .collapse-inner a.collapse-item.collapse-item--supplier-return {
        border-left: 3px solid rgba(251, 191, 36, 0.5);
    }

    .sidebar .collapse .collapse-inner a.collapse-item .collapse-item__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.25rem;
        margin-right: .55rem;
        opacity: .95;
    }

    .sidebar .collapse .collapse-inner a.collapse-item.collapse-item--customer-return .collapse-item__icon {
        color: #67e8f9;
    }

    .sidebar .collapse .collapse-inner a.collapse-item.collapse-item--supplier-return .collapse-item__icon {
        color: #fbbf24;
    }

    .sidebar .collapse .collapse-inner a.collapse-item.collapse-item--customer-return.active {
        background: rgba(56, 189, 248, 0.16);
    }

    .sidebar .collapse .collapse-inner a.collapse-item.collapse-item--supplier-return.active {
        background: rgba(251, 191, 36, 0.16);
    }

    .sidebar-brand-custom {
        background: rgba(255, 255, 255, 0.95) !important;
        border-bottom: 1px solid rgba(15, 23, 42, 0.08);
        margin: 0.75rem 0.75rem 0.5rem;
        border-radius: 18px;
        padding: 1rem 0.75rem;
    }

    .sidebar-logo {
        width: 40px;
        height: 40px;
        object-fit: contain;
    }

    .sidebar-brand-custom .sidebar-brand-text {
        color: #0f172a !important;
        font-weight: 800;
        font-size: 1.05rem;
        letter-spacing: 0.5px;
    }

    .sidebar.toggled .sidebar-brand-custom .sidebar-brand-text {
        display: none;
    }

    .sidebar.toggled .sidebar-logo {
        width: 32px;
        height: 32px;
    }

    .sidebar-divider {
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        margin: 0.9rem 1rem;
    }

    .sidebar .badge {
        border-radius: 999px;
        padding: 0.35rem 0.55rem;
        font-size: 0.7rem;
    }
</style>

<ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">
    <a class="sidebar-brand d-flex align-items-center justify-content-center sidebar-brand-custom"
        href="{{ $current_user && $current_user->is_platform_user() ? route('admin.dashboard') : route('dashboard') }}">
        <div class="sidebar-brand-icon">
            <img src="{{ asset('assets/img/logo/favicon.png') }}" alt="DYMO STOCK" class="sidebar-logo">
        </div>
        <div class="sidebar-brand-text mx-2">DYMO-STOCK</div>
    </a>

    <hr class="sidebar-divider my-0">

    @if ($active_subscription)
        <li class="nav-item py-2 px-3 text-center">
            <span class="badge badge-success text-uppercase shadow-sm d-block text-truncate px-2 py-2">
                <i class="fas fa-crown mr-1"></i>
                <span class="d-none d-sm-inline">{{ $active_subscription->plan->name }}</span>
                <span class="d-inline d-sm-none">{{ Str::limit($active_subscription->plan->name, 10) }}</span>
            </span>
        </li>
        <hr class="sidebar-divider">
    @endif

    @unless ($current_user && $current_user->is_platform_user())
        @can('access_dashboard')
            <div class="sidebar-heading">Dashboard</div>
            <li class="nav-item {{ $isActive(['dashboard', 'home', '/']) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-home"></i>
                    <span>Tableau de bord</span>
                </a>
            </li>
        @endcan

        @if ($hasAnyPermission(['manage_categories', 'read_products', 'manage_units']))
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Catalogue</div>

            @can('read_products')
                <li class="nav-item {{ $isActive(['products.*']) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('products.index') }}">
                        <i class="fas fa-box-open"></i>
                        <span>Produits</span>
                    </a>
                </li>
            @endcan

            @can('manage_categories')
                <li class="nav-item {{ $isActive(['categories.*']) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('categories.index') }}">
                        <i class="fas fa-tags"></i>
                        <span>Catégories</span>
                    </a>
                </li>
            @endcan

            @can('manage_units')
                <li class="nav-item {{ $isActive(['admin.units.*']) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin.units.index') }}">
                        <i class="fas fa-balance-scale"></i>
                        <span>Unités</span>
                    </a>
                </li>
            @endcan
        @endif

        @if ($hasAnyPermission(['read_clients', 'read_suppliers']))
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Contacts</div>

            @can('read_clients')
                <li class="nav-item {{ $isActive(['clients.*']) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('clients.index') }}">
                        <i class="fas fa-user-tie"></i>
                        <span>Clients</span>
                    </a>
                </li>
            @endcan

            @can('read_suppliers')
                <li class="nav-item {{ $isActive(['suppliers.*']) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('suppliers.index') }}">
                        <i class="fas fa-shipping-fast"></i>
                        <span>Fournisseurs</span>
                    </a>
                </li>
            @endcan
        @endif

        @if ($hasAnyPermission(['read_quotes', 'read_sale_orders', 'read_deliveries', 'read_customer_returns', 'read_client_payments', 'manage_client_invoices']))
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Commercial / Ventes</div>

            <li class="nav-item {{ $isSalesActive ? 'active' : '' }}">
                <a class="nav-link {{ $isSalesActive ? '' : 'collapsed' }}" href="#" data-toggle="collapse"
                    data-target="#collapseSales" aria-expanded="{{ $isSalesActive ? 'true' : 'false' }}"
                    aria-controls="collapseSales">
                    <i class="fas fa-handshake"></i>
                    <span>Ventes</span>
                </a>

                <div id="collapseSales" class="collapse {{ $isSalesActive ? 'show' : '' }}" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        @if ($hasAnyPermission(['read_quotes', 'manage_client_invoices']))
                            <a class="collapse-item {{ request()->routeIs('quotes.*') ? 'active' : '' }}"
                                href="{{ route('quotes.index') }}">
                                Devis / Proforma
                            </a>
                        @endif

                        @if ($hasAnyPermission(['read_sale_orders', 'manage_client_invoices']))
                            <a class="collapse-item {{ request()->routeIs('sale-orders.*') ? 'active' : '' }}"
                                href="{{ route('sale-orders.index') }}">
                                Commandes clients
                            </a>
                        @endif

                        @if ($hasAnyPermission(['read_deliveries', 'manage_stock', 'manage_client_invoices']))
                            <a class="collapse-item {{ request()->routeIs('delivery-notes.*') ? 'active' : '' }}"
                                href="{{ route('delivery-notes.index') }}">
                                Bons de livraison
                            </a>
                        @endif

                        @can('read_customer_returns')
                            <a class="collapse-item collapse-item--customer-return {{ $isCustomerReturnRoute ? 'active' : '' }}"
                                href="{{ route('customer-returns.index') }}">
                                <span class="collapse-item__icon"><i class="fas fa-undo"></i></span>
                                Retours clients
                            </a>
                        @endcan

                        @if ($hasAnyPermission(['read_customer_returns', 'read_supplier_returns', 'manage_client_invoices', 'manage_supplier_invoices']))
                            <a class="collapse-item {{ $isReturnsDashboardRoute ? 'active' : '' }}"
                                href="{{ route('returns.index') }}">
                                <span class="collapse-item__icon"><i class="fas fa-chart-pie"></i></span>
                                Tableau retours / avoirs
                            </a>
                        @endif

                        @can('manage_client_invoices')
                            <a class="collapse-item {{ $isClientInvoiceRoute ? 'active' : '' }}"
                                href="{{ route('invoices.index', ['type' => 'clients']) }}">
                                Factures clients
                            </a>
                        @endcan

                        @can('manage_client_invoices')
                            <a class="collapse-item {{ $isCustomerCreditNoteRoute ? 'active' : '' }}"
                                href="{{ route('customer-credit-notes.index') }}">
                                Avoirs clients
                            </a>
                        @endcan

                        @can('read_client_payments')
                            <a class="collapse-item {{ $isClientPaymentRoute ? 'active' : '' }}"
                                href="{{ route('payments.index', ['type' => 'clients']) }}">
                                Paiements clients
                            </a>
                        @endcan
                    </div>
                </div>
            </li>
        @endif

        @if ($hasAnyPermission(['manage_supplier_invoices', 'read_supplier_returns', 'read_supplier_payments', 'read_purchase_orders', 'read_receipts']))
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Achats</div>

            <li class="nav-item {{ $isPurchasesActive ? 'active' : '' }}">
                <a class="nav-link {{ $isPurchasesActive ? '' : 'collapsed' }}" href="#" data-toggle="collapse"
                    data-target="#collapsePurchases" aria-expanded="{{ $isPurchasesActive ? 'true' : 'false' }}"
                    aria-controls="collapsePurchases">
                    <i class="fas fa-shopping-basket"></i>
                    <span>Achats</span>
                </a>

                <div id="collapsePurchases" class="collapse {{ $isPurchasesActive ? 'show' : '' }}"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        @if ($hasAnyPermission(['read_purchase_orders', 'manage_supplier_invoices']))
                            <a class="collapse-item {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}"
                                href="{{ route('purchase-orders.index') }}">
                                Commandes fournisseurs
                            </a>
                        @endif

                        @if ($hasAnyPermission(['read_receipts', 'manage_stock', 'manage_supplier_invoices']))
                            <a class="collapse-item {{ request()->routeIs('goods-receipts.*') ? 'active' : '' }}"
                                href="{{ route('goods-receipts.index') }}">
                                Bons de réception
                            </a>
                        @endif

                        @can('read_supplier_returns')
                            <a class="collapse-item collapse-item--supplier-return {{ $isSupplierReturnRoute ? 'active' : '' }}"
                                href="{{ route('supplier-returns.index') }}">
                                <span class="collapse-item__icon"><i class="fas fa-undo"></i></span>
                                Retours fournisseurs
                            </a>
                        @endcan

                        @can('manage_supplier_invoices')
                            <a class="collapse-item {{ $isSupplierInvoiceRoute ? 'active' : '' }}"
                                href="{{ route('invoices.index', ['type' => 'suppliers']) }}">
                                Factures fournisseurs
                            </a>
                        @endcan

                        @can('manage_supplier_invoices')
                            <a class="collapse-item {{ $isSupplierCreditNoteRoute ? 'active' : '' }}"
                                href="{{ route('supplier-credit-notes.index') }}">
                                Avoirs fournisseurs
                            </a>
                        @endcan

                        @can('read_supplier_payments')
                            <a class="collapse-item {{ $isSupplierPaymentRoute ? 'active' : '' }}"
                                href="{{ route('payments.index', ['type' => 'suppliers']) }}">
                                Paiements fournisseurs
                            </a>
                        @endcan
                    </div>
                </div>
            </li>
        @endif

        @if ($hasAnyPermission(['manage_warehouses', 'manage_stock', 'manage_inventories']))
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Stock &amp; Logistique</div>

            <li class="nav-item {{ $isStockActive ? 'active' : '' }}">
                <a class="nav-link {{ $isStockActive ? '' : 'collapsed' }}" href="#" data-toggle="collapse"
                    data-target="#collapseStock" aria-expanded="{{ $isStockActive ? 'true' : 'false' }}"
                    aria-controls="collapseStock">
                    <i class="fas fa-boxes"></i>
                    <span>Stock</span>
                </a>

                <div id="collapseStock" class="collapse {{ $isStockActive ? 'show' : '' }}" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        @can('manage_warehouses')
                            <a class="collapse-item {{ $isActive(['warehouses.*']) ? 'active' : '' }}"
                                href="{{ route('warehouses.index') }}">
                                Entrepôts
                            </a>
                        @endcan

                        @if ($hasAnyPermission(['manage_stock', 'manage_warehouses']))
                            <a class="collapse-item {{ $isActive(['batches.*']) ? 'active' : '' }}"
                                href="{{ route('batches.index') }}">
                                Lots / Batches
                            </a>
                        @endif

                        @can('manage_inventories')
                            <a class="collapse-item {{ $isActive(['inventories.*']) ? 'active' : '' }}"
                                href="{{ route('inventories.index') }}">
                                Inventaire physique
                            </a>
                        @endcan

                        @if ($hasAnyPermission(['manage_stock', 'manage_inventories']))
                            <a class="collapse-item {{ $isActive(['movements.*']) ? 'active' : '' }}"
                                href="{{ route('movements.index') }}">
                                Mouvements de stock
                            </a>
                        @endif

                        @if ($hasAnyPermission(['manage_warehouses', 'manage_stock']))
                            <a class="collapse-item {{ $isActive(['transfers.*']) ? 'active' : '' }}"
                                href="{{ route('transfers.index') }}">
                                Transferts internes
                            </a>
                        @endif

                        @can('manage_stock_out')
                            <a class="collapse-item {{ $isActive(['stockout.*']) ? 'active' : '' }}"
                                href="{{ route('stockout.index') }}">
                                Sorties de stock
                            </a>
                        @endcan
                    </div>
                </div>
            </li>
        @endif

        @if ($hasAnyPermission(['manage_wallets', 'manage_expenses']))
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Finances</div>

            @can('manage_wallets')
                <li class="nav-item {{ $isActive(['wallet.*']) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('wallet.index') }}">
                        <i class="fas fa-wallet"></i>
                        <span>Wallets / Caisses</span>
                    </a>
                </li>
            @endcan

            @can('manage_expenses')
                <li class="nav-item {{ $isActive(['expenses.*']) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('expenses.index') }}">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Dépenses</span>
                    </a>
                </li>
            @endcan
        @endif

        @can('manage_reports')
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Rapports</div>

            <li class="nav-item {{ $isReportsActive ? 'active' : '' }}">
                <a class="nav-link {{ $isReportsActive ? '' : 'collapsed' }}" href="#" data-toggle="collapse"
                    data-target="#collapseReports" aria-expanded="{{ $isReportsActive ? 'true' : 'false' }}"
                    aria-controls="collapseReports">
                    <i class="fas fa-chart-line"></i>
                    <span>Rapports</span>
                </a>

                <div id="collapseReports" class="collapse {{ $isReportsActive ? 'show' : '' }}"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item {{ request()->routeIs('reports.index') ? 'active' : '' }}"
                            href="{{ route('reports.index') }}">
                            Synthèse financière
                        </a>
                        <a class="collapse-item {{ request()->routeIs('reports.journal') ? 'active' : '' }}"
                            href="{{ route('reports.journal') }}">
                            Journal
                        </a>
                        <a class="collapse-item {{ request()->routeIs('reports.products') ? 'active' : '' }}"
                            href="{{ route('reports.products') }}">
                            Rapport produits
                        </a>
                        <a class="collapse-item {{ request()->routeIs('reports.reportSuppliers') ? 'active' : '' }}"
                            href="{{ route('reports.reportSuppliers') }}">
                            Rapport fournisseurs
                        </a>
                        <a class="collapse-item {{ request()->routeIs('reports.suppliers') ? 'active' : '' }}"
                            href="{{ route('reports.suppliers') }}">
                            Paiements fournisseurs
                        </a>
                    </div>
                </div>
            </li>
        @endcan

        @if ($hasAnyPermission(['manage_settings', 'read_document_sequences', 'manage_users', 'manage_roles', 'view_subscriptions']))
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Paramètres</div>

            <li class="nav-item {{ $isAdministrationActive ? 'active' : '' }}">
                <a class="nav-link {{ $isAdministrationActive ? '' : 'collapsed' }}" href="#" data-toggle="collapse"
                    data-target="#collapseAdministration" aria-expanded="{{ $isAdministrationActive ? 'true' : 'false' }}"
                    aria-controls="collapseAdministration">
                    <i class="fas fa-users-cog"></i>
                    <span>Administration</span>
                </a>

                <div id="collapseAdministration" class="collapse {{ $isAdministrationActive ? 'show' : '' }}"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        @if ($hasAnyPermission(['manage_settings', 'read_taxes', 'manage_taxes']))
                            <a class="collapse-item {{ $isActive(['settings.*']) ? 'active' : '' }}"
                                href="{{ route('settings.index') }}">
                                Taxes / TVA
                            </a>
                        @endif

                        @can('read_document_sequences')
                            <a class="collapse-item {{ $isActive(['document-sequences.*']) ? 'active' : '' }}"
                                href="{{ route('document-sequences.index') }}">
                                Séquences documents
                            </a>
                        @endcan

                        @can('manage_users')
                            <a class="collapse-item {{ $isActive(['users.*']) ? 'active' : '' }}"
                                href="{{ route('users.index') }}">
                                Utilisateurs
                            </a>
                        @endcan

                        @can('manage_roles')
                            <a class="collapse-item {{ $isActive(['roles.*']) ? 'active' : '' }}"
                                href="{{ route('roles.index') }}">
                                Rôles &amp; permissions
                            </a>
                        @endcan

                        @can('view_subscriptions')
                            <a class="collapse-item {{ $isActive(['tenant.subscriptions.*']) ? 'active' : '' }}"
                                href="{{ route('tenant.subscriptions.index') }}">
                                Mes souscriptions
                            </a>
                        @endcan

                        @can('manage_employee')
                            <a class="collapse-item {{ $isActive(['employes.*']) ? 'active' : '' }}"
                                href="{{ route('employes.index') }}">
                                Employés
                            </a>
                        @endcan
                    </div>
                </div>
            </li>
        @endif
    @endunless

    @if ($current_user && $current_user->is_platform_user())
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Administration Plateforme</div>

        @can('view_platform_dashboard')
            <li class="nav-item {{ $isActive(['admin.dashboard']) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard Plateforme</span>
                </a>
            </li>
        @endcan

        @can('manage_permissions')
            <li class="nav-item {{ $isActive(['admin.permissions.*']) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.permissions.index') }}">
                    <i class="fas fa-shield-alt"></i>
                    <span>Permissions globales</span>
                </a>
            </li>
        @endcan

        @can('manage_plans')
            <li class="nav-item {{ $isActive(['admin.plans.*']) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.plans.index') }}">
                    <i class="fas fa-gem"></i>
                    <span>Plans SaaS</span>
                </a>
            </li>
        @endcan

        @can('manage_plan_permissions')
            <li class="nav-item {{ $isActive(['admin.plan-permissions.*']) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.plan-permissions.index') }}">
                    <i class="fas fa-list-check"></i>
                    <span>Permissions par plan</span>
                </a>
            </li>
        @endcan

        @can('manage_tenants')
            <li class="nav-item {{ $isActive(['admin.tenants.*']) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.tenants.index') }}">
                    <i class="fas fa-city"></i>
                    <span>Entreprises</span>
                </a>
            </li>
        @endcan

        @can('manage_subscriptions')
            <li class="nav-item {{ $isActive(['admin.subscriptions.*']) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.subscriptions.index') }}">
                    <i class="fas fa-receipt"></i>
                    <span>Abonnements</span>
                </a>
            </li>
        @endcan

        @can('manage_platform_settings')
            <li class="nav-item {{ $isActive(['admin.settings.*']) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.settings.index') }}">
                    <i class="fas fa-cogs"></i>
                    <span>Paramètres plateforme</span>
                </a>
            </li>
        @endcan
    @endif

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>
