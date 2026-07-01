@extends('back.layouts.admin')

@section('content')
    @php
        $isEdit = (bool) $setting;
    @endphp

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Paramètres plateforme</h1>
            <p class="mb-0 text-muted">Configuration globale du SaaS, stockée sur le tenant `platform`.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left mr-1"></i> Retour au dashboard
        </a>
    </div>

    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card shadow border-left-primary">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="m-0 font-weight-bold">Configuration globale</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ $isEdit ? route('admin.settings.update', $setting) : route('admin.settings.store') }}">
                        @csrf
                        @if($isEdit)
                            @method('PUT')
                        @endif

                        <div class="form-group">
                            <label for="currency">Devise</label>
                            <select name="currency" id="currency" class="form-control" required>
                                @foreach(['XOF', 'GNF', 'FCFA', 'GMD', 'LE'] as $currency)
                                    <option value="{{ $currency }}" {{ old('currency', $setting->currency ?? 'FCFA') === $currency ? 'selected' : '' }}>
                                        {{ $currency }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="tva">TVA (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="tva" id="tva" class="form-control"
                                   value="{{ old('tva', $setting->tva ?? 18) }}" required>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>
                                {{ $isEdit ? 'Mettre à jour' : 'Créer la configuration' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-4">
            <div class="card shadow border-left-info mb-4">
                <div class="card-header bg-info text-white py-3">
                    <h6 class="m-0 font-weight-bold">Contexte</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0 pl-3">
                        <li>Cette page n’impacte pas les paramètres de chaque tenant.</li>
                        <li>Le réglage est associé au tenant <code>platform</code>.</li>
                        <li>Il peut servir de socle à la configuration SaaS globale.</li>
                    </ul>
                </div>
            </div>

            @if($setting)
                <div class="card shadow border-left-secondary">
                    <div class="card-header bg-secondary text-white py-3">
                        <h6 class="m-0 font-weight-bold">Valeurs actuelles</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2"><strong>Devise:</strong> {{ $setting->currency }}</div>
                        <div class="mb-2"><strong>TVA:</strong> {{ $setting->tva }} %</div>
                        <div class="mb-0"><strong>Mise à jour:</strong> {{ optional($setting->updated_at)->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
