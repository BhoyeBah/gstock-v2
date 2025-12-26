{{-- <!-- resources/views/payments/create.blade.php -->

@extends('back.layouts.admin')

@section('content')
<div class="container">
    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white">
            <h6 class="m-0 font-weight-bold"><i class="fas fa-money-bill-transfer"></i> Enregistrer un paiement</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('payments.store') }}" method="POST">
                @csrf

                <!-- Facture (readonly) -->
                <div class="form-group">
                    <label for="invoice_id">Facture</label>
                    <input type="text" id="invoice_id" class="form-control"
                        value="{{ $invoice->invoice_number ?? '' }}" readonly>
                    <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                </div>

                <!-- Tenant (readonly) -->
                <div class="form-group">
                    <label for="tenant_id">Client / Fournisseur</label>
                    <input type="text" id="tenant_id" class="form-control"
                        value="{{ $invoice->tenant->fullname ?? '' }}" readonly>
                    <input type="hidden" name="tenant_id" value="{{ $invoice->tenant_id }}">
                </div>

                <!-- Montant à payer -->
                <div class="form-group">
                    <label for="amount_paid">Montant payé (FCFA)</label>
                    <input type="number" name="amount_paid" id="amount_paid" class="form-control"
                        max="{{ $invoice->balance }}" required placeholder="Entrez le montant payé">
                    <small class="text-muted">Montant restant : {{ number_format($invoice->balance, 0, ',', ' ') }} FCFA</small>
                </div>

                <!-- Date de paiement -->
                <div class="form-group">
                    <label for="payment_date">Date de paiement</label>
                    <input type="date" name="payment_date" id="payment_date" class="form-control"
                        value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required>
                </div>

                <!-- Type de paiement -->
                <div class="form-group">
                    <label for="payment_type">Type de paiement</label>
                    <select name="payment_type" id="payment_type" class="form-control" required>
                        <option value="">-- Sélectionnez le type --</option>
                        <option value="cash">Espèces</option>
                        <option value="bank">Virement bancaire</option>
                        <option value="mobile_money">Mobile Money</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fas fa-money-bill-transfer"></i> Enregistrer le paiement
                </button>
                <a href="{{ route('payments.index') }}" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>
</div>
@endsection --}}
