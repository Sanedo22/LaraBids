<?php

use Illuminate\Support\Facades\Route;

// Website Controllers
use App\Http\Controllers\Api\Website\AuthController;
use App\Http\Controllers\Api\Website\RegisterController;
use App\Http\Controllers\Api\Website\ForgotPasswordController;
use App\Http\Controllers\Api\Website\ResetPasswordController;
use App\Http\Controllers\Api\Website\WebsiteController;

// User Controllers
use App\Http\Controllers\Api\User\AuctionController;
use App\Http\Controllers\Api\User\WatchlistController;
use App\Http\Controllers\Api\User\ProfileController;
use App\Http\Controllers\Api\User\NotificationController;
use App\Http\Controllers\Api\User\BidController;

// Admin Controllers
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\AuctionController as AdminAuctionController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;


Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);

// Public Search & Filter Endpoint
Route::get('/auctions/search', [AuctionController::class, 'search']);

// Public Website Routes
Route::get('/home', [WebsiteController::class, 'index']);
Route::post('/contact', [WebsiteController::class, 'contactStore']);
Route::get('/sellers/{id}', [WebsiteController::class, 'sellerProfile']);


Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Auction Bids
    Route::get('auctions/{id}/bids', [AuctionController::class, 'bids']);
    Route::post('auctions/{id}/bid', [AuctionController::class, 'placeBid']);

    Route::apiResource('auctions', AuctionController::class, ['as' => 'api']);

    // Watchlist
    Route::get('watchlist', [WatchlistController::class, 'index']);
    Route::post('watchlist/{auction_id}', [WatchlistController::class, 'store']);
    Route::delete('watchlist/{auction_id}', [WatchlistController::class, 'destroy']);

    // User Profile Routes
    Route::prefix('user')->name('api.user.')->group(function () {
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/avatar', [ProfileController::class, 'uploadAvatar'])->name('avatar.upload');
        Route::delete('/avatar', [ProfileController::class, 'deleteAvatar'])->name('avatar.delete');
        Route::put('/password', [ProfileController::class, 'changePassword'])->name('password.change');
        Route::get('/stats', [ProfileController::class, 'stats'])->name('stats');
    });

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/clear-all', [NotificationController::class, 'clearAll']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
    });

    // Bidding History
    Route::get('/user/bids', [BidController::class, 'index'])->name('api.user.bids.index');
    Route::get('/user/bids/active', [BidController::class, 'active'])->name('api.user.bids.active');
    Route::get('user/bids/won', [BidController::class, 'won'])->name('api.user.bids.won');
    Route::get('/user/bids/lost', [BidController::class, 'lost'])->name('api.user.bids.lost');
    Route::get('/user/auctions', [AuctionController::class, 'managedAuctions'])->name('api.user.auctions');



    // Admin API Routes
    Route::middleware(['role:admin|super admin'])->prefix('admin')->group(function () {

            // Dashboard
            Route::get('/dashboard', [AdminDashboardController::class, 'index']);

            // Category Management
            Route::apiResource('_categories', AdminCategoryController::class);
            // Auction Management
            Route::apiResource('_auctions', AdminAuctionController::class);
            // User Management
            Route::apiResource('_users', AdminUserController::class);
            // Contact Management
            Route::apiResource('_contacts', AdminContactController::class);

            // Custom Category Actions
            Route::post('_categories/{id}/restore', [AdminCategoryController::class, 'restore']);
            Route::delete('_categories/{id}/force-delete', [AdminCategoryController::class, 'forceDelete']);

            // Custom Auction Actions
            Route::post('_auctions/{id}/restore', [AdminAuctionController::class, 'restore']);
            Route::delete('_auctions/{id}/force-delete', [AdminAuctionController::class, 'forceDelete']);
            Route::post('_auctions/{id}/approve', [AdminAuctionController::class, 'approve']);
            Route::post('_auctions/{id}/cancel', [AdminAuctionController::class, 'cancel']);

            // Custom User Actions
            Route::post('_users/{id}/restore', [AdminUserController::class, 'restore']);
            Route::delete('_users/{id}/force-delete', [AdminUserController::class, 'forceDelete']);

            // Custom Contact Actions
            Route::post('_contacts/{id}/restore', [AdminContactController::class, 'restore']);
            Route::delete('_contacts/{id}/force-delete', [AdminContactController::class, 'forceDelete']);
        });
});
