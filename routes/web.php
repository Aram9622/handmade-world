<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ListingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ListingController::class, 'index'])->name('catalog');
Route::get('/items/{listing}/photo', [ListingController::class, 'photo'])->name('listings.photo');
Route::get('/items/{listing}', [ListingController::class, 'show'])->name('listings.show');

Route::middleware('guest')->group(function () {
    Route::view('/login', 'account', ['register' => false])->name('login');
    Route::view('/register', 'account', ['register' => true])->name('register');
    Route::post('/login', [AccountController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/register', [AccountController::class, 'register'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::view('/sell', 'sell')->name('listings.create');
    Route::post('/items', [ListingController::class, 'store'])->middleware('throttle:10,1')->name('listings.store');
    Route::delete('/items/{listing}', [ListingController::class, 'destroy'])->name('listings.destroy');
    Route::post('/logout', [AccountController::class, 'logout'])->name('logout');
});
