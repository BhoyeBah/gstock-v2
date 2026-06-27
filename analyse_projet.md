# Spécifications techniques détaillées - SaaS Gestion de Stock

Document technique de cadrage pour faire évoluer l'application Laravel actuelle vers un SaaS vendable, sécurisé et maintenable.

Contexte de départ:
- score SaaS readiness: `52/100`;
- maturite metier stock: `70/100`;
- potentiel commercial: `58/100`.

Architecture actuelle a conserver:
- Laravel 10;
- routes web dans `routes/web.php`;
- controllers MVC;
- Form Requests;
- services metier;
- models Eloquent;
- migrations;
- seeders;
- tests feature/unit;
- vues Blade;
- middleware;
- Spatie roles/permissions;
- logique tenant basee sur `tenant_id` et `HasTenant`.

Priorite de livraison:
1. securite multi-tenant;
2. POS / vente rapide;
3. import/export et stock operable;
4. amelioration du module abonnement existant;
5. reporting commercial;
6. premium.

## 1. Resume technique

### Etat actuel
Le projet a deja un noyau metier riche:
- produits;
- clients/fournisseurs;
- entrepots;
- devis;
- factures;
- paiements;
- receptions;
- livraisons;
- retours;
- inventaire;
- lots;
- FIFO;
- rapports;
- abonnements et plans en base.

### Modules a securiser
- assignation de roles;
- validations `exists`;
- rapports;
- paiements;
- retours;
- super-admin;
- debug / routes incomplètes.

### Modules a ajouter
- POS;
- import / export;
- amelioration du module abonnement existant;
- limites par plan;
- onboarding;
- rapports commerciaux plus clairs;
- notifications.

### Priorites techniques
1. supprimer les risques cross-tenant;
2. rendre les documents metiers tenant-safe;
3. rendre la vente rapide utilisable;
4. durcir le module abonnement existant;
5. renforcer reporting et performance.

## 2. Architecture actuelle a conserver

### Laravel
Conserver le socle Laravel actuel. Il est coherent avec un SaaS monolithique decoupe en modules.

### Routes
- `routes/web.php` contient la quasi-totalite du metier;
- `routes/auth.php` gere l'auth;
- `routes/api.php` est minimal.

Ce qu'il faut conserver:
- l'organisation par route groups;
- le systeme de middleware par route;
- les routes resource la ou elles sont deja utiles.

Ce qu'il ne faut pas faire:
- une refonte complete vers SPA;
- casser le contrat des routes existantes sans raison;
- multiplier les micro-services.

### Controllers
Conserver:
- `InvoiceController`;
- `PaymentController`;
- `ReportController`;
- `FrontController`;
- `ProductController`;
- `WarehouseController`;
- `UserController`;
- `RoleController`;
- controllers admin et tenant.

Recommandation:
- garder les controllers fins;
- extraire davantage de logique metier dans les services;
- ne pas tout refactoriser d'un coup.

### Form Requests
Conserver l'usage des Form Requests.

Mais il faut:
- tenant-er les `exists`;
- harmoniser les messages;
- eviter les validations globales sur les entites sensibles.

### Services
Les services sont une bonne base:
- `InvoiceService`;
- `ReceiptService`;
- `DeliveryService`;
- `QuoteConversionService`;
- `PaymentCancellationService`;
- `InventoryReconciliationService`.

Ce qu'il faut faire:
- enrichir les services plutot que remettre la logique dans les controllers;
- garder les transactions DB;
- ajouter des services pour POS, import/export, billing.

### Models
Conserver les models Eloquent, surtout ceux qui portent `HasTenant`.

A ne pas refactoriser inutilement:
- `Invoice`;
- `Payment`;
- `Product`;
- `Contact`;
- `Warehouse`;
- `Batch`;
- `Receipt`;
- `Delivery`;
- `CustomerReturn`;
- `SupplierReturn`;
- `Tax`;
- `Expense`.

### Migrations
Conserver les migrations existantes.

Il faudra ajouter de nouvelles migrations pour:
- usage abonnement;
- cash sessions;
- import logs;
- billing invoices;
- usage tracking;
- plan limits.

### Seeders
Conserver:
- `PermissionSeeder`;
- `PlanSeeder`;
- `SuperAdminSeeder`;
- `TestCompanySeeder`.

Mais:
- securiser le bootstrap admin;
- eviter les secrets faibles;
- ajouter des seeders orientés demo/onboarding si besoin.

### Tests
Conserver et etendre:
- tests de workflow;
- tests de securite;
- tests de non-regression.

### Vues Blade
Conserver Blade.

Ne pas basculer le projet vers un frontend radicalement different avant d'avoir un MVP vendable.

### Middleware
Conserver:
- `CheckActiveUser`;
- `CheckSubscriptionAndPermissions`.

Mais:
- durcir leur logique;
- ajouter des checks plus explicites pour les modules SaaS.

### Spatie roles/permissions
Conserver Spatie.

Il faut surtout:
- isoler les roles par tenant;
- eviter le role plateforme assignable;
- associer les permissions par plan;
- garder les checks en middleware.

