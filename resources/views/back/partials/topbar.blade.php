<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    <!-- Sidebar Toggle (Topbar) -->
    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Topbar Search -->
    <div id="search-wrap" class="d-none d-md-block mr-3">
        <div class="input-group">
            <input id="search-input" type="search" class="form-control bg-light border-1 small"
                placeholder="Rechercher (ex: produit, factures, fournisseurs)" aria-label="Rechercher"
                autocomplete="off">
            {{-- <div class="input-group-append">
                <button class="btn btn-primary" type="button"
                    onclick="document.getElementById('search-input').focus();">
                    <i class="fas fa-search fa-sm"></i>
                </button>
            </div> --}}
        </div>
        <ul id="search-results" class="list-unstyled mt-1"></ul>
    </div>


    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto">

        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
        <li class="nav-item dropdown no-arrow d-sm-none">
            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-search fa-fw"></i>
            </a>
            <!-- Dropdown - Messages -->
            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                aria-labelledby="searchDropdown">
                <form class="form-inline mr-auto w-100 navbar-search">
                    <div class="input-group">
                        <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..."
                            aria-label="Search" aria-describedby="basic-addon2">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </li>

        <!-- Nav Item - Alerts -->
        {{-- <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>
                <!-- Counter - Alerts -->
                <span class="badge badge-danger badge-counter">3+</span>
            </a>
            <!-- Dropdown - Alerts -->
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">
                    Alerts Center
                </h6>

            </div>
        </li> --}}

        <!-- Nav Item - Messages -->
        {{-- <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-envelope fa-fw"></i>
                <!-- Counter - Messages -->
                <span class="badge badge-danger badge-counter">7</span>
            </a>
            <!-- Dropdown - Messages -->
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="messagesDropdown">
                <h6 class="dropdown-header">
                    Message Center
                </h6>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="dropdown-list-image mr-3">
                        <img class="rounded-circle" src="{{ asset('assets/img/undraw_profile_1.svg') }}" alt="...">
                        <div class="status-indicator bg-success"></div>
                    </div>
                    <div class="font-weight-bold">
                        <div class="text-truncate">Hi there! I am wondering if you can help me with a
                            problem I've been having.</div>
                        <div class="small text-gray-500">Emily Fowler · 58m</div>
                    </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="dropdown-list-image mr-3">
                        <img class="rounded-circle" src="{{ asset('assets/img/undraw_profile_2.svg') }}" alt="...">
                        <div class="status-indicator"></div>
                    </div>
                    <div>
                        <div class="text-truncate">I have the photos that you ordered last month, how
                            would you like them sent to you?</div>
                        <div class="small text-gray-500">Jae Chun · 1d</div>
                    </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="dropdown-list-image mr-3">
                        <img class="rounded-circle" src="{{ asset('assets/img/undraw_profile_3.svg') }}" alt="...">
                        <div class="status-indicator bg-warning"></div>
                    </div>
                    <div>
                        <div class="text-truncate">Last month's report looks great, I am very happy with
                            the progress so far, keep up the good work!</div>
                        <div class="small text-gray-500">Morgan Alvarez · 2d</div>
                    </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="dropdown-list-image mr-3">
                        <img class="rounded-circle" src="https://source.unsplash.com/Mv9hjnEUHR4/60x60"
                            alt="...">
                        <div class="status-indicator bg-success"></div>
                    </div>
                    <div>
                        <div class="text-truncate">Am I a good boy? The reason I ask is because someone
                            told me that people say this to all dogs, even if they aren't good...</div>
                        <div class="small text-gray-500">Chicken the Dog · 2w</div>
                    </div>
                </a>
                <a class="dropdown-item text-center small text-gray-500" href="#">Read More Messages</a>
            </div>
        </li> --}}

        <!-- Nav Item - Quick Actions -->
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="quickActionsDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-layer-group"></i>
            </a>

            <!-- Dropdown - Quick Actions -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in p-3"
                aria-labelledby="quickActionsDropdown" style="width: 350px;">
                <h6 class="dropdown-header px-0 mb-3">
                    Actions Rapides
                </h6>

                <div class="row">
                    <!-- Nouvelle Facture Client -->
                    <div class="col-4 text-center mb-3">
                        <a href="{{ route('invoices.store', 'clients') }}" class="text-decoration-none">
                            <div class="icon-circle bg-primary mx-auto mb-2"
                                style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-file-invoice text-white"></i>
                            </div>
                            <div class="small font-weight-bold text-dark">Facture Client</div>
                        </a>
                    </div>

                    <!-- Facture Fournisseur -->
                    <div class="col-4 text-center mb-3">
                        <a href="{{ route('invoices.index', 'suppliers') }}" class="text-decoration-none">
                            <div class="icon-circle bg-warning mx-auto mb-2"
                                style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-receipt text-white"></i>
                            </div>
                            <div class="small font-weight-bold text-dark">Facture Fournisseur</div>
                        </a>
                    </div>

                    <!-- Nouveau Produit -->
                    <div class="col-4 text-center mb-3">
                        <a href="{{ route('products.index') }}" class="text-decoration-none">
                            <div class="icon-circle bg-success mx-auto mb-2"
                                style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-box text-white"></i>
                            </div>
                            <div class="small font-weight-bold text-dark">Produit</div>
                        </a>
                    </div>

                    <!-- Nouvel Entrepôt -->
                    <div class="col-4 text-center mb-3">
                        <a href="{{ route('warehouses.index') }}" class="text-decoration-none">
                            <div class="icon-circle bg-primary mx-auto mb-2"
                                style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-warehouse text-white"></i>
                            </div>
                            <div class="small font-weight-bold text-dark">Entrepôt</div>
                        </a>
                    </div>

                    <!-- Nouvelle Dépense -->
                    <div class="col-4 text-center mb-3">
                        <a href="{{ route('expenses.index') }}" class="text-decoration-none">
                            <div class="icon-circle bg-danger mx-auto mb-2"
                                style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-money-bill-wave text-white"></i>
                            </div>
                            <div class="small font-weight-bold text-dark">Dépense</div>
                        </a>
                    </div>

                    <!-- Rapports -->
                    <div class="col-4 text-center mb-3">
                        <a href="{{ route('reports.index') }}" class="text-decoration-none">
                            <div class="icon-circle bg-info mx-auto mb-2"
                                style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-chart-line text-white"></i>
                            </div>
                            <div class="small font-weight-bold text-dark">Rapports</div>
                        </a>
                    </div>
                </div>
            </div>
        </li>

        <!-- Nav Item - User Information -->
        {{-- <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ auth()->user()->name }}</span>
                <img class="img-profile rounded-circle" src="{{ asset('assets/img/undraw_profile.svg') }}">
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profile
                </a> --}}

                <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ auth()->user()->name }}</span>
                <img class="img-profile rounded-circle" src="{{ asset('assets/img/undraw_profile.svg') }}">
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profile
                </a>

                <a class="dropdown-item" href="{{ route('user.activity.index') }}">
                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                    Activity Log
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Deconnexion
                </a>
            </div>
        </li>

    </ul>

</nav>
