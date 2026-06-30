@php
    $users = $users ?? auth()->user()->tenant->users()->get();
    $isCreate = $method === 'POST';
@endphp

<style>
    .warehouse-modal__header {
        background: linear-gradient(135deg, #111827 0%, #1d4ed8 100%);
        border: 0;
        padding: 1rem 1.2rem;
    }

    .warehouse-modal__title {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: .6rem;
    }

    .warehouse-modal__subtitle {
        margin-top: .15rem;
        color: rgba(255, 255, 255, .8);
        font-size: .88rem;
    }

    .warehouse-modal__body {
        background: linear-gradient(180deg, #f8fbff 0%, #f4f8ff 100%);
        padding: .95rem;
    }

    .warehouse-modal__panel {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, .08);
        border-radius: 16px;
        padding: .95rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
    }

    .warehouse-modal__field {
        margin-bottom: .8rem;
    }

    .warehouse-modal__label {
        display: block;
        margin-bottom: .4rem;
        font-size: .88rem;
        font-weight: 700;
        color: #0f172a;
    }

    .warehouse-modal__input {
        border-radius: 12px;
        min-height: 44px;
        border: 1px solid rgba(15, 23, 42, .12);
        box-shadow: none;
    }

    .warehouse-modal__input:focus {
        border-color: rgba(29, 78, 216, .45);
        box-shadow: 0 0 0 .18rem rgba(29, 78, 216, .12);
    }

    .warehouse-modal__footer {
        background: #fff;
        border-top: 1px solid rgba(15, 23, 42, .08);
        padding: .85rem 1rem;
        gap: .55rem;
    }

    .warehouse-modal__btn {
        border-radius: 12px;
        padding: .62rem 1rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: .45rem;
    }
</style>

<form action="{{ $route }}" method="{{ $method === 'PUT' ? 'POST' : $method }}">
    @csrf
    @if (in_array($method, ['PUT', 'PATCH', 'DELETE']))
        @method($method)
    @endif

    <div class="modal-header warehouse-modal__header text-white">
        <div>
            <h5 class="warehouse-modal__title">
                <i class="fas {{ $isCreate ? 'fa-warehouse' : 'fa-pen-to-square' }}"></i>
                {{ $isCreate ? 'Ajouter un entrepôt' : 'Modifier l’entrepôt' }}
            </h5>
            <div class="warehouse-modal__subtitle">
                Organisez vos stocks dans un espace clair et bien identifié.
            </div>
        </div>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body warehouse-modal__body">
        <div class="warehouse-modal__panel">
            <div class="warehouse-modal__field">
                <label for="name" class="warehouse-modal__label">Nom de l’entrepôt <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control warehouse-modal__input" id="name"
                    placeholder="Ex: Entrepôt Central"
                    value="{{ old('name', $warehouse->name ?? '') }}" required>
                @error('name')
                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                @enderror
            </div>

            <div class="warehouse-modal__field">
                <label for="address" class="warehouse-modal__label">Adresse</label>
                <input type="text" name="address" class="form-control warehouse-modal__input" id="address"
                    placeholder="Ex: Zone industrielle de Dakar"
                    value="{{ old('address', $warehouse->address ?? '') }}">
                @error('address')
                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                @enderror
            </div>

            <div class="warehouse-modal__field">
                <label for="manager_id" class="warehouse-modal__label">Responsable</label>
                <select name="manager_id" id="manager_id" class="form-control warehouse-modal__input">
                    <option value="">-- Sélectionner un responsable --</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}"
                            {{ old('manager_id', $warehouse->manager_id ?? '') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
                @error('manager_id')
                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                @enderror
            </div>

            <div class="warehouse-modal__field mb-0">
                <label for="description" class="warehouse-modal__label">Description</label>
                <textarea name="description" id="description" class="form-control warehouse-modal__input" rows="3"
                    placeholder="Ex: Entrepôt principal servant de stockage des produits finis.">{{ old('description', $warehouse->description ?? '') }}</textarea>
                @error('description')
                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                @enderror
            </div>
        </div>
    </div>

    <div class="modal-footer warehouse-modal__footer justify-content-end">
        <button type="button" class="btn btn-light warehouse-modal__btn" data-dismiss="modal">
            <i class="fas fa-times"></i> Annuler
        </button>
        <button type="submit" class="btn btn-primary warehouse-modal__btn">
            <i class="fas {{ $isCreate ? 'fa-save' : 'fa-check' }}"></i>
            {{ $isCreate ? 'Ajouter' : 'Enregistrer' }}
        </button>
    </div>
</form>
