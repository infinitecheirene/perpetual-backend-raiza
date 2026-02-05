<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BusinessPartnerController;
use App\Http\Controllers\Api\CommunityController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\GoalsController;
use App\Http\Controllers\Api\LegitimacyController;
use App\Http\Controllers\Api\MissionAndVisionController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\ObjectiveController;
use App\Http\Controllers\Api\OfficeContactController;
use App\Http\Controllers\Api\SubscriberController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\JuanTapController;
use App\Http\Controllers\Api\VlogController;
use App\Http\Controllers\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes - NO /api prefix needed (Laravel adds it automatically)
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::middleware('auth:sanctum')->group(function () {
    // Shortcut route for PDF export
    Route::get('/export-pdf', [UserController::class, 'exportPDF']);

    // Original routes
    Route::get('/users/statistics', [UserController::class, 'statistics']);
    Route::get('/users/export/pdf', [UserController::class, 'exportPDF']);
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::patch('/users/{id}/status', [UserController::class, 'updateStatus']);
});

// ===================================
// APPLICATION ROUTES

// Public route - Get approved business partners (no auth required)
Route::get('business-partners', [BusinessPartnerController::class, 'index']);

// MEMBERS
// Protected routes - Require authentication
Route::middleware('auth:sanctum')->group(function () {
    // Member routes for business partners
    Route::prefix('user')->group(function () {
        Route::get('business-partners', [BusinessPartnerController::class, 'userIndex']);
    });

    Route::post('business-partners', [BusinessPartnerController::class, 'store']);
    Route::put('business-partners/{id}', [BusinessPartnerController::class, 'userUpdate']);
    Route::delete('business-partners/{id}', [BusinessPartnerController::class, 'userDestroy']);

    // Admin routes for business partners
    Route::prefix('admin')->group(function () {
        Route::get('business-partners', [BusinessPartnerController::class, 'adminIndex']);
        Route::put('business-partners/{id}', [BusinessPartnerController::class, 'adminUpdate']);
        Route::delete('business-partners/{id}', [BusinessPartnerController::class, 'adminDestroy']);
    });
});

// ===================================
// COMMUNITY ROUTES

// PUBLIC route - Get community data (no auth required)
Route::get('our-community', [CommunityController::class, 'index']);

// PROTECTED routes - Require authentication
Route::middleware('auth:sanctum')->prefix('our-community')->group(function () {
    Route::get('show', [CommunityController::class, 'show']);
    Route::post('/', [CommunityController::class, 'store']);
    Route::put('/', [CommunityController::class, 'update']);
    Route::delete('/', [CommunityController::class, 'destroy']);
});

// ===================================
// LEGITIMACY ROUTES

Route::middleware('auth:sanctum')->group(function () {
    // member legitimacy request routes
    Route::get('legitimacy', [LegitimacyController::class, 'userIndex']);
    Route::post('legitimacy', [LegitimacyController::class, 'userStore']);
    Route::put('legitimacy/{id}', [LegitimacyController::class, 'userUpdate']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    // Admin routes
    Route::prefix('admin')->group(function () {
        Route::get('/legitimacy', [LegitimacyController::class, 'adminIndex']);
        Route::post('/legitimacy', [LegitimacyController::class, 'adminStore']);
        Route::put('/legitimacy/{id}', [LegitimacyController::class, 'adminUpdate']);
        Route::post('/legitimacy/{id}', [LegitimacyController::class, 'adminUpdate']); // For form data with _method
        Route::delete('/legitimacy/{id}', [LegitimacyController::class, 'adminDestroy']);
    });
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/admin/legitimacy/{id}/pdf', [LegitimacyController::class, 'generatePDF']);
});

// ===================================
// CONTACT ROUTES

Route::post('/contacts', [ContactController::class, 'store']);

// Protected routes (add your authentication middleware)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/contacts', [ContactController::class, 'index']);
    Route::get('/contacts/{id}', [ContactController::class, 'show']);
    Route::patch('/contacts/{id}/status', [ContactController::class, 'updateStatus']);
    Route::post('/admin/contacts/{id}/reply', [ContactController::class, 'reply']);
});

// ===================================
// NEWS ROUTES

Route::prefix('news')->group(function () {
    Route::get('/published', [NewsController::class, 'published']);
    Route::get('/published/{id}', [NewsController::class, 'show']);
});

// Admin routes - Require authentication
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('admin')->group(function () {
        Route::prefix('news')->group(function () {
            Route::get('/', [NewsController::class, 'index']);
            Route::post('/', [NewsController::class, 'store']);
            Route::get('/{id}', [NewsController::class, 'show']);
            Route::post('/{id}', [NewsController::class, 'update']);
            Route::put('/{id}', [NewsController::class, 'update']);
            Route::delete('/{id}', [NewsController::class, 'destroy']);
        });
    });
});

// ===================================
// ANNOUNCEMENT ROUTES

Route::get('/announcements', [AnnouncementController::class, 'index']);
Route::get('/announcements/published', [AnnouncementController::class, 'published']);
Route::get('/announcements/{id}', [AnnouncementController::class, 'show']);

// Protected routes - admin only
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/announcements', [AnnouncementController::class, 'store']);
    Route::patch('/announcements/{id}', [AnnouncementController::class, 'update']);
    Route::delete('/announcements/{id}', [AnnouncementController::class, 'destroy']);
    Route::post('/announcements/{id}/toggle-active', [AnnouncementController::class, 'toggleActive']);
});

