# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

SaaS ERP for stock management ("Gestion de Stock") built with Laravel 10, PHP 8.1+. Multi-tenant architecture where each business is a **Tenant**, isolated from others at the query level.

## Commands

```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate
php artisan migrate --seed

# Development
php artisan serve          # Laravel dev server
npm run dev               # Vite asset watcher

# Build
npm run build             # Compile production assets

# Tests
php artisan test                                    # All tests
php artisan test --testsuite=Feature               # Feature tests only
php artisan test --testsuite=Unit                  # Unit tests only
php artisan test tests/Feature/InventoryReconciliationTest.php  # Single file
php artisan test --filter=MethodName               # Single test method

# Code style
./vendor/bin/pint                                  # Laravel Pint (PSR-12)

# Docker
docker build -t gstock .                           # Build image (PHP 8.2 + Apache)
```

**Note**: Tests require a real MySQL database — SQLite/in-memory is commented out in `phpunit.xml`. Set `DB_*` vars in `.env` before running tests.

## Architecture

### Multi-Tenancy

Every business model uses the `HasTenant` trait (`app/Traits/HasTenant.php`), which:
- Auto-injects `tenant_id` on `creating` from the authenticated user
- Applies a global Eloquent scope filtering all queries by the current user's `tenant_id`

This means **all queries on tenant-scoped models are automatically filtered** — no manual `where('tenant_id', ...)` needed in controllers. However, **Form Request validation rules must add tenant scoping manually** (e.g., `Rule::exists('products', 'id')->where('tenant_id', auth()->user()->tenant_id)`), as validation bypasses Eloquent scopes. This is a known security gap.

The "platform" tenant (slug = `"platform"`) is reserved for super-admins who manage all tenants, plans, and subscriptions.

### Authorization

Two layers:
1. **Spatie Laravel-Permission** (`spatie/laravel-permission`) — roles and permissions scoped per tenant via `tenant_id` on the `roles` table
2. **`CheckSubscriptionAndPermissions` middleware** (`app/Http/Middleware/`) — verifies the tenant has an active subscription and that the user has the required permission for the route

### Service Layer

Business logic lives in `app/Services/` (18 services). Controllers are thin — they delegate to services for anything complex:

- `InvoiceService` / `PaymentCancellationService` / `InvoicePaymentStatusService` — invoicing lifecycle
- `DocumentNumberService` — centralized auto-numbering for all document types (invoices, quotes, orders, etc.)
- `QuoteConversionService` / `SaleOrderConversionService` / `PurchaseOrderConversionService` — document-to-document conversion workflows
- `CustomerReturnService` / `SupplierReturnService` — return processing and stock reintegration / sortie
- `CustomerCreditNoteService` / `SupplierCreditNoteService` — credit note creation, invoice application, refunds
- `ReturnProductController` — return / credit dashboard with recent documents and summary totals
- `InventoryReconciliationService` — physical count reconciliation
- `PosSaleService` — fast POS checkout with receipt printing

### Stock Management

Stock is tracked via **batches** (FIFO lots) per product per warehouse:
- `Batch` model: `product_id`, `warehouse_id`, `batch_number`, `expiry_date`, `quantity`
- `InventoryMovement` model: audit trail of all stock changes (type: in/out/adjustment)
- `Product.qty_in_hand` is a denormalized aggregate — updated by services, not directly

### Document Workflow

All commercial documents (Quote → SaleOrder → DeliveryNote → Invoice, and PurchaseOrder → GoodsReceipt → Invoice) follow a status workflow: `draft → validated → complete`. Conversion between document types is handled by `*ConversionService` classes.

### PDF Generation

`barryvdh/laravel-dompdf` is used for printing invoices, quotes, and receipts.

## Module Readiness (current state)

Per `sprint_stabilisation_audit.md`:
- **Production-ready**: Quotes, Sale Orders, Delivery Notes, Customer Returns, Supplier Returns, Customer Credit Notes, Supplier Credit Notes, Return Dashboard, Purchase Orders, Goods Receipts, POS/Quick Sale, Invoices, Payments, Wallets, Inventories, Products, Warehouses, Taxes, Reports, Subscriptions, Roles, Users
- **Partial**: Batches, Stock Movements, Stock Transfers, subscription quota enforcement, some tenant-safe validation hardening
- **Legacy placeholder shells**: `/modules/*` still exists as an audit/navigation hub for modules already delivered; it should no longer be used as the source of truth for workflow readiness

The live workflow routes are now the source of truth. If a module has real routes/controllers/views under `quotes.*`, `sale-orders.*`, `delivery-notes.*`, `customer-returns.*`, `supplier-returns.*`, `purchase-orders.*`, `goods-receipts.*`, `sales.*`, or `taxes.*`, treat it as shipped unless the code says otherwise.

## Planned Sprints (from `analyse_projet.md`)

1. **Sprint 1 — Security hardening** (highest priority): close tenant-safe validation gaps, review cross-tenant access on remaining requests, remove any stale debug helpers
2. **Sprint 2 — Subscription limits**: enforce `max_users`, `max_storage_mb`, and trial-period rules in code
3. **Sprint 3 — Stock UX hardening**: tighten batches, movements, and transfers screens, and keep the stock journal consistent
4. **Sprint 4 — Import/Export**: Excel/CSV product import, stock export
5. **Sprint 5 — Reporting**: tenant-safe analytics, DB indexes
6. **Sprint 6 — Premium features**: WhatsApp/SMS, barcodes, API/webhooks

## Key Files

| File | Purpose |
|---|---|
| `app/Traits/HasTenant.php` | Global tenant scoping — understand this before touching any model |
| `app/Http/Middleware/CheckSubscriptionAndPermissions.php` | Auth gate for all routes |
| `routes/web.php` | All 100+ routes; admin routes prefixed `/admin` |
| `app/Services/InvoiceService.php` | Core invoicing logic |
| `app/Http/Controllers/InvoiceController.php` | Largest controller (~20KB) |
| `app/Services/DocumentNumberService.php` | Centralized document numbering |
| `database/migrations/` | 54 migration files define the full schema |