### Logique tenant
Conserver:
- `tenant_id` partout;
- `HasTenant`;
- tenant resolu a partir de `auth()->user()->tenant_id`.

Mais:
- ne pas supposer que le global scope suffit;
- valider les relations en amont;
- filtrer les requetes brutes.

## 3. Sprint 1 - Securite critique multi-tenant

Objectif:
- verrouiller l'isolation;
- verrouiller le RBAC;
- supprimer les fuites de donnees;
- rendre les documents metiers tenant-safe.

### 3.1 Assignation de roles tenant-safe

| Element | Détail |
| ------- | ------ |
| Objectif | Empêcher l'assignation d'un rôle externe ou plateforme à un utilisateur de tenant |
| Fichiers concernés | `app/Http/Controllers/UserController.php`, `app/Models/Role.php`, `app/Http/Requests/*` si validation extraites |
| Problème actuel | `exists:roles,id` et `exists:roles,name` sont globaux; `Role::findOrFail()` et `Role::where('name', ...)` ne filtrent pas `tenant_id` |
| Solution technique | Valider le rôle avec `Rule::exists('roles','id')->where('tenant_id', $currentTenantId)`; refuser les rôles plateforme; vérifier `tenant_id` avant assignation |
| Exemple de code attendu | `Rule::exists('roles','id')->where(fn($q) => $q->where('tenant_id', auth()->user()->tenant_id))` |
| Tests à écrire | role cross-tenant bloqué, role plateforme non assignable, update role d'un autre tenant refusé |
| Critère d’acceptation | Aucun utilisateur ne peut recevoir un rôle hors de son tenant |

### 3.2 Validation `exists` tenantée

| Element | Détail |
| ------- | ------ |
| Objectif | Empêcher l'association à des ressources externes |
| Fichiers concernés | `StoreInvoiceRequest.php`, `PaymentRequest.php`, `ReturnRequestProduct.php`, `ReceiptController.php`, `DeliveryController.php`, `ProductRequest.php`, `WarehouseRequest.php`, `ContactRequest.php`, `TaxRequest.php` |
| Problème actuel | `exists:contacts,id`, `exists:invoices,id`, `exists:products,id`, `exists:warehouses,id`, etc. sont globaux |
| Solution technique | Ajouter `Rule::exists(...)->where('tenant_id', $tenantId)` ou verifier via relation parent avant persistence |
| Exemple de code attendu | `Rule::exists('contacts', 'id')->where(fn($q) => $q->where('tenant_id', $tenantId))` |
| Tests à écrire | facture avec contact externe rejetée, paiement externe rejeté, retour externe rejeté |
| Critère d’acceptation | Toute ressource liée appartient au tenant courant |

### 3.3 Correction `ReportController`

| Element | Détail |
| ------- | ------ |
| Objectif | Rendre tous les rapports tenant-safe |
| Fichiers concernés | `app/Http/Controllers/ReportController.php`, `app/Http/Controllers/FrontController.php` |
| Problème actuel | Certaines requêtes agrègent sur `InvoiceItem::query()` et `InvoiceItem::where('type', 'in')` sans filtre tenant explicite |
| Solution technique | Filtrer via `whereHas('invoice', fn($q) => $q->where('tenant_id', $tenantId))` ou ajouter un scope tenant sur les models concernés |
| Exemple de code attendu | `InvoiceItem::whereHas('invoice', fn($q) => $q->where('tenant_id', $tenantId))` |
| Tests à écrire | rapport produits tenant-isolé, dashboard tenant-isolé |
| Critère d’acceptation | Un tenant ne voit jamais ni ne calcule les donnees d'un autre tenant |

### 3.4 Sécurisation paiements

| Element | Détail |
| ------- | ------ |
| Objectif | Empêcher paiement hors tenant et incohérence de balance |
| Fichiers concernés | `PaymentController.php`, `PaymentRequest.php`, `InvoiceService.php`, `PaymentCancellationService.php` |
| Problème actuel | `Invoice::findOrFail()` et `Wallet::where('id', ...)` peuvent accepter des refs non isolées si les validations sont globales |
| Solution technique | Verifier la facture et le wallet dans le tenant courant; verrouiller la facture et le wallet en transaction; refuser si tenant mismatch |
| Exemple de code attendu | `Invoice::where('tenant_id', $tenantId)->whereKey($request->invoice_id)->firstOrFail()` |
| Tests à écrire | paiement hors tenant refuse, double paiement concurrent, wallet externe refuse |
| Critère d’acceptation | Aucun paiement ne peut cibler une facture ou un wallet externe |

### 3.5 Sécurisation retours

| Element | Détail |
| ------- | ------ |
| Objectif | Empêcher qu'un retour manipule une ligne de facture externe |
| Fichiers concernés | `ReturnRequestProduct.php`, `InvoiceController.php`, `CustomerReturnController.php`, `SupplierReturnController.php`, `CustomerReturnService.php`, `SupplierReturnService.php` |
| Problème actuel | `invoice_item_id` est validé globalement; le service part du principe que la ligne appartient au tenant |
| Solution technique | Remonter de `invoice_item -> invoice -> tenant_id` avant traitement et refuser si mismatch |
| Exemple de code attendu | `abort_unless($invoiceItem->invoice->tenant_id === $tenantId, 403)` |
| Tests à écrire | retour ligne externe refuse, annulation retour externe refuse |
| Critère d’acceptation | Aucun retour ne peut toucher une ligne de facture externe |

