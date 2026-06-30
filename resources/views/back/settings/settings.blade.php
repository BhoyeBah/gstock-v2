@extends('back.layouts.admin')

@section('content')
    <div class="container mt-4">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-0 text-gray-800">
                <i class="fas fa-cog mr-2"></i> Paramètres de configuration
            </h4>
            @can('read_document_sequences')
                <a href="{{ route('document-sequences.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-list-ol mr-1"></i> Sequences documents
                </a>
            @endcan
        </div>

        {{-- Card affichant l'état actuel --}}
        @if ($setting)
            <div class="card shadow mb-4 border-left-info">
                <div class="card-header bg-info text-white d-flex align-items-center justify-content-between">
                    <div>
                        <strong><i class="fas fa-info-circle mr-2"></i> État actuel de la configuration</strong>
                    </div>
                    <div class="small text-white-50">
                        <i class="fas fa-clock mr-1"></i>
                        @if($setting->updated_at)
                            Dernière mise à jour : {{ $setting->updated_at->format('d/m/Y H:i') }}
                        @else
                            Non modifié
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-6 mb-2">
                            <h6 class="text-muted"><i class="fas fa-money-bill-wave mr-1"></i> Devise</h6>
                            <h5 class="font-weight-bold">
                                <span class="mr-2"><i class="fas fa-coins"></i></span>
                                {{ $setting->currency }}
                            </h5>
                        </div>
                        <div class="col-md-6 mb-2">
                            <h6 class="text-muted"><i class="fas fa-percent mr-1"></i> TVA</h6>
                            <h5 class="font-weight-bold">
                                <span class="mr-2"><i class="fas fa-percentage"></i></span>
                                {{ $setting->tva }} %
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Formulaire de configuration --}}
        <div class="card shadow-lg border-0">
            <div class="card-body">
                <form action="{{ $setting ? route('settings.update', $setting->id) : route('settings.store') }}"
                    method="POST">
                    @csrf
                    @if ($setting)
                        @method('PUT')
                    @endif

                    <div class="form-group">
                        <label for="currency"><strong><i class="fas fa-money-bill-wave mr-1"></i> Devise</strong></label>
                        <select name="currency" id="currency" class="form-control" required>
                            <option value="">-- Sélectionnez une devise --</option>
                            @foreach (['XOF', 'GNF', 'FCFA', 'GMD', 'LE'] as $currency)
                                <option value="{{ $currency }}" {{ $setting && $setting->currency == $currency ? 'selected' : '' }}>
                                    {{ $currency }}
                                </option>
                            @endforeach
                        </select>
                        @error('currency')
                            <small class="text-danger"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group mt-3">
                        <label for="tva"><strong><i class="fas fa-percent mr-1"></i> TVA (%)</strong></label>
                        <input type="number" step="0.01" name="tva" id="tva" class="form-control"
                            value="{{ old('tva', $setting->tva ?? 18) }}" required>
                        @error('tva')
                            <small class="text-danger"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mt-4 text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>
                            {{ $setting ? 'Mettre à jour' : 'Enregistrer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
