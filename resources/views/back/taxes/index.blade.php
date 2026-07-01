@extends('back.layouts.admin')

@section('content')
    <div class="container-fluid">

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Taxes / TVA</h1>
                <p class="text-muted mb-0">Configurez les taux de taxe applicables à vos documents.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <div class="row">

            {{-- Formulaire de création --}}
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-plus mr-1"></i> Nouvelle taxe
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('taxes.store') }}" method="POST">
                            @csrf

                            <div class="form-group">
                                <label for="name">Nom <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}"
                                    placeholder="Ex: TVA 20%, TVA réduite…">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="rate">Taux (%) <span class="text-danger">*</span></label>
                                <input type="number" name="rate" id="rate" step="0.01" min="0" max="100"
                                    class="form-control @error('rate') is-invalid @enderror"
                                    value="{{ old('rate') }}"
                                    placeholder="Ex: 20">
                                @error('rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="is_active"
                                        name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">Active</label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save mr-1"></i> Enregistrer
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Liste des taxes --}}
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-percent mr-1"></i> Taxes configurées
                        </h6>
                        <span class="badge badge-info">{{ $taxes->total() }} taxe(s)</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Nom</th>
                                        <th class="text-center">Taux</th>
                                        <th class="text-center">Statut</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($taxes as $tax)
                                        <tr class="{{ $tax->trashed() ? 'table-secondary text-muted' : '' }}">
                                            <td class="font-weight-bold">
                                                {{ $tax->name }}
                                                @if ($tax->trashed())
                                                    <span class="badge badge-secondary ml-1">Supprimée</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-light border">{{ number_format($tax->rate, 2) }} %</span>
                                            </td>
                                            <td class="text-center">
                                                @if ($tax->trashed())
                                                    <span class="badge badge-secondary">Supprimée</span>
                                                @elseif ($tax->is_active)
                                                    <span class="badge badge-success">Active</span>
                                                @else
                                                    <span class="badge badge-warning">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                @if ($tax->trashed())
                                                    <form action="{{ route('taxes.restore', $tax->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                                            <i class="fas fa-undo"></i> Restaurer
                                                        </button>
                                                    </form>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                        data-toggle="modal" data-target="#editModal{{ $tax->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form action="{{ route('taxes.destroy', $tax) }}" method="POST" class="d-inline"
                                                        onsubmit="return confirm('Supprimer cette taxe ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>

                                        {{-- Modal édition --}}
                                        @unless ($tax->trashed())
                                            <div class="modal fade" id="editModal{{ $tax->id }}" tabindex="-1" role="dialog">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <form action="{{ route('taxes.update', $tax) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Modifier la taxe</h5>
                                                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="form-group">
                                                                    <label>Nom</label>
                                                                    <input type="text" name="name" class="form-control"
                                                                        value="{{ $tax->name }}" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Taux (%)</label>
                                                                    <input type="number" name="rate" step="0.01" min="0" max="100"
                                                                        class="form-control" value="{{ $tax->rate }}" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <div class="custom-control custom-switch">
                                                                        <input type="checkbox" class="custom-control-input"
                                                                            id="is_active_{{ $tax->id }}" name="is_active"
                                                                            value="1" {{ $tax->is_active ? 'checked' : '' }}>
                                                                        <label class="custom-control-label" for="is_active_{{ $tax->id }}">Active</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endunless
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                <i class="fas fa-percent fa-2x mb-2 d-block"></i>
                                                Aucune taxe configurée. Créez votre première taxe.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($taxes->hasPages())
                            <div class="px-3 py-2">
                                {{ $taxes->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