### 3.6 Suppression `dd()`

| Element | Détail |
| ------- | ------ |
| Objectif | Eliminer les blocages de production |
| Fichiers concernés | `SaleController.php`, `InvoiceController.php`, tout le projet via recherche |
| Problème actuel | `dd()` bloque une route ou une page |
| Solution technique | Supprimer le debug, remplacer par logs ou tests |
| Exemple de code attendu | aucun `dd()` en production |
| Tests à écrire | test de route accessible sans debug |
| Critère d’acceptation | aucune page publique/tenant ne s’arrete sur un debug |

### 3.7 Seeder super-admin

| Element | Détail |
| ------- | ------ |
| Objectif | Sécuriser le compte plateforme initial |
| Fichiers concernés | `database/seeders/SuperAdminSeeder.php`, `.env`, `.env.example` |
| Problème actuel | fallback potentiellement faible, secret exposable |
| Solution technique | obliger un secret fort hors production; documenter le bootstrap; interdire un fallback faible en prod |
| Exemple de code attendu | utiliser des variables d’environnement sans mot de passe faible par défaut |
| Tests à écrire | seeder crée un tenant plateforme, mot de passe non faible en env prod |
| Critère d’acceptation | le compte plateforme est securise et documente |

### 3.8 Tests de sécurité

Tests prioritaires:
- role cross-tenant bloqué;
- role plateforme non assignable;
- validation `exists` tenantée;
- paiement hors tenant rejeté;
- retour hors tenant rejeté;
- rapport produits tenant-safe;
- `dd()` absent.

## 4. Sprint 2 - Vente rapide / POS

Objectif:
- rendre la vente simple, rapide et exploitable en boutique;
- permettre une vente en moins de 30 secondes.

### Fonctionnalité 1 - écran vente rapide

| Élément | Détail |
| ------- | ------ |
| Routes nécessaires | `GET /pos`, `POST /pos/sales`, `GET /pos/receipt/{sale}` |
| Controller | `SaleController` ou nouveau `PosController` |
| Service | `SaleService`, `CashSessionService` |
| Models utilisés | `Invoice`, `InvoiceItem`, `Payment`, `Contact`, `Product`, `Batch`, `Wallet`, `InventoryMovement` |
| Validations | produit tenant-safe, quantité positive, paiement coherent |
| Vues Blade | ecran POS, panier, modal paiement, ticket |
| Logique stock | decrementation FIFO via lot |
| Logique paiement | paiement total ou partiel, dette client si reste a payer |
| Tests à écrire | vente rapide cree facture, met à jour stock, enregistre paiement partiel |

### Fonctionnalité 2 - recherche produit

| Élément | Détail |
| ------- | ------ |
| Routes nécessaires | endpoint JSON ou recherche live dans la page POS |
| Controller | `ProductController` ou `PosController` |
| Service | `ProductSearchService` si besoin |
| Models utilisés | `Product`, `Batch`, `Warehouse` |
| Validations | recherche limitée au tenant |
| Vues Blade | input autocomplete, liste produit |
| Logique stock | afficher dispo par entrepot |
| Logique paiement | aucune |
| Tests à écrire | recherche renvoie uniquement les produits du tenant |

### Fonctionnalité 3 - panier

| Élément | Détail |
| ------- | ------ |
| Routes nécessaires | gestion côté front + POST de soumission |
| Controller | `SaleController` / `PosController` |
| Service | `CartService` ou logique session |
| Models utilisés | `Product`, `InvoiceItem` |
| Validations | produit, quantite, remise |
| Vues Blade | tableau panier, totaux |
| Logique stock | calcul de sortie au moment de la validation |
| Logique paiement | total du panier |
| Tests à écrire | ajout/suppression ligne panier |

### Fonctionnalité 4 - quantité

| Élément | Détail |
| ------- | ------ |
| Routes nécessaires | intégrées au panier |
| Controller | `PosController` |
| Service | panier / calculs |
| Models utilisés | `Product`, `Batch` |
| Validations | `min:1`, stock suffisant |
| Vues Blade | champ quantité |
| Logique stock | controle du disponible |
| Logique paiement | impacte le total |
| Tests à écrire | quantite > stock rejetée |

### Fonctionnalité 5 - remise

| Élément | Détail |
| ------- | ------ |
| Routes nécessaires | intégrées au formulaire |
| Controller | `PosController` |
| Service | calcul remise |
| Models utilisés | `InvoiceItem`, `Invoice` |
| Validations | remise non negative, plafond eventuel |
| Vues Blade | champ remise simple |
| Logique stock | aucune |
| Logique paiement | diminue le montant final |
| Tests à écrire | remise appliquee correctement |

### Fonctionnalité 6 - client optionnel

