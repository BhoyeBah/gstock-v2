@extends('back.layouts.admin')

@php
    use Carbon\Carbon;
@endphp

@section('content')
<style>
    .page-header-vente {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 1.5rem 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        color: #fff;
    }
    .page-header-vente h1 { font-weight: 700; margin: 0; font-size: 1.6rem; }
    .vente-card { border: none; border-radius: 12px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); background:#fff; margin-bottom: 1.5rem; }
    .vente-card .card-header { background:#f8f9fc; border-bottom:1px solid #e3e6f0; padding: 1rem 1.25rem; }
    .vente-card .card-header h6 { font-weight:700; color:#4e73df; margin:0; }
    .border-left-primary { border-left:.25rem solid #4e73df !important; }
    .border-left-info { border-left:.25rem solid #36b9cc !important; }
    .header-select-entrepot { max-width:320px; border:2px solid #36b9cc; font-weight:600; color:#2e59d9; border-radius:20px; text-align:center; }
    .produit-item { padding:1rem; background:#f8f9fc; border-radius:10px; margin-bottom:1rem; border:1px solid #e3e6f0; }
    .total-display { font-weight:700; color:#4e73df; padding:.6rem; background:rgba(78,115,223,.1); border-radius:8px; text-align:right; }
    .btn-icon-delete { background:#fff; border:1px solid #e74a3b; color:#e74a3b; width:38px; height:38px; border-radius:8px; display:flex; align-items:center; justify-content:center; }
    .btn-icon-delete:hover { background:#e74a3b; color:#fff; }
    .sticky-top { top:20px; z-index:100; }
    .stock-badge { font-size:.72rem; }
</style>

<div class="container-fluid">

    <div class="page-header-vente">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <h1><i class="fas fa-cash-register mr-2"></i> Point de vente</h1>
            <span class="badge badge-light p-2 shadow-sm">
                <i class="fas fa-calendar-day mr-1"></i> {{ Carbon::now()->locale('fr')->isoFormat('LL') }}
            </span>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form id="venteForm" method="POST" action="{{ route('pos.store') }}">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <div class="card vente-card border-left-primary shadow-sm">
                    <div class="card-header"><h6><i class="fas fa-user-tag mr-2"></i>Client (optionnel)</h6></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 mb-2">
                                <label class="small font-weight-bold text-muted">Client</label>
                                <select class="form-control" id="clientSelect" name="contact_id">
                                    <option value="">— Client de passage —</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->fullname }} @if($client->phone_number) ({{ $client->phone_number }}) @endif</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-2 d-flex align-items-end">
                                <small class="text-muted"><i class="fas fa-info-circle"></i> Un client est requis pour une vente à crédit (paiement partiel).</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card vente-card border-left-info shadow-sm">
                    <div class="card-header d-flex align-items-center">
                        <div style="flex:1;"><h6 class="mb-0 text-nowrap"><i class="fas fa-cubes mr-2"></i>Articles</h6></div>
                        <div class="mx-auto" style="flex:2; display:flex; justify-content:center;">
                            <select class="form-control form-control-sm header-select-entrepot" id="warehouseSelect" name="warehouse_id" required>
                                <option value="">— Sélectionner l'entrepôt —</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="flex:1; text-align:right;">
                            <button type="button" class="btn btn-primary btn-sm shadow-sm" id="btnAddRow" onclick="ajouterLigneProduit()" disabled>
                                <i class="fas fa-plus-circle mr-1"></i> Ajouter
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="produitsSelectionnes"></div>
                        <p class="text-muted small mb-0" id="emptyHint">Choisissez d'abord un entrepôt pour charger les produits disponibles.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card vente-card shadow-lg sticky-top">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h6 class="text-white mb-0 text-uppercase">Résumé</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3 text-dark">
                            <span class="font-weight-bold">Sous-total</span><strong id="sousTotal">0 FCFA</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3 text-danger">
                            <span>Remises</span><strong id="totalRemise">0 FCFA</strong>
                        </div>
                        <hr>
                        <div class="py-3 text-center bg-light rounded mb-4 border">
                            <h6 class="text-uppercase small text-muted font-weight-bold">Total net à payer</h6>
                            <h2 class="font-weight-bold text-primary mb-0" id="totalFinal">0 FCFA</h2>
                        </div>
                        <button type="button" class="btn btn-success btn-block btn-lg shadow" onclick="ouvrirModalPaiement()">
                            <i class="fas fa-cash-register mr-2"></i> Procéder au paiement
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal paiement -->
        <div class="modal fade" id="paiementModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-wallet mr-2"></i> Règlement</h5>
                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body bg-light">
                        <div class="alert alert-primary border-0 shadow-sm d-flex justify-content-between align-items-center mb-3">
                            <span class="font-weight-bold text-uppercase small">Total à payer :</span>
                            <h4 class="mb-0 font-weight-bold" id="montantAPayer">0 FCFA</h4>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="font-weight-bold mb-0 text-dark">Modes de règlement</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="ajouterPaiement()">
                                <i class="fas fa-plus mr-1"></i> Ligne
                            </button>
                        </div>
                        <div id="paiementsContainer"></div>
                        <div class="card border-0 shadow-sm mt-3 bg-white">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small text-muted font-weight-bold text-uppercase">Total reçu :</span>
                                    <strong id="totalPaye" class="text-success">0 FCFA</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="small text-muted font-weight-bold text-uppercase">Reste (dette) :</span>
                                    <strong id="resteAPayer" class="text-danger">0 FCFA</strong>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">Laissez un reste pour une vente à crédit (client obligatoire).</small>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary px-5 shadow-lg font-weight-bold" id="btnFinaliser">
                            <i class="fas fa-check-circle mr-1"></i> Finaliser la vente
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@php
    $walletsJson = $wallets->map(fn ($w) => ['id' => $w->id, 'name' => $w->name])->values();
@endphp

@push('scripts')
<script>
    const POS = {
        productsUrl: "{{ route('pos.products') }}",
        wallets: @json($walletsJson),
        products: [],
        rowIndex: 0,
        payIndex: 0,
        total: 0,
    };

    function formatFCFA(m) { return new Intl.NumberFormat('fr-FR').format(Math.round(m)) + ' FCFA'; }
    function toNumber(v) { const n = parseFloat(v); return isNaN(n) ? 0 : n; }

    async function loadProducts() {
        const warehouseId = document.getElementById('warehouseSelect').value;
        const addBtn = document.getElementById('btnAddRow');
        document.getElementById('produitsSelectionnes').innerHTML = '';
        POS.products = [];
        if (!warehouseId) {
            addBtn.disabled = true;
            document.getElementById('emptyHint').style.display = 'block';
            calculateAll();
            return;
        }
        try {
            const res = await fetch(`${POS.productsUrl}?warehouse_id=${encodeURIComponent(warehouseId)}`, {
                headers: { 'Accept': 'application/json' }
            });
            POS.products = await res.json();
        } catch (e) { POS.products = []; }
        addBtn.disabled = false;
        document.getElementById('emptyHint').style.display = POS.products.length ? 'none' : 'block';
        if (!POS.products.length) {
            document.getElementById('emptyHint').textContent = 'Aucun produit en stock dans cet entrepôt.';
        }
        ajouterLigneProduit();
    }

    function productOptions() {
        return POS.products.map(p =>
            `<option value="${p.id}" data-price="${p.price}" data-available="${p.available}">${p.name} (stock: ${p.available})</option>`
        ).join('');
    }

    function ajouterLigneProduit() {
        if (!POS.products.length) return;
        const container = document.getElementById('produitsSelectionnes');
        const i = POS.rowIndex++;
        const html = `
            <div class="produit-item shadow-sm" id="row-${i}">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label class="small font-weight-bold text-muted text-uppercase">Article</label>
                        <select class="form-control productSelect" name="items[${i}][product_id]" onchange="onProduct(${i}, this)" required>
                            <option value="">-- Article --</option>
                            ${productOptions()}
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="small font-weight-bold text-muted text-uppercase">Qté</label>
                        <input type="number" class="form-control quantity" name="items[${i}][quantity]" value="1" min="1" oninput="calculateAll()" required>
                        <span class="badge badge-info stock-badge stockInfo d-none"></span>
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="small font-weight-bold text-muted text-uppercase">P.U.</label>
                        <input type="number" class="form-control unitPrice" name="items[${i}][unit_price]" value="0" min="0" oninput="calculateAll()" required>
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="small font-weight-bold text-muted text-uppercase">Remise</label>
                        <input type="number" class="form-control discount" name="items[${i}][discount]" value="0" min="0" oninput="calculateAll()">
                    </div>
                    <div class="col-md-1 col-4">
                        <label class="small font-weight-bold text-muted text-uppercase">Total</label>
                        <div class="total-display lineTotal">0</div>
                    </div>
                    <div class="col-md-1 col-2 text-right">
                        <button type="button" class="btn-icon-delete" onclick="removeRow(${i})"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </div>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
    }

    function onProduct(i, select) {
        const opt = select.selectedOptions[0];
        const row = document.getElementById(`row-${i}`);
        const price = opt ? toNumber(opt.dataset.price) : 0;
        const available = opt ? toNumber(opt.dataset.available) : 0;
        row.querySelector('.unitPrice').value = price;
        const qty = row.querySelector('.quantity');
        qty.max = available;
        const info = row.querySelector('.stockInfo');
        info.textContent = 'dispo: ' + available;
        info.classList.remove('d-none');
        calculateAll();
    }

    function removeRow(i) { const r = document.getElementById(`row-${i}`); if (r) r.remove(); calculateAll(); }

    function calculateAll() {
        let st = 0, tr = 0;
        document.querySelectorAll('.produit-item').forEach(row => {
            const price = toNumber(row.querySelector('.unitPrice').value);
            const qty = toNumber(row.querySelector('.quantity').value);
            const disc = toNumber(row.querySelector('.discount').value);
            const line = (price * qty) - disc;
            row.querySelector('.lineTotal').innerText = formatFCFA(line < 0 ? 0 : line);
            st += price * qty;
            tr += disc;
        });
        POS.total = st - tr;
        document.getElementById('sousTotal').innerText = formatFCFA(st);
        document.getElementById('totalRemise').innerText = formatFCFA(tr);
        document.getElementById('totalFinal').innerText = formatFCFA(POS.total < 0 ? 0 : POS.total);
    }

    function validateStock() {
        let ok = true;
        document.querySelectorAll('.produit-item').forEach(row => {
            const sel = row.querySelector('.productSelect');
            const opt = sel.selectedOptions[0];
            if (!opt || !sel.value) return;
            const available = toNumber(opt.dataset.available);
            const qty = toNumber(row.querySelector('.quantity').value);
            if (qty > available) { ok = false; row.querySelector('.quantity').classList.add('is-invalid'); }
            else { row.querySelector('.quantity').classList.remove('is-invalid'); }
        });
        return ok;
    }

    function ouvrirModalPaiement() {
        if (!document.getElementById('warehouseSelect').value) {
            alert("Sélectionnez un entrepôt."); return;
        }
        const validRows = [...document.querySelectorAll('.productSelect')].filter(s => s.value).length;
        if (validRows === 0) { alert('Ajoutez au moins un article.'); return; }
        if (POS.total <= 0) { alert('Le total doit être supérieur à 0.'); return; }
        if (!validateStock()) { alert("Quantité supérieure au stock disponible."); return; }

        document.getElementById('montantAPayer').innerText = formatFCFA(POS.total);
        document.getElementById('paiementsContainer').innerHTML = '';
        POS.payIndex = 0;
        ajouterPaiement(POS.total);
        calcPay();
        $('#paiementModal').modal('show');
    }

    function ajouterPaiement(prefill = '') {
        if (!POS.wallets.length) {
            document.getElementById('paiementsContainer').innerHTML =
                '<div class="alert alert-warning py-2 small mb-2">Aucun wallet actif. Créez un moyen de paiement pour encaisser.</div>';
            return;
        }
        const c = document.getElementById('paiementsContainer');
        const id = POS.payIndex++;
        const options = POS.wallets.map(w => `<option value="${w.id}">${w.name}</option>`).join('');
        const html = `
            <div class="row no-gutters mb-2 align-items-center bg-white p-2 rounded border" id="pay-${id}">
                <div class="col-6 pr-2">
                    <select class="form-control form-control-sm" name="payments[${id}][wallet_id]">${options}</select>
                </div>
                <div class="col-5">
                    <input type="number" min="0" class="form-control form-control-sm amountPay" name="payments[${id}][amount]" value="${prefill}" oninput="calcPay()" placeholder="Montant">
                </div>
                <div class="col-1 text-center">
                    <button type="button" class="btn text-danger btn-sm" onclick="this.closest('.row').remove(); calcPay();"><i class="fas fa-times"></i></button>
                </div>
            </div>`;
        c.insertAdjacentHTML('beforeend', html);
    }

    function calcPay() {
        let paid = 0;
        document.querySelectorAll('.amountPay').forEach(i => paid += toNumber(i.value));
        const reste = POS.total - paid;
        document.getElementById('totalPaye').innerText = formatFCFA(paid);
        const el = document.getElementById('resteAPayer');
        if (reste <= 0) {
            el.innerText = reste < 0 ? 'À rendre: ' + formatFCFA(Math.abs(reste)) : 'Soldé';
            el.className = 'text-success font-weight-bold';
        } else {
            el.innerText = formatFCFA(reste);
            el.className = 'text-danger font-weight-bold';
        }
    }

    // Garde-fou à la soumission : crédit => client obligatoire, pas de sur-paiement.
    document.getElementById('venteForm').addEventListener('submit', function (e) {
        let paid = 0;
        document.querySelectorAll('.amountPay').forEach(i => paid += toNumber(i.value));
        if (paid > POS.total) { e.preventDefault(); alert("Le montant encaissé dépasse le total. Le rendu-monnaie n'est pas enregistré : saisissez au plus le total."); return; }
        if (paid < POS.total && !document.getElementById('clientSelect').value) {
            e.preventDefault();
            alert('Une vente à crédit (paiement partiel) nécessite un client.');
        }
    });

    document.getElementById('warehouseSelect').addEventListener('change', loadProducts);
</script>
@endpush
@endsection
