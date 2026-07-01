# Rapport complet des capacites admin

Date: 2026-07-01

## 1. Perimetre

Ce rapport resume ce que l'administration peut faire dans l'application, en separant:
- l'administration plateforme (super-admin / SaaS)
- l'administration tenant (gestion interne d'une entreprise)

Les permissions et routes proviennent principalement de [`routes/web.php`](./routes/web.php).

## 2. Administration plateforme

L'acces au dashboard plateforme est reserve au super-admin via [`app/Http/Controllers/Admin/DashboardController.php`](./app/Http/Controllers/Admin/DashboardController.php) et les routes admin de [`routes/web.php`](./routes/web.php#L69).

### 2.1 Tableau de bord SaaS

L'admin plateforme peut consulter un tableau de bord SaaS avec:
- nombre total de tenants
- tenants actifs, suspendus, en essai, expirés, sans abonnement
- MRR, ARR et ARPU
- revenu mensuel
- nombre d'abonnements actifs
- abonnements qui expirent bientot
- distribution des plans
- tenants recents
- alertes de sante SaaS

Les calculs sont faits par [`app/Services/Admin/SaasMetricsService.php`](./app/Services/Admin/SaasMetricsService.php#L21).

### 2.2 Gestion des permissions

L'admin peut:
- lister les permissions
- creer une permission
- modifier une permission
- supprimer une permission

Code:
- [`app/Http/Controllers/Admin/PermissionController.php`](./app/Http/Controllers/Admin/PermissionController.php)
- routes admin: [`routes/web.php`](./routes/web.php#L72)
- vues: [`resources/views/back/admin/permissions/`](./resources/views/back/admin/permissions/index.blade.php)

### 2.3 Gestion des plans

L'admin peut:
- lister les plans
- creer un plan
- modifier un plan
- supprimer un plan
- associer des permissions a un plan
- activer ou desactiver un plan via le champ `is_active`

Le controller gere aussi:
- `price`
- `duration_days`
- `max_users`
- `max_storage_mb`
- `description`

Code:
- [`app/Http/Controllers/Admin/PlanController.php`](./app/Http/Controllers/Admin/PlanController.php)
- [`app/Http/Controllers/Admin/PlanPermissionController.php`](./app/Http/Controllers/Admin/PlanPermissionController.php)
- routes admin: [`routes/web.php`](./routes/web.php#L73)

### 2.4 Gestion des tenants

L'admin peut:
- lister les entreprises
- creer une entreprise avec son utilisateur proprietaire
- modifier une entreprise
- definir le logo, le nom, le slug, les coordonnees et l'etat actif

Lors de la creation, le code:
- cree le tenant
- cree un role admin pour ce tenant
- cree le user proprietaire
- assigne le role au user

Code:
- [`app/Http/Controllers/Admin/TenantController.php`](./app/Http/Controllers/Admin/TenantController.php)
- routes admin: [`routes/web.php`](./routes/web.php#L76)
- vues: [`resources/views/back/admin/tenants/`](./resources/views/back/admin/tenants/index.blade.php)

### 2.5 Gestion des abonnements

L'admin peut:
- lister les abonnements
- creer un abonnement
- modifier un abonnement
- voir le detail d'un abonnement
- activer / desactiver un abonnement
- supprimer un abonnement

Le controller bloque notamment:
- la modification d'un abonnement expire
- l'activation d'un abonnement expire
- l'activation d'un second abonnement actif pour le meme tenant

Code:
- [`app/Http/Controllers/Admin/SubscriptionController.php`](./app/Http/Controllers/Admin/SubscriptionController.php)
- routes admin: [`routes/web.php`](./routes/web.php#L77)
- vues: [`resources/views/back/admin/subscriptions/`](./resources/views/back/admin/subscriptions/index.blade.php)

### 2.6 Parametres plateforme

L'admin plateforme peut lire et mettre a jour les parametres globaux du SaaS:
- devise
- TVA

Le stockage se fait sur le tenant `platform`.

Code:
- [`app/Http/Controllers/Admin/PlatformSettingController.php`](./app/Http/Controllers/Admin/PlatformSettingController.php)
- routes admin: [`routes/web.php`](./routes/web.php#L79)
- vue: [`resources/views/back/admin/platform-settings/index.blade.php`](./resources/views/back/admin/platform-settings/index.blade.php)

### 2.7 Permissions par plan

L'admin peut:
- visualiser les plans et leurs permissions
- modifier les permissions associees a un plan

Code:
- [`app/Http/Controllers/Admin/PlanPermissionController.php`](./app/Http/Controllers/Admin/PlanPermissionController.php)
- routes admin: [`routes/web.php`](./routes/web.php#L74)
- vue: [`resources/views/back/admin/plan-permissions/index.blade.php`](./resources/views/back/admin/plan-permissions/index.blade.php)

### 2.8 Unites

Le code expose aussi la gestion des unites:
- lister
- creer
- modifier
- supprimer

Point important: la route `units` est nommee `admin.units`, mais dans l'etat actuel du code elle n'est pas protegee par un middleware `auth` ou `subscription.permission` dans `routes/web.php`. Il faut donc la considerer comme une fonctionnalite exposee, pas comme une zone admin strictement verrouillee.

Code:
- [`app/Http/Controllers/Admin/UnitsController.php`](./app/Http/Controllers/Admin/UnitsController.php)
- route: [`routes/web.php`](./routes/web.php#L95)

### 2.9 Notifications admin

L'intention du code est de permettre a l'administration de:
- lister les notifications envoyees
- composer une notification
- envoyer une notification:
  - a tous les utilisateurs
  - a un tenant
  - a un utilisateur precis

Point de vigilance: dans [`app/Http/Controllers/Admin/AdminNotificationController.php`](./app/Http/Controllers/Admin/AdminNotificationController.php), le ciblage `tenant` utilise `company_id` au lieu de `tenant_id`, et le fichier contient une declaration de namespace anormale. Cette partie doit etre consideree comme fragile tant qu'elle n'est pas corrigee.

Code:
- [`app/Http/Controllers/Admin/AdminNotificationController.php`](./app/Http/Controllers/Admin/AdminNotificationController.php)
- routes: [`routes/web.php`](./routes/web.php#L134)

## 3. Administration tenant

En dehors du bloc `/admin`, l'application expose plusieurs fonctions de gestion qui sont admin ou quasi-admin cote tenant.

### 3.1 Roles et utilisateurs

Un admin tenant peut:
- gerer les roles
- gerer les utilisateurs
- activer / desactiver un utilisateur

Routes:
- [`routes/web.php`](./routes/web.php#L84)
- [`routes/web.php`](./routes/web.php#L85)
- [`routes/web.php`](./routes/web.php#L86)

### 3.2 Catalogue

Un admin tenant peut:
- gerer les categories
- gerer les produits
- activer / desactiver un produit
- gerer les entrepots
- effectuer des echanges entre entrepots
- gerer les taxes / TVA

Routes:
- [`routes/web.php`](./routes/web.php#L96)
- [`routes/web.php`](./routes/web.php#L98)
- [`routes/web.php`](./routes/web.php#L106)
- [`routes/web.php`](./routes/web.php#L109)
- [`routes/web.php`](./routes/web.php#L112)

### 3.3 Stock

Un admin tenant peut:
- consulter les batches
- consulter les mouvements de stock
- consulter les transferts

Routes:
- [`routes/web.php`](./routes/web.php#L114)
- [`routes/web.php`](./routes/web.php#L119)

### 3.4 Documents commerciaux

L'admin tenant peut gerer:
- devis
- commandes clients
- bons de livraison
- retours clients
- factures clients
- notes de credit clients
- commandes fournisseurs
- bons de reception
- retours fournisseurs
- notes de credit fournisseurs
- paiements

Routes principales:
- [`routes/web.php`](./routes/web.php#L167)
- [`routes/web.php`](./routes/web.php#L247)
- [`routes/web.php`](./routes/web.php#L289)
- [`routes/web.php`](./routes/web.php#L307)

### 3.5 Paiements, caisse et depenses

L'admin tenant peut:
- gerer les paiements clients/fournisseurs
- gerer les wallets
- gerer les depenses
- gerer le stock out

Routes:
- [`routes/web.php`](./routes/web.php#L307)
- [`routes/web.php`](./routes/web.php#L322)
- [`routes/web.php`](./routes/web.php#L318)
- [`routes/web.php`](./routes/web.php#L320)

### 3.6 Rapports

L'admin tenant peut consulter:
- rapports generaux
- journal
- produits
- fournisseurs
- synthese fournisseurs

Route:
- [`routes/web.php`](./routes/web.php#L186)

### 3.7 Sequences documentaires et notifications

L'admin tenant peut aussi:
- lire les sequences documentaires
- modifier les sequences documentaires si la permission est presente
- consulter ses notifications de compte

Routes:
- [`routes/web.php`](./routes/web.php#L129)
- [`routes/web.php`](./routes/web.php#L134)

Attention: la route des notifications est sous `/admin`, mais sa logique de ciblage doit etre revue comme indique plus haut.

## 4. Preuves dans les tests

Les tests confirment les fonctions admin suivantes:
- acces super-admin au dashboard plateforme
- blocage des tenants sur le dashboard plateforme
- calcul des metriques SaaS
- gestion des permissions par plan
- enregistrement des parametres plateforme

Voir:
- [`tests/Feature/AdminDashboardTest.php`](./tests/Feature/AdminDashboardTest.php)

Les tests de securite montrent aussi:
- rejet des roles hors tenant
- rejet des references inter-tenants

Voir:
- [`tests/Feature/Sprint1SecurityTest.php`](./tests/Feature/Sprint1SecurityTest.php)

Les tests de devis confirment:
- creation de devis sans impact stock/caisse
- conversion devis -> facture
- validation des regles tenant-safe sur les references

Voir:
- [`tests/Feature/Sprint2AQuoteTaxRatesTest.php`](./tests/Feature/Sprint2AQuoteTaxRatesTest.php)

## 5. Resume court

En pratique, l'admin peut:
- gerer la plateforme SaaS
- piloter les tenants
- piloter les abonnements et les plans
- definir les permissions
- gerer les parametres globaux
- gerer les utilisateurs et les roles
- gerer le catalogue, le stock et les documents commerciaux
- consulter les rapports
- consulter et envoyer des notifications, avec la reserve technique signalee ci-dessus

## 6. Point de vigilance

Le code contient aussi quelques zones a verifier au cas par cas, par exemple le ciblage des notifications tenant dans [`app/Http/Controllers/Admin/AdminNotificationController.php`](./app/Http/Controllers/Admin/AdminNotificationController.php).