| Élément | Détail |
| ------- | ------ |
| Routes nécessaires | submit POS |
| Controller | `SaleController` |
| Service | vente avec ou sans client |
| Models utilisés | `Contact`, `Invoice` |
| Validations | client optionnel selon le mode |
| Vues Blade | select client optional |
| Logique stock | aucune |
| Logique paiement | dette client si client sélectionné |
| Tests à écrire | vente sans client possible |

### Fonctionnalité 7 - paiement total ou partiel

| Élément | Détail |
| ------- | ------ |
| Routes nécessaires | POST paiement POS |
| Controller | `PaymentController` ou `PosController` |
| Service | `InvoiceStatusService`, `PaymentService` |
| Models utilisés | `Invoice`, `Payment`, `Wallet` |
| Validations | montant <= total, wallet valide |
| Vues Blade | modal paiement |
| Logique stock | aucune supplémentaire |
| Logique paiement | creation paiement + balance |
| Tests à écrire | paiement partiel, paiement total |

### Fonctionnalité 8 - dette client

| Élément | Détail |
| ------- | ------ |
| Routes nécessaires | page client / facture |
| Controller | `ContactController`, `InvoiceController` |
| Service | calcul dettes |
| Models utilisés | `Invoice`, `Payment`, `Contact` |
| Validations | tenant-safe |
| Vues Blade | fiche client, liste dettes |
| Logique stock | aucune |
| Logique paiement | suivi du restant |
| Tests à écrire | dette visible apres paiement partiel |

### Fonctionnalité 9 - reçu simple

| Élément | Détail |
| ------- | ------ |
| Routes nécessaires | `GET /pos/receipt/{sale}` ou `GET /invoices/{id}/print` |
| Controller | `InvoiceController` |
| Service | rendu PDF |
| Models utilisés | `Invoice`, `InvoiceItem`, `Payment` |
| Validations | tenant-safe |
| Vues Blade | ticket court |
| Logique stock | aucune |
| Logique paiement | resume du paiement |
| Tests à écrire | ticket généré |

### Fonctionnalité 10 - clôture journalière

| Élément | Détail |
| ------- | ------ |
| Routes nécessaires | `GET /cash-sessions`, `POST /cash-sessions/close` |
| Controller | nouveau `CashSessionController` |
| Service | `CashSessionService` |
| Models utilisés | `CashSession`, `CashSessionPayment` |
| Validations | session ouverte, montant coherent |
| Vues Blade | ouverture/fermeture caisse |
| Logique stock | aucune |
| Logique paiement | total journalier des encaissements |
| Tests à écrire | fermeture caisse, total correct |

### Fonctionnalité 11 - rapport journalier

| Élément | Détail |
| ------- | ------ |
| Routes nécessaires | `GET /reports/daily-sales` |
| Controller | `ReportController` |
| Service | aggregation journalieres |
| Models utilisés | `Invoice`, `Payment`, `CashSession` |
| Validations | date, tenant |
| Vues Blade | tableau journalier |
| Logique stock | aucune |
| Logique paiement | total journalier |
| Tests à écrire | rapport jour correct |

## 5. Sprint 3 - Import/export et stock utilisable

Objectif:
- reduire la friction d’adoption;
- permettre la migration rapide de clients existants.

### Import produits Excel/CSV

#### Fichiers à créer/modifier
- `app/Http/Controllers/ProductImportController.php` ou action dans `ProductController`;
- `app/Imports/*` si Excel;
- `app/Exports/*` si besoin;
- vues import;
- routes import.

#### Packages Laravel eventuels
- `maatwebsite/excel` pour import/export Excel.

#### Regles de validation
- nom requis;
- categorie;
- unite;
- prix;
- stock min;
- tenant-safe;
- lignes invalides isolees.

#### Rapport d'erreur import
- fichier d’erreur telechargeable;
- lignes refusees listees avec motif.

#### Creation automatique categories/unites si autorisé
- option admin;
- sinon blocage avec erreur claire.

#### Tests
- import fichier valide;
- import fichier partiellement invalide;
- import tenant-safe;
- auto creation categorie/unite si activee.

#### Critere d'acceptation
- un client peut importer sa base produits sans intervention technique lourde.

### Export produits

#### Fichiers
- controller export;
- classe export;
- routes export.

#### Regles
- export limite au tenant;
- filtres date / categorie possibles;
- format CSV et XLSX.

#### Tests
- export ne contient que le tenant;
- export respecte les filtres.

### Export stock

#### Fichiers
- report export;
- export stock consolidate.

#### Regles
- total stock par produit;
- par entrepot;
- par lot.

#### Tests
- export stock coherents.

### Stock initial

#### Fichiers
- import initial ou wizard;
- `InventoryController` ou nouveau service.

#### Regles
- stock initial alimente un batch;
- historise l'entree;
- tenant-safe.

#### Tests
- stock initial cree correctement.

### Alertes stock bas

#### Fichiers
- `Product`, `Batch`, notification/job.

#### Regles
- seuil d’alerte par produit;
- alerte sur stock disponible total ou par entrepot.

#### Tests
- seuil declenche alerte.

### Historique stock

#### Fichiers
- `InventoryMovement`;
- vues stock timeline;
- report stock.

