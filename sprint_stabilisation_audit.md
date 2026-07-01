# Sprint de stabilisation - Audit et backlog

## Résumé exécutif

Le projet contient maintenant un noyau métier cohérent pour les devis, commandes, livraisons, réceptions, factures, paiements, wallets, taxes, POS, retours et inventaires.
Les derniers écarts constatés concernent surtout le durcissement sécurité/tenant, l’enforcement des limites d’abonnement et la finition de quelques écrans de stock.

## Changements réalisés

- suppression du `dd('ok')` dans `SaleController::store()`;
- suppression du `dd("okey")` dans `ReturnProductController::index()`;
- livraison du workflow POS avec reçu imprimable;
- livraison des bons de retour client et fournisseur avec actions selon le statut;
- livraison des avoirs client et fournisseur avec application sur facture et remboursement wallet;
- ajout d’un tableau de bord retours / avoirs avec synthèse des documents récents et des totaux;
- livraison des pages de taxe, devis, commandes, livraisons, achats et réceptions comme vrais modules ERP;
- mise à jour du hub `/modules/*` pour refléter les modules réellement livrés;
- ajout d’une synthèse d’audit du sprint.

## Modules audités

| Module | Route existe ? | Contrôleur réel ? | Vue Blade réelle ? | Permission existe ? | Données en base ? | Statut | Action recommandée |
| --- | --- | --- | --- | --- | --- | --- | --- |
| POS / vente rapide | Oui (`/sales`) | Oui | Oui | Oui | Oui | Livré | Garder le durcissement sur reçu et caisse |
| Retours clients | Oui (`/customer-returns`) | Oui | Oui | Oui | Oui | Livré | Continuer à surveiller validation / réintégration |
| Retours fournisseurs | Oui (`/supplier-returns`) | Oui | Oui | Oui | Oui | Livré | Continuer à surveiller validation / sortie |
| Avoirs clients | Oui (`/customer-credit-notes`) | Oui | Oui | Oui | Oui | Livré | Suivre application / remboursement |
| Avoirs fournisseurs | Oui (`/supplier-credit-notes`) | Oui | Oui | Oui | Oui | Livré | Suivre application / remboursement |
| Devis / Proforma | Oui (`/quotes`) | Oui | Oui | Oui | Oui | Livré | Conserver le workflow de conversion |
| Commandes clients | Oui (`/sale-orders`) | Oui | Oui | Oui | Oui | Livré | Conserver le workflow de validation / livraison |
| Bons de livraison | Oui (`/delivery-notes`) | Oui | Oui | Oui | Oui | Livré | Conserver le lien stock / livraison |
| Commandes fournisseurs | Oui (`/purchase-orders`) | Oui | Oui | Oui | Oui | Livré | Conserver le lien vers réceptions / factures |
| Bons de réception | Oui (`/goods-receipts`) | Oui | Oui | Oui | Oui | Livré | Conserver le lien lots / mouvements |
| Lots / batches | Oui indirectement | Oui | Oui | Oui | Oui | Partiel | Améliorer la vue globale et les filtres |
| Mouvements de stock | Oui indirectement | Oui | Oui | Oui | Oui | Partiel | Ajouter un affichage plus exploitable à l’échelle globale |
| Transferts de stock | Oui indirectement | Oui | Oui | Oui | Oui | Partiel | Ajouter un module de gestion plus complet |
| Taxes / TVA | Oui (`/taxes`) | Oui | Oui | Oui | Oui | Livré | Conserver et tester les règles d’isolation |
| Numérotation documents | Oui | Oui | Oui | Oui | Oui | Livré | Conserver et tester |
| Paiements & wallets | Oui | Oui | Oui | Oui | Oui | Livré | Conserver et durcir |
| Inventaires physiques | Oui | Oui | Oui | Oui | Oui | Livré | Conserver et durcir |
| Abonnements / plans / limites | Oui | Oui | Oui | Oui | Oui | Partiel | Enforcement des limites à compléter |
| Rapports | Oui | Oui | Oui | Oui | Oui | Partiel | Tenant-safe et pagination à surveiller |

## Debug supprimé

- `SaleController::store()`
- `ReturnProductController::index()`

## Sidebar

### Avant

- la navigation mélangeait modules livrés et écrans de préparation;
- les retours et le POS n’avaient pas encore de présentation métier cohérente;
- le hub de modules pouvait encore laisser penser que certains parcours n’étaient pas terminés.

### Après

- `Retours clients` et `Retours fournisseurs` sont accessibles via leurs modules dédiés;
- le POS a un écran de vente réel et un reçu imprimable;
- les modules commerciaux et achats reposent sur les vraies routes métier;
- les anciens écrans d’état doivent être considérés comme un hub d’audit, pas comme la source de vérité.

## Permissions vérifiées

- `create_pos_sales`
- `manage_taxes`
- `read_quotes`
- `create_quotes`
- `read_sale_orders`
- `read_deliveries`
- `read_customer_returns`
- `read_supplier_returns`
- `read_sale_orders`
- `read_purchase_orders`
- `read_receipts`
- `manage_client_invoices`
- `manage_supplier_invoices`
- `manage_wallets`
- `manage_inventories`
- `manage_reports`
- `read_document_sequences`

## Observations sur les limites de plan

| Limite | Stockée en base ? | Affichée dans l’UI ? | Appliquée dans le code ? | Endroit où appliquer | Priorité |
| --- | --- | --- | --- | --- | --- |
| `max_users` | Oui | Oui | Non trouvé | Middleware ou création d’utilisateur | Haute |
| `max_storage_mb` | Oui | Oui | Non trouvé | Upload / stockage | Haute |
| Permissions par plan | Oui | Oui | Oui partiellement | Middleware abonnement | Haute |
| Accès modules par plan | Oui | Oui | Oui partiellement | Middleware abonnement / sidebar | Haute |
| Expiration abonnement | Oui | Oui | Oui | Middleware | Haute |
| Suspension tenant | Oui | Oui | Oui partiellement | Middleware + admin tenant | Haute |

## Backlog priorisé

### P0 - Bloquants avant pilote

- supprimer les derniers écarts de validation tenant;
- faire respecter les limites d’abonnement dans le code;
- retirer les éventuels écrans de préparation obsolètes;
- fiabiliser l’inventaire et les modules de stock partiels;
- garder paiements / wallets cohérents.

### P1 - Modules ERP essentiels

- finaliser la navigation d’audit pour les modules déjà livrés;
- Import / export produits;
- enforcement réel des quotas de plan.

### P2 - Améliorations

- reporting avancé;
- code-barres;
- alertes expiration;
- WhatsApp / SMS;
- API;
- premium par plan.

## Recommandation pour le prochain sprint

Priorité recommandée: **A. Sécurité et limites d’abonnement**

Raison:

- le métier principal est désormais livré;
- les risques les plus élevés restants sont l’isolation tenant et l’enforcement des limites;
- ces points touchent plusieurs modules déjà en production;
- corriger cela augmente la stabilité sans remettre en cause le périmètre déjà livré.
