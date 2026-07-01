@extends('back.layouts.admin')

@section('content')
    <style>
        .unit-edit-hero {
            background: linear-gradient(135deg, #0f172a 0%, #2563eb 100%);
            border-radius: 22px;
            padding: 1.15rem 1.35rem;
            color: #fff;
            box-shadow: 0 18px 38px rgba(15, 23, 42, .12);
        }

        .unit-edit-hero__title {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 800;
        }

        .unit-edit-hero__subtitle {
            margin-top: .35rem;
            color: rgba(255, 255, 255, .82);
        }

        .unit-edit-card {
            border: 0;
            border-radius: 22px;
            box-shadow: 0 16px 36px rgba(15, 23, 42, .08);
            overflow: hidden;
        }

        .unit-edit-card__header {
            background: linear-gradient(135deg, #1d4ed8 0%, #0f172a 100%);
            color: #fff;
            padding: 1rem 1.2rem;
        }
    </style>

    <div class="container">
        <div class="unit-edit-hero mb-4 d-flex flex-wrap justify-content-between align-items-start">
            <div>
                <h1 class="unit-edit-hero__title">
                    <i class="fas fa-pen-to-square mr-2"></i> Modifier l’unité
                </h1>
                <p class="unit-edit-hero__subtitle mb-0">
                    Ajustez le nom et le code sans quitter le module de gestion des unités.
                </p>
            </div>
            <a href="{{ route('admin.units.index') }}" class="btn btn-light font-weight-bold mt-3 mt-md-0">
                <i class="fas fa-arrow-left mr-2"></i> Retour à la liste
            </a>
        </div>

        <div class="card unit-edit-card">
            <div class="card-header unit-edit-card__header">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-sliders-h mr-2"></i> Formulaire de modification
                </h6>
            </div>

            <div class="card-body">
                @include("back.admin.units._form", [
                    'method' => "PUT",
                    'route' => route("admin.units.update", $unit),
                    'unit' => $unit
                ])
            </div>
        </div>
    </div>
@endsection
