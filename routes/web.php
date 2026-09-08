<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\GmailOAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SellerChatController;
use App\Http\Controllers\SellerDashboardController;
use App\Http\Controllers\SellerRegistrationController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', [HomeController::class, 'search'])->name('search');
Route::get('/search/results', [HomeController::class, 'searchResults'])->name('search.results');
Route::get('/search/suggestions', [HomeController::class, 'searchSuggestions'])->name('search.suggestions');
Route::get('/cart', [HomeController::class, 'cart'])->name('cart');
Route::get('/official-brand', [HomeController::class, 'officialBrand'])->name('official.brand');
Route::get('/trending', [HomeController::class, 'trendingProducts'])->name('products.trending');
Route::get('/top-up-tagihan', [HomeController::class, 'topUpBills'])->name('topup.bills');
Route::post('/top-up-tagihan/checkout', [HomeController::class, 'processTopUp'])->name('topup.checkout');
Route::get('/promo-terbatas', [HomeController::class, 'limitedPromo'])->name('promo.limited');
Route::get('/kebutuhan-pokok', [HomeController::class, 'dailyEssentials'])->name('kebutuhan.pokok');
Route::get('/product/{product:slug}', [HomeController::class, 'productDetail'])->name('product.detail');
Route::get('/store/{store}', [HomeController::class, 'storeShow'])->name('store.show');
Route::get('/order/{order_code}', [HomeController::class, 'orderDetail'])->name('order.detail');

// Gmail API 1-Click OAuth Connection Routes
Route::get('/oauth/gmail/connect', [GmailOAuthController::class, 'connect'])->name('oauth.gmail.connect');
Route::get('/oauth/gmail/callback', [GmailOAuthController::class, 'callback'])->name('oauth.gmail.callback');

// Guest Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated user routes
Route::middleware('auth')->group(function () {
    // Email OTP Verification Routes
    Route::get('/verify-email', [AuthController::class, 'showVerifyEmail'])->name('verification.notice');
    Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/verify-email/resend', [AuthController::class, 'resendOtp'])->name('verification.resend');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/checkout', [HomeController::class, 'showCheckout'])->name('checkout.show');
    Route::post('/checkout', [HomeController::class, 'checkout'])->name('checkout');
    Route::get('/my-orders', [HomeController::class, 'myOrders'])->name('my.orders');
    Route::post('/my-orders/{order}/cancel', [HomeController::class, 'requestOrderCancellation'])->name('orders.cancel');
    Route::post('/my-orders/{order}/complete', [HomeController::class, 'confirmOrderCompletion'])->name('orders.complete');
    Route::post('/my-orders/{order}/return', [HomeController::class, 'requestOrderReturn'])->name('orders.return');
    Route::get('/settings', [ProfileController::class, 'showSettings'])->name('settings');
    Route::put('/settings/profile', [ProfileController::class, 'updateProfile'])->name('settings.profile');
    Route::put('/settings/address', [ProfileController::class, 'updateAddress'])->name('settings.address');
    Route::put('/settings/password', [ProfileController::class, 'updatePassword'])->name('settings.password');

    // Customer / Buyer Chat Routes (Shopee/Tokopedia Style)
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{conversation}', [ChatController::class, 'index'])->name('chat.show');
    Route::post('/chat/start', [ChatController::class, 'start'])->name('chat.start');
    Route::post('/chat/{conversation}/messages', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/chat/{conversation}/messages', [ChatController::class, 'fetchMessages'])->name('chat.fetch');
    Route::get('/chat-unread-count', [ChatController::class, 'unreadCount'])->name('chat.unread_count');

    // Store Follow Toggle Route
    Route::post('/store/{store:slug}/toggle-follow', [HomeController::class, 'toggleFollowStore'])->name('store.toggle_follow');

    // Product Review Submission Route
    Route::post('/product/{product:slug}/reviews', [HomeController::class, 'storeReview'])->name('product.reviews.store');
});

// Seller Registration & Status Route
Route::get('/seller/register', [SellerRegistrationController::class, 'show'])->name('seller.register');
Route::post('/seller/register', [SellerRegistrationController::class, 'register'])->middleware('auth')->name('seller.register.submit');

// Dedicated Seller Center & Store Management Portal Routes
Route::prefix('seller')->middleware(['auth', 'seller'])->name('seller.')->group(function () {
    Route::get('/', fn () => redirect()->route('seller.dashboard'));
    Route::get('/dashboard', [SellerDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/products', [SellerDashboardController::class, 'products'])->name('products.index');
    Route::get('/products/create', [SellerDashboardController::class, 'createProduct'])->name('products.create');
    Route::post('/products', [SellerDashboardController::class, 'storeProduct'])->name('products.store');
    Route::get('/products/{product}/edit', [SellerDashboardController::class, 'editProduct'])->name('products.edit');
    Route::put('/products/{product}', [SellerDashboardController::class, 'updateProduct'])->name('products.update');
    Route::delete('/products/{product}', [SellerDashboardController::class, 'destroyProduct'])->name('products.destroy');
    Route::get('/orders', [SellerDashboardController::class, 'orders'])->name('orders');
    Route::patch('/orders/{order}/status', [SellerDashboardController::class, 'updateOrderStatus'])->name('orders.update-status');
    Route::patch('/orders/{order}/cancellation', [SellerDashboardController::class, 'respondCancellation'])->name('orders.cancellation');
    Route::patch('/orders/{order}/return', [SellerDashboardController::class, 'respondReturn'])->name('orders.return.respond');
    Route::get('/orders/notifications-check', [SellerDashboardController::class, 'notificationsCheck'])->name('orders.notifications_check');
    Route::get('/settings', [SellerDashboardController::class, 'settings'])->name('settings');
    Route::put('/settings', [SellerDashboardController::class, 'updateSettings'])->name('settings.update');

    // Seller Chat Routes
    Route::get('/chat', [SellerChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{conversation}', [SellerChatController::class, 'index'])->name('chat.show');
    Route::post('/chat/{conversation}/messages', [SellerChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/chat/{conversation}/messages', [SellerChatController::class, 'fetchMessages'])->name('chat.fetch');
    Route::get('/chat-unread-count', [SellerChatController::class, 'unreadCount'])->name('chat.unread_count');
});

// Dedicated Administrator Control & Functional System Operations Panel Routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/cache/clear', [AdminController::class, 'clearCache'])->name('admin.cache.clear');
    Route::post('/database/optimize', [AdminController::class, 'optimizeDatabase'])->name('admin.database.optimize');

    // System Logs Reader, Real-Time Stream, & Cleaner
    Route::get('/logs', [AdminController::class, 'logs'])->name('admin.logs');
    Route::get('/logs/feed', [AdminController::class, 'logs'])->name('admin.logs.feed');
    Route::post('/logs/clear', [AdminController::class, 'clearLogs'])->name('admin.logs.clear');

    // Database & Storage Health Monitor
    Route::get('/database', [AdminController::class, 'database'])->name('admin.database');

    // Seller Store Moderation & Approval
    Route::get('/stores', [AdminController::class, 'stores'])->name('admin.stores');
    Route::post('/stores/{store}/approve', [AdminController::class, 'approveStore'])->name('admin.stores.approve');
    Route::post('/stores/{store}/reject', [AdminController::class, 'rejectStore'])->name('admin.stores.reject');

    // User & Administrator Account Management
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::patch('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('admin.users.role');
    Route::post('/users/{user}/verify', [AdminController::class, 'verifyUser'])->name('admin.users.verify');
    Route::post('/users/{user}/reset-password', [AdminController::class, 'resetUserPassword'])->name('admin.users.reset-password');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
});
