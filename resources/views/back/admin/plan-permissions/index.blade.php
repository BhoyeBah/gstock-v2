@extends('back.layouts.admin')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Permissions par plan</h1>
            <p class="mb-0 text-muted">Associer les modules SaaS disponibles à chaque plan.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left mr-1"></i> Retour au dashboard
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow border-left-primary">
                <div class="card-header bg-primary text-white py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="m-0 font-weight-bold">Plan par plan</h6>
                        <small class="text-white-50">Synchronisation directe des permissions associées.</small>
                    </div>
                </div>
                <div class="card-body">
                    @foreach($plans as $plan)
                        <div class="border rounded-lg p-3 mb-3">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
                                <div>
                                    <div class="h5 mb-1 text-gray-900">{{ $plan->name }}</div>
                                    <small class="text-muted">{{ number_format($plan->price, 0, ',', ' ') }} FCFA · {{ $plan->permissions->count() }} permission(s)</small>
                                </div>
                                <span class="badge badge-info align-self-start align-self-md-center mt-2 mt-md-0">
                                    {{ $plan->slug }}
                                </span>
                            </div>

                            <form method="POST" action="{{ route('admin.plan-permissions.update', $plan) }}">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    @foreach($permissions as $permission)
                                        <div class="col-md-6 col-lg-4 mb-2">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox"
                                                       class="custom-control-input"
                                                       id="plan_{{ $plan->id }}_perm_{{ $permission->id }}"
                                                       name="permissions[]"
                                                       value="{{ $permission->id }}"
                                                       {{ $plan->permissions->contains('id', $permission->id) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="plan_{{ $plan->id }}_perm_{{ $permission->id }}">
                                                    <div class="font-weight-bold">{{ $permission->name }}</div>
                                                    <small class="text-muted">{{ $permission->description ?? 'Aucune description' }}</small>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="d-flex justify-content-end mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-1"></i> Enregistrer
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow border-left-info mb-4">
                <div class="card-header bg-info text-white py-3">
                    <h6 class="m-0 font-weight-bold">Lecture rapide</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0 pl-3">
                        <li>Les permissions sont synchronisées par plan.</li>
                        <li>Les tenants héritent ensuite des capacités du plan.</li>
                        <li>Un plan sans permission ne donne aucun accès métier.</li>
                    </ul>
                </div>
            </div>

            <div class="card shadow border-left-secondary">
                <div class="card-header bg-secondary text-white py-3">
                    <h6 class="m-0 font-weight-bold">Permissions disponibles</h6>
                </div>
                <div class="card-body">
                    @foreach($permissions->take(10) as $permission)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <div class="font-weight-bold">{{ $permission->name }}</div>
                                <small class="text-muted">{{ $permission->description ?? 'Aucune description' }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