#### Regles
- filtrage tenant;
- pagination;
- tri date.

#### Tests
- historique coherent et tenant-safe.

## 5.1 Audit du module abonnement existant

Le module abonnement n'est pas a recreer. Il existe deja dans le code et doit etre fiabilise, clarifie et complete.

| Element | Existe ? | Fichier / table | Etat actuel | A conserver | A corriger | A completer |
| ------- | -------- | --------------- | ----------- | ----------- | ---------- | ----------- |
| Plans | Oui | `app/Models/Plan.php`, `plans` | Table et modele deja en place | Oui | Clarifier les metadonnees utiles si besoin | Eventuelles limites par plan si elles manquent |
| Abonnements | Oui | `app/Models/Subscription.php`, `subscriptions` | Cycle de vie de base present | Oui | Harmoniser `is_active`, expiration et statut | Trial, historique plus lisible si absent |
| Permissions par plan | Oui | `plan_permission` | Pivot deja en place | Oui | Verifier le scope tenant / plan | Completer les permissions manquantes si necessaire |
| Middleware abonnement | Oui | `CheckSubscriptionAndPermissions` | Gate de verification deja active | Oui | Durcir les cas expiration / suspension / super-admin | Ajouter tests de non-regression |
| Gestion plateforme | Oui | `app/Http/Controllers/Admin/SubscriptionController.php` | CRUD abonnement plateforme present | Oui | Verifier validations tenant-safe et statuts | Ajouter l'UX de gestion si manque |
| Vue tenant abonnement | Oui | `app/Http/Controllers/Tenant/SubscriptionController.php` | Consultation tenant deja presente | Oui | Clarifier les vues et le resume d'abonnement | Ajouter une page "Mon abonnement" plus lisible |
| Seeder plans | Oui | `database/seeders/PlanSeeder.php` | Plans de base deja seeds | Oui | Verifier qu'aucun plan faible ne soit expose en prod | Adapter les plans commerciaux reellement vendus |
| Tenant / souscriptions | Oui | `app/Models/Tenant.php` | Relations deja en place | Oui | Verifier `currentSubscription()` et les cas limites | Ajouter indicateurs de quota si necessaire |
| Expiration / actif | Oui | `Subscription` + middleware | Verifications deja presentes | Oui | Rendre les messages plus explicites | Ajouter grace period si strategie produit |
| Suspension tenant | Oui | `CheckActiveUser`, `TenantController` | Bypass platform deja prevu | Oui | Clarifier la logique de blocage | Journaliser proprement les suspensions |
| Routes abonnement | Oui | routes web | Routes existantes cote admin et tenant | Oui | Renommer si necessaire pour la lisibilite | Ajouter uniquement les routes vraiment manquantes |

### Ce qu'il ne faut surtout pas recreer
- ne pas recreer `plans`;
- ne pas recreer `subscriptions`;
- ne pas recrire un second moteur d'abonnement;
- ne pas dupliquer `plan_permission`;
- ne pas remplacer le middleware existant sans raison;
- ne pas introduire un `BillingController` separé si les pages abonnement existantes suffisent;
- ne pas ajouter de tables de billing fictives avant verification de leur absence reelle;
- ne pas refactoriser le module en profondeur si une correction ciblee suffit.

## 6. Sprint 4 - Amelioration du module abonnement existant

Objectif:
- rendre le module abonnement fiable, lisible et exploitable commercialement;
- conserver l'existant au lieu de recreer un second module;
- combler uniquement les manques reels du code deja present.

### 4.1 Existant a conserver
- `plans` et `subscriptions` deja presentes;
- pivot `plan_permission` deja present;
- `Plan` et `Subscription` deja implementes;
- `Admin/SubscriptionController` deja en place;
- `Tenant/SubscriptionController` deja en place;
- `CheckSubscriptionAndPermissions` deja en place;
- `PlanSeeder` deja en place;
- relations tenant / subscription deja definies.

### 4.2 Corrections necessaires
- clarifier le statut de l'abonnement;
- distinguer expiration, suspension et inactivite;
- durcir les validations globales sur tenant / plan;
- verifier que les permissions autorisees par plan sont bien appliquees;
- rendre les messages de blocage plus compréhensibles;
- tester les cas super-admin, tenant actif et tenant suspendu;
- documenter les routes existantes au lieu d'en inventer de nouvelles.

### 4.3 Complements utiles a ajouter si absents du code
- essai gratuit;
- limites reelles par plan sur les usages critiques;
- compteur d'utilisation tenant;
- historique des paiements d'abonnement si la facturation separée est voulue;
- page tenant "Mon abonnement";
- page plateforme de gestion des abonnements;
- paiement manuel admin pour demarrage commercial;
- preparation technique a Wave / Orange Money via service dedie.

### 4.4 Ce qu'il ne faut pas faire
- ne pas creer un deuxieme module abonnement;
- ne pas recreer `plans` ou `subscriptions`;
- ne pas ajouter des tables de billing sans verification prealable;
- ne pas casser les controllers existants pour un design plus theorique;
- ne pas deplacer toute la logique vers des services sans besoin concret.

