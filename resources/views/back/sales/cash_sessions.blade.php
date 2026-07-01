@extends('back.layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-cash-register mr-2"></i>Caisse</h1>
    </div>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Ouvrir une caisse</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('cash-sessions.open') }}">
                        @csrf
                        <div class="form-group">
                            <label>Tiroir / moyen de paiement</label>
                            <select name="wallet_id" class="form-control" required>
                                <option value="">— Choisir —</option>
                                @foreach ($wallets as $wallet)
                                    <option value="{{ $wallet->id }}">{{ $wallet->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Fonds d'ouverture</label>
                            <input type="number" name="opening_amount" class="form-control" value="0" min="0" required>
                        </div>
                        <div class="form-group">
                            <label>Note (optionnel)</label>
                            <input type="text" name="note" class="form-control" maxlength="500">
                        </div>
                        <button class="btn btn-primary btn-block"><i class="fas fa-lock-open mr-1"></i> Ouvrir la caisse</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-success">Caisses ouvertes</h6></div>
                <div class="card-body">
                    @forelse ($openSessions as $session)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <strong>{{ optional($session->wallet)->name ?? '—' }}</strong>
                                <div class="small text-muted">Ouverte le {{ $session->opened_at->format('d/m/Y H:i') }} · Fonds {{ number_format($session->opening_amount, 0, ',', ' ') }}</div>
                            </div>
                            <a href="{{ route('cash-sessions.show', $session->id) }}" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-lock mr-1"></i> Clôturer
                            </a>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Aucune caisse ouverte.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Historique des caisses</h6></div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Caisse</th><th>Ouverture</th><th>Fermeture</th><th>Statut</th>
                        <th class="text-right">Fonds</th><th class="text-right">Attendu</th>
                        <th class="text-right">Compté</th><th class="text-right">Écart</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sessions as $session)
                        <tr>
                            <td>{{ optional($session->wallet)->name ?? '—' }}</td>
                            <td>{{ $session->opened_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $session->closed_at ? $session->closed_at->format('d/m/Y H:i') : '—' }}</td>
                            <td>
                                @if ($session->isOpen())
                                    <span class="badge badge-success">Ouverte</span>
                                @else
                                    <span class="badge badge-secondary">Clôturée</span>
                                @endif
                            </td>
                            <td class="text-right">{{ number_format($session->opening_amount, 0, ',', ' ') }}</td>
                            <td class="text-right">{{ $session->expected_amount !== null ? number_format($session->expected_amount, 0, ',', ' ') : '—' }}</td>
                            <td class="text-right">{{ $session->counted_amount !== null ? number_format($session->counted_amount, 0, ',', ' ') : '—' }}</td>
                            <td class="text-right {{ $session->difference < 0 ? 'text-danger' : '' }}">
                                {{ $session->difference !== null ? number_format($session->difference, 0, ',', ' ') : '—' }}
                            </td>
                            <td><a href="{{ route('cash-sessions.show', $session->id) }}" class="btn btn-sm btn-outline-primary">Voir</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted">Aucune session.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $sessions->links() }}
        </div>
    </div>
</div>
@endsection
