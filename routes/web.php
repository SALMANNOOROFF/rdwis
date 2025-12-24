<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});


Route::get('/openproject', function () {
    return view('openproject');
})->name('openproject');

Route::get('/viewproject', function () {
    return view('viewproject');
})->name('viewproject');

