# Sprint de stabilisation - Audit et backlog

## Résumé exécutif

Le projet contient un noyau métier solide pour les factures, paiements, inventaires, wallets, rapports et abonnements.
En revanche, plusieurs modules visibles dans l’UI sont encore des placeholders ou des écrans de préparation. Le POS n’était pas branché sur un vrai workflow, et deux routes utilisaient encore du debug bloquant.

## Changements réalisés

- suppression du `dd('ok')` dans `SaleController::store()`;
- suppression du `dd("okey")` dans `ReturnProductController::index()`;
- remplacement des écrans sales / retours par des pages de préparation honnêtes;
- ajout de badges explicites `En préparation` et `Bientôt` dans la sidebar;
- ajout d’une synthèse d’audit du sprint.

## Modules audités

| Module | Route existe ? | Contrôleur réel ? | Vue Blade réelle ? | Permission existe ? | Données en base ? | Statut | Action recommandée |
| --- | --- | --- | --- | --- | --- | --- | --- |
| POS / vente rapide | Oui (`/sales`) | Partiel | Partiel | Non dédiée | Non | En préparation | Garder une page de préparation et lancer le vrai sprint POS |
| Retours clients | Via facture | Partiel | Partiel | Oui | Oui | Partiel | Garder l’interface depuis la facture, puis créer un module autonome |
| Retours fournisseurs | Non claire | Non | Non | Non dédiée | Partiel | Absent / partiel | Ne pas l’exposer comme terminé |
| Devis / Proforma | Placeholder | Non | Placeholder | Oui | Non | Absent | Rester en badge `Bientôt` |
| Commandes clients | Placeholder | Non | Placeholder | Oui | Non | Absent | Rester en badge `Bientôt` |
| Bons de livraison | Placeholder | Non | Placeholder | Oui | Non | Absent | Rester en badge `Bientôt` |
| Commandes fournisseurs | Placeholder | Non | Placeholder | Oui | Non | Absent | Rester en badge `Bientôt` |
| Bons de réception | Placeholder | Non | Placeholder | Oui | Non | Absent | Rester en badge `Bientôt` |
| Lots / batches | Oui indirectement | Partiel | Partiel | Oui | Oui | Partiel | Continuer à lister depuis stock / entrepôts |
| Mouvements de stock | Oui indirectement | Partiel | Partiel | Oui | Oui | Partiel | Ajouter un listing global plus tard |
| Transferts de stock | Oui indirectement | Partiel | Partiel | Oui | Oui | Partiel | Ajouter un module autonome plus tard |
| Taxes / TVA | Oui | Oui | Oui | Oui | Oui | Fonctionnel | Conserver |
| Numérotation documents | Oui | Oui | Oui | Oui | Oui | Fonctionnel | Conserver et tester |
| Paiements & wallets | Oui | Oui | Oui | Oui | Oui | Fonctionnel | Conserver et durcir |
| Inventaires physiques | Oui | Oui | Oui | Oui | Oui | Fonctionnel | Conserver et durcir |
| Abonnements / plans / limites | Oui | Oui | Oui | Oui | Oui | Partiel | Limites visibles mais enforcement à compléter |
| Rapports | Oui | Oui | Oui | Oui | Oui | Partiel | Tenant-safe et pagination à surveiller |

## Debug supprimé

- `SaleController::store()`
- `ReturnProductController::index()`

## Sidebar

### Avant

- les modules `quotes`, `sale-orders`, `deliveries`, `purchase-orders`, `receipts`, `batches`, `movements`, `transfers` étaient affichés comme des entrées ERP classiques;
- aucun badge n’indiquait clairement qu’ils étaient en préparation ou partiels;
- le POS n’était pas distingué comme module non finalisé.

### Après

- `POS / Vente rapide` est affiché comme `En préparation`;
- les modules non livrés sont marqués `Bientôt`;
- les modules partiels sont marqués `Partiel`;
- les liens morts sont évités;
- la hiérarchie reste lisible.

## Permissions vérifiées

- `read_quotes`
- `create_quotes`
- `read_sale_orders`
- `read_deliveries`
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

- supprimer le debug bloquant;
- stabiliser le POS minimal;
- clarifier sidebar et placeholders;
- garder paiements / wallets cohérents;
- sécuriser les parcours tenant sensibles;
- fiabiliser l’inventaire.

### P1 - Modules ERP essentiels

- Devis / Proforma réel;
- Commandes clients;
- Bons de livraison;
- Commandes fournisseurs;
- Bons de réception;
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

Priorité recommandée: **A. POS / Vente rapide**

Raison:

- c’est le plus visible commercialement;
- le noyau stock / facturation est déjà en place;
- le sprint de stabilisation a clarifié ce qui est prêt et ce qui ne l’est pas;
- le vrai levier de valeur maintenant est de rendre une vente rapide utilisable.
