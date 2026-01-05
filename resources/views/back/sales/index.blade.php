@extends('back.layouts.admin')

@section("content")
<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Vente Directe</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#venteModal">
            <i class="fas fa-plus mr-2"></i>Nouvelle Vente
        </button>
    </div>

    <!-- TABLE -->
    <div class="card shadow">
        <div class="card-header bg-white">
            <h5 class="mb-0">Historique des ventes</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Total</th>
                        <th>Paiement</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>V-001</td>
                        <td>05/01/2026</td>
                        <td>Client comptant</td>
                        <td><strong>450 000 FCFA</strong></td>
                        <td><span class="badge badge-success">Espèces</span></td>
                        <td>
                            <button class="btn btn-sm btn-primary"><i class="fas fa-print"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL VENTE -->
<div class="modal fade" id="venteModal" tabindex="-1">
    <div class="modal-dialog modal-fullscreen-lg-down" style="max-width: 1400px;">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-shopping-cart mr-2"></i>Nouvelle Vente</h5>
                <button class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <form id="venteForm">

                    <!-- INFORMATIONS CLIENT -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-user mr-2"></i>Informations Client</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nom complet</label>
                                    <input type="text" class="form-control" id="clientNom" placeholder="Ex: Jean Dupont">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" id="clientTelephone" placeholder="Ex: +221 77 123 45 67">
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Adresse</label>
                                    <input type="text" class="form-control" id="clientAdresse" placeholder="Ex: Parcelles Assainies, Dakar">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Mode de paiement *</label>
                                    <select class="form-control" id="modePaiement" required>
                                        <option value="especes">Espèces</option>
                                        <option value="carte">Carte bancaire</option>
                                        <option value="virement">Virement</option>
                                        <option value="mobile">Mobile Money</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PRODUITS -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-box mr-2"></i>Produits</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle" id="venteLinesTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 250px;">Produit</th>
                                            <th style="width: 130px;">Prix unit.</th>
                                            <th style="width: 100px;">Qté</th>
                                            <th style="width: 100px;">Remise</th>
                                            <th style="width: 130px;">Total</th>
                                            <th style="width: 100px;" class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <select name="items[0][product_id]" class="form-control productSelect" required>
                                                    <option value="">Choisir...</option>
                                                    <option value="1" data-nom="Ordinateur HP" data-prix="450000">Ordinateur HP</option>
                                                    <option value="2" data-nom="Samsung S23" data-prix="850000">Samsung S23</option>
                                                    <option value="3" data-nom="MacBook Air M2" data-prix="950000">MacBook Air M2</option>
                                                    <option value="4" data-nom="iPhone 15 Pro" data-prix="1200000">iPhone 15 Pro</option>
                                                    <option value="5" data-nom="Dell XPS 13" data-prix="750000">Dell XPS 13</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="items[0][unit_price]" class="form-control unit_price" value="0" min="0" required>
                                            </td>
                                            <td>
                                                <input type="number" name="items[0][quantity]" class="form-control quantity" value="1" min="1" required>
                                            </td>
                                            <td>
                                                <input type="number" name="items[0][discount]" class="form-control discount" value="0" min="0">
                                            </td>
                                            <td class="total_line text-end fw-bold">0</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-success me-1 addLineBtn" title="Ajouter">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger removeLineBtn" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- TOTAUX -->
                    <div class="card border-primary">
                        <div class="card-body py-3">
                            <div class="row">
                                <div class="col-md-8 text-end">
                                    <div class="mb-1"><strong>Sous-total :</strong></div>
                                    <div class="mb-1"><strong>Total Remise :</strong></div>
                                    <div class="mt-2"><h5 class="mb-0"><strong>Total à payer :</strong></h5></div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="mb-1"><span id="sousTotal">0 FCFA</span></div>
                                    <div class="mb-1"><span id="totalRemise" class="text-danger">0 FCFA</span></div>
                                    <div class="mt-2"><h5 class="mb-0 text-primary"><strong id="totalFinal">0 FCFA</strong></h5></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button class="btn btn-primary" onclick="validerVente()">
                    <i class="fas fa-check me-1"></i>Valider la vente
                </button>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
