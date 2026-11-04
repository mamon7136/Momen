<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dashboard;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\RegisterStoreController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/* ---------- Platform (marketing + onboarding) ---------- */
Route::get('/', [PlatformController::class, 'home'])->name('home');
Route::get('/pricing', [PlatformController::class, 'pricing'])->name('pricing');
Route::get('/slug-check', [PlatformController::class, 'slugCheck'])->name('slug.check');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/login/otp', [AuthController::class, 'sendOtp'])->middleware('throttle:5,1')->name('login.otp');
    Route::get('/register', [RegisterStoreController::class, 'create'])->name('register');
    Route::post('/register', [RegisterStoreController::class, 'store'])->middleware('throttle:10,1');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/register/store', [RegisterStoreController::class, 'createStore'])->name('register.store');
    Route::post('/register/store', [RegisterStoreController::class, 'storeStore']);
});

/* ---------- Merchant dashboard ---------- */
Route::middleware(['auth', 'merchant'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [Dashboard\HomeController::class, 'index'])->name('home');
    Route::post('/switch/{store}', [Dashboard\HomeController::class, 'switch'])->name('switch');
    Route::post('/goal', [Dashboard\HomeController::class, 'goal'])->name('goal');

    Route::middleware('merchant:products')->group(function () {
        Route::resource('products', Dashboard\ProductController::class)->except('show');
        Route::post('products/{product}/quick', [Dashboard\ProductController::class, 'quick'])->name('products.quick');
        Route::post('products/{product}/stock', [Dashboard\ProductController::class, 'stock'])->name('products.stock');
        Route::get('categories', [Dashboard\CategoryController::class, 'index'])->name('categories.index');
        Route::post('categories', [Dashboard\CategoryController::class, 'store'])->name('categories.store');
        Route::delete('categories/{category}', [Dashboard\CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    Route::middleware('merchant:orders')->group(function () {
        Route::get('orders', [Dashboard\OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [Dashboard\OrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/status', [Dashboard\OrderController::class, 'status'])->name('orders.status');
        Route::post('orders/{order}/shipment', [Dashboard\OrderController::class, 'shipment'])->name('orders.shipment');
        Route::get('orders/{order}/waybill', [Dashboard\OrderController::class, 'waybill'])->name('orders.waybill');
        Route::get('orders/{order}/invoice', [Dashboard\OrderController::class, 'invoice'])->name('orders.invoice');
        Route::get('customers', [Dashboard\CustomerController::class, 'index'])->name('customers.index');
    });

    Route::middleware('merchant:payments')->group(function () {
        Route::post('payments/{payment}/approve', [Dashboard\PaymentController::class, 'approve'])->name('payments.approve');
        Route::post('payments/{payment}/reject', [Dashboard\PaymentController::class, 'reject'])->name('payments.reject');
        Route::get('payments/{payment}/receipt', [Dashboard\PaymentController::class, 'receipt'])->name('payments.receipt');
        Route::get('payment-methods', [Dashboard\PaymentController::class, 'methods'])->name('payment-methods.index');
        Route::post('payment-methods', [Dashboard\PaymentController::class, 'saveMethod'])->name('payment-methods.save');
    });

    Route::middleware('merchant:coupons')->group(function () {
        Route::get('coupons', [Dashboard\CouponController::class, 'index'])->name('coupons.index');
        Route::post('coupons', [Dashboard\CouponController::class, 'store'])->name('coupons.store');
        Route::post('coupons/{coupon}/toggle', [Dashboard\CouponController::class, 'toggle'])->name('coupons.toggle');
        Route::delete('coupons/{coupon}', [Dashboard\CouponController::class, 'destroy'])->name('coupons.destroy');
        Route::get('marketing', [Dashboard\MarketingController::class, 'index'])->name('marketing.index');
        Route::post('marketing/abandoned/{cart}/remind', [Dashboard\MarketingController::class, 'remind'])->name('marketing.remind');
    });

    Route::middleware('merchant:shipping')->group(function () {
        Route::get('shipping', [Dashboard\ShippingController::class, 'index'])->name('shipping.index');
        Route::post('shipping/zones', [Dashboard\ShippingController::class, 'storeZone'])->name('shipping.zones.store');
        Route::delete('shipping/zones/{zone}', [Dashboard\ShippingController::class, 'destroyZone'])->name('shipping.zones.destroy');
        Route::post('shipping/companies', [Dashboard\ShippingController::class, 'storeCompany'])->name('shipping.companies.store');
        Route::post('branches', [Dashboard\ShippingController::class, 'storeBranch'])->name('branches.store');
    });

    Route::middleware('merchant:theme')->group(function () {
        Route::get('themes', [Dashboard\ThemeController::class, 'index'])->name('themes.index');
        Route::get('design', [Dashboard\DesignController::class, 'edit'])->name('design.edit');
        Route::post('design', [Dashboard\DesignController::class, 'update'])->name('design.update');
        Route::post('design/theme', [Dashboard\DesignController::class, 'theme'])->name('design.theme');
    });

    Route::get('reports', [Dashboard\ReportController::class, 'index'])->middleware('merchant:reports')->name('reports.index');
    Route::get('wallet', [Dashboard\WalletController::class, 'index'])->middleware('merchant:wallet')->name('wallet.index');
    Route::post('wallet/payout', [Dashboard\WalletController::class, 'payout'])->middleware('merchant:wallet')->name('wallet.payout');

    // owner-only
    Route::middleware('merchant:settings')->group(function () {
        Route::get('settings', [Dashboard\SettingsController::class, 'edit'])->name('settings.edit');
        Route::post('settings', [Dashboard\SettingsController::class, 'update'])->name('settings.update');
        Route::post('domains', [Dashboard\SettingsController::class, 'addDomain'])->name('domains.store');
        Route::post('domains/{domain}/verify', [Dashboard\SettingsController::class, 'verifyDomain'])->name('domains.verify');
        Route::get('staff', [Dashboard\StaffController::class, 'index'])->name('staff.index');
        Route::post('staff', [Dashboard\StaffController::class, 'store'])->name('staff.store');
        Route::delete('staff/{user}', [Dashboard\StaffController::class, 'destroy'])->name('staff.destroy');
        Route::get('billing', [Dashboard\BillingController::class, 'index'])->name('billing.index');
        Route::post('billing', [Dashboard\BillingController::class, 'renew'])->name('billing.renew');
        Route::get('apps', [Dashboard\BillingController::class, 'apps'])->name('apps.index');
        Route::post('apps/{app}', [Dashboard\BillingController::class, 'toggleApp'])->name('apps.toggle');
    });
});

/* ---------- Super admin ---------- */
Route::middleware(['auth', 'superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\AdminController::class, 'index'])->name('home');
    Route::get('stores', [Admin\AdminController::class, 'stores'])->name('stores');
    Route::post('stores/{store}/status', [Admin\AdminController::class, 'storeStatus'])->name('stores.status');
    Route::get('subscriptions', [Admin\AdminController::class, 'subscriptions'])->name('subscriptions');
    Route::post('subscriptions/{subscription}/approve', [Admin\AdminController::class, 'approveSubscription'])->name('subscriptions.approve');
    Route::get('subscriptions/{subscription}/receipt', [Admin\AdminController::class, 'subscriptionReceipt'])->name('subscriptions.receipt');
    Route::get('plans', [Admin\AdminController::class, 'plans'])->name('plans');
    Route::post('plans', [Admin\AdminController::class, 'savePlan'])->name('plans.save');
    Route::get('themes', [Admin\AdminController::class, 'themes'])->name('themes');
    Route::post('themes', [Admin\AdminController::class, 'saveTheme'])->name('themes.save');
    Route::get('payouts', [Admin\AdminController::class, 'payouts'])->name('payouts');
    Route::post('payouts/{payout}/paid', [Admin\AdminController::class, 'payoutPaid'])->name('payouts.paid');
    Route::get('settings', [Admin\AdminController::class, 'settings'])->name('settings');
    Route::post('rates', [Admin\AdminController::class, 'saveRate'])->name('rates.save');
    Route::post('shipping-companies', [Admin\AdminController::class, 'saveCompany'])->name('companies.save');
});

/* ---------- Webhooks ---------- */
Route::post('/webhooks/syberpay/{store}', [WebhookController::class, 'syberpay'])->name('webhooks.syberpay');
Route::get('/webhooks/whatsapp', [WebhookController::class, 'whatsappVerify']);
Route::post('/webhooks/whatsapp', [WebhookController::class, 'whatsapp']);
