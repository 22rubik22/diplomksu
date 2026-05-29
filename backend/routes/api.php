<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductImageController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\YandexAuthController;
use App\Http\Controllers\Api\PaymentController;


// Публичные маршруты
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/check', [AuthController::class, 'check']);



    
// Защищенные маршруты (требуют авторизации)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/users', [AuthController::class, 'getUsersList']);
    Route::get('/admin/users/{id}', [AuthController::class, 'getUserById']);
    Route::put('/admin/users/{id}/role', [AuthController::class, 'assignManager']);
    Route::put('/admin/users/{id}', [AuthController::class, 'adminUpdateUser']);
    Route::post('/admin/users/{id}/toggle-block', [AuthController::class, 'toggleUserBlock']);
});

// Маршруты для категорий
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/tree', [CategoryController::class, 'tree']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

// Защищенные маршруты для категорий (только админ)
Route::middleware('auth')->group(function () {
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
    Route::patch('/categories/{category}/toggle-active', [CategoryController::class, 'toggleActive']);
    Route::delete('/categories/{category}/image', [CategoryController::class, 'deleteImage']);
});

// Маршруты для брендов
Route::get('/brands', [BrandController::class, 'index']);
Route::get('/brands/{brand}', [BrandController::class, 'show']);
Route::get('/brands/{brand}/product', [BrandController::class, 'products']);

// Защищенные маршруты для брендов (только админ)
Route::middleware('auth')->group(function () {
    Route::post('/brands', [BrandController::class, 'store']);
    Route::put('/brands/{brand}', [BrandController::class, 'update']);
    Route::delete('/brands/{brand}', [BrandController::class, 'destroy']);
});

// Публичные маршруты для товаров
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/products/slug/{slug}', [ProductController::class, 'bySlug']);
Route::get('/products/{product}/reviews', [ProductController::class, 'reviews']);
Route::get('/products/{product}/similar', [ProductController::class, 'similar']);

// Защищенные маршруты (только админ)
Route::middleware('auth')->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    Route::patch('/products/{product}/toggle-featured', [ProductController::class, 'toggleFeatured']);
    Route::patch('/products/{product}/toggle-active', [ProductController::class, 'toggleActive']);
    Route::get('/products/export/excel', [ProductController::class, 'exportExcel']);
});



// Маршруты для изображений товара
Route::get('/products/{product}/images', [ProductImageController::class, 'index']);
Route::get('/products/{product}/images/primary', [ProductImageController::class, 'primary']);

// Защищенные маршруты (только админ)
Route::middleware('auth')->group(function () {
    Route::post('/products/{product}/images', [ProductImageController::class, 'store']);
    Route::put('/product-images/{image}', [ProductImageController::class, 'update']);
    Route::delete('/product-images/{image}', [ProductImageController::class, 'destroy']);
    Route::post('/products/{product}/images/reorder', [ProductImageController::class, 'reorder']);
    Route::patch('/product-images/{image}/set-primary', [ProductImageController::class, 'setPrimary']);
});


Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart/add', [CartController::class, 'add']);
Route::put('/cart/items/{cartItem}', [CartController::class, 'update']);
Route::delete('/cart/items/{cartItem}', [CartController::class, 'remove']);
Route::delete('/cart/clear', [CartController::class, 'clear']);
Route::post('/cart/merge', [CartController::class, 'merge'])->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::get('/favorites/ids', [FavoriteController::class, 'ids']);
    Route::post('/favorites/{product}', [FavoriteController::class, 'add']);
    Route::delete('/favorites/{product}', [FavoriteController::class, 'remove']);
    Route::get('/favorites/{product}/check', [FavoriteController::class, 'check']);
});

Route::middleware('auth')->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::put('/orders/{order}/payment-status', [OrderController::class, 'updatePaymentStatus']);
    Route::get('/bonus-info', [OrderController::class, 'getBonusInfo']);
    // Админские маршруты
    Route::get('/admin/orders', [OrderController::class, 'adminIndex']);
    Route::put('/admin/orders/{order}/status', [OrderController::class, 'updateStatus']);
    Route::put('/admin/orders/{order}/payment-status', [OrderController::class, 'updatePaymentStatus']);
    Route::get('/admin/orders/export/excel', [OrderController::class, 'exportExcel'])->middleware('auth');
    Route::get('/admin/orders/export/detailed', [OrderController::class, 'exportDetailedExcel'])->middleware('auth');
    // Платежи
    Route::post('/payments/create', [PaymentController::class, 'createPayment']);
    Route::post('/payments/check', [PaymentController::class, 'checkPayment']);
});

// Webhook (без авторизации, для ЮKassa)
Route::post('/payments/webhook', [PaymentController::class, 'webhook']);



// Публичные маршруты для отзывов
Route::get('/products/{product}/reviews', [ReviewController::class, 'index']);

// Маршруты для авторизованных пользователей
Route::middleware('auth')->group(function () {
    Route::get('/products/{product}/reviews/user', [ReviewController::class, 'userReview']);
    Route::post('/products/{product}/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);
    Route::get('/products/{product}/review-permission', [ReviewController::class, 'reviewPermission']);
});

// Админские маршруты (админ/менеджер)
Route::middleware('auth')->group(function () {
    Route::get('/admin/reviews', [ReviewController::class, 'adminIndex']);
    Route::patch('/admin/reviews/{review}/approve', [ReviewController::class, 'approve']);
    Route::delete('/admin/reviews/{review}/reject', [ReviewController::class, 'reject']);
});






