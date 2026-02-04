<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminOrderController;
use App\Http\Controllers\Api\BusinessPartnerController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CommunityController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\GoalsController;
use App\Http\Controllers\Api\LegitimacyController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\MerchandizeController;
use App\Http\Controllers\Api\MissionAndVisionController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\ObjectiveController;
use App\Http\Controllers\Api\OfficeContactController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SubscriberController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\JuanTapController;
use App\Http\Controllers\Api\VlogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

// ── Auth / user ───────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/user', fn(Request $r) => $r->user());
    Route::get('/auth/account', [AuthController::class, 'account']);
});

// ── User order routes ─────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/orders', [OrderController::class, 'index']);
    Route::get('/users/orders/{id}', [OrderController::class, 'show']);
    Route::post('/users/orders', [OrderController::class, 'store']);
    Route::post('/users/orders/{orderCode}/cancel', [OrderController::class, 'cancel']);
});

// ── Admin order routes ────────────────────────────────────────────────────
// Single block.  The duplicate Route::patch below has been removed.
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::get('/orders/{id}', [AdminOrderController::class, 'show']);

        // Update status of an order
        Route::post('/orders/{orderCode}/status', [AdminOrderController::class, 'updateStatus']);


    });
});



// ── User management ───────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/export-pdf', [UserController::class, 'exportPDF']);
    Route::get('/users/statistics', [UserController::class, 'statistics']);
    Route::get('/users/export/pdf', [UserController::class, 'exportPDF']);
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::patch('/users/{id}/status', [UserController::class, 'updateStatus']);
});

// ===================================
// BUSINESS PARTNERS

Route::get('business-partners', [BusinessPartnerController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('user')->group(function () {
        Route::get('business-partners', [BusinessPartnerController::class, 'userIndex']);
    });
    Route::post('business-partners', [BusinessPartnerController::class, 'store']);
    Route::put('business-partners/{id}', [BusinessPartnerController::class, 'userUpdate']);
    Route::delete('business-partners/{id}', [BusinessPartnerController::class, 'userDestroy']);

    Route::prefix('admin')->group(function () {
        Route::get('business-partners', [BusinessPartnerController::class, 'adminIndex']);
        Route::put('business-partners/{id}', [BusinessPartnerController::class, 'adminUpdate']);
        Route::delete('business-partners/{id}', [BusinessPartnerController::class, 'adminDestroy']);
    });
});

// ===================================
// COMMUNITY

Route::get('our-community', [CommunityController::class, 'index']);

Route::middleware('auth:sanctum')->prefix('our-community')->group(function () {
    Route::get('show', [CommunityController::class, 'show']);
    Route::post('/', [CommunityController::class, 'store']);
    Route::put('/', [CommunityController::class, 'update']);
    Route::delete('/', [CommunityController::class, 'destroy']);
});

// ===================================
// LEGITIMACY

Route::middleware('auth:sanctum')->group(function () {
    Route::get('legitimacy', [LegitimacyController::class, 'userIndex']);
    Route::post('legitimacy', [LegitimacyController::class, 'userStore']);
    Route::put('legitimacy/{id}', [LegitimacyController::class, 'userUpdate']);
});

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('/legitimacy', [LegitimacyController::class, 'adminIndex']);
    Route::post('/legitimacy', [LegitimacyController::class, 'adminStore']);
    Route::put('/legitimacy/{id}', [LegitimacyController::class, 'adminUpdate']);
    Route::post('/legitimacy/{id}', [LegitimacyController::class, 'adminUpdate']);
    Route::delete('/legitimacy/{id}', [LegitimacyController::class, 'adminDestroy']);
    Route::get('/legitimacy/{id}/pdf', [LegitimacyController::class, 'generatePDF']);
});

// ===================================
// MERCHANDIZE (public)

Route::prefix('users')->group(function () {
    Route::get('/merchandize', [MerchandizeController::class, 'index']);
    Route::get('/merchandize/{id}', [MerchandizeController::class, 'show']);
    Route::get('/merchandize/categories/list', [MerchandizeController::class, 'getCategories']);
});

// ===================================
// CART

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::delete('cart/clear', [CartController::class, 'clear']);
    Route::get('/cart-count', [CartController::class, 'count']);
    Route::put('/cart/{productId}', [CartController::class, 'update']);
    Route::delete('/cart/{productId}', [CartController::class, 'destroy']);
});

// ===================================
// PRODUCTS (admin)

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::match(['put', 'patch', 'post'], '/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
});

// ===================================
// CONTACTS

Route::post('/contacts', [ContactController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/contacts', [ContactController::class, 'index']);
    Route::get('/contacts/{id}', [ContactController::class, 'show']);
    Route::patch('/contacts/{id}/status', [ContactController::class, 'updateStatus']);
    Route::post('/admin/contacts/{id}/reply', [ContactController::class, 'reply']);
});

// ===================================
// NEWS

