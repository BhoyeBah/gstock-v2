@extends('back.layouts.admin')

@section('content')
    <style>
        .units-hero {
            background: linear-gradient(135deg, #0f172a 0%, #2563eb 100%);
            border-radius: 24px;
            padding: 1.2rem 1.35rem;
            color: #fff;
            box-shadow: 0 18px 38px rgba(15, 23, 42, .12);
        }

        .units-hero__eyebrow {
            font-size: .78rem;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .72);
            font-weight: 700;
        }

        .units-hero__title {
            margin: .25rem 0 0;
            font-size: 1.45rem;
            font-weight: 800;
        }

        .units-hero__subtitle {
            margin-top: .35rem;
            color: rgba(255, 255, 255, .82);
            max-width: 680px;
        }

        .units-hero__stats {
            display: flex;
            flex-wrap: wrap;
            gap: .7rem;
            margin-top: 1rem;
        }

        .units-chip {
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: 999px;
            padding: .55rem .9rem;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            font-weight: 700;
        }

        .units-card {
            border: 0;
            border-radius: 22px;
            box-shadow: 0 16px 36px rgba(15, 23, 42, .08);
            overflow: hidden;
        }

        .units-card__header {
            background: linear-gradient(135deg, #1d4ed8 0%, #0f172a 100%);
            color: #fff;
            padding: 1rem 1.2rem;
        }

        .units-card__title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: .55rem;
        }

        .units-table thead th {
            border-top: 0;
            border-bottom: 0;
            text-transform: uppercase;
            font-size: .74rem;
            letter-spacing: .08em;
            color: #64748b;
        }

        .units-table tbody tr {
            transition: background-color .2s ease, transform .2s ease;
        }

        .units-table tbody tr:hover {
            background: rgba(37, 99, 235, .03);
        }

        .unit-pill {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .42rem .72rem;
            border-radius: 999px;
            background: rgba(37, 99, 235, .08);
            color: #1d4ed8;
            font-weight: 700;
            font-size: .85rem;
        }

        .unit-actions .btn {
            border-radius: 10px;
            width: 34px;
            height: 34px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .units-empty {
            border: 1px dashed rgba(37, 99, 235, .25);
            border-radius: 18px;
            padding: 2rem 1rem;
            background: linear-gradient(180deg, #f8fbff 0%, #eef4ff 100%);
        }

        .modal-unit-compact {
            max-width: 560px;
        }
    </style>

    <div class="units-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start">
            <div>
                <div class="units-hero__eyebrow">Catalogue</div>
                <h1 class="units-hero__title">
                    <i class="fas fa-balance-scale mr-2"></i> Unités de mesure
                </h1>
                <p class="units-hero__subtitle mb-0">
                    Centralisez les unités utilisées dans les produits, les achats et les documents commerciaux.
                </p>
            </div>
            <div class="mt-3 mt-md-0">
                <button type="button" class="btn btn-light shadow-sm font-weight-bold" data-toggle="modal" data-target="#addUnitModal">
                    <i class="fas fa-plus mr-2 text-primary"></i> Nouvelle unité
                </button>
            </div>
        </div>

        <div class="units-hero__stats">
            <div class="units-chip">
                <i class="fas fa-layer-group"></i>
                {{ $units->count() }} unité(s)
            </div>
            <div class="units-chip">
                <i class="fas fa-boxes-stacked"></i>
                Utilisées dans les produits
            </div>
        </div>
    </div>

    <div class="card units-card">
        <div class="card-header units-card__header">
            <h6 class="units-card__title">
                <i class="fas fa-list-ul"></i>
                Liste des unités disponibles
            </h6>
        </div>
        <div class="card-body p-0">
            @if ($units->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 units-table">
                        <thead class="bg-light">
                            <tr>
                                <th class="pl-4">#</th>
                                <th>Nom</th>
                                <th>Code</th>
                                <th class="text-center pr-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($units as $unit)
                                <tr>
                                    <td class="pl-4">{{ $loop->iteration }}</td>
                                    <td>
                                        <strong class="text-dark">{{ $unit->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="unit-pill">
                                            <i class="fas fa-tag"></i>
                                            {{ $unit->code }}
                                        </span>
                                    </td>
                                    <td class="text-center pr-4">
                                        <div class="unit-actions d-inline-flex align-items-center">
                                            <a href="{{ route('admin.units.edit', $unit->id) }}" class="btn btn-sm btn-outline-warning mr-2" title="Modifier">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <form action="{{ route('admin.units.destroy', $unit->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirmer la suppression de cette unité ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="units-empty text-center m-4">
                    <i class="fas fa-info-circle fa-2x text-primary mb-3"></i>
                    <h5 class="font-weight-bold mb-2">Aucune unité disponible</h5>
                    <p class="text-muted mb-3">Ajoutez votre première unité pour structurer le catalogue produit.</p>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUnitModal">
                        <i class="fas fa-plus mr-2"></i> Créer une unité
                    </button>
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="addUnitModal" tabindex="-1" role="dialog" aria-labelledby="addUnitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-unit-compact" role="document">
            <div class="modal-content border-0 shadow-lg">
                @include("back.admin.units._form", [
                    "route" => route("admin.units.store"),
                    "method" => "POST",
                    "unit" => new \App\Models\Units()
                ])
            </div>
        </div>
    </div>
@endsection