### 4.5 Tables a verifier avant toute migration

| Table | Existe deja ? | Action recommandee | Colonnes a ajouter si necessaire | Pourquoi | Sprint |
| ----- | ------------- | ------------------ | -------------------------------- | -------- | ------ |
| `plans` | Oui | Conserver et ajuster | `is_active`, metadata, ordre d'affichage | Le catalogue de plans existe deja | 4 |
| `subscriptions` | Oui | Conserver et etendre | `trial_ends_at`, `status`, `billing_status` si absent | Le cycle de vie abonnement existe deja | 4 |
| `plan_permission` | Oui | Conserver | aucune a priori | Le pivot permissions / plans existe deja | 4 |
| `tenant_usage` | Non confirme | Creer seulement si quota / usage devient reel | `tenant_id`, `metric`, `value`, `period` | Suivre les limites par plan | 4 |
| `billing_invoices` | Non confirme | Creer seulement si facturation SaaS separee | `tenant_id`, `subscription_id`, `amount`, `status`, `due_at` | Historique de facturation SaaS | 4 |
| `billing_payments` | Non confirme | Creer seulement si factures SaaS externes | `billing_invoice_id`, `provider`, `amount`, `status` | Traquer les paiements SaaS | 4 |
| `tenant_suspensions` | Non confirme | Creer seulement si suspension tracee hors logs existants | `tenant_id`, `reason`, `suspended_by` | Tracer les suspensions plateforme | 4 |

### 4.6 Routes et interfaces a garder en tete
- routes admin abonnement existantes;
- routes tenant abonnement existantes;
- aucune nouvelle route billing tant que le besoin n'est pas confirme;
- aucune duplication des pages de gestion deja en place.

### 4.7 Tests a ecrire
- abonnement actif autorise;
- abonnement expire bloque;
- tenant suspendu bloque;
- super-admin conserve l'acces;
- permissions par plan appliquees;
- role duplique ou externe refuse;
- vue tenant abonnement charge correctement;
- route admin abonnement reste fonctionnelle.

### 4.8 Criteres d'acceptation
- aucun doublon de module abonnement;
- les tables existantes sont conservees;
- seules les vraies tables manquantes sont proposees en creation;
- le tenant voit clairement son etat d'abonnement;
- la plateforme admin peut gerer les abonnements sans bricolage;
- les tests du module abonnement passent.

## 7. Sprint 5 - Reporting commercial

Objectif:
- donner du pilotage au dirigeant;
- produire des requêtes rapides et tenant-safe.

### Indicateurs a couvrir
- ventes par periode;
- chiffre d’affaires;
- marge;
- benefice;
- creances clients;
- dettes fournisseurs;
- stock faible;
- top produits;
- produits dormants;
- export Excel / PDF.

### Requêtes tenant-safe
- toujours filtrer par `tenant_id`;
- preferer `whereHas` sur les relations tenantées;
- ne pas utiliser des agrégations globales sur des tables enfants non tenantées sans contrainte parent.

### Pagination
- toujours paginer les listes longues;
- eviter les `get()` sur des collections massives.

### Performance
- prevoir cache sur les stats dashboard;
- eviter les join inutiles;
- compter les totaux avec des indexes.

### Index DB
Probables indexes utiles:
- `tenant_id`;
- `tenant_id + created_at`;
- `tenant_id + status`;
- `tenant_id + invoice_date`;
- `tenant_id + payment_date`;
- `product_id + warehouse_id + remaining`.

### Tests
- rapport tenant-isolé;
- filtres date;
- export tenant-safe;
- top produits;
- creances correctes;
- dettes correctes.

## 8. Sprint 6 - Premium

Backlog premium seulement:
- WhatsApp;
- SMS;
- code-barres;
- scan;
- alertes expiration;
- API;
- webhooks;
- multi-sites avancé.

Structure recommandée:
- un epic par feature;
- un service par integration externe;
- activation par plan;
- tests de gating par permission/plan.

## 9. Modifications base de données

| Table | Existe deja ? | Action recommandee | Colonnes a ajouter si necessaire | Pourquoi | Sprint |
| ----- | ------------- | ------------------ | -------------------------------- | -------- | ------ |
| `plans` | Oui | Conserver | `is_active`, metadata, ordre d'affichage | Module abonnement deja present | 4 |
| `subscriptions` | Oui | Conserver et etendre | `trial_ends_at`, `status`, `billing_status` si absent | Cycle abonnement deja present | 4 |
| `plan_permission` | Oui | Conserver | aucune a priori | Pivot permission / plan deja present | 4 |
| `cash_sessions` | Non confirme | Creer | `tenant_id`, `user_id`, `opened_at`, `closed_at`, `opening_amount`, `closing_amount` | Necessaire pour le POS | 2 |
| `cash_session_payments` | Non confirme | Creer | `cash_session_id`, `payment_id` | Lier paiements et caisse | 2 |
| `import_jobs` | Non confirme | Creer | `tenant_id`, `type`, `status`, `file_path`, `started_at`, `finished_at` | Imports asynchrones | 3 |
| `import_errors` | Non confirme | Creer | `import_job_id`, `row_number`, `field`, `message` | Rapport d'erreurs import | 3 |
| `activities` | Oui | Conserver et verifier | `tenant_id`, `actor_id`, `action`, `payload` si absent | Audit trail deja commence via table existante | 1 |
| `tenant_usage` | Non confirme | Creer seulement si quota reel | `tenant_id`, `metric`, `value`, `period` | Suivi des limites par plan | 4 |
| `billing_invoices` | Non confirme | Creer seulement si facturation SaaS separee | `tenant_id`, `subscription_id`, `amount`, `status`, `due_at` | Historique de facturation SaaS | 4 |
| `billing_payments` | Non confirme | Creer seulement si factures SaaS externes | `billing_invoice_id`, `provider`, `amount`, `status` | Paiements SaaS | 4 |
| `tenant_suspensions` | Non confirme | Creer seulement si suspension tracee hors logs | `tenant_id`, `reason`, `suspended_by` | Traquer les suspensions plateforme | 4 |

