<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\ListingInteractionController;
use App\Http\Controllers\SearchIndexController;
use Illuminate\Support\Facades\Route;

Route::get('/robots.txt', [SearchIndexController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SearchIndexController::class, 'sitemap'])->name('sitemap');

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
    Route::get('/items/{listing}/participate', [ListingInteractionController::class, 'participate'])->name('listings.participate');
    Route::put('/items/{listing}/like', [ListingInteractionController::class, 'like'])->middleware('throttle:30,1')->name('listings.like');
    Route::delete('/items/{listing}/like', [ListingInteractionController::class, 'unlike'])->middleware('throttle:30,1')->name('listings.unlike');
    Route::post('/items/{listing}/comments', [ListingInteractionController::class, 'comment'])->middleware('throttle:10,1')->name('listings.comment');
    Route::get('/my-items', [ListingController::class, 'mine'])->name('listings.mine');
    Route::view('/sell', 'sell', ['listing' => null])->name('listings.create');
    Route::post('/items', [ListingController::class, 'store'])->middleware('throttle:10,1')->name('listings.store');
    Route::get('/items/{listing}/edit', [ListingController::class, 'edit'])->can('update', 'listing')->name('listings.edit');
    Route::put('/items/{listing}', [ListingController::class, 'update'])->can('update', 'listing')->middleware('throttle:10,1')->name('listings.update');
    Route::delete('/items/{listing}', [ListingController::class, 'destroy'])->can('delete', 'listing')->name('listings.destroy');
    Route::post('/logout', [AccountController::class, 'logout'])->name('logout');
});
