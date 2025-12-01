<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\UnitsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockOutController;
use App\Http\Controllers\Tenant\SubscriptionController as TenantSubscriptionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
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

Route::get('/', [FrontController::class, 'index'])->middleware(['auth'])->name('home');

Route::get('/dashboard', function () {
    return redirect()->route('home');
})->middleware(['auth', 'verified'])->name('dashboard');

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

Route::middleware(['auth', 'subscription.permission:manage_invoices'])->prefix('tenant')->name('tenant.')->group(function () {
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

Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');
Route::put('/settings/{setting}', [SettingController::class, 'update'])->name('settings.update');

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

Route::prefix('invoices/{type}')->controller(InvoiceController::class)->middleware(['auth'])->name('invoices.')->group(function () {
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

})->where('type', 'client|supplier');

// Route::resource('/reports', ReportController::class)->middleware(['auth'])->names('reports');
Route::middleware(['auth'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index')->middleware(['subscription.permission:view_report']);
    Route::get('/reports/journal', [ReportController::class, 'journal'])->name('reports.journal');
    Route::get('/reports/products', [ReportController::class, 'products'])->name('reports.products');
    Route::get('/reports/suppliers', [ReportController::class, 'suppliers'])->name('reports.suppliers');
});

Route::middleware(['auth'])->group(function () {
    // Inventaires
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventories.index');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventories.store');
    Route::get('/inventory/{id}', [InventoryController::class, 'show'])->name('inventories.show');

    // Validation d'un item
    Route::get('/inventory/{inventory}/print', [InventoryController::class, 'print'])->name('inventories.print');
    Route::patch('/inventory/{id}/validate', [InventoryController::class, 'validateItem'])->name('inventories.validate');
});


Route::prefix('payments/{type}')->controller(PaymentController::class)->name('payments.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
    Route::delete('/{payment}', 'destroy')->name('destroy');
    Route::get('/{payment}', 'show')->where('payment', '[0-9a-fA-F\-]{36}')->name('show');
})->where('type', 'client|supplier');

Route::get('/expenses/print', [ExpenseController::class, 'print'])
    ->middleware(['auth'])
    ->name('expenses.print');

Route::resource('expenses', ExpenseController::class)->middleware(['auth', 'subscription.permission:manage_expenses'])->names('expenses');

Route::resource('stock/out', StockOutController::class)->middleware(['auth'])->names('stockout');
require __DIR__.'/auth.php';
