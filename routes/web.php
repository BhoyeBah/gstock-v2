<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\UnitsController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DocumentSequenceController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryMovmentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ModulePlaceholderController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SaleOrderController;
use App\Http\Controllers\DeliveryNoteController;
use App\Http\Controllers\CustomerReturnController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockOutController;
use App\Http\Controllers\StockTransfertController;
use App\Http\Controllers\SupplierReturnController;
use App\Http\Controllers\Tenant\SubscriptionController as TenantSubscriptionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WarehouseController;
use App\Models\Wallet;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/dashboard', [FrontController::class, 'index'])->middleware(['auth'])->name('dashboard');

Route::get('/', function () {
    return view('front.index');
});

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::resource('/permissions', PermissionController::class)->middleware('subscription.permission:manage_permissions');
    Route::resource('/plans', PlanController::class)->middleware('subscription.permission:manage_plans');
    Route::resource('/tenants', TenantController::class)->middleware('subscription.permission:manage_tenants');
    Route::resource('/subscriptions', SubscriptionController::class)->middleware('subscription.permission:manage_subscriptions');
    Route::patch('/subscriptions/toggle/{subscription}', [SubscriptionController::class, 'toggleActive'])->name('subscriptions.toggle');
});

Route::middleware(['auth', 'subscription.permission:manage_roles'])->resource('/roles', RoleController::class);
Route::middleware(['auth', 'subscription.permission:manage_users'])->resource('/users', UserController::class);
Route::patch('/users/{id}/toggle', [UserController::class, 'toggle'])->middleware(['auth', 'subscription.permission:manage_users'])->name('users.toggle');

