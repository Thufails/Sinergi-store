<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\TestimoniController;
use App\Http\Controllers\Store\CustomerAddressController;
use App\Http\Controllers\Store\CustomerAccountController;
use App\Http\Controllers\Store\CustomerAuthController;
use App\Http\Controllers\Store\HomeController;
use App\Http\Controllers\Store\ProductController as StoreProductController;
use App\Http\Controllers\Store\TestimonialController as StoreTestimonialController;

// ==================== ADMIN AUTHENTICATION ====================
Route::get('/admin/login', [AuthController::class, 'showLoginForm'])
    ->name('admin.login');

Route::post('/admin/login', [AuthController::class, 'login'])
    ->name('admin.login.submit');

// ==================== ADMIN AREA ====================
Route::middleware('auth')
    ->prefix('admin')
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('admin.dashboard');

        // Profil admin
        Route::get('/profile', [DashboardController::class, 'profile'])
            ->name('admin.profile');
        Route::put('/profile', [DashboardController::class, 'updateProfile'])
            ->name('admin.profile.update');

        // CRUD kategori dalam satu halaman
        Route::get('/categories', [CategoryController::class, 'index'])
            ->name('admin.categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])
            ->name('admin.categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])
            ->name('admin.categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])
            ->name('admin.categories.destroy');

        // CRUD produk dalam satu halaman
        Route::get('/products', [ProductController::class, 'index'])
            ->name('admin.products.index');
        Route::post('/products', [ProductController::class, 'store'])
            ->name('admin.products.store');
        Route::put('/products/{product}', [ProductController::class, 'update'])
            ->name('admin.products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])
            ->name('admin.products.destroy');

        // Kelola pesanan yang dibuat dari halaman publik
        Route::get('/orders', [OrderController::class, 'index'])
            ->name('admin.orders.index');
        Route::get('/orders/export', [OrderController::class, 'exportOrders'])
            ->name('admin.orders.export');
        Route::get('/orders/export-sales', [OrderController::class, 'exportSales'])
            ->name('admin.orders.export-sales');
        Route::get('/orders/{order}', [OrderController::class, 'show'])
            ->name('admin.orders.show');
        Route::get('/orders/{order}/payment-proof', [OrderController::class, 'showPaymentProof'])
            ->name('admin.orders.payment-proof');
        Route::patch('/orders/{order}/shipping-quote', [OrderController::class, 'quoteShipping'])
            ->name('admin.orders.shipping-quote');
        Route::patch('/orders/{order}/payment-review', [OrderController::class, 'reviewPayment'])
            ->name('admin.orders.payment-review');
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])
            ->name('admin.orders.status');
        Route::patch('/orders/{order}/shipping', [OrderController::class, 'updateShipping'])
            ->name('admin.orders.shipping');

        // CRUD testimoni dalam satu halaman
        Route::get('/testimonials', [TestimoniController::class, 'index'])
            ->name('admin.testimonials.index');
        Route::post('/testimonials', [TestimoniController::class, 'store'])
            ->name('admin.testimonials.store');
        Route::put('/testimonials/{testimonial}', [TestimoniController::class, 'update'])
            ->name('admin.testimonials.update');
        Route::delete('/testimonials/{testimonial}', [TestimoniController::class, 'destroy'])
            ->name('admin.testimonials.destroy');

        // Logout
        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('admin.logout');
    });

// ==================== PUBLIC STORE ====================
Route::prefix('account')->name('account.')->group(function () {
    Route::middleware('guest:customer')->group(function () {
        Route::get('/register', [CustomerAuthController::class, 'showRegistrationForm'])
            ->name('register');
        Route::post('/register', [CustomerAuthController::class, 'register'])
            ->middleware('throttle:5,1')
            ->name('register.submit');
        Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])
            ->name('login');
        Route::post('/login', [CustomerAuthController::class, 'login'])
            ->middleware('throttle:5,1')
            ->name('login.submit');
        Route::get('/forgot-password', [CustomerAuthController::class, 'showForgotPasswordForm'])
            ->name('password.request');
        Route::post('/forgot-password', [CustomerAuthController::class, 'sendResetLink'])
            ->middleware('throttle:5,1')
            ->name('password.email');
        Route::get('/reset-password/{token}', [CustomerAuthController::class, 'showResetPasswordForm'])
            ->name('password.reset');
        Route::post('/reset-password', [CustomerAuthController::class, 'resetPassword'])
            ->middleware('throttle:5,1')
            ->name('password.update');
    });

    Route::middleware('auth:customer')->group(function () {
        Route::post('/logout', [CustomerAuthController::class, 'logout'])
            ->name('logout');
        Route::get('/addresses', [CustomerAddressController::class, 'index'])
            ->name('addresses.index');
        Route::get('/addresses/create', [CustomerAddressController::class, 'create'])
            ->name('addresses.create');
        Route::post('/addresses', [CustomerAddressController::class, 'store'])
            ->name('addresses.store');
        Route::get('/addresses/{address}/edit', [CustomerAddressController::class, 'edit'])
            ->name('addresses.edit');
        Route::put('/addresses/{address}', [CustomerAddressController::class, 'update'])
            ->name('addresses.update');
        Route::delete('/addresses/{address}', [CustomerAddressController::class, 'destroy'])
            ->name('addresses.destroy');
        Route::patch('/addresses/{address}/default', [CustomerAddressController::class, 'setDefault'])
            ->name('addresses.default');
        Route::get('/orders', [CustomerAccountController::class, 'orders'])
            ->name('orders');
        Route::post('/orders/{order}/payment-proof', [CustomerAccountController::class, 'uploadPaymentProof'])
            ->middleware('throttle:5,1')
            ->name('orders.payment-proof.upload');
        Route::get('/orders/{order}/payment-proof', [CustomerAccountController::class, 'showPaymentProof'])
            ->name('orders.payment-proof');
        Route::get('/orders/{order}', [CustomerAccountController::class, 'showOrder'])
            ->name('orders.show');
        Route::get('/profile', [CustomerAccountController::class, 'profile'])
            ->name('profile');
        Route::put('/profile', [CustomerAccountController::class, 'updateProfile'])
            ->name('profile.update');
        Route::put('/profile/password', [CustomerAccountController::class, 'updatePassword'])
            ->name('profile.password.update');
    });
});

Route::get('/', [HomeController::class, 'index'])
    ->name('store.home');

Route::get('/products', [StoreProductController::class, 'index'])
    ->name('store.products.index');

Route::get('/products/{product:slug}/checkout', [StoreProductController::class, 'checkout'])
    ->middleware('auth:customer')
    ->name('store.products.checkout');

Route::get('/products/{product:slug}', [StoreProductController::class, 'show'])
    ->name('store.products.show');

Route::post('/products/{product:slug}/order', [StoreProductController::class, 'order'])
    ->middleware('auth:customer')
    ->middleware('throttle:5,1')
    ->name('store.products.order');

Route::post('/testimonials', [StoreTestimonialController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('store.testimonials.store');
