@php
    $current_user = auth()->user();

    // Helper pour marquer l'item actif selon les patterns de route ou d'URI
    $isActive = function ($patterns) {
        foreach ((array) $patterns as $p) {
            if (request()->routeIs($p) || request()->is($p)) {
                return true;
            }
        }
        return false;
    };

    // Trouver l'abonnement actif pour l'affichage du badge
    $active_subscription = null;
    if (isset($current_user->tenant) && isset($current_user->tenant->subscriptions)) {
        $active_subscription = $current_user->tenant->subscriptions
            ->where('is_active', true)
            ->where('ends_at', '>=', now())
            ->sortByDesc('ends_at')
            ->first();
    }

    // Détermine si un menu parent doit être ouvert/actif
    $isVentesActive =
        ($isActive(['invoices.*']) && request('type') == 'clients') ||
        ($isActive(['payments.*']) && request('type') == 'clients');
    $isAchatsActive =
        ($isActive(['invoices.*']) && request('type') == 'suppliers') ||
        ($isActive(['payments.*']) && request('type') == 'suppliers');
    $isGestionActive = $isActive(['roles.*', 'users.*', 'tenant.subscriptions.*']);

    $isVentesOpen = $isVentesActive ? 'show' : '';
    $isAchatsOpen = $isAchatsActive ? 'show' : '';
    $isGestionOpen = $isGestionActive ? 'show' : '';
@endphp

<style>
    .sidebar {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        color: #ffffff;
    }

    .sidebar .nav-item .nav-link {
        padding: 0.9rem 1rem;
        font-size: 0.95rem;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.85);
    }

    .sidebar .nav-item .nav-link:hover {
        color: #ffffff;
        background-color: rgba(255, 255, 255, 0.15);
    }

    .sidebar .nav-item .nav-link i {
        font-size: 1.1rem;
        margin-right: 0.75rem;
        color: #ffffff;
    }

    .sidebar .nav-item.active .nav-link {
        font-weight: 700;
        color: #ffffff;
        background-color: rgba(0, 0, 0, 0.2);
        border-left: 4px solid #f9f7a7;
    }

    .sidebar .nav-item.active .nav-link i {
        color: #f9f7a7;
    }

    .sidebar .sidebar-heading {
        padding: 0.8rem 1rem 0.5rem;
        font-size: 0.75rem;
        color: #e0e0e0;
    }

    .sidebar .sidebar-brand-text {
        color: #ffffff;
    }

    .sidebar .collapse .collapse-inner {
        padding: 0;
        background-color: #ffffff;
        color: #333333;
    }

    .sidebar .collapse .collapse-inner a.collapse-item {
        padding: 0.4rem 1.5rem;
        font-size: 0.85rem;
        color: #333333;
    }

    .sidebar .collapse .collapse-inner a.collapse-item:hover {
        background-color: #f0f0f0;
    }

    .sidebar .collapse .collapse-inner a.collapse-item.active {
        color: #764ba2;
        background-color: #f0f0f0;
        font-weight: 600;
    }

    /* ===== BRAND SIDEBAR PERSONNALISÉ ===== */
    .sidebar-brand-custom {
        background: #ffffff !important;
        border-bottom: 1px solid #e5e7eb;
    }

    /* Logo */
    .sidebar-logo {
        width: 40px;
        height: 40px;
        object-fit: contain;
    }


    .sidebar-brand-custom .sidebar-brand-text {
        color: #667eea !important;
        /* bleu */
        font-weight: 800;
        font-size: 1.05rem;
        letter-spacing: 0.5px;
    }


    /* Sidebar réduite */
    .sidebar.toggled .sidebar-brand-custom .sidebar-brand-text {
        display: none;
    }

    .sidebar.toggled .sidebar-logo {
        width: 32px;
        height: 32px;
    }
</style>

<ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center sidebar-brand-custom"
        href="{{ url('/dashboard') }}">

        <div class="sidebar-brand-icon">
            <img src="{{ asset('assets/img/logo/favicon.png') }}" alt="DYMO STOCK" class="sidebar-logo">
        </div>

        <div class="sidebar-brand-text mx-2">
            DYMO-STOCK
        </div>
    </a>


    <hr class="sidebar-divider my-0">
    @if ($active_subscription)
        <li class="nav-item py-2 px-3 text-center">
            <span class="badge badge-success text-uppercase shadow-sm d-block text-truncate px-2 py-2">
                <i class="fas fa-crown mr-1"></i>

                <!-- Sur écrans ≥ SM : nom complet -->
                <span class="d-none d-sm-inline">
                    {{ $active_subscription->plan->name }}
                </span>

                <!-- Sur mobile : nom raccourci -->
                <span class="d-inline d-sm-none">
                    {{ Str::limit($active_subscription->plan->name, 10) }}
                </span>
            </span>
        </li>
        <hr class="sidebar-divider">
    @endif


    <!-- Dashboard -->
    @can('access_dashboard')
        <li class="nav-item {{ $isActive(['home', 'dashboard', '/']) ? 'active' : '' }}">
            <a class="nav-link" href="{{ url('/dashboard') }}">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </li>
    @endcan
    @can('manage_categories')
        <hr class="sidebar-divider">

        <!-- Catalogue & Partenaires -->
        <div class="sidebar-heading">Catalogue & Partenaires</div>

        <li class="nav-item {{ $isActive(['categories.*']) ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('categories.index') }}">
                <i class="fas fa-th-large"></i>
                <span>Catégories</span>
            </a>
        </li>
    @endcan

    @can('read_products')
        <li class="nav-item {{ $isActive(['products.*']) ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('products.index') }}">
                <i class="fas fa-box-open"></i>
                <span>Produits</span>
            </a>
        </li>
    @endcan

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
        <hr class="sidebar-divider">
    @endcan
    <!-- Stock & Logistique -->
    @canAny(['manage_warehouses', 'manage_inventory'])
        <div class="sidebar-heading">Stock & Logistique</div>

        @can('manage_warehouses')
            <li class="nav-item {{ $isActive(['warehouses.*']) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('warehouses.index') }}">
                    <i class="fas fa-warehouse"></i>
                    <span>Entrepôts</span>
                </a>
            </li>
        @endcan
    @endcanAny

    <!-- Transactions -->
    @canAny(['manage_supplier_invoices', 'manage_client_invoices', 'read_supplier_payments', 'read_client_payments'])
        <div class="sidebar-heading">Transactions</div>
        <li class="nav-item {{ $isVentesActive ? 'active' : '' }}">
            <a class="nav-link {{ $isVentesActive ? '' : 'collapsed' }}" href="#" data-toggle="collapse"
                data-target="#collapseVentes" aria-expanded="{{ $isVentesActive ? 'true' : 'false' }}"
                aria-controls="collapseVentes">
                <i class="fas fa-handshake"></i>
                <span>Ventes</span>
            </a>
            <div id="collapseVentes" class="collapse {{ $isVentesOpen }}" aria-labelledby="headingVentes"
                data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    @can('manage_client_invoices')
                        <a class="collapse-item {{ request()->routeIs('invoices.index') && request('type') === 'clients' && !request()->has('status') ? 'active' : '' }}"
                            href="{{ route('invoices.index', ['type' => 'clients']) }}">
                            Factures Clients
                        </a>
                    @endcan

                    @can('read_client_payments')
                        <a class="collapse-item {{ request()->routeIs('payments.index') && request('type') === 'clients' ? 'active' : '' }}"
                            href="{{ route('payments.index', ['type' => 'clients']) }}">
                            Paiements Clients
                        </a>
                    @endcan

                    @can('read_recouvrement')
                        <a class="collapse-item {{ request()->routeIs('invoices.unpaid') && request('type') === 'clients' && !request()->has('status') ? 'active' : '' }}"
                            href="{{ route('invoices.unpaid', ['type' => 'clients']) }}">
                            Récouvrements
                        </a>
                    @endcan

                </div>
            </div>
        </li>


        <li class="nav-item {{ $isAchatsActive ? 'active' : '' }}">
            <a class="nav-link {{ $isAchatsActive ? '' : 'collapsed' }}" href="#" data-toggle="collapse"
                data-target="#collapseAchats" aria-expanded="{{ $isAchatsActive ? 'true' : 'false' }}"
                aria-controls="collapseAchats">
                <i class="fas fa-shopping-basket"></i>
                <span>Achats</span>
            </a>
            <div id="collapseAchats" class="collapse {{ $isAchatsOpen }}" aria-labelledby="headingAchats"
                data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    @can('manage_supplier_invoices')
                        <a class="collapse-item {{ $isActive(['invoices.*']) && request('type') == 'suppliers' ? 'active' : '' }}"
                            href="{{ route('invoices.index', ['type' => 'suppliers']) }}">Factures Fournisseurs</a>
                    @endcan
                    @can('read_supplier_payments')
                        <a class="collapse-item {{ $isActive(['payments.*']) && request('type') == 'suppliers' ? 'active' : '' }}"
                            href="{{ route('payments.index', ['type' => 'suppliers']) }}">Paiements Fournisseurs</a>
                    @endcan
                </div>
            </div>
        </li>
    @endcanAny
    <!-- Finance & Rapports -->
    @can('manage_expenses')
        <hr class="sidebar-divider">
        <li class="nav-item {{ $isActive(['expenses.*']) ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('expenses.index') }}">
                <i class="fas fa-money-bill-wave"></i>
                <span>Dépenses</span>
            </a>
        </li>
    @endcan
    @can('view_report')
        <div class="sidebar-heading">Rapport</div>

        <li class="nav-item {{ $isRapportActive ?? false ? 'active' : '' }}">
            <a class="nav-link {{ $isRapportActive ?? false ? '' : 'collapsed' }}" href="#" data-toggle="collapse"
                data-target="#collapseRapport" aria-expanded="{{ $isRapportActive ?? false ? 'true' : 'false' }}"
                aria-controls="collapseRapport">

                <i class="fas fa-chart-line"></i>
                <span>Rapports</span>
            </a>

            <div id="collapseRapport" class="collapse {{ $isRapportOpen ?? '' }}" aria-labelledby="headingRapport"
                data-parent="#accordionSidebar">

                <div class="bg-white py-2 collapse-inner rounded">

                    <!-- Rapport produit -->
                    <a class="collapse-item {{ request()->routeIs('rapport.produit') ? 'active' : '' }}"
                        href="{{ route('reports.products') }}">
                        <i class="fas fa-box-open mr-1"></i>
                        Par produit
                    </a>

                    <a class="collapse-item {{ request()->routeIs('rapport.fournisseurs') ? 'active' : '' }}"
                        href="{{ route('reports.reportSuppliers') }}">
                        <i class="fas fa-users mr-1"></i>
                        Par fournisseur
                    </a>

                    <!-- Rapport fournisseur -->
                    <a class="collapse-item {{ request()->routeIs('rapport.fournisseur') ? 'active' : '' }}"
                        href="{{ route('reports.suppliers') }}">
                        <i class="fas fa-truck mr-1"></i>
                        Paiement fournisseur
                    </a>

                    <!-- Rapport financiers -->
                    <a class="collapse-item {{ request()->routeIs('rapport.financiers') ? 'active' : '' }}"
                        href="{{ route('reports.index') }}">
                        <i class="fas fa-file-invoice-dollar mr-1"></i>
                        Financiers
                    </a>
                </div>
            </div>
        </li>
    @endcan



    {{-- @can('read_reports')
        <li class="nav-item {{ $isActive(['reports.*']) ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('reports.index') }}">
                <i class="fas fa-chart-line"></i>
                <span>Rapports Financiers</span>
            </a>
        </li>
    @endcan --}}


    <hr class="sidebar-divider">

    <!-- Gestion du Système -->
    <div class="sidebar-heading">Gestion du Système</div>

    <li class="nav-item {{ $isGestionActive ? 'active' : '' }}">
        <a class="nav-link {{ $isGestionActive ? '' : 'collapsed' }}" href="#" data-toggle="collapse"
            data-target="#collapseGestion" aria-expanded="{{ $isGestionActive ? 'true' : 'false' }}"
            aria-controls="collapseGestion">
            <i class="fas fa-users-cog"></i>
            <span>Utilisateurs & Accès</span>
        </a>
        <div id="collapseGestion" class="collapse {{ $isGestionOpen }}" aria-labelledby="headingGestion"
            data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                @can('manage_roles')
                    <a class="collapse-item {{ $isActive(['roles.*']) ? 'active' : '' }}"
                        href="{{ route('roles.index') }}">Rôles</a>
                @endcan
                @can('manage_users')
                    <a class="collapse-item {{ $isActive(['users.*']) ? 'active' : '' }}"
                        href="{{ route('users.index') }}">Utilisateurs</a>
                @endcan
                @can('view_subscriptions')
                    <a class="collapse-item {{ $isActive(['tenant.subscriptions.*']) ? 'active' : '' }}"
                        href="{{ route('tenant.subscriptions.index') }}">Mes Souscriptions</a>
                @endcan
            </div>
        </div>
    </li>

    @can('manage_stock')
        <!-- Sidebar Stock -->
        <div class="sidebar-heading">Stock</div>


        <li class="nav-item {{ $isStockActive ?? false ? 'active' : '' }}">
            <a class="nav-link {{ $isStockActive ?? false ? '' : 'collapsed' }}" href="#" data-toggle="collapse"
                data-target="#collapseStock" aria-expanded="{{ $isStockActive ?? false ? 'true' : 'false' }}"
                aria-controls="collapseStock">
                <i class="fas fa-boxes"></i>
                <span>Stock</span>
            </a>

            <div id="collapseStock" class="collapse {{ $isStockOpen ?? '' }}" aria-labelledby="headingStock"
                data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">

                    <!-- Inventaires -->
                    <a class="collapse-item" href="{{ route('inventories.index') }}">
                        <i class="fas fa-clipboard-list mr-1"></i>
                        Inventaires
                    </a>

                    {{-- <!-- Mouvement général -->
                    <a class="collapse-item {{ request()->routeIs('inventory.movements') ? 'active' : '' }}"
                        href="#">
                        <i class="fas fa-exchange-alt mr-1"></i>
                        Mouvement de stock
                    </a> --}}

                    <!-- Liste des sorties -->
                    <a class="collapse-item {{ request()->routeIs('stockouts.index') ? 'active' : '' }}"
                        href="{{ route('stockout.index') }}">
                        <i class="fas fa-truck-loading mr-1"></i>
                        Sorties de stock
                    </a>
                    <hr class="sidebar-divider">
                </div>
            </div>
        </li>
    @endcan




    <!-- Administration Plateforme -->
    @if ($current_user->is_platform_user())
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Administration Plateforme</div>

        @can('manage_permissions')
            <li class="nav-item {{ $isActive(['admin.permissions.*']) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.permissions.index') }}">
                    <i class="fas fa-shield-alt"></i>
                    <span>Permissions Globales</span>
                </a>
            </li>
        @endcan

        @can('manage_plans')
            <li class="nav-item {{ $isActive(['admin.plans.*']) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.plans.index') }}">
                    <i class="fas fa-gem"></i>
                    <span>Plans d'Abonnement</span>
                </a>
            </li>
        @endcan

        @can('manage_tenants')
            <li class="nav-item {{ $isActive(['admin.tenants.*']) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.tenants.index') }}">
                    <i class="fas fa-city"></i>
                    <span>Entreprises (Tenants)</span>
                </a>
            </li>
        @endcan

        @can('manage_subscriptions')
            <li class="nav-item {{ $isActive(['admin.subscriptions.*']) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.subscriptions.index') }}">
                    <i class="fas fa-receipt"></i>
                    <span>Souscriptions Globales</span>
                </a>
            </li>
        @endcan
    @endif

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>


<noscript>
    <style>
        body {
            display: none;
        }
    </style>
</noscript>
