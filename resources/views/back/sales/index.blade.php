@extends('back.layouts.admin')

@php
    use Carbon\Carbon;
@endphp

@section('content')
<style>
    /* --- DESIGN PRINCIPAL --- */
    .page-header-vente {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        color: #fff;
    }

    .page-header-vente h1 {
        font-weight: 700;
        margin: 0;
        font-size: 1.75rem;
    }

    .vente-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        background: #fff;
        margin-bottom: 1.5rem;
    }

    .vente-card .card-header {
        background: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
        padding: 1rem 1.25rem;
    }

    .vente-card .card-header h6 {
        font-weight: 700;
        color: #4e73df;
        margin: 0;
    }

    .border-left-primary { border-left: 0.25rem solid #4e73df !important; }
    .border-left-info { border-left: 0.25rem solid #36b9cc !important; }

    /* Style du sélecteur d'entrepôt centré */
    .header-select-entrepot {
        max-width: 300px;
        border: 2px solid #36b9cc;
        font-weight: 600;
        color: #2e59d9;
        border-radius: 20px;
        text-align: center;
    }

    .produit-item {
        padding: 1.25rem;
        background-color: #f8f9fc;
        border-radius: 10px;
        margin-bottom: 1rem;
        border: 1px solid #e3e6f0;
        transition: all 0.2s ease;
    }

    .total-display {
        font-weight: 700;
        color: #4e73df;
        padding: 0.6rem;
        background-color: rgba(78, 115, 223, 0.1);
        border-radius: 8px;
        text-align: right;
    }

    .btn-icon-delete {
        background-color: #fff;
        border: 1px solid #e74a3b;
        color: #e74a3b;
        width: 38px; height: 38px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
    }

    .btn-icon-delete:hover { background-color: #e74a3b; color: #fff; }

    .sticky-top { top: 20px; z-index: 100; }
</style>

<div class="container-fluid">

    <div class="page-header-vente">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <h1><i class="fas fa-shopping-cart mr-2"></i> Vente Directe</h1>
            <span class="badge badge-light p-2 shadow-sm">
                <i class="fas fa-calendar-day mr-1"></i> {{ Carbon::now()->locale('fr')->isoFormat('LL') }}
            </span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="venteForm">

                <div class="card vente-card border-left-primary shadow-sm">
                    <div class="card-header">
                        <h6><i class="fas fa-user-tag mr-2"></i>Informations du Client</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="small font-weight-bold text-muted">Nom complet</label>
                                <input type="text" class="form-control" id="clientNom" placeholder="Nom du client">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="small font-weight-bold text-muted">Téléphone</label>
                                <input type="tel" class="form-control" id="clientTelephone" placeholder="Ex: 77...">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="small font-weight-bold text-muted">Adresse</label>
                                <input type="text" class="form-control" id="clientAdresse" placeholder="Dakar, Sénégal">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card vente-card border-left-info shadow-sm">
                    <div class="card-header d-flex align-items-center">
                        <div style="flex: 1;">
                            <h6 class="mb-0 text-nowrap"><i class="fas fa-cubes mr-2"></i>Articles</h6>
                        </div>

                        <div class="mx-auto" style="flex: 2; display: flex; justify-content: center;">
                            <select class="form-control form-control-sm header-select-entrepot" id="entrepot_id" required>
                                <option value="">--- Sélectionner l'entrepôt ---</option>
                                <option value="1">Entrepôt Principal - Dakar</option>
                                <option value="2">Dépôt Thies</option>
                                <option value="3">Magasin Saint-Louis</option>
                            </select>
                        </div>

                        <div style="flex: 1; text-align: right;">
                            <button type="button" class="btn btn-primary btn-sm shadow-sm" onclick="ajouterLigneProduit()">
                                <i class="fas fa-plus-circle mr-1"></i> Ajouter
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="produitsSelectionnes">
                            </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="card vente-card shadow-lg sticky-top">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h6 class="text-white mb-0 text-uppercase letter-spacing-1">Résumé de la transaction</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3 text-dark">
                        <span class="font-weight-bold">Sous-total</span>
                        <strong id="sousTotal">0 FCFA</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3 text-danger">
                        <span>Remises</span>
                        <strong id="totalRemise">0 FCFA</strong>
                    </div>
                    <hr>
                    <div class="py-3 text-center bg-light rounded mb-4 border">
                        <h6 class="text-uppercase small text-muted font-weight-bold">Total Net à payer</h6>
                        <h2 class="font-weight-bold text-primary mb-0" id="totalFinal">0 FCFA</h2>
                    </div>
                    <button type="button" class="btn btn-success btn-block btn-lg shadow" onclick="ouvrirModalPaiement()">
                        <i class="fas fa-cash-register mr-2"></i> PROCÉDER AU PAIEMENT
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="paiementModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-wallet mr-2"></i> Règlement</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body bg-light">
                <div class="alert alert-primary border-0 shadow-sm d-flex justify-content-between align-items-center mb-4">
                    <span class="font-weight-bold text-uppercase small">Total Facture :</span>
                    <h4 class="mb-0 font-weight-bold" id="montantAPayer">0 FCFA</h4>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="font-weight-bold mb-0 text-dark">Modes de règlement</h6>
                    <button class="btn btn-sm btn-outline-primary" onclick="ajouterPaiement()">
                        <i class="fas fa-plus mr-1"></i> Multi-mode
                    </button>
                </div>
                <div id="paiementsContainer"></div>
                <div class="card border-0 shadow-sm mt-3 bg-white">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small text-muted font-weight-bold text-uppercase">Total Reçu :</span>
                            <strong id="totalPaye" class="text-success">0 FCFA</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="small text-muted font-weight-bold text-uppercase">Reste à percevoir :</span>
                            <strong id="resteAPayer" class="text-danger">0 FCFA</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button class="btn btn-primary px-5 shadow-lg font-weight-bold" onclick="confirmerPaiement()">
                    <i class="fas fa-check-circle mr-1"></i> FINALISER LA VENTE
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let produitIndex = 0;
    let paiementIndex = 0;
    let montantTotal = 0;

    const produitsDisponibles = [
        { id: 1, nom: "Ordinateur HP", prix: 450000 },
        { id: 2, nom: "Samsung S23", prix: 850000 },
        { id: 3, nom: "MacBook Air M2", prix: 950000 },
        { id: 4, nom: "iPhone 15 Pro", prix: 1200000 }
    ];

    function formatFCFA(montant) {
        return new Intl.NumberFormat('fr-FR').format(montant) + ' FCFA';
    }

    function toNumber(v) { return isNaN(parseFloat(v)) ? 0 : parseFloat(v); }

    function ajouterLigneProduit() {
        const container = document.getElementById('produitsSelectionnes');
        const index = produitIndex++;

        const html = `
            <div class="produit-item shadow-sm" id="produit-row-${index}">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label class="small font-weight-bold text-muted text-uppercase">Désignation</label>
                        <select class="form-control productSelect" onchange="updateRow(${index}, this)">
                            <option value="">-- Sélectionner l'article --</option>
                            ${produitsDisponibles.map(p => `<option value="${p.id}" data-prix="${p.prix}">${p.nom}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="small font-weight-bold text-muted text-uppercase">Qté</label>
                        <input type="number" class="form-control quantity" value="1" min="1" oninput="calculateAll()">
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="small font-weight-bold text-muted text-uppercase">Remise</label>
                        <input type="number" class="form-control discount" value="0" min="0" oninput="calculateAll()">
                    </div>
                    <div class="col-md-3">
                        <label class="small font-weight-bold text-muted text-right d-block text-uppercase">Total</label>
                        <div class="total-display" id="total-row-${index}">0 FCFA</div>
                        <input type="hidden" class="unit-price" value="0">
                    </div>
                    <div class="col-md-1 text-right mt-3">
                        <button type="button" class="btn-icon-delete" onclick="removeRow(${index})">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    }

    function updateRow(index, select) {
        const option = select.selectedOptions[0];
        const price = option.dataset.prix || 0;
        const row = document.getElementById(`produit-row-${index}`);
        row.querySelector('.unit-price').value = price;
        calculateAll();
    }

    function removeRow(index) {
        document.getElementById(`produit-row-${index}`).remove();
        calculateAll();
    }

    function calculateAll() {
        let st = 0; let tr = 0;
        document.querySelectorAll('.produit-item').forEach(row => {
            const price = toNumber(row.querySelector('.unit-price').value);
            const qty = toNumber(row.querySelector('.quantity').value);
            const disc = toNumber(row.querySelector('.discount').value);
            const lineTotal = (price * qty) - disc;
            row.querySelector('.total-display').innerText = formatFCFA(lineTotal);
            st += (price * qty);
            tr += disc;
        });
        montantTotal = st - tr;
        document.getElementById('sousTotal').innerText = formatFCFA(st);
        document.getElementById('totalRemise').innerText = formatFCFA(tr);
        document.getElementById('totalFinal').innerText = formatFCFA(montantTotal);
    }

    function ouvrirModalPaiement() {
        if(document.getElementById('entrepot_id').value === "") {
            alert("Erreur: Vous devez choisir un entrepôt avant d'encaisser.");
            document.getElementById('entrepot_id').style.borderColor = "red";
            document.getElementById('entrepot_id').focus();
            return;
        }
        if(montantTotal <= 0) return alert("Veuillez ajouter au moins un produit.");

        document.getElementById('montantAPayer').innerText = formatFCFA(montantTotal);
        document.getElementById('paiementsContainer').innerHTML = "";
        ajouterPaiement();
        $('#paiementModal').modal('show');
    }

    function ajouterPaiement() {
        const container = document.getElementById('paiementsContainer');
        const id = paiementIndex++;
        const html = `
            <div class="row no-gutters mb-2 align-items-center bg-white p-2 rounded border" id="pay-row-${id}">
                <div class="col-6 pr-2">
                    <select class="form-control form-control-sm mode-pay">
                        <option value="especes">Espèces</option>
                        <option value="wave">Wave</option>
                        <option value="orange">Orange Money</option>
                        <option value="carte">Carte Bancaire</option>
                    </select>
                </div>
                <div class="col-5">
                    <input type="number" class="form-control form-control-sm amount-pay" oninput="calcPay()" placeholder="Montant">
                </div>
                <div class="col-1 text-center">
                    <button class="btn text-danger btn-sm" onclick="this.closest('.row').remove();calcPay();"><i class="fas fa-times"></i></button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    }

    function calcPay() {
        let paid = 0;
        document.querySelectorAll('.amount-pay').forEach(input => paid += toNumber(input.value));
        const reste = montantTotal - paid;
        document.getElementById('totalPaye').innerText = formatFCFA(paid);
        const resteEl = document.getElementById('resteAPayer');
        if(reste <= 0) {
            resteEl.innerText = (reste < 0) ? "À rendre: " + formatFCFA(Math.abs(reste)) : "Soldé";
            resteEl.className = "text-success font-weight-bold";
        } else {
            resteEl.innerText = formatFCFA(reste);
            resteEl.className = "text-danger font-weight-bold";
        }
    }

    function confirmerPaiement() {
        // Envoi au serveur ici via fetch ou $.post
        alert("Succès: Vente enregistrée et stock mis à jour !");
        location.reload();
    }

    document.addEventListener('DOMContentLoaded', () => {
        ajouterLigneProduit();
        // Reset border color on select change
        document.getElementById('entrepot_id').addEventListener('change', function() {
            this.style.borderColor = "#36b9cc";
        });
    });
</script>
@endpush
@endsection
