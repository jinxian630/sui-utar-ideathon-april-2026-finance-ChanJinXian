<?php

use App\Http\Controllers\BadgeController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuiSyncController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletWelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\ZkLoginController;

// Public Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/register', [RegisterController::class, 'showRegister'])->name('register');

// ZkLogin Routes (Web3)
Route::post('/auth/zklogin/status', [ZkLoginController::class, 'status']);
Route::post('/auth/zklogin', [ZkLoginController::class, 'authenticate']);

// Protected
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('role:user,admin')->group(function () {
        // Default dashboard mapped to existing logic
        Route::get('/dashboard', [TransactionController::class, 'index'])->name('dashboard');
        Route::get('/verify-email', EmailVerificationPromptController::class)->name('verification.notice');
        Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');
        Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('verification.send');

        Route::get('/badges', [BadgeController::class, 'index'])->name('badges');
        Route::post('/api/chat', [ChatController::class, 'store'])->name('api.chat.store');
        Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
        Route::delete('/transactions/{id}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
        Route::get('/user', [UserController::class, 'index'])->name('user');

        Route::resource('savings', \App\Http\Controllers\SavingsController::class)->only(['index', 'create', 'edit']);
        Route::resource('savings', \App\Http\Controllers\SavingsController::class)
            ->only(['store', 'update', 'destroy'])
            ->middleware('wallet.linked');

        Route::post('/sui/profile', [SuiSyncController::class, 'storeProfile'])
            ->middleware('wallet.linked')
            ->name('sui.profile.store');
        Route::post('/sui/savings/{entry}/mark-on-chain', [SuiSyncController::class, 'markEntryOnChain'])
            ->middleware('wallet.linked')
            ->name('sui.savings.mark-on-chain');

        Route::resource('goals', \App\Http\Controllers\GoalController::class)
            ->only(['store', 'update', 'destroy'])
            ->middleware('wallet.linked');
        Route::post('/goals/{goal}/withdraw', [\App\Http\Controllers\GoalController::class, 'withdraw'])
            ->middleware('wallet.linked')
            ->name('goals.withdraw');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::patch('/profile/pin', [ProfileController::class, 'updatePin'])->name('profile.pin.update');
        Route::post('/profile/verify-pin', [ProfileController::class, 'verifyPin'])->name('profile.pin.verify');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::get('/wallet/welcome', [WalletWelcomeController::class, 'show'])
            ->middleware('wallet.linked')
            ->name('wallet.welcome');
        Route::post('/wallet/welcome/complete', [WalletWelcomeController::class, 'complete'])
            ->middleware('wallet.linked')
            ->name('wallet.welcome.complete');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/analytics', [AdminController::class, 'index'])->name('analytics');
    });
});
