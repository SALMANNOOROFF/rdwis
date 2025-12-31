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

Route::get('/projecthistory', function () {
    return view('projects.projecthistory');
})->name('projecthistory');

Route::get('/gantchartpr', function () {
    return view('projects.gantchartpr');
})->name('gantchartpr');

Route::get('/openmprs', function () {
    return view('projects.openmprs');
})->name('openmprs');

Route::get('/viewmpr', function () {
    return view('projects.viewmpr');
})->name('viewmpr');