<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PurchaseController;

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

    // --- Dashboard ---
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', function () {
        return view('index');
    })->name('dashboard');


    // ====================================================
    // PROJECT MANAGEMENT
    // ====================================================

    Route::get('/view-projects', [ProjectController::class, 'index'])->name('view-projects');
    Route::get('/addnewproject', [ProjectController::class, 'create'])->name('addnewproject');
    Route::post('/save-project', [ProjectController::class, 'store'])->name('save-project');

    Route::post('/finalize-project/{id}', [ProjectController::class, 'finalizeProject'])->name('finalize-project');
    Route::get('/openprojectdetails/{id}', [ProjectController::class, 'show'])->name('projects.show');

    // Attachments
    Route::post('/project/{id}/upload-other', [ProjectController::class, 'storeOtherAttachment'])->name('projects.upload-other');
    Route::post('/project/{id}/upload-single', [ProjectController::class, 'uploadSingleAttachment'])->name('projects.upload.single');
    Route::get('/attachment/delete/{id}', [ProjectController::class, 'deleteAttachment'])->name('attachment.delete');
    Route::get('/attachment/view/{id}', [ProjectController::class, 'viewAttachment'])->name('attachment.view');

    // Milestones
    Route::get('/project/{id}/add-milestone', [ProjectController::class, 'createMilestone'])->name('projects.add-milestone');
    Route::post('/project/{id}/save-milestone', [ProjectController::class, 'storeMilestone'])->name('projects.store-milestone');
    Route::get('/project/{id}/spendings', [ProjectController::class, 'projectSpendings'])->name('projects.spendings');

    Route::get('/milestone/{id}/edit', [ProjectController::class, 'editMilestone'])->name('milestone.edit');
    Route::post('/milestone/{id}/update', [ProjectController::class, 'updateMilestone'])->name('milestone.update');
    Route::get('/milestone/{id}/delete', [ProjectController::class, 'deleteMilestone'])->name('milestone.delete');

    // MPR
    Route::get('/project/{id}/view-mpr', [ProjectController::class, 'mprProjectView'])->name('mpr.view');
    Route::post('/project/{id}/save-mpr', [ProjectController::class, 'storeMpr'])->name('mpr.store');

    // Project History
    Route::get('/projecthistory', [ProjectController::class, 'projectHistory'])->name('projecthistory');

    // Gantt Chart
    Route::get('/gantchartpr', function () {
        return view('projects.gantchartpr');
    })->name('gantchartpr');


    // ====================================================
    // PURCHASE MANAGEMENT
    // ====================================================

    // Create New Case
    Route::get('/purchase/create', function () {
        return view('purchase.new_case.createnewcase');
    })->name('createnewcase');

    // View Purchase Cases (Controller based)
    Route::get('/viewpurchasecase', [PurchaseController::class, 'index'])->name('viewpurchasecase');

    // Purchase Case Details
    Route::get('/purchase/details/{id}', [PurchaseController::class, 'show'])->name('purchasecasedetails');

    // Minute Sheet
    Route::get('/minute-sheet', function () {
        return view('purchase.new_case.minutesheet');
    })->name('minutesheet');

    Route::get('/print-minute', function () {
        return view('purchase.new_case.print_minute');
    })->name('purchase.new_case.print_minute');

    // Store Quote
    Route::post('/purchase/quote/store', [PurchaseController::class, 'storeQuote'])->name('quotes.store');

});