## 10. Routes à prévoir

### Routes tenant

| Méthode | Route | Controller | Permission | Description |
| ------- | ----- | ---------- | ---------- | ----------- |
| GET | `/pos` | `PosController@index` | `manage_pos` | écran POS |
| POST | `/pos/sales` | `PosController@store` | `create_sale` | créer une vente |
| GET | `/pos/receipt/{sale}` | `PosController@receipt` | `view_sales` | ticket vente |
| GET | `/cash-sessions` | `CashSessionController@index` | `close_cash_session` | sessions caisse |
| POST | `/cash-sessions/open` | `CashSessionController@open` | `close_cash_session` | ouvrir session |
| POST | `/cash-sessions/close` | `CashSessionController@close` | `close_cash_session` | fermer session |
| GET | `/imports/products` | `ProductImportController@create` | `import_products` | import produits |
| POST | `/imports/products` | `ProductImportController@store` | `import_products` | lancer import |
| GET | `/exports/products` | `ProductExportController@index` | `export_products` | export produits |
| GET | `/reports/daily-sales` | `ReportController@dailySales` | `view_reports` | rapport jour |

### Routes plateforme

| Méthode | Route | Controller | Permission | Description |
| ------- | ----- | ---------- | ---------- | ----------- |
| GET | `/admin/tenants` | `Admin/TenantController@index` | `manage_tenants` | liste tenants |
| PATCH | `/admin/tenants/{tenant}/suspend` | `Admin/TenantController@suspend` | `manage_tenants` | suspendre tenant |
| PATCH | `/admin/tenants/{tenant}/activate` | `Admin/TenantController@activate` | `manage_tenants` | activer tenant |
| PATCH | `/admin/tenants/{tenant}/plan` | `Admin/TenantController@changePlan` | `manage_tenants` | changer plan |
| GET | `/admin/subscriptions` | `Admin/SubscriptionController@index` | `manage_subscriptions` | gerer les abonnements |
| POST | `/admin/subscriptions` | `Admin/SubscriptionController@store` | `manage_subscriptions` | creer un abonnement |
| PATCH | `/admin/subscriptions/{subscription}` | `Admin/SubscriptionController@update` | `manage_subscriptions` | mettre a jour un abonnement |
| PATCH | `/admin/subscriptions/{subscription}/toggle` | `Admin/SubscriptionController@toggleActive` | `manage_subscriptions` | activer / desactiver |

### Routes auth
- conserver `login`, `logout`, `forgot-password`, `reset-password`;
- ajouter onboarding si nécessaire.

### Routes billing
- `/tenant/subscriptions`;
- `/tenant/subscriptions/{subscription}`;
- `/tenant/subscription`;
- actions d'upgrade / downgrade seulement si elles existent deja cote code.

### Routes reports
- `/reports`;
- `/reports/products`;
- `/reports/suppliers`;
- `/reports/daily-sales`;
- `/reports/stock`;
- `/reports/export`.

## 11. Permissions à prévoir

| Permission | Module | Plan concerné | Rôle recommandé |
| ---------- | ------ | ------------- | --------------- |
| `manage_pos` | POS | Starter+ | admin entreprise / vendeur |
| `create_sale` | POS | Starter+ | vendeur |
| `refund_sale` | POS | Pro+ | admin entreprise |
| `close_cash_session` | POS | Pro+ | admin / caissier |
| `import_products` | Import | Pro+ | admin entreprise |
| `export_products` | Export | Pro+ | admin / manager |
| `view_reports` | Reports | Starter+ | admin / manager |
| `manage_subscription` | Billing | Tous | admin tenant |
| `manage_plan` | Billing plateforme | Enterprise interne | super-admin |
| `manage_tenant_billing` | Billing plateforme | plateforme | super-admin |
| `view_usage` | Billing | Tous | admin tenant |
| `view_billing_invoices` | Billing SaaS futur | Tous | admin tenant |
| `manage_limits` | Billing plateforme | plateforme | super-admin |
| `manage_tenants` | Platform | plateforme | super-admin |

## 12. Tests détaillés à écrire

