@extends('back.layouts.admin')

@section('content')
    <div class="page-hero page-hero--accent mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <div class="page-hero__eyebrow mb-2">Paramètres</div>
                <h1 class="page-hero__title mb-0">✏️ Modifier l'utilisateur</h1>
                <p class="page-hero__subtitle">Ajustez le profil, le rôle et le statut sans perdre la lisibilité de l’écran.</p>
            </div>
            <a href="{{ route('users.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left mr-1"></i> Retour
            </a>
        </div>
    </div>

    <div class="panel-card">
        <form action="{{ route('users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-lg-6">
                    <div class="modern-input-group">
                        <label for="name" class="modern-label">Nom complet <span class="text-danger">*</span></label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" name="name" id="name" class="form-control"
                                value="{{ old('name', $user->name) }}" required>
                        </div>
                        @error('name')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="modern-input-group">
                        <label for="email" class="modern-label">Adresse email <span class="text-danger">*</span></label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email" id="email" class="form-control"
                                value="{{ old('email', $user->email) }}" required>
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
                                value="{{ old('phone', $user->phone) }}">
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
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}"
                                        {{ old('role', $user->roles->first()?->name) === $role->name ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', \Illuminate\Support\Str::after($role->name, '_'))) }}
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
                        <label for="password" class="modern-label">Mot de passe</label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="password" id="password" class="form-control"
                                placeholder="Laisser vide pour conserver le mot de passe">
                        </div>
                        @error('password')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="modern-input-group">
                        <label for="password_confirmation" class="modern-label">Confirmation du mot de passe</label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    @if (!$user->is_owner)
                        <label for="is_active" class="modern-label">Statut</label>
                        <div class="modern-input-group">
                            <select name="is_active" id="is_active" class="form-control">
                                <option value="1" {{ old('is_active', $user->is_active) ? 'selected' : '' }}>Actif</option>
                                <option value="0" {{ !old('is_active', $user->is_active) ? 'selected' : '' }}>Inactif</option>
                            </select>
                        </div>
                        @error('is_active')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    @else
                        <div class="empty-state text-start">
                            <div class="empty-state__icon mx-0 mb-3">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="font-weight-bold text-gray-900 mb-1">Compte propriétaire protégé</div>
                            <div class="text-muted">Le statut du propriétaire ne peut pas être modifié.</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('users.index') }}" class="btn-modern btn-secondary">
                    <i class="fas fa-times"></i> Annuler
                </a>
                <button type="submit" class="btn-modern btn-primary">
                    <i class="fas fa-save"></i> Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
@endsection
