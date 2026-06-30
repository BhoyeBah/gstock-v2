@extends('back.layouts.admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">
                    <i class="fas fa-puzzle-piece mr-2 text-primary"></i>
                    {{ $module['title'] }}
                </h1>
                <p class="mb-0 text-muted">
                    Navigation ERP préparée sans lien mort.
                </p>
            </div>

            <span class="badge badge-{{ $module['status'] === 'Partiel' ? 'warning' : ($module['status'] === 'En préparation' ? 'info' : 'secondary') }} px-3 py-2">
                {{ $module['status'] }}
            </span>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="alert alert-light border mb-4">
                    <strong>État du module :</strong>
                    {{ $module['description'] }}
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <h5 class="mb-3">Pourquoi cette page existe</h5>
                        <p class="text-muted">
                            La sidebar expose ce module pour rendre le parcours ERP lisible pendant l’audit et les tests.
                            Tant que les routes, contrôleurs et vues métier ne sont pas réellement implémentés, cette page
                            évite d’afficher un lien cassé ou de laisser croire que la fonctionnalité est terminée.
                        </p>
                        @if ($module['status'] === 'En préparation')
                            <div class="alert alert-info border-0">
                                <strong>Bientôt disponible.</strong>
                                Le module est identifié dans le backlog mais n’est pas encore livré.
                            </div>
                        @endif
                    </div>

                    <div class="col-lg-4">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h6 class="text-uppercase text-muted mb-3">Accès existants utiles</h6>

                                @if ($moduleKey === 'batches')
                                    <a href="{{ route('batches.index') }}" class="btn btn-outline-primary btn-sm btn-block mb-2">
                                        <i class="fas fa-boxes mr-1"></i>
                                        Voir les lots
                                    </a>
                                @endif

                                @if ($moduleKey === 'movements')
                                    <a href="{{ route('movements.index') }}" class="btn btn-outline-primary btn-sm btn-block mb-2">
                                        <i class="fas fa-exchange-alt mr-1"></i>
                                        Voir les mouvements
                                    </a>
                                @endif

                                @if ($moduleKey === 'transfers')
                                    <a href="{{ route('transfers.index') }}" class="btn btn-outline-primary btn-sm btn-block mb-2">
                                        <i class="fas fa-random mr-1"></i>
                                        Voir les transferts
                                    </a>
                                @endif

                                @if ($moduleKey === 'quotes')
                                    <a href="{{ route('invoices.index', ['type' => 'clients']) }}" class="btn btn-outline-primary btn-sm btn-block mb-2">
                                        <i class="fas fa-file-invoice mr-1"></i>
                                        Voir les factures clients
                                    </a>
                                @endif

                                @if ($moduleKey === 'receipts')
                                    <a href="{{ route('invoices.index', ['type' => 'suppliers']) }}" class="btn btn-outline-primary btn-sm btn-block mb-2">
                                        <i class="fas fa-file-invoice-dollar mr-1"></i>
                                        Voir les factures fournisseurs
                                    </a>
                                @endif

                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm btn-block">
                                    <i class="fas fa-arrow-left mr-1"></i>
                                    Retour au dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
