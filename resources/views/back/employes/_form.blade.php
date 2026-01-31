<form action="{{ $route }}" method="{{ $method === 'PUT' ? 'POST' : $method }}">
    @csrf
    @if (in_array($method, ['PUT', 'PATCH', 'DELETE']))
        @method($method)
    @endif

    {{-- ================= HEADER ================= --}}
    <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
            <i class="fas {{ $method === 'POST' ? 'fa-user-plus' : 'fa-user-edit' }}"></i>
            {{ $method === 'POST' ? 'Ajouter un employé' : 'Modifier l’employé' }}
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal">
            <span>&times;</span>
        </button>
    </div>

    {{-- ================= BODY ================= --}}
    <div class="modal-body">

        {{-- Nom complet --}}
        <div class="form-group">
            <label for="full_name">
                Nom complet <span class="text-danger">*</span>
            </label>
            <input type="text"
                   name="full_name"
                   id="full_name"
                   class="form-control @error('full_name') is-invalid @enderror"
                   placeholder="Ex : John Doe"
                   value="{{ old('full_name', $employe->full_name ?? '') }}"
                   required>

            @error('full_name')
                <small class="text-danger d-block mt-1">{{ $message }}</small>
            @enderror
        </div>

        {{-- Téléphone --}}
        <div class="form-group">
            <label for="phone">Téléphone</label>
            <input type="text"
                   name="phone"
                   id="phone"
                   required
                   class="form-control @error('phone') is-invalid @enderror"
                   placeholder="Ex : +221 77 000 00 00"
                   value="{{ old('phone', $employe->phone ?? '') }}">

            @error('phone')
                <small class="text-danger d-block mt-1">{{ $message }}</small>
            @enderror
        </div>

        {{-- Poste --}}
        <div class="form-group">
            <label for="position">
                Poste <span class="text-danger">*</span>
            </label>
            <input type="text"
                   name="position"
                   id="position"
                   class="form-control @error('position') is-invalid @enderror"
                   placeholder="Ex : Développeur, Comptable..."
                   value="{{ old('position', $employe->position ?? '') }}"
                   required>

            @error('position')
                <small class="text-danger d-block mt-1">{{ $message }}</small>
            @enderror
        </div>

        {{-- Salaire --}}
        <div class="form-group">
            <label for="salary">Salaire (FCFA)</label>
            <input type="number"
                   name="salary"
                   id="salary"
                   min="0"
                   step="1"
                   class="form-control @error('salary') is-invalid @enderror"
                   placeholder="Ex : 250000"
                   value="{{ old('salary', $employe->salary ?? '') }}">

            @error('salary')
                <small class="text-danger d-block mt-1">{{ $message }}</small>
            @enderror
        </div>

    </div>

    {{-- ================= FOOTER ================= --}}
    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times"></i> Annuler
        </button>

        <button type="submit" class="btn btn-primary">
            <i class="fas {{ $method === 'POST' ? 'fa-save' : 'fa-check' }}"></i>
            {{ $method === 'POST' ? 'Ajouter' : 'Enregistrer les modifications' }}
        </button>
    </div>
</form>
