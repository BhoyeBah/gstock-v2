@extends('back.layouts.admin')

@section('content')
<div class="container-fluid" x-data="posApp()" x-init="init()">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-cash-register mr-2"></i>Vente rapide (POS)</h1>
            <p class="text-muted mb-0">Caisse — saisie rapide, paiement immédiat.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <form action="{{ route('sales.store') }}" method="POST" id="pos-form">
        @csrf

        <div class="row">

            {{-- ── Colonne gauche : produits ── --}}
            <div class="col-lg-7 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-shopping-cart mr-1"></i> Lignes de vente
                        </h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" @click="addLine()">
                            <i class="fas fa-plus mr-1"></i> Ajouter ligne
                        </button>
                    </div>
                    <div class="card-body">

                        <template x-for="(line, index) in lines" :key="index">
                            <div class="row align-items-end mb-3 pb-3 border-bottom">
                                {{-- Produit --}}
                                <div class="col-md-5">
                                    <label class="small font-weight-bold">Produit <span class="text-danger">*</span></label>
                                    <select :name="`items[${index}][product_id]`" class="form-control form-control-sm"
                                        x-model="line.product_id" @change="onProductChange(index)" required>
                                        <option value="">— choisir —</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}"
                                                data-price="{{ $p->price ?? 0 }}">
                                                {{ $p->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Entrepôt (caché, hérité du formulaire) --}}
                                <input type="hidden" :name="`items[${index}][warehouse_id]`" x-model="line.warehouse_id">

                                {{-- Quantité --}}
                                <div class="col-md-2">
                                    <label class="small font-weight-bold">Qté</label>
                                    <input type="number" :name="`items[${index}][quantity]`" min="1" value="1"
                                        class="form-control form-control-sm"
                                        x-model.number="line.quantity"
                                        @input="recalcLine(index)" required>
                                </div>

                                {{-- Prix unitaire --}}
                                <div class="col-md-2">
                                    <label class="small font-weight-bold">Prix HT</label>
                                    <input type="number" :name="`items[${index}][unit_price]`" min="0"
                                        class="form-control form-control-sm"
                                        x-model.number="line.unit_price"
                                        @input="recalcLine(index)" required>
                                </div>

                                {{-- Remise --}}
                                <div class="col-md-2">
                                    <label class="small font-weight-bold">Remise</label>
                                    <input type="number" :name="`items[${index}][discount]`" min="0" value="0"
                                        class="form-control form-control-sm"
                                        x-model.number="line.discount"
                                        @input="recalcLine(index)">
                                </div>

                                {{-- Sous-total + suppr --}}
                                <div class="col-md-1 text-right">
                                    <label class="small font-weight-bold">Total</label>
                                    <div class="font-weight-bold text-primary small" x-text="fmt(line.total)"></div>
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0 mt-1"
                                        @click="removeLine(index)" x-show="lines.length > 1">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <div class="text-right mt-2">
                            <span class="font-weight-bold">Total TTC :
                                <span class="text-primary h5" x-text="fmt(grandTotal)"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Colonne droite : paiement ── --}}
            <div class="col-lg-5 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-money-bill-wave mr-1"></i> Paiement
                        </h6>
                    </div>
                    <div class="card-body">

                        {{-- Client (optionnel) --}}
                        <div class="form-group">
                            <label class="font-weight-bold">Client <span class="text-muted small">(optionnel)</span></label>
                            <select name="contact_id" class="form-control">
                                <option value="">Client comptoir</option>
                                @foreach($clients as $c)
                                    <option value="{{ $c->id }}" {{ old('contact_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->fullname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Entrepôt --}}
                        <div class="form-group">
                            <label class="font-weight-bold">Entrepôt <span class="text-danger">*</span></label>
                            <select name="warehouse_id" class="form-control" x-model="warehouseId"
                                @change="syncWarehouseToLines()" required>
                                <option value="">— sélectionner —</option>
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}" {{ old('warehouse_id') == $w->id ? 'selected' : '' }}>
                                        {{ $w->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Caisse / Wallet --}}
                        <div class="form-group">
                            <label class="font-weight-bold">Caisse <span class="text-danger">*</span></label>
                            <select name="wallet_id" class="form-control" required>
                                <option value="">— sélectionner —</option>
                                @foreach($wallets as $w)
                                    <option value="{{ $w->id }}" {{ old('wallet_id') == $w->id ? 'selected' : '' }}>
                                        {{ $w->name }}
                                        ({{ number_format($w->current_balance, 0, ',', ' ') }} FCFA)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Date de vente --}}
                        <div class="form-group">
                            <label class="font-weight-bold">Date de vente</label>
                            <input type="date" name="payment_date" class="form-control"
                                value="{{ old('payment_date', now()->toDateString()) }}">
                        </div>

                        {{-- Total à payer --}}
                        <div class="form-group">
                            <label class="font-weight-bold">Total à payer</label>
                            <div class="form-control bg-light font-weight-bold text-primary h5"
                                x-text="fmt(grandTotal)"></div>
                        </div>

                        {{-- Montant reçu --}}
                        <div class="form-group">
                            <label class="font-weight-bold">Montant reçu <span class="text-danger">*</span></label>
                            <input type="number" name="amount_paid" class="form-control form-control-lg font-weight-bold"
                                min="1" x-model.number="amountPaid"
                                @input="recalcChange()" required>
                        </div>

                        {{-- Rendu monnaie --}}
                        <div class="alert" :class="change >= 0 ? 'alert-success' : 'alert-warning'" x-show="amountPaid > 0">
                            <div class="d-flex justify-content-between">
                                <span class="font-weight-bold">
                                    <span x-text="change >= 0 ? 'Rendu :' : 'Reste dû :'"></span>
                                </span>
                                <span class="h5 mb-0 font-weight-bold" x-text="fmt(Math.abs(change))"></span>
                            </div>
                        </div>

                        <hr>

                        <button type="submit" class="btn btn-success btn-block btn-lg"
                            :disabled="lines.length === 0 || grandTotal === 0 || amountPaid < 1 || !warehouseId">
                            <i class="fas fa-check-circle mr-2"></i> Valider la vente
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

