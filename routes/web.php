<?php

use App\Http\Controllers;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

require __DIR__ . '/auth.php';

// home
Route::get('/', [Controllers\HomeController::class, 'index'])
    ->name('home');

// image sources
Route::get('/img/{path}', [Controllers\ImageController::class, 'show'])
    ->where('path', '.*')
    ->name('image.show');

// listings
Route::get('/listings', [Controllers\ListingController::class, 'index'])
    ->name('listing.index');

Route::get('/listings/create', [Controllers\ListingController::class, 'create'])
    ->name('listing.create');

Route::get('/listings/{listing:nanoid}', [Controllers\ListingController::class, 'show'])
    ->name('listing.show');

Route::post('/listings', [Controllers\ListingController::class, 'store'])
    ->name('listing.store');

Route::get('/listings/{listing:nanoid}/edit', [Controllers\ListingController::class, 'edit'])
    ->middleware(['auth', 'verified', 'can:update,listing'])
    ->name('listing.edit');

Route::put('/listings/{listing:nanoid}', [Controllers\ListingController::class, 'update'])
    ->middleware(['auth', 'verified', 'can:update,listing'])
    ->name('listing.update');

Route::get('/listings/{listing:nanoid}/apply', [Controllers\ListingController::class, 'apply'])
    ->name('listing.apply');

Route::get('/my-listings', [Controllers\ListingController::class, 'indexOwned'])
    ->middleware(['auth', 'verified'])
    ->name('listing.indexOwned');

// companies
Route::get('/companies', [Controllers\CompanyController::class, 'index'])
    ->name('company.index');

Route::get('/companies/create', [Controllers\CompanyController::class, 'create'])
    ->middleware(['auth', 'verified'])
    ->name('company.create');

Route::get('/companies/{company:nanoid}', [Controllers\CompanyController::class, 'show'])
    ->name('company.show');

Route::post('/companies', [Controllers\CompanyController::class, 'store'])
    ->name('company.store');

Route::get('/companies/{company:nanoid}/edit', [Controllers\CompanyController::class, 'edit'])
    ->middleware(['auth', 'verified', 'can:update,company'])
    ->name('company.edit');

Route::put('/companies/{company:nanoid}/', [Controllers\CompanyController::class, 'update'])
    ->middleware(['auth', 'verified', 'can:update,company'])
    ->name('company.update');

Route::get('/my-companies', [Controllers\CompanyController::class, 'indexOwned'])
    ->middleware(['auth', 'verified'])
    ->name('company.indexOwned');
