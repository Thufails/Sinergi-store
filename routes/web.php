<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\TestimoniController;
use App\Http\Controllers\Store\HomeController;
use App\Http\Controllers\Store\ProductController as StoreProductController;

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
Route::get('/', [HomeController::class, 'index'])
    ->name('store.home');

Route::get('/products', [StoreProductController::class, 'index'])
    ->name('store.products.index');

Route::get('/products/{product:slug}', [StoreProductController::class, 'show'])
    ->name('store.products.show');
