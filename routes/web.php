<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScrapingController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/quotes', [ScrapingController::class, 'viewQuotes']);

// routes/web.php

Route::get('/quotes', [ScrapingController::class, 'viewQuotes'])->name('quotes.index');
Route::get('/quotes/export', [ScrapingController::class, 'export'])->name('quotes.export');