let lineIndex = 1;

function toNumber(v) {
    const n = Number(v);
    return isFinite(n) ? n : 0;
}

function formatFCFA(montant) {
    return new Intl.NumberFormat('fr-FR').format(montant) + ' FCFA';
}

function updateLineTotal(row) {
    const qty = toNumber(row.querySelector('.quantity').value);
    const price = toNumber(row.querySelector('.unit_price').value);
    const discount = toNumber(row.querySelector('.discount').value);
    const total = Math.max(0, qty * price - discount);
    row.querySelector('.total_line').textContent = formatFCFA(total);
    updateInvoiceTotals();
}

function updateInvoiceTotals() {
    let sousTotal = 0;
    let totalRemise = 0;

    document.querySelectorAll('#venteLinesTable tbody tr').forEach(row => {
        const qty = toNumber(row.querySelector('.quantity').value);
        const price = toNumber(row.querySelector('.unit_price').value);
        const discount = toNumber(row.querySelector('.discount').value);

        sousTotal += qty * price;
        totalRemise += discount;
    });

    const totalFinal = sousTotal - totalRemise;

    document.getElementById('sousTotal').textContent = formatFCFA(sousTotal);
    document.getElementById('totalRemise').textContent = formatFCFA(totalRemise);
    document.getElementById('totalFinal').textContent = formatFCFA(totalFinal);
}

function reindexRows() {
    document.querySelectorAll('#venteLinesTable tbody tr').forEach((row, i) => {
        row.querySelectorAll('select, input').forEach(input => {
            const name = input.getAttribute('name');
            if (name) input.setAttribute('name', name.replace(/\[\d+\]/, `[${i}]`));
        });
    });
    lineIndex = document.querySelectorAll('#venteLinesTable tbody tr').length;
}

// Ajouter une ligne
document.querySelector('#venteLinesTable tbody').addEventListener('click', function(e) {
    if (e.target.closest('#addLineBtn') || e.target.closest('.addLineBtn')) {
        const tbody = document.querySelector('#venteLinesTable tbody');

        tbody.insertAdjacentHTML('beforeend', `
            <tr>
                <td>
                    <select name="items[${lineIndex}][product_id]" class="form-control productSelect" required>
                        <option value="">Choisir...</option>
                        <option value="1" data-nom="Ordinateur HP" data-prix="450000">Ordinateur HP</option>
                        <option value="2" data-nom="Samsung S23" data-prix="850000">Samsung S23</option>
                        <option value="3" data-nom="MacBook Air M2" data-prix="950000">MacBook Air M2</option>
                        <option value="4" data-nom="iPhone 15 Pro" data-prix="1200000">iPhone 15 Pro</option>
                        <option value="5" data-nom="Dell XPS 13" data-prix="750000">Dell XPS 13</option>
                    </select>
                </td>
                <td>
                    <input type="number" name="items[${lineIndex}][unit_price]" class="form-control unit_price" value="0" min="0" required>
                </td>
                <td>
                    <input type="number" name="items[${lineIndex}][quantity]" class="form-control quantity" value="1" min="1" required>
                </td>
                <td>
                    <input type="number" name="items[${lineIndex}][discount]" class="form-control discount" value="0" min="0">
                </td>
                <td class="total_line text-end fw-bold">0 FCFA</td>
                <td class="text-center">
                    <button type="button" class="btn btn-success me-1 addLineBtn" title="Ajouter">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button type="button" class="btn btn-danger removeLineBtn" title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `);

        const newRow = tbody.querySelector('tr:last-child');
        updateLineTotal(newRow);
        reindexRows();
    }
});

