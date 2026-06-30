@extends('back.layouts.admin')

@section('content')
    <div class="page-hero page-hero--accent mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <div class="page-hero__eyebrow mb-2">Accès</div>
                <h1 class="page-hero__title mb-0">✏️ Modifier le rôle</h1>
                <p class="page-hero__subtitle">Ajustez le périmètre du rôle sans casser les droits déjà distribués.</p>
            </div>
            <a href="{{ route('roles.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left mr-1"></i> Retour
            </a>
        </div>
    </div>

    <div class="panel-card">
        <form action="{{ route('roles.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-lg-6">
                    <div class="modern-input-group">
                        <label for="tenant_id" class="modern-label">Entreprise concernée <span class="text-danger">*</span></label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-building input-icon"></i>
                            <select name="tenant_id" id="tenant_id" class="form-control" required {{ !auth()->user()->is_platform_user() ? 'disabled' : '' }}>
                                @foreach ($tenants as $tenant)
                                    <option value="{{ $tenant->id }}" {{ old('tenant_id', $role->tenant_id) == $tenant->id ? 'selected' : '' }}>
                                        {{ $tenant->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('tenant_id')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror

                        @if (!auth()->user()->is_platform_user())
                            <input type="hidden" name="tenant_id" value="{{ $role->tenant_id }}">
                        @endif
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="modern-input-group">
                        <label for="name" class="modern-label">Nom du rôle <span class="text-danger">*</span></label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-user-shield input-icon"></i>
                            <input type="text" name="name" id="name" class="form-control"
                                placeholder="Ex: manager, vendeur, caissier..."
                                value="{{ old('name', \Illuminate\Support\Str::after($role->name, '_')) }}" required>
                        </div>
                        @error('name')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <div class="section-title">
                    <div>
                        <h3>Permissions associées</h3>
                        <p>Gardez seulement les droits utiles pour ce niveau d’accès.</p>
                    </div>
                </div>

                <div class="row">
                    @forelse ($permissions as $permission)
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="modern-checkbox h-100">
                                <input type="checkbox"
                                    id="perm_{{ $permission->id }}"
                                    name="permissions[]"
                                    value="{{ $permission->id }}"
                                    {{ in_array($permission->id, old('permissions', $role->permissions->pluck('id')->toArray())) ? 'checked' : '' }}>
                                <label for="perm_{{ $permission->id }}">
                                    {{ ucfirst(str_replace('_', ' ', $permission->description)) }}
                                </label>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="empty-state">
                                <div class="empty-state__icon">
                                    <i class="fas fa-lock"></i>
                                </div>
                                <div class="font-weight-bold text-gray-900">Aucune permission disponible</div>
                            </div>
                        </div>
                    @endforelse
                </div>

                @error('permissions')
                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                @enderror
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('roles.index') }}" class="btn-modern btn-secondary">
                    <i class="fas fa-times"></i> Annuler
                </a>
                <button type="submit" class="btn-modern btn-primary">
                    <i class="fas fa-save"></i> Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
@endsection
