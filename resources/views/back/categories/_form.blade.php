<style>
    .category-modal__header {
        background: linear-gradient(135deg, #111827 0%, #1d4ed8 100%);
        border-bottom: 0;
        padding: 1.15rem 1.35rem;
    }

    .category-modal__title {
        margin: 0;
        font-weight: 800;
        font-size: 1.05rem;
        display: flex;
        align-items: center;
        gap: .65rem;
    }

    .category-modal__subtitle {
        margin-top: .2rem;
        color: rgba(255, 255, 255, .82);
        font-size: .9rem;
    }

    .category-modal__body {
        background: linear-gradient(180deg, #f8fbff 0%, #f3f7ff 100%);
        padding: 1.35rem;
    }

    .category-modal__panel {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 18px;
        padding: 1rem;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
    }

    .category-modal__label {
        display: block;
        margin-bottom: .5rem;
        font-size: .88rem;
        font-weight: 700;
        color: #0f172a;
    }

    .category-modal__input-group {
        position: relative;
    }

    .category-modal__icon {
        position: absolute;
        left: .95rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
        z-index: 2;
    }

    .category-modal__input {
        border-radius: 14px;
        border: 1px solid rgba(15, 23, 42, 0.12);
        min-height: 50px;
        padding-left: 2.7rem;
        box-shadow: none;
    }

    .category-modal__input:focus {
        border-color: rgba(29, 78, 216, .45);
        box-shadow: 0 0 0 .18rem rgba(29, 78, 216, .12);
    }

    .category-modal__hint {
        margin-top: .4rem;
        font-size: .8rem;
        color: #64748b;
    }

    .category-modal__footer {
        background: #fff;
        border-top: 1px solid rgba(15, 23, 42, 0.08);
        padding: 1rem 1.35rem;
        gap: .75rem;
    }

    .category-modal__btn {
        border-radius: 14px;
        padding: .72rem 1.2rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
    }
</style>

<form action="{{ $route }}" method="{{ $method === 'PUT' ? 'POST' : $method }}">
    @csrf
    @if (in_array($method, ['PUT', 'PATCH', 'DELETE']))
        @method($method)
    @endif

    <div class="modal-header category-modal__header text-white">
        <div>
            <h5 class="category-modal__title">
                <i class="fas {{ $method === 'POST' ? 'fa-folder-plus' : 'fa-pen-to-square' }}"></i>
                {{ $method === 'POST' ? 'Ajouter une catégorie' : 'Modifier la catégorie' }}
            </h5>
            <div class="category-modal__subtitle">
                Créez un classement propre pour accélérer la saisie des produits.
            </div>
        </div>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body category-modal__body">
        <div class="category-modal__panel">
            <label for="name" class="category-modal__label">Nom de la catégorie <span class="text-danger">*</span></label>
            <div class="category-modal__input-group">
                <i class="fas fa-tag category-modal__icon"></i>
                <input type="text" name="name" class="form-control category-modal__input" id="name"
                    placeholder="Ex : Boissons" value="{{ old('name', $categorie->name ?? '') }}" required>
            </div>
            <div class="category-modal__hint">Le nom sera utilisé dans les listes produits et les filtres.</div>
            @error('name')
                <small class="text-danger d-block mt-1">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <div class="modal-footer category-modal__footer justify-content-end">
        <button type="button" class="btn btn-light category-modal__btn" data-dismiss="modal">
            <i class="fas fa-times"></i> Annuler
        </button>
        <button type="submit" class="btn btn-primary category-modal__btn">
            <i class="fas {{ $method === 'POST' ? 'fa-save' : 'fa-check' }}"></i>
            {{ $method === 'POST' ? 'Ajouter' : 'Enregistrer' }}
        </button>
    </div>
</form>