// Gestion du changement de produit
document.querySelector('#venteLinesTable tbody').addEventListener('change', function(e) {
    const row = e.target.closest('tr');
    if (e.target.classList.contains('productSelect')) {
        const selectedOption = e.target.selectedOptions[0];
        const price = selectedOption?.dataset.prix || 0;
        row.querySelector('.unit_price').value = price;
        updateLineTotal(row);
    }
});

// Supprimer une ligne
document.querySelector('#venteLinesTable tbody').addEventListener('click', e => {
    if (e.target.closest('.removeLineBtn')) {
        const tbody = document.querySelector('#venteLinesTable tbody');
        if (tbody.querySelectorAll('tr').length > 1) {
            e.target.closest('tr').remove();
            reindexRows();
            updateInvoiceTotals();
        } else {
            alert('Vous devez avoir au moins une ligne de produit');
        }
    }
});

// Calcul automatique lors de la saisie
document.querySelector('#venteLinesTable tbody').addEventListener('input', function(e) {
    if (e.target.classList.contains('quantity') ||
        e.target.classList.contains('unit_price') ||
        e.target.classList.contains('discount')) {
        updateLineTotal(e.target.closest('tr'));
    }
});

// Initialiser les totaux
document.querySelectorAll('#venteLinesTable tbody tr').forEach(row => {
    updateLineTotal(row);
});

function validerVente() {
    const rows = document.querySelectorAll('#venteLinesTable tbody tr');
    if (rows.length === 0) {
        alert('Veuillez ajouter au moins un produit');
        return;
    }

    // Vérifier que tous les produits sont sélectionnés
    let valid = true;
    rows.forEach(row => {
        const productSelect = row.querySelector('.productSelect');
        if (!productSelect.value) {
            valid = false;
        }
    });

    if (!valid) {
        alert('Veuillez sélectionner un produit pour chaque ligne');
        return;
    }

    // Récupérer les données client (optionnelles)
    const clientNom = document.getElementById('clientNom').value.trim();
    const clientTelephone = document.getElementById('clientTelephone').value.trim();
    const clientAdresse = document.getElementById('clientAdresse').value.trim();
    const modePaiement = document.getElementById('modePaiement').value;

    console.log('Client:', { clientNom, clientTelephone, clientAdresse, modePaiement });

    alert('Vente validée ! Prête à être enregistrée en backend');
    $('#venteModal').modal('hide');
}
</script>
@endpush

<style>
.table td, .table th {
    vertical-align: middle;
    padding: 0.75rem;
}

.card {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

#venteLinesTable {
    font-size: 1rem;
}

#venteLinesTable input,
#venteLinesTable select {
    font-size: 1rem;
    border-radius: 4px;
    padding: 0.5rem 0.75rem;
}

#venteLinesTable input[type="number"] {
    height: calc(1.5em + 1rem + 2px);
}

.form-control {
    font-size: 1rem;
}

.form-control-sm {
    padding: 0.35rem 0.5rem;
    font-size: 0.875rem;
}

.btn {
    padding: 0.45rem 0.75rem;
    font-size: 1rem;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
}

.text-end {
    text-align: right;
}

.fw-bold {
    font-weight: 600;
}

.align-middle {
    vertical-align: middle !important;
}

.me-1 {
    margin-right: 0.25rem;
}

.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
    font-size: 1rem;
}

.modal-header {
    border-bottom: 2px solid #e9ecef;
}

.modal-footer {
    border-top: 2px solid #e9ecef;
}

.table-light {
    background-color: #f8f9fa;
    font-weight: 500;
}

.badge {
    padding: 0.35em 0.65em;
    font-size: 0.9rem;
}

.total_line {
    font-size: 1.05rem;
}

.card-header h6 {
    font-weight: 600;
    color: #495057;
}

/* Largeur du modal élargie */
@media (min-width: 992px) {
    .modal-fullscreen-lg-down {
        max-width: 1400px !important;
    }
}
</style>
@endsection
