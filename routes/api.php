<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\GenderController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\UserTypeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Route Definitions — BHARAT API
|--------------------------------------------------------------------------
|
| Authentication : Laravel Sanctum (Bearer token)
| Role Guard     : 'role' middleware (CheckRole)
| Base URL prefix: /api
|
*/

// =============================================================================
// PUBLIC ROUTES
// =============================================================================
Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login')->middleware('throttle:6,1')->name('login');
});

// =============================================================================
// AUTHENTICATED ROUTES
// =============================================================================
Route::group(['middleware' => 'auth:sanctum'], function () {

    // ── Self-Service (Authenticated User) ─────────────────────────────────────
    Route::controller(AuthController::class)->group(function () {
        Route::get('me', 'getCurrentUser');
        Route::get('me2', 'getCurrentUser2');
        Route::post('logout', 'logout');
        Route::match(['get', 'post'], 'revokeAll', 'revoke_all');
    });

    // ── Reference / Master Data (All Authenticated Users) ─────────────────────
    Route::get('user-types', [UserTypeController::class, 'index']);
    Route::get('designations', [DesignationController::class, 'index']);
    Route::get('genders', [GenderController::class, 'index']);

    Route::controller(StateController::class)->prefix('states')->group(function () {
        Route::get('/', 'index');
        Route::get('/{stateId}', 'show');
    });

    Route::get('districts', [DistrictController::class, 'index']);

    // =========================================================================
    // TIER 1 — Administration & Management
    // Roles: Admin | Developer | Owner
    // =========================================================================
    Route::middleware('role:Admin,Developer,Owner')->group(function () {

        // ── User Management ───────────────────────────────────────────────────
        Route::controller(AuthController::class)->prefix('users')->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'register')->middleware('throttle:10,1');
        });

        // ── Department Management ─────────────────────────────────────────────
        Route::controller(DepartmentController::class)->prefix('departments')->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::put('/', 'update');
        });

        // ── Employee Management ───────────────────────────────────────────────
        Route::controller(EmployeeController::class)->prefix('employees')->group(function () {
            Route::get('/', 'index');
            Route::get('/{id}', 'show');
            Route::post('/', 'store');
            Route::put('/{employeeId}', 'update');
            Route::delete('/{employeeId}', 'destroy');
        });

        // ── State Management ──────────────────────────────────────────────────
        Route::post('states', [StateController::class, 'store']);

        // ── Database Backups ──────────────────────────────────────────────────
        Route::controller(BackupController::class)->prefix('backups')->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'create')->middleware('throttle:3,1');
            Route::get('/{filename}', 'download');
            Route::delete('/{filename}', 'destroy');
            Route::delete('/', 'destroyAll');
        });

    }); // end Tier 1

    // =========================================================================
    // JEWELLERY ERP & RETAIL SYSTEM MODULES
    // =========================================================================

    // ── Dashboard Overview & Statistics ─────────────────────────────────────
    Route::get('dashboard', [\App\Http\Controllers\Api\DashboardController::class, 'stats']);
    Route::get('dashboard/stats', [\App\Http\Controllers\Api\DashboardController::class, 'stats']);

    // ── Business Profile & GST Settings ──────────────────────────────────────
    Route::controller(\App\Http\Controllers\Api\BusinessProfileController::class)->prefix('business-profile')->group(function () {
        Route::get('/', 'show');
        Route::put('/', 'update');
    });

    // ── Jewellery Masters (Purities, Categories, Daily Gold Rates) ───────────
    Route::controller(\App\Http\Controllers\Api\JewelleryMasterController::class)->group(function () {
        Route::get('purities', 'purities');
        Route::post('purities', 'storePurity');
        Route::get('jewellery-categories', 'categories');
        Route::post('jewellery-categories', 'storeCategory');
        Route::put('jewellery-categories/{id}', 'updateCategory');
        Route::delete('jewellery-categories/{id}', 'deleteCategory');
        Route::get('daily-gold-rates', 'dailyRates');
        Route::get('daily-gold-rates/today', 'todayRate');
        Route::post('daily-gold-rates', 'storeDailyRate');
    });

    // ── Parties (Customers, Vendors, Karigars) ───────────────────────────────
    Route::apiResource('parties', \App\Http\Controllers\Api\PartyController::class);

    // ── Jewellery Inventory Items & Barcode/RFID Tags ────────────────────────
    Route::get('jewellery-items/tag/{tagCode}', [\App\Http\Controllers\Api\JewelleryItemController::class, 'byTag']);
    Route::apiResource('jewellery-items', \App\Http\Controllers\Api\JewelleryItemController::class);

    // ── Stock Entries (Production Inward / Showroom Stock Generation) ────────
    Route::controller(\App\Http\Controllers\Api\StockEntryController::class)->prefix('stock-entries')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
    });

    // ── Purchases & Procurement (Bullion, Ornaments, Scrap Old Gold) ────────
    Route::controller(\App\Http\Controllers\Api\PurchaseController::class)->prefix('purchases')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::post('/{id}/cancel', 'cancel');
    });

    // ── Sales & Point of Sale (POS, Old Gold Exchange, GST Billing) ──────────
    Route::controller(\App\Http\Controllers\Api\SaleController::class)->prefix('sales')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::post('/{id}/cancel', 'cancel');
    });

    // ── Sales Returns (Credit Notes, Restock, GST Reversals) ─────────────────
    Route::controller(\App\Http\Controllers\Api\SalesReturnController::class)->prefix('sales-returns')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
    });

    // ── Accounts, Cash-in-Hand Drawer & Ledger Statements ────────────────────
    Route::controller(\App\Http\Controllers\Api\AccountController::class)->prefix('accounts')->group(function () {
        Route::get('/', 'index');
        Route::get('/drawer/cash-summary', 'cashDrawer');
        Route::get('/{id}', 'show');
        Route::get('/{id}/statement', 'statement');
    });

    // ── Cash & Bank Book Receipts & Payments ─────────────────────────────────
    Route::controller(\App\Http\Controllers\Api\PaymentController::class)->prefix('payments')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
    });

    // ── Operating Expenses & Incomes ─────────────────────────────────────────
    Route::controller(\App\Http\Controllers\Api\ExpenseIncomeController::class)->prefix('expense-incomes')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::delete('/{id}', 'destroy');
    });

    // ── Statutory GST Reports (GSTR-1, GSTR-3B, Status) ─────────────────────
    Route::controller(\App\Http\Controllers\Api\GstReportController::class)->prefix('gst')->group(function () {
        Route::get('/status', 'status');
        Route::get('/gstr1-summary', 'gstr1Summary');
        Route::get('/gstr3b-summary', 'gstr3bSummary');
    });

}); // end auth:sanctum
