<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminRedeemCodeController;
use App\Http\Controllers\AdminVoucherController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\GmailOAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewInteractionController;
use App\Http\Controllers\SellerChatController;
use App\Http\Controllers\SellerDashboardController;
use App\Http\Controllers\SellerRegistrationController;
use App\Http\Controllers\SuspensionAppealController;
use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Route;

// Public routes (Only Home / Beranda is accessible without login)
Route::get('/', [HomeController::class, 'index'])->name('home');

// Gmail API 1-Click OAuth Connection Routes
Route::get('/oauth/gmail/connect', [GmailOAuthController::class, 'connect'])->name('oauth.gmail.connect');
Route::get('/oauth/gmail/callback', [GmailOAuthController::class, 'callback'])->name('oauth.gmail.callback');

// Guest Authentication routes (Only for unauthenticated users)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Public appeal submission route for suspended accounts
Route::post('/appeal', [SuspensionAppealController::class, 'store'])->name('appeals.store');

// Authenticated user routes (All other pages require login)
Route::middleware('auth')->group(function () {
    // Discovery & Marketplace Pages (Now Protected)
    Route::get('/search', [HomeController::class, 'search'])->name('search');
    Route::get('/search/results', [HomeController::class, 'searchResults'])->name('search.results');
    Route::get('/search/suggestions', [HomeController::class, 'searchSuggestions'])->name('search.suggestions');
    Route::get('/cart', [HomeController::class, 'cart'])->name('cart');
    Route::get('/api/products/{product}/variants', [HomeController::class, 'getProductVariants'])->name('api.products.variants');
    Route::get('/official-brand', [HomeController::class, 'officialBrand'])->name('official.brand');
    Route::get('/trending', [HomeController::class, 'trendingProducts'])->name('products.trending');
    Route::get('/top-up-tagihan', [HomeController::class, 'topUpBills'])->name('topup.bills');
    Route::post('/top-up-tagihan/checkout', [HomeController::class, 'processTopUp'])->name('topup.checkout');
    Route::get('/promo-terbatas', [HomeController::class, 'limitedPromo'])->name('promo.limited');
    Route::get('/kebutuhan-pokok', [HomeController::class, 'dailyEssentials'])->name('kebutuhan.pokok');
    Route::get('/gratis-ongkir', [HomeController::class, 'freeShipping'])->name('gratis.ongkir');
    Route::get('/penawaran-spesial', [HomeController::class, 'specialOffers'])->name('penawaran.spesial');
    Route::get('/produk-baru', [HomeController::class, 'newProducts'])->name('produk.baru');
    Route::get('/voucher', [VoucherController::class, 'index'])->name('voucher.index');
    Route::get('/product/{product}', [HomeController::class, 'productDetail'])->name('product.detail');
    Route::get('/store/{store}', [HomeController::class, 'storeShow'])->name('store.show');
    Route::get('/order/{order_code}', [HomeController::class, 'orderDetail'])->name('order.detail');

    // Seller Registration & Status Route
    Route::get('/seller/register', [SellerRegistrationController::class, 'show'])->name('seller.register');
    Route::post('/seller/register', [SellerRegistrationController::class, 'register'])->name('seller.register.submit');

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
    Route::delete('/settings/account', [ProfileController::class, 'deleteAccount'])->name('settings.delete_account');

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

    // Product Review Interactions (Like & Comment)
    Route::post('/reviews/{review}/like', [ReviewInteractionController::class, 'toggleLike'])->name('reviews.like');
    Route::post('/reviews/{review}/comments', [ReviewInteractionController::class, 'storeComment'])->name('reviews.comments.store');

    // Voucher Claims & Checkout Routes
    Route::post('/voucher/{voucher}/claim', [VoucherController::class, 'claim'])->name('voucher.claim');
    Route::get('/api/vouchers/checkout', [VoucherController::class, 'getAvailableForCheckout'])->name('voucher.checkout.list');
    Route::get('/api/redeem-codes/check', [AdminRedeemCodeController::class, 'checkCode'])->name('redeem-codes.check');
});

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
    Route::post('/settings/toggle-status', [SellerDashboardController::class, 'toggleStoreStatus'])->name('store.toggle_status');
    Route::delete('/settings/close-store', [SellerDashboardController::class, 'closeStorePermanently'])->name('store.close_permanently');

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

    // Seller Store Moderation, Approval & Suspension
    Route::get('/stores', [AdminController::class, 'stores'])->name('admin.stores');
    Route::post('/stores/{store}/approve', [AdminController::class, 'approveStore'])->name('admin.stores.approve');
    Route::post('/stores/{store}/reject', [AdminController::class, 'rejectStore'])->name('admin.stores.reject');
    Route::post('/stores/{store}/suspend', [AdminController::class, 'suspendStore'])->name('admin.stores.suspend');
    Route::post('/stores/{store}/unsuspend', [AdminController::class, 'unsuspendStore'])->name('admin.stores.unsuspend');

    // Store Safety Monitoring
    Route::get('/monitor', [AdminController::class, 'monitorStores'])->name('admin.monitor');

    // User & Administrator Account Management
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::patch('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('admin.users.role');
    Route::post('/users/{user}/verify', [AdminController::class, 'verifyUser'])->name('admin.users.verify');
    Route::post('/users/{user}/reset-password', [AdminController::class, 'resetUserPassword'])->name('admin.users.reset-password');
    Route::post('/users/{user}/suspend', [AdminController::class, 'suspendUser'])->name('admin.users.suspend');
    Route::post('/users/{user}/unsuspend', [AdminController::class, 'unsuspendUser'])->name('admin.users.unsuspend');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');

    // Voucher & Weekly Recurring Management
    Route::get('/vouchers', [AdminVoucherController::class, 'index'])->name('admin.vouchers.index');
    Route::post('/vouchers', [AdminVoucherController::class, 'store'])->name('admin.vouchers.store');
    Route::put('/vouchers/{voucher}', [AdminVoucherController::class, 'update'])->name('admin.vouchers.update');
    Route::delete('/vouchers/{voucher}', [AdminVoucherController::class, 'destroy'])->name('admin.vouchers.destroy');
    Route::post('/vouchers/{voucher}/toggle', [AdminVoucherController::class, 'toggle'])->name('admin.vouchers.toggle');
    Route::post('/vouchers/{voucher}/reset-quota', [AdminVoucherController::class, 'resetQuota'])->name('admin.vouchers.reset_quota');

    // Redeem Code (Kode Promo Input Manual) Management
    Route::get('/redeem-codes', [AdminRedeemCodeController::class, 'index'])->name('admin.redeem-codes.index');
    Route::post('/redeem-codes', [AdminRedeemCodeController::class, 'store'])->name('admin.redeem-codes.store');
    Route::put('/redeem-codes/{redeemCode}', [AdminRedeemCodeController::class, 'update'])->name('admin.redeem-codes.update');
    Route::delete('/redeem-codes/{redeemCode}', [AdminRedeemCodeController::class, 'destroy'])->name('admin.redeem-codes.destroy');
    Route::post('/redeem-codes/{redeemCode}/toggle', [AdminRedeemCodeController::class, 'toggle'])->name('admin.redeem-codes.toggle');
    Route::post('/redeem-codes/{redeemCode}/reset-used', [AdminRedeemCodeController::class, 'resetUsed'])->name('admin.redeem-codes.reset_used');

    // Suspension Appeals (Sistem Aju Banding) Management
    Route::get('/appeals', [SuspensionAppealController::class, 'adminIndex'])->name('admin.appeals.index');
    Route::post('/appeals/{appeal}/approve', [SuspensionAppealController::class, 'adminApprove'])->name('admin.appeals.approve');
    Route::post('/appeals/{appeal}/reject', [SuspensionAppealController::class, 'adminReject'])->name('admin.appeals.reject');
});
