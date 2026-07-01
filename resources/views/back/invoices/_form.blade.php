@php
    $isCreate = $method === 'POST';
    $isSupplier = $type === 'suppliers';
    $entityLabel = $isSupplier ? 'fournisseur' : 'client';
@endphp

<style>
    .invoice-modal__header {
        background: linear-gradient(135deg, #111827 0%, #1d4ed8 100%);
        border: 0;
        padding: 1rem 1.2rem;
    }

    .invoice-modal__title {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: .6rem;
    }

    .invoice-modal__subtitle {
        margin-top: .15rem;
        color: rgba(255, 255, 255, .8);
        font-size: .88rem;
    }

    .invoice-modal__body {
        background: linear-gradient(180deg, #f8fbff 0%, #f4f8ff 100%);
        padding: .95rem;
    }

    .invoice-modal__panel {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, .08);
        border-radius: 16px;
        padding: .95rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
    }

    .invoice-modal__label {
        display: block;
        margin-bottom: .4rem;
        font-size: .88rem;
        font-weight: 700;
        color: #0f172a;
    }

    .invoice-modal__input {
        border-radius: 12px;
        min-height: 44px;
        border: 1px solid rgba(15, 23, 42, .12);
        box-shadow: none;
    }

    .invoice-modal__input:focus {
        border-color: rgba(29, 78, 216, .45);
        box-shadow: 0 0 0 .18rem rgba(29, 78, 216, .12);
    }

    .invoice-modal__section-title {
        display: flex;
        align-items: center;
        gap: .5rem;
        margin-bottom: .85rem;
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #1d4ed8;
        font-weight: 800;
    }

    .invoice-modal__footer {
        background: #fff;
        border-top: 1px solid rgba(15, 23, 42, .08);
        padding: .85rem 1rem;
        gap: .55rem;
    }

    .invoice-modal__btn {
        border-radius: 12px;
        padding: .62rem 1rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: .45rem;
    }
</style>

@php
    $invoice = $invoice ?? new \App\Models\Invoice();
@endphp

<form action="{{ $route }}" method="{{ $method === 'PUT' ? 'POST' : $method }}">
    @csrf
    @if (in_array($method, ['PUT', 'PATCH', 'DELETE']))
        @method($method)
    @endif

    <input type="hidden" name="type" value="{{ $type === 'clients' ? 'client' : 'supplier' }}">

    <div class="modal-header invoice-modal__header text-white">
        <div>
            <h5 class="invoice-modal__title">
                <i class="fas {{ $isCreate ? 'fa-file-invoice' : 'fa-pen-to-square' }}"></i>
                {{ $isCreate ? "Nouvelle facture $entityLabel" : "Modifier la facture $entityLabel" }}
            </h5>
            <div class="invoice-modal__subtitle">
                Gardez les informations principales claires avant la saisie des lignes.
            </div>
        </div>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body invoice-modal__body">
        <div class="invoice-modal__panel">
            <div class="invoice-modal__section-title">
                <i class="fas fa-info-circle"></i>
                Informations générales
            </div>

            <div class="row mb-3">
                <div class="col-12 col-sm-6 col-md-3 mb-3">
                    <label for="invoice_number" class="invoice-modal__label">Numéro facture (optionnel)</label>
                    <input type="text" name="invoice_number" id="invoice_number" class="form-control invoice-modal__input"
                        value="{{ old('invoice_number', $invoice->invoice_number ?? '') }}" placeholder="Ex: FAC-2025-001">
                </div>

                <div class="col-12 col-sm-6 col-md-4 mb-3">
                    <label for="contact_id" class="invoice-modal__label text-capitalize">
                        {{ ucfirst($entityLabel) }} <span class="text-danger">*</span>
                    </label>
                    <select name="contact_id" id="contact_id" class="form-control invoice-modal__input" required>
                        <option value="">Sélectionnez un {{ $entityLabel }}</option>
                        @foreach ($contacts as $contact)
                            <option value="{{ $contact->id }}"
                                {{ old('contact_id', $invoice->contact_id) == $contact->id ? 'selected' : '' }}>
                                {{ $contact->info() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-md-3 mb-3">
                    <label for="invoice_date" class="invoice-modal__label">
                        Date facture <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="invoice_date" class="form-control invoice-modal__input"
                        value="{{ old('invoice_date', $invoice->invoice_date) }}" required>
                </div>

                <div class="col-12 col-sm-6 col-md-2 mb-3">
                    <label for="due_date" class="invoice-modal__label">Date d’échéance <span class="text-danger">*</span></label>
                    <input type="date" name="due_date" class="form-control invoice-modal__input"
                        value="{{ old('due_date', $invoice->due_date) }}" required>
                </div>
            </div>

            @include('back.invoices._lines', ['invoice' => $invoice, 'products' => $products])
        </div>
    </div>

    <div class="modal-footer invoice-modal__footer justify-content-end">
        <button type="button" class="btn btn-light invoice-modal__btn" data-dismiss="modal">
            <i class="fas fa-times"></i> Annuler
        </button>
        <button type="submit" class="btn btn-primary invoice-modal__btn">
            <i class="fas {{ $isCreate ? 'fa-save' : 'fa-check' }}"></i>
            {{ $isCreate ? 'Créer la facture' : 'Enregistrer les modifications' }}
        </button>
    </div>
</form>
