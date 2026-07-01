<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journal Comptable - Magasin ABC</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: linear-gradient(to bottom, #f5f7fa 0%, #ffffff 100%);
            font-size: 14px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #2c3e50;
        }
        .header-section {
            background: #ffffff;
            color: #2c3e50;
            padding: 40px 0;
            margin-bottom: 40px;
            border-bottom: 1px solid #e8ecef;
        }
        .header-section h1 {
            font-weight: 300;
            font-size: 2rem;
            color: #34495e;
        }
        .header-section h5 {
            font-weight: 300;
            color: #7f8c8d;
        }
        .summary-card {
            background: #ffffff;
            border: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }
        .summary-card .card-body {
            padding: 24px;
        }
        .summary-card h6 {
            font-weight: 400;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }
        .summary-card h3 {
            font-weight: 300;
            font-size: 1.8rem;
            margin: 0;
        }
        .table-wrapper {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            overflow: hidden;
            border: 1px solid #e8ecef;
        }
        .table {
            margin-bottom: 0;
        }
        .table thead {
            background-color: #f8f9fa;
            color: #2c3e50;
            border-bottom: 2px solid #e8ecef;
        }
        .table thead th {
            font-weight: 500;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 16px;
            border: none;
        }
        .table td {
            vertical-align: middle;
            padding: 14px 16px;
            border-top: 1px solid #f1f3f5;
            color: #495057;
        }
        .table tbody tr {
            transition: background-color 0.2s ease;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .badge {
            font-weight: 400;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
        }
        .badge-info {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        .badge-warning {
            background-color: #fff3e0;
            color: #f57c00;
        }
        .badge-danger {
            background-color: #ffebee;
            color: #c62828;
        }
        .total-row {
            background-color: #f8f9fa;
            font-weight: 500;
            border-top: 2px solid #dee2e6;
        }
        .total-row td {
            padding: 18px 16px;
            font-size: 0.95rem;
        }
        .filter-section {
            background: #ffffff;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border: 1px solid #e8ecef;
        }
        .filter-section label {
            font-weight: 500;
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 8px;
        }
        .form-control {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px 14px;
            transition: all 0.2s ease;
            background-color: #ffffff;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }
        .form-control:focus {
            border-color: #95a5a6;
            box-shadow: 0 0 0 0.15rem rgba(149, 165, 166, 0.15);
            background-color: #ffffff;
        }
        select.form-control {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236c757d' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 40px;
        }
        .btn {
            border-radius: 8px;
            padding: 10px 24px;
            font-weight: 500;
            transition: all 0.2s ease;
            border: none;
        }
        .btn-primary {
            background-color: #5d6d7e;
            color: white;
        }
        .btn-primary:hover {
            background-color: #4a5a6a;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(93, 109, 126, 0.3);
        }
        .btn-success {
            background-color: #27ae60;
            color: white;
        }
        .btn-success:hover {
            background-color: #229954;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #7f8c8d;
            transform: translateY(-1px);
        }
        .text-danger {
            color: #e74c3c !important;
        }
        .text-success {
            color: #27ae60 !important;
        }
        .text-info {
            color: #3498db !important;
        }
        .text-primary {
            color: #5d6d7e !important;
        }
    </style>
</head>
<body>
    <div class="page-hero page-hero--accent">
        <div class="container">
            <div class="page-hero__eyebrow mb-2">Rapports</div>
            <h1 class="page-hero__title mb-2"><i class="fas fa-book mr-2"></i> Journal Comptable</h1>
            <p class="page-hero__subtitle">Vue d’ensemble des écritures et opérations comptables.</p>
        </div>
    </div>

    <div class="container">
        <!-- Résumé financier -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="card-body">
                        <h6 class="text-muted">Total Débits</h6>
                        <h3 class="text-danger" id="totalDebit">125 500 €</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="card-body">
                        <h6 class="text-muted">Total Crédits</h6>
                        <h3 class="text-success" id="totalCredit">125 500 €</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="card-body">
                        <h6 class="text-muted">Solde</h6>
                        <h3 class="text-info" id="solde">0 €</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="card-body">
                        <h6 class="text-muted">Nb Opérations</h6>
                        <h3 class="text-primary" id="nbOps">15</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="panel-card p-4 mb-4">
            <div class="row">
                <div class="col-md-3">
                    <label>Date début</label>
                    <input type="date" class="form-control" id="dateDebut" value="2024-10-01">
                </div>
                <div class="col-md-3">
                    <label>Date fin</label>
                    <input type="date" class="form-control" id="dateFin" value="2024-11-13">
                </div>
                <div class="col-md-3">
                    <label>Type</label>
                    <select class="form-control" id="typeFilter">
                        <option value="">Tous</option>
                        <option value="Vente">Ventes</option>
                        <option value="Achat">Achats</option>
                        <option value="Charge">Charges</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>&nbsp;</label>
                    <button class="btn btn-primary btn-block" onclick="filtrer()">
                        <i class="fas fa-filter"></i> Filtrer
                    </button>
                </div>
            </div>
        </div>

        <!-- Tableau du journal -->
        <div class="table-card">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>N° Pièce</th>
                        <th>Libellé</th>
                        <th>Compte</th>
                        <th class="text-center">Type</th>
                        <th class="text-right">Débit</th>
                        <th class="text-right">Crédit</th>
                    </tr>
                </thead>
                <tbody id="journalBody">
                    <tr>
                        <td>01/10/2024</td>
                        <td>V-001</td>
                        <td>Vente marchandises Client Dubois</td>
                        <td>411000</td>
                        <td class="text-center"><span class="badge badge-info">Vente</span></td>
                        <td class="text-right">1 200,00 €</td>
                        <td class="text-right">-</td>
                    </tr>
                    <tr>
                        <td>01/10/2024</td>
                        <td>V-001</td>
                        <td>Vente marchandises Client Dubois</td>
                        <td>707000</td>
                        <td class="text-center"><span class="badge badge-info">Vente</span></td>
                        <td class="text-right">-</td>
                        <td class="text-right">1 200,00 €</td>
                    </tr>
                    <tr>
                        <td>03/10/2024</td>
                        <td>A-105</td>
                        <td>Achat stock Fournisseur Martin</td>
                        <td>607000</td>
                        <td class="text-center"><span class="badge badge-warning">Achat</span></td>
                        <td class="text-right">3 500,00 €</td>
                        <td class="text-right">-</td>
                    </tr>
                    <tr>
                        <td>03/10/2024</td>
                        <td>A-105</td>
                        <td>Achat stock Fournisseur Martin</td>
                        <td>401000</td>
                        <td class="text-center"><span class="badge badge-warning">Achat</span></td>
                        <td class="text-right">-</td>
                        <td class="text-right">3 500,00 €</td>
                    </tr>
                    <tr>
                        <td>05/10/2024</td>
                        <td>V-002</td>
                        <td>Vente comptoir espèces</td>
                        <td>530000</td>
                        <td class="text-center"><span class="badge badge-info">Vente</span></td>
                        <td class="text-right">850,00 €</td>
                        <td class="text-right">-</td>
                    </tr>
                    <tr>
                        <td>05/10/2024</td>
                        <td>V-002</td>
                        <td>Vente comptoir espèces</td>
                        <td>707000</td>
                        <td class="text-center"><span class="badge badge-info">Vente</span></td>
                        <td class="text-right">-</td>
                        <td class="text-right">850,00 €</td>
                    </tr>
                    <tr>
                        <td>08/10/2024</td>
                        <td>C-045</td>
                        <td>Paiement loyer octobre</td>
                        <td>613000</td>
                        <td class="text-center"><span class="badge badge-danger">Charge</span></td>
                        <td class="text-right">2 800,00 €</td>
                        <td class="text-right">-</td>
                    </tr>
                    <tr>
                        <td>08/10/2024</td>
                        <td>C-045</td>
                        <td>Paiement loyer octobre</td>
                        <td>512000</td>
                        <td class="text-center"><span class="badge badge-danger">Charge</span></td>
                        <td class="text-right">-</td>
                        <td class="text-right">2 800,00 €</td>
                    </tr>
                    <tr>
                        <td>12/10/2024</td>
                        <td>V-003</td>
                        <td>Vente Client Entreprise Leclerc</td>
                        <td>411000</td>
                        <td class="text-center"><span class="badge badge-info">Vente</span></td>
                        <td class="text-right">5 600,00 €</td>
                        <td class="text-right">-</td>
                    </tr>
                    <tr>
                        <td>12/10/2024</td>
                        <td>V-003</td>
                        <td>Vente Client Entreprise Leclerc</td>
                        <td>707000</td>
                        <td class="text-center"><span class="badge badge-info">Vente</span></td>
                        <td class="text-right">-</td>
                        <td class="text-right">5 600,00 €</td>
                    </tr>
                    <tr>
                        <td>15/10/2024</td>
                        <td>A-106</td>
                        <td>Achat fournitures diverses</td>
                        <td>606000</td>
                        <td class="text-center"><span class="badge badge-warning">Achat</span></td>
                        <td class="text-right">450,00 €</td>
                        <td class="text-right">-</td>
                    </tr>
                    <tr>
                        <td>15/10/2024</td>
                        <td>A-106</td>
                        <td>Achat fournitures diverses</td>
                        <td>401000</td>
                        <td class="text-center"><span class="badge badge-warning">Achat</span></td>
                        <td class="text-right">-</td>
                        <td class="text-right">450,00 €</td>
                    </tr>
                    <tr>
                        <td>20/10/2024</td>
                        <td>C-046</td>
                        <td>Salaires octobre</td>
                        <td>641000</td>
                        <td class="text-center"><span class="badge badge-danger">Charge</span></td>
                        <td class="text-right">8 500,00 €</td>
                        <td class="text-right">-</td>
                    </tr>
                    <tr>
                        <td>20/10/2024</td>
                        <td>C-046</td>
                        <td>Salaires octobre</td>
                        <td>512000</td>
                        <td class="text-center"><span class="badge badge-danger">Charge</span></td>
                        <td class="text-right">-</td>
                        <td class="text-right">8 500,00 €</td>
                    </tr>
                    <tr>
                        <td>22/10/2024</td>
                        <td>V-004</td>
                        <td>Vente marchandises Client Petit</td>
                        <td>411000</td>
                        <td class="text-center"><span class="badge badge-info">Vente</span></td>
                        <td class="text-right">2 350,00 €</td>
                        <td class="text-right">-</td>
                    </tr>
                    <tr>
                        <td>22/10/2024</td>
                        <td>V-004</td>
                        <td>Vente marchandises Client Petit</td>
                        <td>707000</td>
                        <td class="text-center"><span class="badge badge-info">Vente</span></td>
                        <td class="text-right">-</td>
                        <td class="text-right">2 350,00 €</td>
                    </tr>
                    <tr>
                        <td>25/10/2024</td>
                        <td>A-107</td>
                        <td>Achat stock Fournisseur Dupont</td>
                        <td>607000</td>
                        <td class="text-center"><span class="badge badge-warning">Achat</span></td>
                        <td class="text-right">12 500,00 €</td>
                        <td class="text-right">-</td>
                    </tr>
                    <tr>
                        <td>25/10/2024</td>
                        <td>A-107</td>
                        <td>Achat stock Fournisseur Dupont</td>
                        <td>401000</td>
                        <td class="text-center"><span class="badge badge-warning">Achat</span></td>
                        <td class="text-right">-</td>
                        <td class="text-right">12 500,00 €</td>
                    </tr>
                    <tr>
                        <td>28/10/2024</td>
                        <td>V-005</td>
                        <td>Vente en ligne PayPal</td>
                        <td>512000</td>
                        <td class="text-center"><span class="badge badge-info">Vente</span></td>
                        <td class="text-right">1 750,00 €</td>
                        <td class="text-right">-</td>
                    </tr>
                    <tr>
                        <td>28/10/2024</td>
                        <td>V-005</td>
                        <td>Vente en ligne PayPal</td>
                        <td>707000</td>
                        <td class="text-center"><span class="badge badge-info">Vente</span></td>
                        <td class="text-right">-</td>
                        <td class="text-right">1 750,00 €</td>
                    </tr>
                    <tr>
                        <td>30/10/2024</td>
                        <td>C-047</td>
                        <td>Facture électricité</td>
                        <td>606100</td>
                        <td class="text-center"><span class="badge badge-danger">Charge</span></td>
                        <td class="text-right">320,00 €</td>
                        <td class="text-right">-</td>
                    </tr>
                    <tr>
                        <td>30/10/2024</td>
                        <td>C-047</td>
                        <td>Facture électricité</td>
                        <td>401000</td>
                        <td class="text-center"><span class="badge badge-danger">Charge</span></td>
                        <td class="text-right">-</td>
                        <td class="text-right">320,00 €</td>
                    </tr>
                    <tr>
                        <td>02/11/2024</td>
                        <td>V-006</td>
                        <td>Vente Client Bernard SARL</td>
                        <td>411000</td>
                        <td class="text-center"><span class="badge badge-info">Vente</span></td>
                        <td class="text-right">8 900,00 €</td>
                        <td class="text-right">-</td>
                    </tr>
                    <tr>
                        <td>02/11/2024</td>
                        <td>V-006</td>
                        <td>Vente Client Bernard SARL</td>
                        <td>707000</td>
                        <td class="text-center"><span class="badge badge-info">Vente</span></td>
                        <td class="text-right">-</td>
                        <td class="text-right">8 900,00 €</td>
                    </tr>
                    <tr>
                        <td>05/11/2024</td>
                        <td>A-108</td>
                        <td>Achat emballages</td>
                        <td>606400</td>
                        <td class="text-center"><span class="badge badge-warning">Achat</span></td>
                        <td class="text-right">680,00 €</td>
                        <td class="text-right">-</td>
                    </tr>
                    <tr>
                        <td>05/11/2024</td>
                        <td>A-108</td>
                        <td>Achat emballages</td>
                        <td>401000</td>
                        <td class="text-center"><span class="badge badge-warning">Achat</span></td>
                        <td class="text-right">-</td>
                        <td class="text-right">680,00 €</td>
                    </tr>
                    <tr>
                        <td>08/11/2024</td>
                        <td>V-007</td>
                        <td>Vente comptoir CB</td>
                        <td>512000</td>
                        <td class="text-center"><span class="badge badge-info">Vente</span></td>
                        <td class="text-right">3 200,00 €</td>
                        <td class="text-right">-</td>
                    </tr>
                    <tr>
                        <td>08/11/2024</td>
                        <td>V-007</td>
                        <td>Vente comptoir CB</td>
                        <td>707000</td>
                        <td class="text-center"><span class="badge badge-info">Vente</span></td>
                        <td class="text-right">-</td>
                        <td class="text-right">3 200,00 €</td>
                    </tr>
                    <tr>
                        <td>10/11/2024</td>
                        <td>A-109</td>
                        <td>Achat stock Fournisseur Lambert</td>
                        <td>607000</td>
                        <td class="text-center"><span class="badge badge-warning">Achat</span></td>
                        <td class="text-right">6 750,00 €</td>
                        <td class="text-right">-</td>
                    </tr>
                    <tr>
                        <td>10/11/2024</td>
                        <td>A-109</td>
                        <td>Achat stock Fournisseur Lambert</td>
                        <td>401000</td>
                        <td class="text-center"><span class="badge badge-warning">Achat</span></td>
                        <td class="text-right">-</td>
                        <td class="text-right">6 750,00 €</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="5" class="text-right"><strong>TOTAUX</strong></td>
                        <td class="text-right"><strong>62 750,00 €</strong></td>
                        <td class="text-right"><strong>62 750,00 €</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-4 mb-5">
            <button class="btn btn-success" onclick="exporterPDF()">
                <i class="fas fa-file-pdf"></i> Exporter PDF
            </button>
            <button class="btn btn-primary" onclick="exporterExcel()">
                <i class="fas fa-file-excel"></i> Exporter Excel
            </button>
            <button class="btn btn-secondary" onclick="imprimer()">
                <i class="fas fa-print"></i> Imprimer
            </button>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function filtrer() {
            alert('Fonction de filtrage activée (à implémenter selon vos besoins)');
        }

        function exporterPDF() {
            alert('Export PDF en cours... (nécessite une bibliothèque comme jsPDF)');
        }

        function exporterExcel() {
            alert('Export Excel en cours... (nécessite une bibliothèque comme SheetJS)');
        }

        function imprimer() {
            window.print();
        }

        // Animation au chargement
        $(document).ready(function() {
            $('.metric-card').hide().fadeIn(1000);
            $('.table-card').hide().slideDown(800);
        });
    </script>
</body>
</html>