// ===================================
// SUBSCRIBER ROUTES

Route::get('/subscribers/active', [SubscriberController::class, 'getActiveSubscribers']);
Route::prefix('subscribers')->group(function () {
    Route::post('subscribe', [SubscriberController::class, 'subscribe']);
    Route::get('verify/{token}', [SubscriberController::class, 'verify']);
    Route::get('unsubscribe/{token}', [SubscriberController::class, 'unsubscribe']);
    Route::get('active', [SubscriberController::class, 'getActiveSubscribers']);
});

// Admin subscriber routes (protected)
Route::middleware(['auth:sanctum'])->prefix('admin/subscribers')->group(function () {
    Route::get('/', [SubscriberController::class, 'index']);
    Route::get('statistics', [SubscriberController::class, 'statistics']);
    Route::delete('{id}', [SubscriberController::class, 'destroy']);
});

// ===================================
// GOALS ROUTES

// Public route - Get goals data
Route::get('goals', [GoalsController::class, 'show']);

// Protected admin routes - require authentication
Route::middleware('auth:sanctum')->prefix('goals')->group(function () {
    Route::post('/', [GoalsController::class, 'store']);
    Route::put('/', [GoalsController::class, 'update']);
    Route::delete('/', [GoalsController::class, 'destroy']);
});

// ===================================
// MISSION AND VISION ROUTES

// Public mission and vision routes - no require authentication
Route::get('/mission-and-vision', [MissionAndVisionController::class, 'index']);

// Protected mission and vision routes - require authentication
Route::middleware('auth:sanctum')->prefix('mission-and-vision')->group(function () {
    Route::get('/admin', [MissionAndVisionController::class, 'show']);
    Route::post('/', [MissionAndVisionController::class, 'store']);
    Route::put('/', [MissionAndVisionController::class, 'update']);
    Route::delete('/', [MissionAndVisionController::class, 'destroy']);
});

// ===================================
// OBJECTIVES ROUTES

// Public route - Get objectives data
Route::get('objectives', [ObjectiveController::class, 'show']);

// Protected objectives routes - require authentication
Route::middleware('auth:sanctum')->prefix('objectives')->group(function () {
    Route::post('/', [ObjectiveController::class, 'store']);
    Route::put('/', [ObjectiveController::class, 'update']);
    Route::delete('/', [ObjectiveController::class, 'destroy']);
});

// ===================================
// OFFICE CONTACT ROUTES

// Protected office-contact routes - require authentication
Route::middleware('auth:sanctum')->prefix('office-contact')->group(function () {
    Route::get('/', [OfficeContactController::class, 'show']);
    Route::post('/', [OfficeContactController::class, 'store']);
    Route::put('/', [OfficeContactController::class, 'update']);
    Route::delete('/', [OfficeContactController::class, 'destroy']);
});

// ===================================
// VLOG ROUTES

// Public active vlogs
Route::get('/vlogs', [VlogController::class, 'index']);

Route::middleware(['auth:sanctum'])->group(function () {
    // Admin list
    Route::get('admin/vlogs', [VlogController::class, 'adminIndex']);
    //  CHUNKED UPLOAD (CREATE)
    Route::post('admin/vlogs/chunk-upload', [VlogController::class, 'uploadChunk']);
    //  CHUNKED UPLOAD (UPDATE)
    Route::post('admin/vlogs/{vlog}/chunk-upload', [VlogController::class, 'uploadChunk']);
    // Normal CRUD (optional fallback)
    Route::post('admin/vlogs', [VlogController::class, 'store']);
    Route::put('admin/vlogs/{vlog}', [VlogController::class, 'update']);
    Route::delete('admin/vlogs/{vlog}', [VlogController::class, 'destroy']);
});

// ===================================
// GALLERY ROUTES

Route::get('/galleries', [GalleryController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('admin/galleries', [GalleryController::class, 'store']);
    Route::put('admin/galleries/{id}', [GalleryController::class, 'update']);
    Route::delete('admin/galleries/{id}', [GalleryController::class, 'destroy']);
});

// JUANTAP ROUTES 

Route::middleware('auth:sanctum')->group(function () {
    Route::get('juantap', [JuanTapController::class, 'show']);
    Route::post('juantap', [JuanTapController::class, 'store']);
    Route::put('juantap/{id}', [JuanTapController::class, 'update']);
    Route::delete('juantap/{id}', [JuanTapController::class, 'destroy']);
});

// MEMBER PROFILE ROUTES

Route::middleware('auth:sanctum')->group(function () {
    Route::get('member/profile', [UserController::class, 'me']);           // read-only user
    Route::get('/member/profile', [UserController::class, 'getProfile']);
    Route::put('member/profile', [UserController::class, 'updateProfile']);  // editable
    Route::get('member/profile/{id}', [UserController::class, 'show']);       // read-only by id
});

// ===================================
// EVENT ROUTES

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/events', [EventController::class, 'store']);
    Route::get('/events', [EventController::class, 'index']);
    Route::post('/events/{id}/respond', [EventController::class, 'respond']);
    Route::middleware('auth:sanctum')->get('/events/invites', [EventController::class, 'getInvites']);
});
