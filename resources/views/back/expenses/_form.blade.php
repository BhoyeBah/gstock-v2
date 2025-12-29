<form action="{{ $route }}" method="{{ $method === 'PUT' ? 'POST' : $method }}">
    @csrf
    @if (in_array($method, ['PUT', 'PATCH', 'DELETE']))
        @method($method)
    @endif

    <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
            <i class="fas {{ $method === 'POST' ? 'fa-plus-circle' : 'fa-edit' }}"></i>
            {{ $method === 'POST' ? 'Ajouter une dépense' : 'Modifier la dépense' }}
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body">
        <!-- Motif -->
        <div class="form-group">
            <label for="reason">Motif de la dépense <span class="text-danger">*</span></label>
            <input type="text" name="reason" id="reason"
                class="form-control @error('reason') is-invalid @enderror"
                placeholder="Ex : Achat de carburant, entretien matériel..."
                value="{{ old('reason', $expense->reason ?? '') }}" required>
            @error('reason')
                <small class="text-danger d-block mt-1">{{ $message }}</small>
            @enderror
        </div>

        <!-- Montant -->
        <div class="form-group">
            <label for="amount">Montant (FCFA) <span class="text-danger">*</span></label>
            <input type="number" min="0" step="1" name="amount" id="amount"
                class="form-control @error('amount') is-invalid @enderror" placeholder="Ex : 25 000"
                value="{{ old('amount', $expense->amount ?? '') }}" required>
            @error('amount')
                <small class="text-danger d-block mt-1">{{ $message }}</small>
            @enderror
        </div>

        <div class="form-group">
            <label for="wallet_id">Wallet <span class="text-danger">*</span></label>
            <select name="wallet_id" id="wallet_id" class="form-control @error('wallet_id') is-invalid @enderror"
                required>

                <option value="">-- Sélectionner un wallet --</option>

                @foreach ($wallets as $wallet)
                    <option value="{{ $wallet->id }}">{{ $wallet->name }} ({{ $wallet->current_balance }})</option>
                @endforeach
            </select>

            @error('wallet_id')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <!-- Date de la dépense -->
        <div class="form-group">
            <label for="expense_date">Date de la dépense <span class="text-danger">*</span></label>
            <input type="date" name="expense_date" id="expense_date"
                class="form-control @error('expense_date') is-invalid @enderror"
                value="{{ old('expense_date', isset($expense) && $expense->expense_date ? $expense->expense_date->format('Y-m-d') : '') }}"
                required>

            @error('expense_date')
                <small class="text-danger d-block mt-1">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times"></i> Annuler
        </button>
        <button type="submit" class="btn btn-primary">
            <i class="fas {{ $method === 'POST' ? 'fa-save' : 'fa-check' }}"></i>
            {{ $method === 'POST' ? 'Ajouter' : 'Enregistrer les modifications' }}
        </button>
    </div>
</form>
