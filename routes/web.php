<?php

use Illuminate\Support\Facades\Route;


//master 
Route::get('/', function () {
    return view('index');
})->name('index');


//for projects of div
Route::get('/viewprojects', function () {
    return view('projects.viewprojects');
})->name('viewprojects');

Route::get('/openprojectdetails', function () {
    return view('projects.openprojectdetails');
})->name('openprojectdetails');

Route::get('/addmilestonepr', function () {
    return view('projects.addmilestonepr');
})->name('addmilestonepr');

Route::get('/addnewproject', function () {
    return view('projects.addnewproject');
})->name('addnewproject');

// Purchase Cases (PCs)

Route::get('/createnewcase', function () {
    return view('purchase.new_case.createnewcase');
})->name('createnewcase');

Route::get('/purchasecasedetails', function () {
    return view('purchase.new_case.purchasecasedetails');
})->name('purchasecasedetails');

Route::get('/viewpurchasecase', function () {
    return view('purchase.new_case.viewpurchasecase');
})->name('viewpurchasecase');