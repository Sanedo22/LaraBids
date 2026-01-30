<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\AuctionController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AuctionController as AdminAuctionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BidController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;

// Public Routes

// Public Website Routes
Route::get('/', [WebsiteController::class, 'index'])->name('home');
Route::get('/about', [WebsiteController::class, 'about'])->name('about');
Route::get('/contact', [WebsiteController::class, 'contact'])->name('contact');
Route::post('/contact', [WebsiteController::class, 'contactStore'])->name('contact.store');

// Auction Routes (Public)
Route::get('/auctions', [AuctionController::class, 'index'])->name('auctions.index');
Route::get('/search', [AuctionController::class, 'search'])->name('auctions.search');


// Smart Dashboard Redirect
Route::get('/dashboard', [WebsiteController::class, 'dashboard'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Profile Routes

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Auction Creation Routes
    Route::get('/auctions/create', [AuctionController::class, 'create'])->name('auctions.create');
    Route::post('/auctions', [AuctionController::class, 'store'])->name('auctions.store');

    // User Dashboard Routes
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
        Route::get('/my-bids', [UserDashboardController::class, 'myBids'])->name('my-bids');
        Route::get('/winning-items', [UserDashboardController::class, 'winningItems'])->name('winning-items');
        Route::get('/wishlist', [UserDashboardController::class, 'wishlist'])->name('wishlist');
        Route::get('/profile', [UserDashboardController::class, 'profile'])->name('profile');
    });
});

// Parameterized Routes (Catch-all for /auctions/ prefix)
Route::get('/auctions/{id}', [AuctionController::class, 'show'])->name('auctions.show');


// Admin Routes
Route::middleware(['auth', 'role:admin|super admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Auctions
        Route::get('/auctions', [AdminAuctionController::class, 'index'])->name('auctions.index');
        Route::get('/auctions/{auction}', [AdminAuctionController::class, 'show'])->name('auctions.show');
        Route::post('/auctions/{auction}/restore', [AdminAuctionController::class, 'restore'])->name('auctions.restore');
        Route::post('/auctions/{auction}/approve', [AdminAuctionController::class, 'approve'])->name('auctions.approve');
        Route::delete('/auctions/{auction}/force-delete', [AdminAuctionController::class, 'forceDelete'])->name('auctions.force_delete');
        Route::delete('/auctions/{auction}', [AdminAuctionController::class, 'destroy'])->name('auctions.destroy');
        Route::post('/auctions/{auction}/cancel', [AdminAuctionController::class, 'cancel'])->name('auctions.cancel');

        // Bids
        Route::get('/bids', [BidController::class, 'index'])->name('bids.index');

        /*
        |--------------------------------------------------------------------------
        | Users CRUD (FULL: index, create, store, show, edit, update, delete)
        |--------------------------------------------------------------------------
        */
        Route::resource('users', UserController::class);

        // Soft delete extras
        Route::post('users/{user}/restore', [UserController::class, 'restore'])
            ->name('users.restore');

        Route::delete('users/{user}/force-delete', [UserController::class, 'forceDelete'])
            ->name('users.force_delete');

        // Payments
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports');

        // Categories
        Route::post('categories/toggle-status', [CategoryController::class, 'toggleStatus'])
            ->name('categories.toggle_status');

        Route::post('categories/{category}/restore', [CategoryController::class, 'restore'])
            ->name('categories.restore');
            
        Route::delete('categories/{category}/force-delete', [CategoryController::class, 'forceDelete'])
            ->name('categories.force_delete');
            
        Route::resource('categories', CategoryController::class);

        // Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings');

        // Admin Profile
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile');
        Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');

        // Blank Page
        Route::get('/blank', [DashboardController::class, 'blank'])->name('blank');
    });

// Auth Routes

require __DIR__.'/auth.php';
