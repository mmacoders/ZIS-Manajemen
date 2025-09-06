<?php

use Illuminate\Support\Facades\Route;

// SPA Route - catch all routes and serve Vue.js app
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');
