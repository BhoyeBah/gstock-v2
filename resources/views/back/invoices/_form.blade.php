@php
    $isCreate = $method === 'POST';
    $isSupplier = $type === 'suppliers';
    $entityLabel = $isSupplier ? 'fournisseur' : 'client';
@endphp

<form action="{{ $route }}" method="{{ $method === 'PUT' ? 'POST' : $method }}">
    @csrf
    @if (in_array($method, ['PUT', 'PATCH', 'DELETE']))
        @method($method)
    @endif

    <input type="hidden" name="type" value="{{ $type === 'clients' ? 'client' : 'supplier' }}">

    @if ($method == 'POST')
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">
                <i class="fas {{ $isCreate ? 'fa-plus-circle' : 'fa-edit' }}"></i>
                {{ $isCreate ? "Nouvelle facture $entityLabel" : "Modifier la facture $entityLabel" }}
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="modal-body">
        <div class="row mb-3">

            <div class="col-12 col-sm-6 col-md-3">
                <label for="invoice_number" class="font-weight-bold">Numéro facture (optionnel)</label>
                <input type="text" name="invoice_number" id="invoice_number" class="form-control"
                    value="{{ old('invoice_number', $invoice->invoice_number ?? '') }}" placeholder="Ex: FAC-2025-001">
            </div>

            <div class="col-12 col-sm-6 col-md-4">
                <label for="contact_id" class="font-weight-bold text-capitalize">
                    {{ ucfirst($entityLabel) }} <span class="text-danger">*</span>
                </label>
                <select name="contact_id" id="contact_id" class="form-control" required>
                    <option value="">Sélectionnez un {{ $entityLabel }}</option>
                    @foreach ($contacts as $contact)
                        <option value="{{ $contact->id }}"
                            {{ old('contact_id', $invoice->contact_id) == $contact->id ? 'selected' : '' }}>
                            {{ $contact->info() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-sm-6 col-md-3">
                <label for="invoice_date" class="font-weight-bold">
                    Date facture <span class="text-danger">*</span>
                </label>
                <input type="date" name="invoice_date" class="form-control"
                    value="{{ old('invoice_date', $invoice->invoice_date) }}" required>
            </div>

            <div class="col-12 col-sm-6 col-md-2">
                <label for="due_date" class="font-weight-bold">Date d’échéance <span class="text-danger">*</span></label>
                <input type="date" name="due_date" class="form-control"
                    value="{{ old('due_date', $invoice->due_date) }}" required>
            </div>

        </div>


        <!-- Lignes facture -->
        @include('back.invoices._lines', ['invoice' => $invoice, 'products' => $products])
    </div>

    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times"></i> Annuler
        </button>
        <button type="submit" class="btn btn-primary">
            <i class="fas {{ $isCreate ? 'fa-save' : 'fa-check' }}"></i>
            {{ $isCreate ? 'Créer la facture' : 'Enregistrer les modifications' }}
        </button>
    </div>

</form>
