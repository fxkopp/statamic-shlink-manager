<?php

use Fxkopp\StatamicShlinkManager\Http\Controllers\ShortUrlController;
use Illuminate\Support\Facades\Route;

Route::prefix('shlink-manager')->name('shlink-manager.')->group(function () {
    Route::get('/', [ShortUrlController::class, 'index'])->name('index');
    Route::get('/create', [ShortUrlController::class, 'create'])->name('create');
    Route::get('/servers-csv', [ShortUrlController::class, 'serversCsv'])->name('servers-csv');
    Route::post('/', [ShortUrlController::class, 'store'])->name('store');
    Route::get('/{shortCode}', [ShortUrlController::class, 'show'])->name('show')->where('shortCode', '[a-zA-Z0-9_-]+');
    Route::get('/{shortCode}/edit', [ShortUrlController::class, 'edit'])->name('edit')->where('shortCode', '[a-zA-Z0-9_-]+');
    Route::patch('/{shortCode}', [ShortUrlController::class, 'update'])->name('update')->where('shortCode', '[a-zA-Z0-9_-]+');
    Route::delete('/{shortCode}', [ShortUrlController::class, 'destroy'])->name('destroy')->where('shortCode', '[a-zA-Z0-9_-]+');
});
