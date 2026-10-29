<?php

use Illuminate\Support\Facades\Route;

// ColCampus API - CI/CD FTP Deployment Test (colcampusapi.lavoixdabouloussi.org)
Route::get('/', function () {
    return view('welcome');
});
