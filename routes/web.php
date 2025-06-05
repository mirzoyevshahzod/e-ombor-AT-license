<?php

use App\Http\Controllers\EomborController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScrapingController;

Route::get('/', function () {
    return view('e-ombor');
});

// Route::get('/quotes', [ScrapingController::class, 'viewQuotes']);
// 
// routes/web.php

// Route::get('/quotes', [ScrapingController::class, 'viewQuotes'])->name('quotes.index');
// Route::get('/quotes/export', [ScrapingController::class, 'export'])->name('quotes.export');

Route::post('/e-ombor-login', [EomborController::class, 'loginPage'])->name('e-ombor-login');