Route::prefix('news')->group(function () {
    Route::get('/published', [NewsController::class, 'published']);
    Route::get('/published/{id}', [NewsController::class, 'show']);
});

Route::middleware('auth:sanctum')->prefix('admin/news')->group(function () {
    Route::get('/', [NewsController::class, 'index']);
    Route::post('/', [NewsController::class, 'store']);
    Route::get('/{id}', [NewsController::class, 'show']);
    Route::post('/{id}', [NewsController::class, 'update']);
    Route::put('/{id}', [NewsController::class, 'update']);
    Route::delete('/{id}', [NewsController::class, 'destroy']);
});

// ===================================
// ANNOUNCEMENTS

Route::get('/announcements', [AnnouncementController::class, 'index']);
Route::get('/announcements/published', [AnnouncementController::class, 'published']);
Route::get('/announcements/{id}', [AnnouncementController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/announcements', [AnnouncementController::class, 'store']);
    Route::patch('/announcements/{id}', [AnnouncementController::class, 'update']);
    Route::delete('/announcements/{id}', [AnnouncementController::class, 'destroy']);
    Route::post('/announcements/{id}/toggle-active', [AnnouncementController::class, 'toggleActive']);
});

// ===================================
// SUBSCRIBERS

Route::get('/subscribers/active', [SubscriberController::class, 'getActiveSubscribers']);
Route::prefix('subscribers')->group(function () {
    Route::post('subscribe', [SubscriberController::class, 'subscribe']);
    Route::get('verify/{token}', [SubscriberController::class, 'verify']);
    Route::get('unsubscribe/{token}', [SubscriberController::class, 'unsubscribe']);
    Route::get('active', [SubscriberController::class, 'getActiveSubscribers']);
});

Route::middleware('auth:sanctum')->prefix('admin/subscribers')->group(function () {
    Route::get('/', [SubscriberController::class, 'index']);
    Route::get('statistics', [SubscriberController::class, 'statistics']);
    Route::delete('{id}', [SubscriberController::class, 'destroy']);
});

// ===================================
// GOALS

Route::get('goals', [GoalsController::class, 'show']);

Route::middleware('auth:sanctum')->prefix('goals')->group(function () {
    Route::post('/', [GoalsController::class, 'store']);
    Route::put('/', [GoalsController::class, 'update']);
    Route::delete('/', [GoalsController::class, 'destroy']);
});

// ===================================
// MISSION AND VISION

Route::get('/mission-and-vision', [MissionAndVisionController::class, 'index']);

Route::middleware('auth:sanctum')->prefix('mission-and-vision')->group(function () {
    Route::get('/admin', [MissionAndVisionController::class, 'show']);
    Route::post('/', [MissionAndVisionController::class, 'store']);
    Route::put('/', [MissionAndVisionController::class, 'update']);
    Route::delete('/', [MissionAndVisionController::class, 'destroy']);
});

// ===================================
// OBJECTIVES

Route::get('objectives', [ObjectiveController::class, 'show']);

Route::middleware('auth:sanctum')->prefix('objectives')->group(function () {
    Route::post('/', [ObjectiveController::class, 'store']);
    Route::put('/', [ObjectiveController::class, 'update']);
    Route::delete('/', [ObjectiveController::class, 'destroy']);
});

// ===================================
// OFFICE CONTACT

Route::middleware('auth:sanctum')->prefix('office-contact')->group(function () {
    Route::get('/', [OfficeContactController::class, 'show']);
    Route::post('/', [OfficeContactController::class, 'store']);
    Route::put('/', [OfficeContactController::class, 'update']);
    Route::delete('/', [OfficeContactController::class, 'destroy']);
});

// ===================================
// VLOGS

Route::get('/vlogs', [VlogController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('admin/vlogs', [VlogController::class, 'adminIndex']);
    Route::post('admin/vlogs/chunk-upload', [VlogController::class, 'uploadChunk']);
    Route::post('admin/vlogs/{vlog}/chunk-upload', [VlogController::class, 'uploadChunk']);
    Route::post('admin/vlogs', [VlogController::class, 'store']);
    Route::put('admin/vlogs/{vlog}', [VlogController::class, 'update']);
    Route::delete('admin/vlogs/{vlog}', [VlogController::class, 'destroy']);
});

// ===================================
// GALLERY

Route::get('/galleries', [GalleryController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('admin/galleries', [GalleryController::class, 'store']);
    Route::put('admin/galleries/{id}', [GalleryController::class, 'update']);
    Route::delete('admin/galleries/{id}', [GalleryController::class, 'destroy']);
});

// ===================================
// JUAN TAP

Route::middleware('auth:sanctum')->group(function () {
    Route::get('juantap', [JuanTapController::class, 'show']);
    Route::post('juantap', [JuanTapController::class, 'store']);
    Route::put('juantap', [JuanTapController::class, 'update']);
    Route::delete('juantap', [JuanTapController::class, 'destroy']);
});