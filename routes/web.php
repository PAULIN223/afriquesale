<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageProxyController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\Admin;
use App\Livewire\Auth\LoginForm;
use App\Livewire\Admin\SettingsForm;
use App\Livewire\Admin\ParametresForm;
use App\Livewire\Admin\CustomersIndex;
use App\Livewire\Admin\MessagesShow;
use App\Livewire\Admin\WebMessagesShow;
use App\Livewire\Client\MessagesThread;
use Illuminate\Support\Facades\Route;

// Image proxy (public, no auth)
Route::get('/image-proxy', [ImageProxyController::class, 'show'])->name('image.proxy');

// Public shop routes — accessibles à tous (admin peut voir la boutique)
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
Route::get('/vente-flash', [ProductController::class, 'venteFlash'])->name('vente.flash');
Route::get('/messages', MessagesThread::class)->name('messages.index')->middleware('auth');

// Auth routes (guests only)
Route::middleware('guest')->group(function () {
    Route::get('/login',    LoginForm::class)->name('login');
    Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register',[RegisterController::class, 'register']);
});

// Email verification (not fully authed)
Route::get('/verify-email',         [VerificationController::class, 'showForm'])->name('verification.notice');
Route::post('/verify-email',        [VerificationController::class, 'verify'])->name('verification.verify');
Route::post('/verify-email/resend', [VerificationController::class, 'resend'])->name('verification.resend');

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Client routes (auth + verified + NOT admin)
Route::middleware(['auth', 'verified.email', 'redirect.admin'])->group(function () {
    Route::get('/cart',          [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add',     [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update',  [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove',  [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear',   [CartController::class, 'clear'])->name('cart.clear');

    Route::get('/checkout',              [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout',             [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success/{id}', [CheckoutController::class, 'success'])->name('checkout.success');

    Route::get('/account/profile',    [AccountController::class, 'profile'])->name('account.profile');
    Route::post('/account/profile',   [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::get('/account/orders',     [AccountController::class, 'orders'])->name('account.orders');
    Route::get('/account/orders/{id}',[AccountController::class, 'showOrder'])->name('account.order.show');
    Route::post('/account/orders/{id}/cancel', [AccountController::class, 'cancelOrder'])->name('account.order.cancel');

    Route::get('/wishlist',              [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle/{id}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
});

// ═══════════════════════════════════════════════════════════
// ADMIN ROUTES — MySQL data (web shop)
// ═══════════════════════════════════════════════════════════
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Products (Google Sheets / MySQL)
    Route::get('/products',               [Admin\ProductController::class, 'index'])->name('products.index');
    Route::post('/products/{id}/toggle',  [Admin\ProductController::class, 'toggle'])->name('products.toggle');
    Route::post('/products/sync',         [Admin\ProductController::class, 'sync'])->name('products.sync');
    Route::delete('/products/{id}',       [Admin\ProductController::class, 'destroy'])->name('products.destroy');

    // Orders (MySQL)
    Route::get('/orders',              [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}',         [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{id}/statut', [Admin\OrderController::class, 'updateStatut'])->name('orders.statut');
    Route::get('/orders/export/csv',   [Admin\OrderController::class, 'exportCsv'])->name('orders.export');

    // Customers (MySQL) — recherche + toggle statut en Livewire (CustomersIndex)
    Route::get('/customers',             CustomersIndex::class)->name('customers.index');
    Route::get('/customers/{id}',        [Admin\CustomerController::class, 'show'])->name('customers.show');
    Route::get('/customers/export/csv',  [Admin\CustomerController::class, 'exportCsv'])->name('customers.export');

    // Categories
    Route::get('/categories',         [Admin\CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories',        [Admin\CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{id}',    [Admin\CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [Admin\CategoryController::class, 'destroy'])->name('categories.destroy');

    // Banners
    Route::get('/banners',        [Admin\BannerController::class, 'index'])->name('banners.index');
    Route::post('/banners',       [Admin\BannerController::class, 'store'])->name('banners.store');
    Route::put('/banners/{id}',   [Admin\BannerController::class, 'update'])->name('banners.update');
    Route::delete('/banners/{id}',[Admin\BannerController::class, 'destroy'])->name('banners.destroy');

    // Settings (Livewire)
    Route::get('/settings', SettingsForm::class)->name('settings.index');

    // Messages (MySQL) — Livewire
    Route::get('/web-messages',        [Admin\MessageController::class, 'index'])->name('webmessages.index');
    Route::get('/web-messages/{user}', WebMessagesShow::class)->name('webmessages.show');

    // ───────────────────────────────────────────────────────
    // FIREBASE / Android App routes
    // ───────────────────────────────────────────────────────
    Route::get('/clients',              [Admin\FirebaseClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/{uid}',        [Admin\FirebaseClientController::class, 'show'])->name('clients.show');
    Route::post('/clients/{uid}/block', [Admin\FirebaseClientController::class, 'block'])->name('clients.block');
    Route::post('/clients/{uid}/unblock',[Admin\FirebaseClientController::class, 'unblock'])->name('clients.unblock');
    Route::post('/clients/{uid}/notify',[Admin\FirebaseClientController::class, 'notify'])->name('clients.notify');

    Route::get('/commandes',                  [Admin\FirebaseOrderController::class, 'index'])->name('commandes.index');
    Route::get('/commandes/{id}',             [Admin\FirebaseOrderController::class, 'show'])->name('commandes.show');
    Route::post('/commandes/{id}/statut',     [Admin\FirebaseOrderController::class, 'updateStatut'])->name('commandes.statut');

    Route::get('/produits',               [Admin\FirebaseProduitController::class, 'index'])->name('produits.index');
    Route::post('/produits/{id}/toggle',  [Admin\FirebaseProduitController::class, 'toggle'])->name('produits.toggle');
    Route::post('/produits/sync',         [Admin\FirebaseProduitController::class, 'sync'])->name('produits.sync');

    Route::get('/messages',         [Admin\FirebaseMessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{uid}',   MessagesShow::class)->name('messages.show');

    Route::get('/notifications',                    [Admin\FirebaseNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/send-all',          [Admin\FirebaseNotificationController::class, 'sendAll'])->name('notifications.sendAll');
    Route::post('/notifications/send-user',         [Admin\FirebaseNotificationController::class, 'sendUser'])->name('notifications.sendUser');
    Route::post('/notifications/{id}/read',         [Admin\FirebaseNotificationController::class, 'markRead'])->name('notifications.read');

    Route::get('/parametres', ParametresForm::class)->name('parametres.index');
});
