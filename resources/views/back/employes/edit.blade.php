@extends('back.layouts.admin')

@section('content')
<div class="container-fluid">

    {{-- ================= HEADER ================= --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">
            <i class="fas fa-user-edit mr-2"></i> Modifier l’employé
        </h1>

        <a href="{{ route('employes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Retour à la liste
        </a>
    </div>

    {{-- ================= CARD ================= --}}
    <div class="card shadow">
        <div class="card-body">

            <form action="{{ route('employes.update', $employe->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Nom complet --}}
                <div class="form-group">
                    <label for="full_name">
                        Nom complet <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           name="full_name"
                           id="full_name"
                           class="form-control @error('full_name') is-invalid @enderror"
                           value="{{ old('full_name', $employe->full_name) }}"
                           placeholder="Ex : John Doe"
                           required>

                    @error('full_name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Téléphone --}}
                <div class="form-group">
                    <label for="phone">Téléphone</label>
                    <input type="text"
                           name="phone"
                           id="phone"
                           class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone', $employe->phone) }}"
                           placeholder="Ex : +221 77 000 00 00">

                    @error('phone')
                        <small class="text-danger">{{ $message }}</small>
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
                           value="{{ old('position', $employe->position) }}"
                           placeholder="Ex : Développeur, Comptable..."
                           required>

                    @error('position')
                        <small class="text-danger">{{ $message }}</small>
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
                           value="{{ old('salary', $employe->salary) }}"
                           placeholder="Ex : 250000">

                    @error('salary')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Boutons --}}
                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('employes.index') }}" class="btn btn-light mr-2">
                        Annuler
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Enregistrer les modifications
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
@endsection
