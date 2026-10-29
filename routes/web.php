<?php

use Illuminate\Support\Facades\Route;

// ColCampus API - Production Deploy
Route::get('/', function () {
    return view('welcome');
});
