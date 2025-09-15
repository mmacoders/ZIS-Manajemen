<?php

use Illuminate\Support\Facades\Route;

// SPA Route - catch all routes except API routes and login
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '^(?!api|login).*$');