Route::middleware(['auth', 'subscription.permission:view_subscriptions'])->prefix('tenant')->name('tenant.')->group(function () {
    Route::get('/subscriptions', [TenantSubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('/subscriptions/{subscription}', [TenantSubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::get('/subscriptions/{subscription}/pdf', [TenantSubscriptionController::class, 'pdf'])->name('subscriptions.pdf');
});

Route::resource('/activities', ActivityController::class)->middleware('auth')->names('user.activity');
Route::resource('/units', UnitsController::class)->names('admin.units');
Route::resource('/categories', CategoryController::class)->middleware(['auth', 'subscription.permission:manage_categories'])->names('categories');

Route::patch('/products/{id}', [ProductController::class, 'toggleActive'])->middleware(['auth', 'subscription.permission:read_products'])->name('products.toggle');
Route::resource('/products', ProductController::class)->middleware(['auth', 'subscription.permission:read_products'])->names('products');

Route::get('/warehouses/{id}/exchange', [WarehouseController::class, 'exchangeIndex'])->name('warehouses.exchange');
Route::post('/warehouses/{id}/exchange', [WarehouseController::class, 'exchange'])->name('warehouses.exchange');
Route::patch('/warehouses/{id}', [WarehouseController::class, 'toggleActive'])->name('warehouses.toggle');
Route::resource('/warehouses', WarehouseController::class)->middleware(['auth', 'subscription.permission:manage_warehouses'])->names('warehouses');

Route::middleware(['auth', 'subscription.permission:manage_stock'])->group(function () {
    Route::get('/batches', [BatchController::class, 'index'])->name('batches.index');
    Route::get('/movements', [InventoryMovmentController::class, 'index'])->name('movements.index');
});

Route::middleware(['auth', 'subscription.permission:manage_warehouses'])->group(function () {
    Route::get('/transfers', [StockTransfertController::class, 'index'])->name('transfers.index');
});

Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');
Route::put('/settings/{setting}', [SettingController::class, 'update'])->name('settings.update');
Route::middleware(['auth'])->prefix('modules')->name('modules.')->group(function () {
    Route::get('/{module}', [ModulePlaceholderController::class, 'show'])->name('show');
});
Route::middleware(['auth', 'can:read_document_sequences'])->prefix('document-sequences')->name('document-sequences.')->group(function () {
    Route::get('/', [DocumentSequenceController::class, 'index'])->name('index');
    Route::put('/{id}', [DocumentSequenceController::class, 'update'])->middleware('can:manage_document_sequences')->name('update');
});

Route::middleware(['auth', 'can:manage_notifications'])->prefix('admin')->group(function () {
    Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('admin.notifications.index');
    Route::get('/notifications/create', [AdminNotificationController::class, 'create'])->name('admin.notifications.create');
    Route::post('/notifications', [AdminNotificationController::class, 'store'])->name('admin.notifications.store');
});

Route::middleware(['auth'])->prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [ProfileController::class, 'edit'])->name('edit');
    Route::put('/', [ProfileController::class, 'update'])->name('update');
});

Route::prefix('clients')->controller(ContactController::class)->name('clients.')->group(function () {
    Route::get('/', 'index')->name('index')->defaults('type', 'clients')->middleware(['auth', 'subscription.permission:read_clients']);
    Route::get('/create', 'create')->name('create')->defaults('type', 'clients')->middleware(['auth', 'subscription.permission:create_clients']);
    Route::post('/', 'store')->name('store')->defaults('type', 'clients')->middleware(['auth', 'subscription.permission:read_clients']);
    Route::get('/{contact}', 'show')->name('show')->defaults('type', 'clients')->middleware(['auth', 'subscription.permission:read_client']);
    Route::get('/{contact}/edit', 'edit')->name('edit')->defaults('type', 'clients')->middleware(['auth', 'subscription.permission:update_clients']);
    Route::put('/{contact}', 'update')->name('update')->defaults('type', 'clients')->middleware(['auth', 'subscription.permission:update_clients']);
    Route::delete('/{contact}', 'destroy')->name('destroy')->defaults('type', 'clients')->middleware(['auth', 'subscription.permission:delete_clients']);
    Route::patch('/{id}', 'toggleActive')->name('toggle')->defaults('type', 'clients')->middleware(['auth', 'subscription.permission:toggle_clients']);
});

Route::prefix('suppliers')->controller(ContactController::class)->name('suppliers.')->group(function () {
    Route::get('/', 'index')->name('index')->defaults('type', 'suppliers')->middleware(['auth', 'subscription.permission:read_suppliers']);
    Route::get('/create', 'create')->name('create')->defaults('type', 'suppliers')->middleware(['auth', 'subscription.permission:create_suppliers']);
    Route::post('/', 'store')->name('store')->defaults('type', 'suppliers')->middleware(['auth', 'subscription.permission:create_suppliers']);
    Route::get('/{contact}', 'show')->name('show')->defaults('type', 'suppliers')->middleware(['auth', 'subscription.permission:read_supplier']);
    Route::get('/{contact}/edit', 'edit')->name('edit')->defaults('type', 'suppliers')->middleware(['auth', 'subscription.permission:read_suppliers']);
    Route::put('/{contact}', 'update')->name('update')->defaults('type', 'suppliers')->middleware(['auth', 'subscription.permission:read_suppliers']);
    Route::delete('/{contact}', 'destroy')->name('destroy')->defaults('type', 'suppliers')->middleware(['auth', 'subscription.permission:delete_suppliers']);
    Route::patch('/{id}', 'toggleActive')->name('toggle')->defaults('type', 'suppliers')->middleware(['auth', 'subscription.permission:toggle_suppliers']);
});

Route::prefix('invoices/{type}')->controller(InvoiceController::class)->middleware(['auth', 'subscription.permission:manage_invoices'])->name('invoices.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/unpaid', 'unpaid')->name('unpaid');
    Route::post('/', 'store')->name('store')->middleware('subscription.permission:create_invoices');
    Route::get('/{invoice}/edit', 'edit')->name('edit');
    Route::patch('/{invoice}/validate', 'validateInvoice')->where('invoice', '[0-9a-fA-F\-]{36}')->name('validate')->middleware(['subscription.permission:validate_invoices']);
    Route::patch('/{invoice}/pay', 'validatePay')->where('invoice', '[0-9a-fA-F\-]{36}')->name('pay')->middleware(['subscription.permission:make_payment']);
    Route::post('/{invoice}/return', 'returnProduct')->where('invoice', '[0-9a-fA-F\-]{36}')->name('returnProduct');
    Route::get('/{invoice}/print', 'print')->where('invoice', '[0-9a-fA-F\-]{36}')->name('print');
    Route::get('/{invoice}', 'show')->where('invoice', '[0-9a-fA-F\-]{36}')->name('show');
    Route::put('/{invoice}', 'update')->name('update');
    Route::delete('/{invoice}', 'destroy')->name('destroy');
    Route::delete('/{invoice}/cancel', 'cancel')->name('cancel');
    Route::delete('/{invoice}/force', 'forceDestroy')->name('forceDestroy');

})->where('type', 'client|supplier');

