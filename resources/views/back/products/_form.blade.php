<style>
    .product-modal__header {
        background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%);
        border-bottom: 0;
        padding: 1.15rem 1.35rem;
    }

    .product-modal__title {
        font-weight: 800;
        font-size: 1.05rem;
        display: flex;
        align-items: center;
        gap: .65rem;
        margin: 0;
    }

    .product-modal__subtitle {
        margin-top: .2rem;
        color: rgba(255, 255, 255, .82);
        font-size: .9rem;
    }

    .product-modal__body {
        background: linear-gradient(180deg, #f8fbff 0%, #f3f7ff 100%);
        padding: .85rem;
    }

    .modal-product-compact {
        max-width: 720px;
    }

    .product-modal__section {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 18px;
        padding: .72rem;
        height: 100%;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
    }

    .product-modal__section-title {
        display: flex;
        align-items: center;
        gap: .55rem;
        font-size: .78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #1d4ed8;
        margin-bottom: .7rem;
    }

    .product-modal__field {
        margin-bottom: .68rem;
    }

    .product-modal__label {
        display: block;
        font-size: .88rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: .45rem;
    }

    .product-modal__hint {
        display: block;
        margin-top: .35rem;
        font-size: .8rem;
        color: #64748b;
    }

    .product-modal__input,
    .product-modal__select,
    .product-modal__textarea {
        border-radius: 14px;
        border: 1px solid rgba(15, 23, 42, 0.12);
        box-shadow: none;
        min-height: 42px;
        background: #fff;
    }

    .product-modal__textarea {
        min-height: 84px;
        resize: vertical;
    }

    .product-modal__input:focus,
    .product-modal__select:focus,
    .product-modal__textarea:focus {
        border-color: rgba(29, 78, 216, .45);
        box-shadow: 0 0 0 .18rem rgba(29, 78, 216, .12);
    }

    .product-modal__input-group {
        position: relative;
    }

    .product-modal__icon {
        position: absolute;
        left: .95rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        z-index: 2;
        pointer-events: none;
    }

    .product-modal__input-group .product-modal__input,
    .product-modal__input-group .product-modal__select {
        padding-left: 2.65rem;
    }

    .product-modal__file {
        border: 1px dashed rgba(29, 78, 216, .22);
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(29, 78, 216, .04), rgba(15, 23, 42, .02));
        padding: .65rem .75rem;
    }

    .product-modal__file-label {
        display: flex;
        align-items: center;
        gap: .6rem;
        margin-bottom: .6rem;
        font-weight: 700;
        color: #0f172a;
    }

    .product-modal__file-input {
        width: 100%;
    }

    .product-modal__footer {
        background: #fff;
        border-top: 1px solid rgba(15, 23, 42, 0.08);
        padding: .85rem 1.1rem;
        gap: .55rem;
    }

    .product-modal__btn {
        border-radius: 14px;
        padding: .62rem 1rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
    }

    @media (max-width: 767.98px) {
        .product-modal__body { padding: .75rem; }
        .product-modal__section { border-radius: 16px; }
        .modal-product-compact { max-width: none; margin: .5rem; }
    }
</style>

