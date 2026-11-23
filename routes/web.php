<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategorySubscriptionController;
use App\Http\Controllers\EditorialController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\ListingInteractionController;
use App\Http\Controllers\SearchIndexController;
use App\Http\Controllers\StorefrontMenuController;
use Illuminate\Support\Facades\Route;

Route::get('/editorial-images/{page}/{language}/{slot}', [EditorialController::class, 'image'])
    ->where('slot', 'cover|work-[0-3]')->name('editorial.image');

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
    Route::get('/subscriptions', [ListingController::class, 'index'])->name('subscriptions.index');
    Route::controller(CategorySubscriptionController::class)
        ->prefix('/categories/{category}/subscription')->where(['category' => '[a-z][a-z0-9-]{0,22}'])
        ->name('subscriptions.')->group(function () {
            Route::get('/', 'participate')->name('participate');
            Route::put('/', 'store')->name('store');
            Route::delete('/', 'destroy')->name('destroy');
        });

    Route::get('/items/{listing}/participate', [ListingInteractionController::class, 'participate'])->name('listings.participate');
    Route::put('/items/{listing}/like', [ListingInteractionController::class, 'like'])->middleware('throttle:30,1')->name('listings.like');
    Route::delete('/items/{listing}/like', [ListingInteractionController::class, 'unlike'])->middleware('throttle:30,1')->name('listings.unlike');
    Route::post('/items/{listing}/comments', [ListingInteractionController::class, 'comment'])->middleware('throttle:10,1')->name('listings.comment');
    Route::get('/my-items', [ListingController::class, 'mine'])->name('listings.mine');
    Route::get('/sell', [ListingController::class, 'create'])->name('listings.create');
    Route::post('/items', [ListingController::class, 'store'])->middleware('throttle:10,1')->name('listings.store');
    Route::get('/items/{listing}/edit', [ListingController::class, 'edit'])->can('update', 'listing')->name('listings.edit');
    Route::put('/items/{listing}', [ListingController::class, 'update'])->can('update', 'listing')->middleware('throttle:10,1')->name('listings.update');
    Route::delete('/items/{listing}', [ListingController::class, 'destroy'])->can('delete', 'listing')->name('listings.destroy');
    Route::post('/logout', [AccountController::class, 'logout'])->name('logout');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'can:manage-marketplace'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/menu', [StorefrontMenuController::class, 'edit'])->name('menu.edit');
    Route::put('/menu', [StorefrontMenuController::class, 'update'])->name('menu.update');
    Route::post('/menu/categories/create', [StorefrontMenuController::class, 'createCategory'])->name('menu.categories.create');
    Route::post('/menu/categories', [StorefrontMenuController::class, 'addCategory'])->name('menu.categories.store');
    Route::delete('/menu/categories/{item}', [StorefrontMenuController::class, 'removeCategory'])
        ->where('item', 'category_[a-z][a-z0-9-]{0,22}')->name('menu.categories.destroy');
    Route::patch('/catalog/categories/order', [StorefrontMenuController::class, 'orderCategories'])->name('catalog.categories.order');
    Route::delete('/catalog/categories/{category}', [StorefrontMenuController::class, 'deleteCategory'])
        ->where('category', '[a-z][a-z0-9-]{0,22}')->name('catalog.categories.destroy');
    Route::get('/content/{page?}/{language?}', [EditorialController::class, 'edit'])->name('editorial.edit');
    Route::put('/content/{page}/{language}', [EditorialController::class, 'update'])->name('editorial.update');
    Route::patch('/users/{user}', [AdminController::class, 'account'])->name('users.update');
    Route::patch('/listings/{listing}', [AdminController::class, 'listing'])->name('listings.update');
    Route::patch('/comments/{comment}', [AdminController::class, 'comment'])->name('comments.update');
});
