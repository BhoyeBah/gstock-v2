@extends('back.layouts.admin')

@section('content')
    <div class="page-hero page-hero--accent mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <div class="page-hero__eyebrow mb-2">Paramètres</div>
                <h1 class="page-hero__title mb-0">➕ Ajouter un utilisateur</h1>
                <p class="page-hero__subtitle">Créez un compte avec un accès clair, un rôle précis et un statut maîtrisé.</p>
            </div>
            <a href="{{ route('users.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left mr-1"></i> Retour
            </a>
        </div>
    </div>

    <div class="panel-card">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-lg-6">
                    <div class="modern-input-group">
                        <label for="name" class="modern-label">Nom complet <span class="text-danger">*</span></label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" name="name" id="name" class="form-control"
                                value="{{ old('name') }}" placeholder="Ex: Jean Dupont" required>
                        </div>
                        @error('name')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="modern-input-group">
                        <label for="email" class="modern-label">Email <span class="text-danger">*</span></label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email" id="email" class="form-control"
                                value="{{ old('email') }}" placeholder="Ex: exemple@mail.com" required>
                        </div>
                        @error('email')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="modern-input-group">
                        <label for="phone" class="modern-label">Téléphone</label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-phone input-icon"></i>
                            <input type="text" name="phone" id="phone" class="form-control"
                                value="{{ old('phone') }}" placeholder="Ex: +221 77 123 45 67">
                        </div>
                        @error('phone')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="modern-input-group">
                        <label for="role" class="modern-label">Rôle <span class="text-danger">*</span></label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-user-shield input-icon"></i>
                            <select name="role" id="role" class="form-control" required>
                                <option value="">-- Sélectionnez un rôle --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role') == $role->id ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('role')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="modern-input-group">
                        <label for="password" class="modern-label">Mot de passe <span class="text-danger">*</span></label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="password" id="password" class="form-control"
                                placeholder="Mot de passe" required>
                        </div>
                        @error('password')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="modern-input-group">
                        <label for="password_confirmation" class="modern-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
                                placeholder="Confirmer le mot de passe" required>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <label class="modern-label">Statut du compte</label>
                    <div class="modern-checkbox">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                            {{ old('is_active', true) ? 'checked' : '' }}>
                        <label for="is_active">Activer le compte dès maintenant</label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('users.index') }}" class="btn-modern btn-secondary">
                    <i class="fas fa-times"></i> Annuler
                </a>
                <button type="submit" class="btn-modern btn-primary">
                    <i class="fas fa-save"></i> Créer l'utilisateur
                </button>
            </div>
        </form>
    </div>
@endsection
