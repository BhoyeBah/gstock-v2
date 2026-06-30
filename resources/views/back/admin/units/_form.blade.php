<style>
    .unit-modal__header {
        background: linear-gradient(135deg, #0f172a 0%, #2563eb 100%);
        border: 0;
        padding: 1rem 1.2rem;
    }

    .unit-modal__title {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: .65rem;
    }

    .unit-modal__subtitle {
        margin-top: .15rem;
        font-size: .88rem;
        color: rgba(255, 255, 255, .8);
    }

    .unit-modal__body {
        padding: .95rem 1rem 1rem;
        background: linear-gradient(180deg, #f8fbff 0%, #f4f8ff 100%);
    }

    .unit-modal__panel {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, .08);
        border-radius: 16px;
        padding: .9rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
    }

    .unit-modal__field {
        margin-bottom: .85rem;
    }

    .unit-modal__label {
        display: block;
        margin-bottom: .4rem;
        font-size: .88rem;
        font-weight: 700;
        color: #0f172a;
    }

    .unit-modal__input {
        border-radius: 12px;
        min-height: 44px;
        border: 1px solid rgba(15, 23, 42, .12);
        box-shadow: none;
    }

    .unit-modal__input:focus {
        border-color: rgba(37, 99, 235, .45);
        box-shadow: 0 0 0 .18rem rgba(37, 99, 235, .12);
    }

    .unit-modal__footer {
        padding: .85rem 1rem 1rem;
        background: #fff;
        border-top: 1px solid rgba(15, 23, 42, .08);
        gap: .55rem;
    }

    .unit-modal__btn {
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

    <div class="modal-header unit-modal__header text-white">
        <div>
            <h5 class="unit-modal__title">
                <i class="fas {{ $method === 'POST' ? 'fa-plus-circle' : 'fa-pen-to-square' }}"></i>
                {{ $method === 'POST' ? 'Ajouter une unité' : 'Modifier l’unité' }}
            </h5>
            <div class="unit-modal__subtitle">
                Renseignez le nom et le code court de l’unité.
            </div>
        </div>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body unit-modal__body">
        <div class="unit-modal__panel">
            <div class="unit-modal__field">
                <label for="name" class="unit-modal__label">Nom de l’unité <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control unit-modal__input" id="name"
                    placeholder="Ex: Kilogramme" value="{{ old('name', $unit->name ?? '') }}" required>
                @error('name')
                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                @enderror
            </div>

            <div class="unit-modal__field mb-0">
                <label for="code" class="unit-modal__label">Code <span class="text-danger">*</span></label>
                <input type="text" name="code" class="form-control unit-modal__input" id="code"
                    placeholder="Ex: kg" value="{{ old('code', $unit->code ?? '') }}" required>
                @error('code')
                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                @enderror
            </div>
        </div>
    </div>

    <div class="modal-footer unit-modal__footer justify-content-end">
        <button type="button" class="btn btn-light unit-modal__btn" data-dismiss="modal">
            <i class="fas fa-times"></i> Annuler
        </button>
        <button type="submit" class="btn btn-primary unit-modal__btn">
            <i class="fas {{ $method === 'POST' ? 'fa-save' : 'fa-check' }}"></i>
            {{ $method === 'POST' ? 'Ajouter' : 'Enregistrer' }}
        </button>
    </div>
</form>
