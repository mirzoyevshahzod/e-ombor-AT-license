<?php

use App\Http\Controllers\EomborController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('e-ombor');
});

Route::post('/e-ombor-login', [EomborController::class, 'loginPage'])->name('e-ombor-login');