| Test | Type | Module | Scénario | Résultat attendu | Priorité |
| ---- | ---- | ------ | -------- | ---------------- | -------- |
| `user_cannot_assign_foreign_role` | Sécurité | RBAC | un admin tente d’assigner un role externe | refus | P0 |
| `invoice_creation_rejects_foreign_refs` | Sécurité | Documents | facture avec contact/produit/entrepot externe | validation refusee | P0 |
| `payment_rejects_foreign_invoice` | Sécurité | Paiement | paiement sur facture d’un autre tenant | refus | P0 |
| `return_rejects_foreign_invoice_item` | Sécurité | Retours | retour sur ligne externe | refus | P0 |
| `report_products_is_tenant_safe` | Sécurité | Reports | deux tenants, meme produit simule | resultat isole | P0 |
| `pos_sale_creates_invoice_and_stock_movement` | Metier | POS | vente rapide complete | facture + mouvement + paiement | P1 |
| `pos_partial_payment_creates_remaining_due` | Metier | POS | paiement partiel | dette restante juste | P1 |
| `cash_session_closure_aggregates_payments` | Metier | POS | fermeture caisse | total correct | P1 |
| `import_products_valid_file` | Import | Import/Export | fichier valide | produits crees | P1 |
| `import_products_invalid_rows_reported` | Import | Import/Export | lignes invalides | erreurs exportees | P1 |
| `export_products_contains_only_tenant_data` | Export | Import/Export | export | donnees tenant only | P1 |
| `subscription_expired_blocks_access` | SaaS | Billing | abonnement expire | acces bloque | P1 |
| `plan_limits_enforced` | SaaS | Billing | depassement quota | refus | P1 |
| `daily_sales_report_is_correct` | Reports | Reporting | vente sur periode | total juste | P1 |
| `low_stock_alert_triggered` | Stock | Stock | seuil atteint | alerte declenchee | P2 |
| `product_fifo_stock_remains_consistent` | Stock | FIFO | plusieurs lots | stock correct | P2 |

## 13. Critères d’acceptation par sprint

### Sprint 1 accepté si:
- aucune validation `exists` critique n’est globale;
- aucun role externe n’est assignable;
- les rapports sont isolés par tenant;
- aucun `dd()` ne reste;
- les tests de securite passent.

### Sprint 2 accepté si:
- une vente simple est réalisable en moins de 30 secondes;
- le paiement total et partiel fonctionne;
- la dette client est visible;
- le ticket de caisse est imprimable;
- la clôture journalière est juste.

### Sprint 3 accepté si:
- un fichier produits peut être importe;
- les erreurs d’import sont lisibles;
- l’export produits est tenant-safe;
- le stock initial est géré;
- les alertes stock bas sont déclenchées.

### Sprint 4 accepté si:
- l’essai gratuit existe;
- les limites par plan bloquent correctement;
- un tenant peut être suspendu;
- l’upgrade/downgrade est possible;
- une facture SaaS est traçable.

### Sprint 5 accepté si:
- les rapports sont tenant-safe;
- les exports sont corrects;
- les calculs de marge/benefice sont cohérents;
- la pagination est présente;
- les indexes nécessaires sont en place.

### Sprint 6 accepté si:
- les features premium sont backloguées;
- l’activation par plan est possible;
- le scope est clair;
- aucun premium n’empiète sur le MVP.

## 14. Risques techniques

| Risque | Impact | Probabilité | Mitigation |
| ------ | ------ | ----------- | ---------- |
| Régression sécurité | fuite ou escalade | élevée | tests de non-regression avant tout |
| Performance reporting | lenteur forte | moyenne | indexes + cache + pagination |
| Concurrence stock | stock faux | moyenne | lockForUpdate + transactions |
| Risque multi-tenant | mélange de données | élevée | tenant-safe partout |
| Risque UX | produit trop complexe | élevée | POS simple, écrans courts |
| Risque paiement | incohérences financières | moyenne | transactions + règles strictes |
| Risque migration | import cassé | moyenne | rapport erreurs + mapping explicite |

## 15. Ordre exact d’exécution recommandé

1. écrire les tests sécurité;
2. corriger la sécurité multi-tenant;
3. rendre les tests verts;
4. construire le POS;
5. ajouter import/export;
6. mettre en place le billing;
7. durcir le reporting;
8. préparer le premium.

## 16. Conclusion

### Avant client pilote
- securite tenant;
- RBAC;
- debug supprimé;
- imports;
- POS minimum;
- rapports essentiels.

### Avant vente payante
- module abonnement existant durci;
- plans et limites si elles sont effectivement implémentees;
- suspension;
- facturation SaaS seulement si une couche separée est vraiment ajoutee;
- support minimal;
- backup et monitoring.

### Ce qui peut attendre
- API publique;
- webhooks;
- offline complet;
- code-barres;
- scan;
- comptabilité avancée;
- enterprise custom.

### Meilleure stratégie technique pour sortir vite un MVP vendable
1. sécuriser le noyau;
2. livrer le POS;
3. simplifier l’adoption avec import/export;
4. monétiser avec plans et limites;
5. améliorer le reporting;
6. ajouter le premium après les premiers clients.
