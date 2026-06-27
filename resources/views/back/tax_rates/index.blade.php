@extends('back.layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Taxes / TVA</h4>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('tax_rates.store') }}" method="POST" class="row">
                @csrf
                <div class="form-group col-md-4">
                    <label>Nom</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group col-md-2">
                    <label>Taux (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="rate" class="form-control" required>
                </div>
                <div class="form-group col-md-2">
                    <label>Défaut</label>
                    <select name="is_default" class="form-control">
                        <option value="0">Non</option>
                        <option value="1">Oui</option>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>Actif</label>
                    <select name="is_active" class="form-control">
                        <option value="1">Oui</option>
                        <option value="0">Non</option>
                    </select>
                </div>
                <div class="form-group col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary btn-block" type="submit">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Taux</th>
                        <th>Défaut</th>
                        <th>Actif</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($taxRates as $taxRate)
                        <tr>
                            <td>{{ $taxRate->name }}</td>
                            <td>{{ $taxRate->rate }}%</td>
                            <td>{{ $taxRate->is_default ? 'Oui' : 'Non' }}</td>
                            <td>{{ $taxRate->is_active ? 'Oui' : 'Non' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">Aucune taxe</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $taxRates->links() }}</div>
</div>
@endsection
