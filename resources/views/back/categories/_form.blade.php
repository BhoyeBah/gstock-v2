<form action="{{ $route }}" method="{{ $method === 'PUT' ? 'POST' : $method }}">
    @csrf
    @if (in_array($method, ['PUT', 'PATCH', 'DELETE']))
        @method($method)
    @endif

    @if ($method == 'POST')
        <div class="modal-header text-white" style="background: linear-gradient(135deg, #4f46e5 0%, #224abe 100%);">
            <h5 class="modal-title">
                <i class="fas {{ $method === 'POST' ? 'fa-plus-circle' : 'fa-edit' }} mr-1"></i>
                {{ $method === 'POST' ? 'Ajouter une catégorie' : 'Modifier la catégorie' }}
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="modal-body">
        <div class="form-group mb-4">
            <label for="name" class="font-weight-bold text-muted">Nom de la catégorie <span class="text-danger">*</span></label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                </div>
                <input type="text" name="name" class="form-control" id="name"
                       placeholder="Ex: Boissons" value="{{ old('name', $categorie->name ?? '') }}" required>
            </div>
            @error('name')
                <small class="text-danger d-block mt-1">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Annuler
        </button>
        <button type="submit" class="btn btn-primary">
            <i class="fas {{ $method === 'POST' ? 'fa-save' : 'fa-check' }} mr-1"></i>
            {{ $method === 'POST' ? 'Ajouter' : 'Enregistrer les modifications' }}
        </button>
    </div>
</form>