@push('scripts')
<script>
function posApp() {
    return {
        lines: [],
        warehouseId: '{{ old('warehouse_id', '') }}',
        amountPaid: 0,
        grandTotal: 0,
        change: 0,

        init() {
            this.addLine();
        },

        addLine() {
            this.lines.push({
                product_id: '',
                warehouse_id: this.warehouseId,
                quantity: 1,
                unit_price: 0,
                discount: 0,
                total: 0,
            });
        },

        removeLine(index) {
            this.lines.splice(index, 1);
            this.recalcTotal();
        },

        onProductChange(index) {
            const select = document.querySelectorAll(`select[name="items[${index}][product_id]"]`)[0];
            const option = select?.options[select.selectedIndex];
            if (option) {
                this.lines[index].unit_price = parseInt(option.dataset.price || 0);
                this.lines[index].warehouse_id = this.warehouseId;
                this.recalcLine(index);
            }
        },

        recalcLine(index) {
            const l = this.lines[index];
            const base = Math.max((l.unit_price * l.quantity) - l.discount, 0);
            l.total = base;
            this.recalcTotal();
        },

        recalcTotal() {
            this.grandTotal = this.lines.reduce((s, l) => s + (l.total || 0), 0);
            this.recalcChange();
        },

        recalcChange() {
            this.change = this.amountPaid - this.grandTotal;
        },

        syncWarehouseToLines() {
            this.lines.forEach(l => l.warehouse_id = this.warehouseId);
        },

        fmt(n) {
            return new Intl.NumberFormat('fr-FR').format(n || 0) + ' FCFA';
        },
    };
}
</script>
@endpush
@endsection
