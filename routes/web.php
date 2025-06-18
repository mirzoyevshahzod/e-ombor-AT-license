<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EomborScrapeController;

Route::get('/', [EomborScrapeController::class, 'index'])->name('scrape.eombor');
Route::post('/', [EomborScrapeController::class, 'scrape'])->name('scrape.eombor.process');
