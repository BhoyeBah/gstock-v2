@extends('back.layouts.admin')

@section('content')
    <div class="container-fluid">
        <!-- En-tête -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-900 font-weight-bold">
                    <i class="fas fa-edit text-warning"></i> Modifier le plan
                </h1>
                <p class="text-muted mb-0">
                    <span class="badge badge-warning">{{ $plan->name }}</span>
                </p>
            </div>
            <a href="{{ route('admin.plans.index') }}" class="btn btn-outline-secondary shadow-sm">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>

        <form method="POST" action="{{ route('admin.plans.update', $plan->id) }}">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- Colonne gauche : Informations principales -->
                <div class="col-lg-8">
                    <!-- Informations de base -->
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-info-circle"></i> Informations de base
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="name" class="font-weight-bold">
                                        Nom du plan <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name', $plan->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="slug" class="font-weight-bold">
                                        Identifiant (slug) <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control form-control-lg @error('slug') is-invalid @enderror"
                                        id="slug" name="slug" value="{{ old('slug', $plan->slug) }}" required>
                                    <small class="form-text text-muted">URL-friendly identifier</small>
                                    @error('slug')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description" class="font-weight-bold">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                    id="description" name="description" rows="4"
                                    placeholder="Décrivez les avantages et caractéristiques de ce plan...">{{ old('description', $plan->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Tarification et limites -->
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="m-0 font-weight-bold text-success">
                                <i class="fas fa-coins"></i> Tarification et limites
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="price" class="font-weight-bold">
                                        Prix <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('price') is-invalid @enderror"
                                            id="price" name="price" value="{{ old('price', $plan->price) }}"
                                            min="0" step="1" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">FCFA</span>
                                        </div>
                                        @error('price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="duration_days" class="font-weight-bold">
                                        Durée <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('duration_days') is-invalid @enderror"
                                            id="duration_days" name="duration_days"
                                            value="{{ old('duration_days', $plan->duration_days) }}"
                                            min="1" step="1" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">jours</span>
                                        </div>
                                        @error('duration_days')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="max_users" class="font-weight-bold">
                                        Utilisateurs max
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('max_users') is-invalid @enderror"
                                            id="max_users" name="max_users"
                                            value="{{ old('max_users', $plan->max_users) }}"
                                            min="1" step="1" placeholder="Illimité">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-users"></i></span>
                                        </div>
                                        @error('max_users')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted">Laisser vide pour illimité</small>
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <label for="max_storage_mb" class="font-weight-bold">
                                    Stockage maximum
                                </label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('max_storage_mb') is-invalid @enderror"
                                        id="max_storage_mb" name="max_storage_mb"
                                        value="{{ old('max_storage_mb', $plan->max_storage_mb) }}"
                                        min="1" step="100" placeholder="Illimité">
                                    <div class="input-group-append">
                                        <span class="input-group-text">Mo</span>
                                    </div>
                                    @error('max_storage_mb')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="form-text text-muted">Laisser vide pour illimité</small>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions -->
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-info">
                                <i class="fas fa-shield-alt"></i> Permissions associées
                            </h6>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="checkAllPermissions">
                                <label class="custom-control-label font-weight-bold text-primary" for="checkAllPermissions">
                                    Tout sélectionner
                                </label>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($permissions->isEmpty())
                                <div class="alert alert-warning mb-0">
                                    <i class="fas fa-exclamation-triangle"></i> Aucune permission disponible.
                                </div>
                            @else
                                <div class="row">
                                    @foreach ($permissions as $permission)
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                                    class="custom-control-input permission-checkbox"
                                                    id="perm_{{ $permission->id }}"
                                                    {{ $plan->permissions->pluck('id')->contains($permission->id) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="perm_{{ $permission->id }}">
                                                    <span class="font-weight-medium">{{ $permission->description ?? $permission->name }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Colonne droite : Actions et statut -->
                <div class="col-lg-4">
                    <!-- Statut -->
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="m-0 font-weight-bold text-dark">
                                <i class="fas fa-toggle-on"></i> Statut
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="custom-control custom-switch custom-switch-lg">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                    {{ old('is_active', $plan->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold" for="is_active">
                                    <span id="statusText">
                                        {{ old('is_active', $plan->is_active) ? '✓ Plan actif' : '✗ Plan inactif' }}
                                    </span>
                                </label>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle"></i> Les plans inactifs ne seront pas visibles pour les utilisateurs
                            </small>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="m-0 font-weight-bold text-dark">
                                <i class="fas fa-cog"></i> Actions
                            </h6>
                        </div>
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary btn-block btn-lg shadow-sm mb-2">
                                <i class="fas fa-save"></i> Enregistrer les modifications
                            </button>
                            <a href="{{ route('admin.plans.index') }}" class="btn btn-outline-secondary btn-block">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </div>

                    <!-- Informations supplémentaires -->
                    <div class="card shadow-sm border-0 mt-4">
                        <div class="card-header bg-light py-3">
                            <h6 class="m-0 font-weight-bold text-muted">
                                <i class="fas fa-clock"></i> Informations
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <small class="text-muted d-block">Créé le</small>
                                <span class="font-weight-bold">{{ $plan->created_at->format('d/m/Y à H:i') }}</span>
                            </div>
                            <div>
                                <small class="text-muted d-block">Dernière modification</small>
                                <span class="font-weight-bold">{{ $plan->updated_at->format('d/m/Y à H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    <style>
        .custom-switch-lg .custom-control-label::before {
            width: 3rem;
            height: 1.5rem;
            border-radius: 3rem;
        }
        .custom-switch-lg .custom-control-label::after {
            width: calc(1.5rem - 4px);
            height: calc(1.5rem - 4px);
        }
        .custom-switch-lg .custom-control-input:checked ~ .custom-control-label::after {
            transform: translateX(1.5rem);
        }
        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
        }
        .card {
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .custom-control-label {
            cursor: pointer;
        }
        .badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Toggle toutes les permissions
        document.getElementById('checkAllPermissions').addEventListener('change', function() {
            document.querySelectorAll('.permission-checkbox').forEach(cb => {
                cb.checked = this.checked;
            });
        });

        // Mise à jour du texte de statut
        document.getElementById('is_active').addEventListener('change', function() {
            const statusText = document.getElementById('statusText');
            if (this.checked) {
                statusText.innerHTML = '✓ Plan actif';
                statusText.classList.add('text-success');
                statusText.classList.remove('text-danger');
            } else {
                statusText.innerHTML = '✗ Plan inactif';
                statusText.classList.add('text-danger');
                statusText.classList.remove('text-success');
            }
        });

        // Génération automatique du slug à partir du nom
        document.getElementById('name').addEventListener('input', function() {
            const slug = this.value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            document.getElementById('slug').value = slug;
        });

        // Validation du formulaire
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                const forms = document.getElementsByClassName('needs-validation');
                Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    </script>
@endpush