// Route::resource('/reports', ReportController::class)->middleware(['auth'])->names('reports');
Route::middleware(['auth', 'subscription.permission:manage_reports'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/journal', [ReportController::class, 'journal'])->name('reports.journal');
    Route::get('/reports/products', [ReportController::class, 'products'])->name('reports.products');
    Route::get('/reports/suppliers', [ReportController::class, 'suppliers'])->name('reports.suppliers');
    Route::get('/reports/reportSuppliers', [ReportController::class, 'reportSuppliers'])->name('reports.reportSuppliers');
});

Route::middleware(['auth', 'subscription.permission:read_quotes'])->prefix('quotes')->name('quotes.')->controller(QuoteController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->middleware('subscription.permission:create_quotes')->name('create');
    Route::post('/', 'store')->middleware('subscription.permission:create_quotes')->name('store');
    Route::get('/{quote}', 'show')->where('quote', '[0-9a-fA-F\-]{36}')->name('show');
    Route::get('/{quote}/edit', 'edit')->where('quote', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:update_quotes')->name('edit');
    Route::put('/{quote}', 'update')->where('quote', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:update_quotes')->name('update');
    Route::delete('/{quote}', 'destroy')->where('quote', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:delete_quotes')->name('destroy');
    Route::post('/{quote}/send', 'send')->where('quote', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:update_quotes')->name('send');
    Route::post('/{quote}/accept', 'accept')->where('quote', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:convert_quotes')->name('accept');
    Route::post('/{quote}/reject', 'reject')->where('quote', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:convert_quotes')->name('reject');
    Route::post('/{quote}/cancel', 'cancel')->where('quote', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:delete_quotes')->name('cancel');
    Route::post('/{quote}/convert-to-order', 'convertToOrder')->where('quote', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:convert_quotes')->name('convert-to-order');
    Route::post('/{quote}/convert-to-invoice', 'convertToInvoice')->where('quote', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:convert_quotes')->name('convert-to-invoice');
    Route::get('/{quote}/print', 'print')->where('quote', '[0-9a-fA-F\-]{36}')->name('print');
});

Route::middleware(['auth', 'subscription.permission:read_sale_orders'])->prefix('sale-orders')->name('sale-orders.')->controller(SaleOrderController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->middleware('subscription.permission:create_sale_orders')->name('create');
    Route::post('/', 'store')->middleware('subscription.permission:create_sale_orders')->name('store');
    Route::get('/{saleOrder}', 'show')->where('saleOrder', '[0-9a-fA-F\-]{36}')->name('show');
    Route::get('/{saleOrder}/edit', 'edit')->where('saleOrder', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:update_sale_orders')->name('edit');
    Route::put('/{saleOrder}', 'update')->where('saleOrder', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:update_sale_orders')->name('update');
    Route::post('/{saleOrder}/confirm', 'confirm')->where('saleOrder', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:confirm_sale_orders')->name('confirm');
    Route::post('/{saleOrder}/cancel', 'cancel')->where('saleOrder', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:cancel_sale_orders')->name('cancel');
    Route::post('/{saleOrder}/create-delivery', 'createDelivery')->where('saleOrder', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:confirm_sale_orders')->name('create-delivery');
    Route::post('/{saleOrder}/create-invoice', 'createInvoice')->where('saleOrder', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:confirm_sale_orders')->name('create-invoice');
    Route::get('/{saleOrder}/print', 'print')->where('saleOrder', '[0-9a-fA-F\-]{36}')->name('print');
});

Route::middleware(['auth', 'subscription.permission:read_deliveries'])->prefix('delivery-notes')->name('delivery-notes.')->controller(DeliveryNoteController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->middleware('subscription.permission:create_deliveries')->name('create');
    Route::post('/', 'store')->middleware('subscription.permission:create_deliveries')->name('store');
    Route::get('/{deliveryNote}', 'show')->where('deliveryNote', '[0-9a-fA-F\-]{36}')->name('show');
    Route::post('/{deliveryNote}/validate', 'validateDelivery')->where('deliveryNote', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:validate_deliveries')->name('validate');
    Route::post('/{deliveryNote}/cancel', 'cancel')->where('deliveryNote', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:cancel_deliveries')->name('cancel');
    Route::get('/{deliveryNote}/print', 'print')->where('deliveryNote', '[0-9a-fA-F\-]{36}')->name('print');
});

Route::middleware(['auth', 'subscription.permission:read_customer_returns'])->prefix('customer-returns')->name('customer-returns.')->controller(CustomerReturnController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->middleware('subscription.permission:create_customer_returns')->name('create');
    Route::post('/', 'store')->middleware('subscription.permission:create_customer_returns')->name('store');
    Route::get('/{customerReturn}', 'show')->where('customerReturn', '[0-9a-fA-F\-]{36}')->name('show');
    Route::get('/{customerReturn}/edit', 'edit')->where('customerReturn', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:update_customer_returns')->name('edit');
    Route::put('/{customerReturn}', 'update')->where('customerReturn', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:update_customer_returns')->name('update');
    Route::post('/{customerReturn}/validate', 'validateReturn')->where('customerReturn', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:validate_customer_returns')->name('validate');
    Route::post('/{customerReturn}/cancel', 'cancel')->where('customerReturn', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:cancel_customer_returns')->name('cancel');
    Route::get('/{customerReturn}/print', 'print')->where('customerReturn', '[0-9a-fA-F\-]{36}')->name('print');
});

Route::middleware(['auth', 'subscription.permission:read_purchase_orders'])->prefix('purchase-orders')->name('purchase-orders.')->controller(PurchaseOrderController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->middleware('subscription.permission:create_purchase_orders')->name('create');
    Route::post('/', 'store')->middleware('subscription.permission:create_purchase_orders')->name('store');
    Route::get('/{purchaseOrder}', 'show')->where('purchaseOrder', '[0-9a-fA-F\-]{36}')->name('show');
    Route::get('/{purchaseOrder}/edit', 'edit')->where('purchaseOrder', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:update_purchase_orders')->name('edit');
    Route::put('/{purchaseOrder}', 'update')->where('purchaseOrder', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:update_purchase_orders')->name('update');
    Route::post('/{purchaseOrder}/confirm', 'confirm')->where('purchaseOrder', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:confirm_purchase_orders')->name('confirm');
    Route::post('/{purchaseOrder}/cancel', 'cancel')->where('purchaseOrder', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:cancel_purchase_orders')->name('cancel');
    Route::post('/{purchaseOrder}/create-receipt', 'createReceipt')->where('purchaseOrder', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:confirm_purchase_orders')->name('create-receipt');
    Route::post('/{purchaseOrder}/create-supplier-invoice', 'createSupplierInvoice')->where('purchaseOrder', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:confirm_purchase_orders')->name('create-supplier-invoice');
});

Route::middleware(['auth', 'subscription.permission:read_receipts'])->prefix('goods-receipts')->name('goods-receipts.')->controller(GoodsReceiptController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->middleware('subscription.permission:create_receipts')->name('create');
    Route::post('/', 'store')->middleware('subscription.permission:create_receipts')->name('store');
    Route::get('/{goodsReceipt}', 'show')->where('goodsReceipt', '[0-9a-fA-F\-]{36}')->name('show');
    Route::post('/{goodsReceipt}/validate', 'validateReceipt')->where('goodsReceipt', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:validate_receipts')->name('validate');
    Route::post('/{goodsReceipt}/cancel', 'cancel')->where('goodsReceipt', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:cancel_receipts')->name('cancel');
    Route::get('/{goodsReceipt}/print', 'print')->where('goodsReceipt', '[0-9a-fA-F\-]{36}')->name('print');
});

Route::middleware(['auth', 'subscription.permission:read_supplier_returns'])->prefix('supplier-returns')->name('supplier-returns.')->controller(SupplierReturnController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->middleware('subscription.permission:create_supplier_returns')->name('create');
    Route::post('/', 'store')->middleware('subscription.permission:create_supplier_returns')->name('store');
    Route::get('/{supplierReturn}', 'show')->where('supplierReturn', '[0-9a-fA-F\-]{36}')->name('show');
    Route::get('/{supplierReturn}/edit', 'edit')->where('supplierReturn', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:update_supplier_returns')->name('edit');
    Route::put('/{supplierReturn}', 'update')->where('supplierReturn', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:update_supplier_returns')->name('update');
    Route::post('/{supplierReturn}/validate', 'validateReturn')->where('supplierReturn', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:validate_supplier_returns')->name('validate');
    Route::post('/{supplierReturn}/cancel', 'cancel')->where('supplierReturn', '[0-9a-fA-F\-]{36}')->middleware('subscription.permission:cancel_supplier_returns')->name('cancel');
    Route::get('/{supplierReturn}/print', 'print')->where('supplierReturn', '[0-9a-fA-F\-]{36}')->name('print');
});

Route::middleware(['auth', 'subscription.permission:manage_inventories'])->group(function () {
    // Inventaires
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventories.index');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventories.store');
    Route::get('/inventory/{id}', [InventoryController::class, 'show'])->name('inventories.show');

    // Validation d'un item
    Route::get('/inventory/{inventory}/print', [InventoryController::class, 'print'])->name('inventories.print');
    Route::patch('/inventory/{id}/validate', [InventoryController::class, 'validateItem'])->name('inventories.validate');
});

Route::prefix('payments/{type}')->controller(PaymentController::class)->middleware(['auth', 'subscription.permission:manage_payments'])->name('payments.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
    Route::delete('/{payment}', 'destroy')->middleware('subscription.permission:cancel_payments')->name('destroy');
    Route::get('/{payment}', 'show')->where('payment', '[0-9a-fA-F\-]{36}')->name('show');
})->where('type', 'clients|suppliers');

Route::get('/expenses/print', [ExpenseController::class, 'print'])
    ->middleware(['auth'])
    ->name('expenses.print');

Route::resource('expenses', ExpenseController::class)->middleware(['auth', 'subscription.permission:manage_expenses'])->names('expenses');

Route::resource('stock/out', StockOutController::class)->middleware(['auth', 'subscription.permission:manage_stock_out'])->names('stockout');

Route::middleware(['auth', 'subscription.permission:manage_wallets'])->group(function () {
    Route::get('/wallets', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallets', [WalletController::class, 'store'])->name('wallet.store');
    Route::post('/wallets/transfert', [WalletController::class, 'transfert'])->name('wallet.transfert');
});
Route::middleware(['auth'])->get('/sales', function () {
    return redirect()->route('dashboard');
})->name('sales.index');

Route::prefix("/employes")->name("employes.")->controller(EmployeController::class)->middleware(['auth'])->group(function() {
    Route::get("/", "index")->name("index");
    Route::post("/store", "store")->name("store");
    Route::get("/{employe}/show", "show")->name("show");
    Route::get("/{employe}/edit", "edit")->name("edit");
    Route::put("/{employe}/update", "update")->name("update");
    Route::patch("/{employe}/toggle", "toggleActive")->name("toggleActive");
    Route::post("/{employe}/pay", "pay")->name("pay");
    Route::delete("/{employe}/delete", "destroy")->name("destroy");
});

require __DIR__.'/auth.php';