<form action="{{ $route }}" method="{{ $method === 'PUT' ? 'POST' : $method }}" enctype="multipart/form-data">
    @csrf
    @if (in_array($method, ['PUT', 'PATCH', 'DELETE']))
        @method($method)
    @endif

    <div class="modal-header product-modal__header text-white">
        <div>
            <h5 class="product-modal__title">
                <i class="fas {{ $method === 'POST' ? 'fa-plus-circle' : 'fa-pen-to-square' }}"></i>
                {{ $method === 'POST' ? 'Ajouter un produit' : 'Modifier le produit' }}
            </h5>
            <div class="product-modal__subtitle">
                Renseignez le catalogue, le prix et les paramètres de stock dans un seul écran.
            </div>
        </div>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body product-modal__body">
        <div class="row">
            <div class="col-lg-6 mb-2">
                <div class="product-modal__section">
                    <div class="product-modal__section-title">
                        <i class="fas fa-box-open"></i>
                        Informations générales
                    </div>

                    <div class="product-modal__field">
                        <label for="name" class="product-modal__label">Nom du produit <span class="text-danger">*</span></label>
                        <div class="product-modal__input-group">
                            <i class="fas fa-box product-modal__icon"></i>
                            <input type="text" name="name" class="form-control product-modal__input" id="name"
                                placeholder="Ex : Boisson gazeuse" value="{{ old('name', $product->name ?? '') }}" required>
                        </div>
                        @error('name')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="product-modal__field">
                        <label for="category_id" class="product-modal__label">Catégorie <span class="text-danger">*</span></label>
                        <div class="product-modal__input-group">
                            <i class="fas fa-tag product-modal__icon"></i>
                            <select name="category_id" id="category_id" class="form-control product-modal__select" required>
                                <option value="">-- Sélectionner --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('category_id')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="product-modal__field">
                        <label for="unit_id" class="product-modal__label">Unité de mesure <span class="text-danger">*</span></label>
                        <div class="product-modal__input-group">
                            <i class="fas fa-scale-balanced product-modal__icon"></i>
                            <select name="unit_id" id="unit_id" class="form-control product-modal__select" required>
                                <option value="">-- Sélectionner --</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}"
                                        {{ old('unit_id', $product->unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('unit_id')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="product-modal__field mb-0">
                        <div class="custom-control custom-checkbox modern-checkbox" style="border-radius:14px;">
                            <input type="checkbox" name="is_perishable" id="is_perishable" class="custom-control-input"
                                value="1" {{ old('is_perishable', $product->is_perishable ?? false) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_perishable">
                                <i class="fas fa-leaf mr-1 text-success"></i>
                                Produit périssable
                            </label>
                        </div>
                        @error('is_perishable')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-2">
                <div class="product-modal__section">
                    <div class="product-modal__section-title">
                        <i class="fas fa-chart-line"></i>
                        Prix & stock
                    </div>

                    <div class="product-modal__field">
                        <label for="price" class="product-modal__label">Prix de vente (FCFA)</label>
                        <div class="product-modal__input-group">
                            <i class="fas fa-money-bill-wave product-modal__icon"></i>
                            <input type="number" name="price" class="form-control product-modal__input" id="price"
                                placeholder="Ex : 1500" value="{{ old('price', $product->price ?? 0) }}">
                        </div>
                        @error('price')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="product-modal__field">
                        <label for="seuil_alert" class="product-modal__label">Seuil d'alerte de stock</label>
                        <div class="product-modal__input-group">
                            <i class="fas fa-triangle-exclamation product-modal__icon"></i>
                            <input type="number" name="seuil_alert" class="form-control product-modal__input" id="seuil_alert"
                                placeholder="Ex : 10" value="{{ old('seuil_alert', $product->seuil_alert ?? 10) }}">
                        </div>
                        <span class="product-modal__hint">Ce seuil sert à signaler les produits proches de la rupture.</span>
                        @error('seuil_alert')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="product-modal__field mb-0">
                        <label for="image" class="product-modal__label">Image du produit</label>
                        <div class="product-modal__file">
                            <div class="product-modal__file-label">
                                <i class="fas fa-image text-primary"></i>
                                <span id="file-name">Choisir une image...</span>
                            </div>
                            <input type="file" name="image" id="image" accept="image/*" class="product-modal__file-input">
                        </div>
                        @error('image')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="product-modal__section">
                    <div class="product-modal__section-title">
                        <i class="fas fa-align-left"></i>
                        Description
                    </div>

                    <div class="product-modal__field mb-0">
                        <label for="description" class="product-modal__label">Détails du produit</label>
                        <textarea name="description" id="description" class="form-control product-modal__textarea"
                            rows="2" placeholder="Décrivez le produit (saveur, format, particularités...)">{{ old('description', $product->description ?? '') }}</textarea>
                        @error('description')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer product-modal__footer justify-content-end">
        <button type="button" class="btn btn-light product-modal__btn" data-dismiss="modal">
            <i class="fas fa-times"></i> Annuler
        </button>
        <button type="submit" class="btn btn-primary product-modal__btn">
            <i class="fas {{ $method === 'POST' ? 'fa-save' : 'fa-check' }}"></i>
            {{ $method === 'POST' ? 'Ajouter' : 'Enregistrer' }}
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fileInput = document.getElementById('image');
        const fileName = document.getElementById('file-name');

        if (!fileInput || !fileName) {
            return;
        }

        fileInput.addEventListener('change', function (event) {
            fileName.textContent = event.target.files.length > 0
                ? event.target.files[0].name
                : 'Choisir une image...';
        });
    });
</script>
