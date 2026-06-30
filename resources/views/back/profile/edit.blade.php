@extends('back.layouts.admin')

@section('title', 'Mon profil')

@section('content')
    <div class="page-hero page-hero--accent mb-4">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
            <div>
                <div class="page-hero__eyebrow mb-2">Compte utilisateur</div>
                <h1 class="page-hero__title">Mon profil</h1>
                <p class="page-hero__subtitle">Mettre à jour vos informations personnelles et votre accès sécurisé.</p>
            </div>
            <span class="status-pill status-pill--info">
                <i class="fas fa-user-shield"></i> Compte actif
            </span>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 mb-4">
            <div class="panel-card p-4 h-100">
                <div class="section-title">
                    <div>
                        <h4 class="mb-1">{{ $user->name }}</h4>
                        <p class="mb-0">Informations de connexion</p>
                    </div>
                </div>

                <div class="info-row">
                    <div>
                        <div class="info-row__label">Email</div>
                        <div class="info-row__value">{{ $user->email }}</div>
                    </div>
                </div>
                <div class="info-row">
                    <div>
                        <div class="info-row__label">Téléphone</div>
                        <div class="info-row__value">{{ $user->phone ?? 'Non renseigné' }}</div>
                    </div>
                </div>
                <div class="info-row">
                    <div>
                        <div class="info-row__label">Rôle</div>
                        <div class="info-row__value">{{ $user->is_owner ? 'Propriétaire' : 'Utilisateur' }}</div>
                    </div>
                </div>
                <div class="info-row">
                    <div>
                        <div class="info-row__label">Entreprise</div>
                        <div class="info-row__value">{{ $user->tenant?->name ?? 'N/A' }}</div>
                    </div>
                </div>

                <div class="alert alert-info border-0 mt-4 mb-0">
                    <i class="fas fa-shield-alt mr-1"></i>
                    La modification du mot de passe reste facultative. Laissez les champs vides pour garder le mot de passe actuel.
                </div>
            </div>
        </div>

        <div class="col-xl-8 mb-4">
            <div class="panel-card p-4">
                <div class="section-title">
                    <div>
                        <h4 class="mb-1">Modifier les informations</h4>
                        <p class="mb-0">Formulaire simple, lisible et compatible avec votre espace actuel.</p>
                    </div>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" class="auth-form">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="font-weight-bold">Nom complet</label>
                                <input type="text" name="name" id="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="font-weight-bold">Adresse e-mail</label>
                                <input type="email" name="email" id="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone" class="font-weight-bold">Téléphone</label>
                                <input type="text" name="phone" id="phone"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone', $user->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password" class="font-weight-bold">Nouveau mot de passe</label>
                                <input type="password" name="password" id="password"
                                       class="form-control @error('password') is-invalid @enderror">
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password_confirmation" class="font-weight-bold">Confirmer le mot de passe</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center justify-content-between mt-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Retour au tableau de bord
                        </a>
                        <button type="submit" class="btn btn-success px-4">
                            <i class="fas fa-save mr-1"></i> Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
