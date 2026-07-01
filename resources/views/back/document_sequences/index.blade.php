@extends('back.layouts.admin')

@section('content')
    <div class="container mt-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <div>
                <h4 class="mb-1">Sequences de documents</h4>
                <p class="text-muted mb-0">Configuration des prefixes et des compteurs par type de document.</p>
            </div>
            <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary mt-3 mt-md-0">
                Retour aux parametres
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Prefixe</th>
                                <th>Dernier numero</th>
                                <th>Reset</th>
                                <th>Actif</th>
                                <th>Prochain numero</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sequences as $sequence)
                                <tr>
                                    <td><strong>{{ $sequence->document_type }}</strong></td>
                                    <td>{{ $sequence->prefix }}</td>
                                    <td>{{ $sequence->current_number }}</td>
                                    <td>{{ $sequence->reset_period }}</td>
                                    <td>
                                        <span class="badge {{ $sequence->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $sequence->is_active ? 'Oui' : 'Non' }}
                                        </span>
                                    </td>
                                    <td>{{ $previews[$sequence->id] ?? 'N/A' }}</td>
                                    <td>
                                        @can('manage_document_sequences')
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="collapse"
                                                data-bs-target="#edit-sequence-{{ $sequence->id }}">
                                                Modifier
                                            </button>
                                        @endcan
                                    </td>
                                </tr>
                                @can('manage_document_sequences')
                                    <tr class="collapse" id="edit-sequence-{{ $sequence->id }}">
                                        <td colspan="7" class="bg-light">
                                            <form action="{{ route('document-sequences.update', $sequence->id) }}"
                                                method="POST" class="row g-3 align-items-end">
                                                @csrf
                                                @method('PUT')
                                                <div class="col-md-3">
                                                    <label class="form-label">Prefixe</label>
                                                    <input type="text" name="prefix" class="form-control"
                                                        value="{{ old('prefix', $sequence->prefix) }}">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Padding</label>
                                                    <input type="number" name="padding" class="form-control"
                                                        value="{{ old('padding', $sequence->padding) }}" min="2"
                                                        max="8">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Reset</label>
                                                    <select name="reset_period" class="form-control">
                                                        @foreach (['never', 'yearly', 'monthly'] as $period)
                                                            <option value="{{ $period }}"
                                                                @selected(old('reset_period', $sequence->reset_period) === $period)>
                                                                {{ $period }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-check mt-4">
                                                        <input type="hidden" name="is_active" value="0">
                                                        <input class="form-check-input" type="checkbox" name="is_active"
                                                            value="1" id="is_active_{{ $sequence->id }}"
                                                            @checked(old('is_active', $sequence->is_active))>
                                                        <label class="form-check-label"
                                                            for="is_active_{{ $sequence->id }}">Actif</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <button class="btn btn-success w-100" type="submit">
                                                        Enregistrer
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @endcan
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
