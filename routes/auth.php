<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\LinkSteamController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\LocalAccountController;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

// Distinct entry pages; authentication still uses the existing Discord flow.
Route::view('login', 'pages.login')->name('auth.login')->middleware('guest');
Route::post('login', [LocalAccountController::class, 'login'])->name('auth.local.login')->middleware(['guest', 'throttle:5,1']);
Route::view('register', 'pages.register')->name('auth.register')->middleware('guest');
Route::post('register', [LocalAccountController::class, 'register'])->name('auth.local.register')->middleware(['guest', 'throttle:3,1']);
Route::get('verify-email/{user}', [LocalAccountController::class, 'verify'])->name('auth.local.verify')->middleware(['guest', 'signed', 'throttle:10,1']);
Route::post('verify-email/resend', [LocalAccountController::class, 'resend'])->name('auth.local.resend')->middleware(['guest', 'throttle:3,1']);

Route::get('login/discord', function () {
    return Socialite::driver('discord')->redirect();
})->name('auth.discord')->middleware('guest');

Route::get('login/discord/handle', [AuthController::class, 'login'])->middleware('guest');

Route::get('link/steam', function () {
    return Socialite::driver('steam')->redirect();
})->name('auth.steam')->middleware('guest');

Route::get('/link/steam/handle', [LinkSteamController::class, 'link'])->middleware('guest');

Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

Route::resource('account', AccountController::class, ['parameters' => ['account' => 'user']]);
