<?php

use Illuminate\Support\Facades\Route;

Route::middleware('all')->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });
});
