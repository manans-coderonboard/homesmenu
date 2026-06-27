<?php

use App\Http\Controllers\Api\Admin\AdminPropertyController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\MetaController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\PropertyDealerController;
use App\Http\Controllers\Api\ReferralController;
use App\Http\Controllers\Api\SavedPropertyController;
use App\Http\Controllers\Api\SiteVisitController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HomesMenu — Headless API (Sanctum Bearer tokens)
|--------------------------------------------------------------------------
*/

// ---------- Public ----------
Route::get('meta', [MetaController::class, 'index']);

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::get('auth/google/redirect', [GoogleAuthController::class, 'redirect']);
Route::get('auth/google/callback', [GoogleAuthController::class, 'callback']);

Route::get('properties', [PropertyController::class, 'index']);
Route::get('properties/featured', [PropertyController::class, 'featured']);
Route::get('properties/{property}', [PropertyController::class, 'show']);
Route::get('properties/{property}/panoramas', [PropertyController::class, 'panoramas']);
Route::post('properties/{property}/enquire', [LeadController::class, 'store']);
Route::post('properties/{property}/site-visits', [SiteVisitController::class, 'store']);

// ---------- Authenticated ----------
Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    // Profile & wallet
    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);
    Route::post('profile/avatar', [ProfileController::class, 'avatar']);

    Route::get('wallet', [WalletController::class, 'show']);
    Route::post('wallet/withdraw', [WalletController::class, 'withdraw']);

    // Refer & Earn
    Route::get('referrals', [ReferralController::class, 'index']);
    Route::post('referrals', [ReferralController::class, 'store']);

    // Offline transactions (initiate -> confirm receipt)
    Route::get('payments', [PaymentController::class, 'index']);
    Route::post('payments', [PaymentController::class, 'store']);
    Route::patch('payments/{payment}/confirm', [PaymentController::class, 'confirm']);
    Route::patch('payments/{payment}/dispute', [PaymentController::class, 'dispute']);
    Route::patch('payments/{payment}/cancel', [PaymentController::class, 'cancel']);

    // Saved / shortlist
    Route::get('saved', [SavedPropertyController::class, 'index']);
    Route::post('saved/{property}', [SavedPropertyController::class, 'store']);
    Route::delete('saved/{property}', [SavedPropertyController::class, 'destroy']);

    // Listings owned by the user + create/update/delete
    Route::get('my/properties', [PropertyController::class, 'mine']);
    Route::post('properties', [PropertyController::class, 'store']);
    Route::put('properties/{property}', [PropertyController::class, 'update']);
    Route::delete('properties/{property}', [PropertyController::class, 'destroy']);
    Route::post('uploads', [UploadController::class, 'store']);

    // Dealer multi-listing: pick up admin inventory & list your contact details
    Route::get('dealer/available-properties', [PropertyDealerController::class, 'available']);
    Route::get('dealer/my-listings', [PropertyDealerController::class, 'myListings']);
    Route::post('properties/{property}/dealers', [PropertyDealerController::class, 'join']);
    Route::delete('properties/{property}/dealers', [PropertyDealerController::class, 'leave']);

    // ---- Super-admin portal ----
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('stats', [AdminPropertyController::class, 'stats']);
        Route::get('properties', [AdminPropertyController::class, 'index']);
        Route::get('properties/{property}', [AdminPropertyController::class, 'show']);
        Route::put('properties/{property}', [AdminPropertyController::class, 'update']);
        Route::patch('properties/{property}/approve', [AdminPropertyController::class, 'approve']);
        Route::patch('properties/{property}/reject', [AdminPropertyController::class, 'reject']);
    });

    // Seller / dealer inbox
    Route::get('leads', [LeadController::class, 'index']);
    Route::patch('leads/{lead}', [LeadController::class, 'update']);
    Route::get('site-visits', [SiteVisitController::class, 'index']);

    // Role dashboards (buyer | seller | dealer)
    Route::get('dashboard/{role}', [DashboardController::class, 'show']);
});
