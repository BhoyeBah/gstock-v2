<style>
    /* Style du modal header */
    .modal-header.modern-header {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        border-bottom: none;
        padding: 1.25rem 1.5rem;
        border-radius: 0;
    }

    .modal-header.modern-header .modal-title {
        font-weight: 700;
        font-size: 1.15rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .modal-header.modern-header .close {
        opacity: 1;
        text-shadow: none;
        font-size: 1.5rem;
        font-weight: 300;
        transition: all 0.3s ease;
        padding: 0;
        margin: -0.5rem -0.5rem -0.5rem auto;
    }

    .modal-header.modern-header .close:hover {
        transform: rotate(90deg);
        opacity: 0.8;
    }

    /* Style du modal body */
    .modal-body.modern-body {
        padding: 2rem;
        background: #fafbfc;
        max-height: calc(100vh - 250px);
        overflow-y: auto;
    }

    /* Labels modernes */
    .modern-label {
        font-weight: 600;
        color: #5a5c69;
        margin-bottom: 0.5rem;
        display: block;
        font-size: 0.875rem;
    }

    /* Input groups modernisés avec icônes intégrées */
    .modern-input-group {
        margin-bottom: 1.75rem;
        position: relative;
    }

    .modern-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .modern-input-wrapper .input-icon {
        position: absolute;
        left: 0.875rem;
        color: #858796;
        z-index: 10;
        font-size: 0.9rem;
        pointer-events: none;
    }

    .modern-input-group .form-control,
    .modern-input-group select.form-control {
        padding-left: 2.75rem;
        padding-right: 1rem;
        border: 2px solid #e3e6f0;
        border-radius: 8px;
        height: 44px;
        transition: all 0.2s ease;
        background: #fff;
        font-size: 0.9rem;
        color: #495057;
        width: 100%;
    }

    .modern-input-group textarea.form-control {
        padding-left: 2.75rem;
        padding-right: 1rem;
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
        height: auto;
        resize: vertical;
    }

    .modern-input-group .form-control:focus,
    .modern-input-group select.form-control:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 0.15rem rgba(102, 126, 234, 0.15);
        outline: none;
    }

    /* Select modernisé */
    .modern-input-group select.form-control {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%23858796" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"%3e%3cpolyline points="6 9 12 15 18 9"%3e%3c/polyline%3e%3c/svg%3e');
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 1em;
        padding-right: 2.5rem;
        cursor: pointer;
    }

    .modern-input-group select.form-control option {
        color: #212529;
        background: #fff;
        padding: 8px;
    }

    /* Checkbox moderne */
    .modern-checkbox {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        background: #fff;
        border: 2px solid #e3e6f0;
        border-radius: 8px;
        transition: all 0.2s ease;
        cursor: pointer;
        margin-bottom: 1.25rem;
    }

    .modern-checkbox:hover {
        border-color: #4f46e5;
        background: #f8f9ff;
    }

    .modern-checkbox input[type="checkbox"] {
        width: 1.25rem;
        height: 1.25rem;
        margin: 0;
        margin-right: 0.65rem;
        cursor: pointer;
        border: 2px solid #d1d3e2;
        border-radius: 5px;
        accent-color: #4f46e5;
    }

    .modern-checkbox label {
        margin: 0;
        cursor: pointer;
        font-weight: 600;
        color: #5a5c69;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    /* Input file moderne */
    .modern-file-wrapper {
        position: relative;
    }

    .modern-file-input {
        position: relative;
        overflow: hidden;
        display: block;
    }

    .modern-file-input input[type="file"] {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 5;
    }

    .modern-file-input .file-label {
        display: flex;
        align-items: center;
        padding: 0.65rem 1rem 0.65rem 2.75rem;
        background: #fff;
        border: 2px dashed #e3e6f0;
        border-radius: 8px;
        color: #858796;
        transition: all 0.2s ease;
        cursor: pointer;
        font-size: 0.875rem;
        min-height: 42px;
    }

    .modern-file-input .file-label i {
        position: absolute;
        left: 0.875rem;
        font-size: 0.9rem;
    }

    .modern-file-input:hover .file-label {
        border-color: #4f46e5;
        background: #f8f9ff;
        color: #4f46e5;
    }

    /* Modal footer moderne */
    .modal-footer.modern-footer {
        background: #fff;
        border-top: 2px solid #e3e6f0;
        padding: 1rem 1.5rem;
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }

    /* Boutons modernes */
    .btn-modern {
        border-radius: 8px;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }

    .btn-modern:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
    }

    .btn-modern:active {
        transform: translateY(0);
    }

    .btn-modern.btn-primary {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: #fff;
    }

    .btn-modern.btn-primary:hover {
        background: linear-gradient(135deg, #5568d3 0%, #653a8a 100%);
    }

    .btn-modern.btn-secondary {
        background: #858796;
        color: #fff;
    }

    .btn-modern.btn-secondary:hover {
        background: #6c6d7c;
    }

    /* Messages d'erreur */
    small.text-danger {
        font-size: 0.8rem;
        font-weight: 500;
        display: block;
        margin-top: 0.35rem;
        padding-left: 0.25rem;
    }

    /* Séparateurs de sections */
    .section-divider {
        border-bottom: 2px solid #e3e6f0;
        margin: 0 -1.5rem 1.25rem;
        padding-bottom: 0.75rem;
    }

    .section-title {
        font-size: 0.875rem;
        font-weight: 700;
        color: #4f46e5;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    /* Responsive pour modal */
    @media (max-width: 768px) {
        .modal-body.modern-body {
            padding: 1rem;
            max-height: calc(100vh - 200px);
        }
        
        .modern-input-group {
            margin-bottom: 1rem;
        }

        .section-divider {
            margin: 0 -1rem 1rem;
        }
    }

    /* Scroll personnalisé pour le modal body */
    .modal-body.modern-body::-webkit-scrollbar {
        width: 6px;
    }

    .modal-body.modern-body::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .modal-body.modern-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    .modal-body.modern-body::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>

<form action="{{ $route }}" method="{{ $method === 'PUT' ? 'POST' : $method }}" enctype="multipart/form-data">
    @csrf
    @if (in_array($method, ['PUT', 'PATCH', 'DELETE']))
        @method($method)
    @endif

    <div class="modal-header text-white modern-header">
        <h5 class="modal-title">
            <i class="fas {{ $method === 'POST' ? 'fa-plus-circle' : 'fa-edit' }}"></i>
            {{ $method === 'POST' ? 'Ajouter un produit' : 'Modifier le produit' }}
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body modern-body">
        <div class="row">
            <!-- Colonne gauche -->
            <div class="col-md-6">
                <div class="section-title section-divider">
                    <i class="fas fa-info-circle"></i>
                    Informations générales
                </div>

                <div class="modern-input-group">
                    <label for="name" class="modern-label">
                        Nom du produit <span class="text-danger">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <i class="fas fa-box-open input-icon"></i>
                        <input type="text" name="name" class="form-control" id="name"
                            placeholder="Ex: Boisson gazeuse" value="{{ old('name', $product->name ?? '') }}" required>
                    </div>
                    @error('name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="modern-input-group">
                    <label for="category_id" class="modern-label">
                        Catégorie <span class="text-danger">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <i class="fas fa-tags input-icon"></i>
                        <select name="category_id" id="category_id" class="form-control" required>
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
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="modern-input-group">
                    <label for="unit_id" class="modern-label">
                        Unité de mesure <span class="text-danger">*</span>
                    </label>
                    <div class="modern-input-wrapper">
                        <i class="fas fa-balance-scale input-icon"></i>
                        <select name="unit_id" id="unit_id" class="form-control" required>
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
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="modern-checkbox">
                    <input type="checkbox" name="is_perishable" id="is_perishable"
                        value="1" {{ old('is_perishable', $product->is_perishable ?? false) ? 'checked' : '' }}>
                    <label for="is_perishable">
                        <i class="fas fa-leaf"></i>
                        Produit périssable
                    </label>
                </div>
                @error('is_perishable')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- Colonne droite -->
            <div class="col-md-6">
                <div class="section-title section-divider">
                    <i class="fas fa-chart-line"></i>
                    Prix & Stock
                </div>

                <div class="modern-input-group">
                    <label for="price" class="modern-label">
                        Prix de vente (FCFA)
                    </label>
                    <div class="modern-input-wrapper">
                        <i class="fas fa-money-bill-wave input-icon"></i>
                        <input type="number" name="price" class="form-control" id="price" placeholder="Ex: 1500"
                            value="{{ old('price', $product->price ?? 0) }}">
                    </div>
                    @error('price')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="modern-input-group">
                    <label for="seuil_alert" class="modern-label">
                        Seuil d'alerte de stock
                    </label>
                    <div class="modern-input-wrapper">
                        <i class="fas fa-exclamation-triangle input-icon"></i>
                        <input type="number" name="seuil_alert" class="form-control" id="seuil_alert" placeholder="Ex: 10"
                            value="{{ old('seuil_alert', $product->seuil_alert ?? 10) }}">
                    </div>
                    @error('seuil_alert')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="modern-input-group">
                    <label for="image" class="modern-label">
                        <i class="fas fa-camera"></i>
                        Image du produit
                    </label>
                    <div class="modern-file-wrapper">
                        <div class="modern-file-input">
                            <div class="file-label">
                                <i class="fas fa-image"></i>
                                <span id="file-name">Choisir une image...</span>
                            </div>
                            <input type="file" name="image" id="image" accept="image/*">
                        </div>
                    </div>
                    @error('image')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <!-- Description sur toute la largeur -->
            <div class="col-12">
                <div class="section-title section-divider">
                    <i class="fas fa-align-left"></i>
                    Description
                </div>

                <div class="modern-input-group">
                    <label for="description" class="modern-label">
                        Détails du produit
                    </label>
                    <div class="modern-input-wrapper">
                        <i class="fas fa-pencil-alt input-icon"></i>
                        <textarea name="description" id="description" class="form-control" rows="3"
                            placeholder="Décrivez le produit (ex: saveur, format, particularités...)">{{ old('description', $product->description ?? '') }}</textarea>
                    </div>
                    @error('description')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer modern-footer">
        <button type="button" class="btn btn-modern btn-secondary" data-dismiss="modal">
            <i class="fas fa-times"></i> Annuler
        </button>
        <button type="submit" class="btn btn-modern btn-primary">
            <i class="fas {{ $method === 'POST' ? 'fa-save' : 'fa-check' }}"></i>
            {{ $method === 'POST' ? 'Ajouter' : 'Enregistrer' }}
        </button>
    </div>
</form>

<script>
    // Preview du nom de fichier
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('image');
        const fileName = document.getElementById('file-name');
        
        if (fileInput && fileName) {
            fileInput.addEventListener('change', function(e) {
                if (e.target.files.length > 0) {
                    fileName.textContent = e.target.files[0].name;
                } else {
                    fileName.textContent = 'Choisir une image...';
                }
            });
        }
    });
</script>