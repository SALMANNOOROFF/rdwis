<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ====================================================
// 1. GUEST ROUTES
// ====================================================

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ====================================================
// 2. PROTECTED ROUTES
// ====================================================
Route::middleware('auth')->group(function () {

    // --- Dashboard Redirection ---
    // Rule 1: Login ke baad ya '/' kholne par Dashboard par jao
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // Rule 2: Dashboard ka actual Route (Ye index.blade.php khol raha hai)
    Route::get('/dashboard', function () {
        return view('index'); 
    })->name('dashboard');


    // --- Project Management Module ---
    Route::get('/view-projects', [ProjectController::class, 'index'])->name('view-projects');
    Route::get('/openprojectdetails/{id}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('/addnewproject', [ProjectController::class, 'create'])->name('addnewproject');
    Route::post('/save-project', [ProjectController::class, 'store'])->name('save-project');

    // --- Project Sub-Features ---
    Route::get('/addmilestonepr', function () { return view('projects.addmilestonepr'); })->name('addmilestonepr');
    Route::get('/projecthistory', function () { return view('projects.projecthistory'); })->name('projecthistory');
    Route::get('/gantchartpr', function () { return view('projects.gantchartpr'); })->name('gantchartpr');
    Route::get('/openmprs', function () { return view('projects.openmprs'); })->name('openmprs');
    Route::get('/viewmpr', function () { return view('projects.viewmpr'); })->name('viewmpr');

    // --- Purchase Cases Module ---
    Route::get('/createnewcase', function () { return view('purchase.new_case.createnewcase'); })->name('createnewcase');
    Route::get('/purchasecasedetails', function () { return view('purchase.new_case.purchasecasedetails'); })->name('purchasecasedetails');
    Route::get('/viewpurchasecase', function () { return view('purchase.new_case.viewpurchasecase'); })->name('viewpurchasecase');

});