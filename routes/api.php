<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['auth:sanctum', 'api'])->group(function () {
    // Auth
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me', [AuthController::class, 'update']);

    // Addresses
    Route::get('/addresses', [AuthController::class, 'addresses']);
    Route::post('/addresses', [AuthController::class, 'storeAddress']);
    Route::put('/addresses/{id}', [AuthController::class, 'updateAddress']);
    Route::delete('/addresses/{id}', [AuthController::class, 'deleteAddress']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{orderNumber}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/orders/{orderNumber}/cancel', [OrderController::class, 'cancel']);

    // Checkout
    Route::post('/checkout/payment-intent', [CheckoutController::class, 'createPaymentIntent']);

    // Coupons
    Route::post('/products/{slug}/reviews', [ReviewController::class, 'store']);
    Route::post('/coupons/validate', [CouponController::class, 'validate']);
    Route::get('/coupons', [CouponController::class, 'index']);
});

// Cart (accessible to both guests and authenticated users)
Route::middleware(['api', 'cart.token'])->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'add']);
    Route::put('/cart/{itemId}', [CartController::class, 'update']);
    Route::delete('/cart/{itemId}', [CartController::class, 'remove']);
    Route::delete('/cart', [CartController::class, 'clear']);
    Route::get('/cart/count', [CartController::class, 'count']);
});

// Public auth routes with auth rate limiter
Route::middleware(['api', 'cart.token'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [PasswordResetController::class, 'forgot']);
    Route::post('/reset-password', [PasswordResetController::class, 'reset']);
});

// Public webhook routes (no auth required, but validated by Stripe signature)
Route::middleware('api')->group(function () {
    Route::post('/webhooks/stripe', [WebhookController::class, 'stripe']);
});

// Public product routes
Route::middleware('api')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/featured', [ProductController::class, 'featured']);
    Route::get('/products/new-arrivals', [ProductController::class, 'newArrivals']);
    Route::get('/products/on-sale', [ProductController::class, 'onSale']);
    Route::get('/products/{slug}/related', [ProductController::class, 'related']);
    Route::get('/products/{slug}/reviews', [ReviewController::class, 'index']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);

    // Public category routes
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/tree', [CategoryController::class, 'tree']);
    Route::get('/categories/{slug}', [CategoryController::class, 'show']);
    Route::get('/categories/{slug}/products', [CategoryController::class, 'products']);

    // Public brand routes
    Route::get('/brands', [BrandController::class, 'index']);
    Route::get('/brands/featured', [BrandController::class, 'featured']);
    Route::get('/brands/{slug}', [BrandController::class, 'show']);
    Route::get('/brands/{slug}/products', [BrandController::class, 'products']);

    // Public collection routes
    Route::get('/collections', [CollectionController::class, 'index']);
    Route::get('/collections/{slug}', [CollectionController::class, 'show']);
    Route::get('/collections/{slug}/products', [CollectionController::class, 'products']);
});

// Public content routes (storefront CMS data and settings)
Route::middleware('api')->group(function () {
    Route::get('/config', [ContentController::class, 'config']);
    Route::get('/banners', [ContentController::class, 'banners']);
    Route::get('/homepage', [ContentController::class, 'homepage']);
    Route::get('/menus', [ContentController::class, 'menus']);
    Route::get('/pages', [ContentController::class, 'pages']);
    Route::get('/pages/{slug}', [ContentController::class, 'page']);
    Route::get('/tags', [ContentController::class, 'tags']);
    Route::get('/colors', [ContentController::class, 'colors']);
    Route::get('/sizes', [ContentController::class, 'sizes']);
});
