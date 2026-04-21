<?php

use App\Http\Controllers\BadgeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ZkLoginController;
use App\Http\Controllers\Auth\PasswordController;

// Public Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [RegisterController::class, 'showRegister'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// ZkLogin Routes (Web3)
Route::post('/auth/zklogin', [ZkLoginController::class, 'authenticate']);

// Protected
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Default dashboard mapped to existing logic
    Route::get('/dashboard', [TransactionController::class, 'index'])->name('dashboard');
    Route::get('/badges', [BadgeController::class, 'index'])->name('badges');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
    Route::get('/user', [UserController::class, 'index'])->name('user');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

    // Admin guarded routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        // e.g. Route::get('/users', [AdminController::class, 'index'])->name('users');
    });
});
