@php
    $isCreate = $method === 'POST';
    $isSupplier = $type === 'suppliers';
    $entityLabel = $isSupplier ? 'fournisseur' : 'client';
@endphp

<style>
    .entity-modal__header {
        background: linear-gradient(135deg, #111827 0%, #1d4ed8 100%);
        border: 0;
        padding: 1rem 1.2rem;
    }

    .entity-modal__title {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: .6rem;
    }

    .entity-modal__subtitle {
        margin-top: .15rem;
        color: rgba(255, 255, 255, .8);
        font-size: .88rem;
    }

    .entity-modal__body {
        background: linear-gradient(180deg, #f8fbff 0%, #f4f8ff 100%);
        padding: .95rem;
    }

    .entity-modal__panel {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, .08);
        border-radius: 16px;
        padding: .95rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
    }

    .entity-modal__field {
        margin-bottom: .8rem;
    }

    .entity-modal__label {
        display: block;
        margin-bottom: .4rem;
        font-size: .88rem;
        font-weight: 700;
        color: #0f172a;
    }

    .entity-modal__input {
        border-radius: 12px;
        min-height: 44px;
        border: 1px solid rgba(15, 23, 42, .12);
        box-shadow: none;
    }

    .entity-modal__input:focus {
        border-color: rgba(29, 78, 216, .45);
        box-shadow: 0 0 0 .18rem rgba(29, 78, 216, .12);
    }

    .entity-modal__footer {
        background: #fff;
        border-top: 1px solid rgba(15, 23, 42, .08);
        padding: .85rem 1rem;
        gap: .55rem;
    }

    .entity-modal__btn {
        border-radius: 12px;
        padding: .62rem 1rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: .45rem;
    }
</style>

<form action="{{ $route }}" method="POST">
    @csrf
    @if (in_array($method, ['PUT', 'PATCH', 'DELETE']))
        @method($method)
    @endif

    <input type="hidden" name="type" value="{{ $type === 'clients' ? 'client' : 'supplier' }}">

    <div class="modal-header entity-modal__header text-white">
        <div>
            <h5 class="entity-modal__title">
                <i class="fas {{ $isCreate ? 'fa-plus-circle' : 'fa-pen-to-square' }}"></i>
                {{ $isCreate ? "Ajouter un $entityLabel" : "Modifier le $entityLabel" }}
            </h5>
            <div class="entity-modal__subtitle">
                Renseignez l’identité et les coordonnées du {{ $entityLabel }}.
            </div>
        </div>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body entity-modal__body">
        <div class="entity-modal__panel">
            <div class="entity-modal__field">
                <label for="fullname" class="entity-modal__label">Nom complet <span class="text-danger">*</span></label>
                <input type="text" name="fullname" id="fullname" class="form-control entity-modal__input"
                    placeholder="Ex: John Doe" value="{{ old('fullname', $contact->fullname ?? '') }}" required>
                @error('fullname')
                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                @enderror
            </div>

            <div class="entity-modal__field">
                <label for="phone_number" class="entity-modal__label">Numéro de téléphone <span class="text-danger">*</span></label>
                <input type="text" name="phone_number" id="phone_number" class="form-control entity-modal__input"
                    placeholder="Ex: 77 123 45 67" value="{{ old('phone_number', $contact->phone_number ?? '') }}" required>
                @error('phone_number')
                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                @enderror
            </div>

            <div class="entity-modal__field mb-0">
                <label for="address" class="entity-modal__label">Adresse <span class="text-danger">*</span></label>
                <input type="text" name="address" id="address" class="form-control entity-modal__input"
                    placeholder="Ex: 123 Rue Principale" value="{{ old('address', $contact->address ?? '') }}" required>
                @error('address')
                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                @enderror
            </div>
        </div>
    </div>

    <div class="modal-footer entity-modal__footer justify-content-end">
        <button type="button" class="btn btn-light entity-modal__btn" data-dismiss="modal">
            <i class="fas fa-times"></i> Annuler
        </button>
        <button type="submit" class="btn btn-primary entity-modal__btn">
            <i class="fas {{ $isCreate ? 'fa-save' : 'fa-check' }}"></i>
            {{ $isCreate ? 'Ajouter' : 'Enregistrer' }}
        </button>
    </div>
</form>
