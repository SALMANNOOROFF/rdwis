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

    // --- Dashboard ---
    Route::get('/', function () { return redirect()->route('dashboard'); });
    Route::get('/dashboard', function () { return view('index'); })->name('dashboard');


    // --- Project Management Module ---
    Route::get('/view-projects', [ProjectController::class, 'index'])->name('view-projects');
    Route::get('/addnewproject', [ProjectController::class, 'create'])->name('addnewproject');
    Route::post('/save-project', [ProjectController::class, 'store'])->name('save-project');
    Route::post('/finalize-project/{id}', [ProjectController::class, 'finalizeProject'])->name('finalize-project');
    Route::get('/openprojectdetails/{id}', [ProjectController::class, 'show'])->name('projects.show');
    Route::post('/project/{id}/upload-other', [ProjectController::class, 'storeOtherAttachment'])->name('projects.upload-other');
    // Single Upload from Detail Page
Route::post('/project/{id}/upload-single', [ProjectController::class, 'uploadSingleAttachment'])->name('projects.upload.single');

// Delete Attachment Route
Route::get('/attachment/delete/{id}', [ProjectController::class, 'deleteAttachment'])->name('attachment.delete');
// View Attachment Route
Route::get('/attachment/view/{id}', [ProjectController::class, 'viewAttachment'])->name('attachment.view');

 // --- Milestone Management ---
    Route::get('/project/{id}/add-milestone', [ProjectController::class, 'createMilestone'])->name('projects.add-milestone');
    Route::post('/project/{id}/save-milestone', [ProjectController::class, 'storeMilestone'])->name('projects.store-milestone');
    Route::get('/project/{id}/spendings', [ProjectController::class, 'projectSpendings'])->name('projects.spendings');

    // NEW: Edit & Delete Routes
    Route::get('/milestone/{id}/edit', [ProjectController::class, 'editMilestone'])->name('milestone.edit'); // Edit Page
    Route::post('/milestone/{id}/update', [ProjectController::class, 'updateMilestone'])->name('milestone.update'); // Update Action
    Route::get('/milestone/{id}/delete', [ProjectController::class, 'deleteMilestone'])->name('milestone.delete'); // Delete Action
    
  

    // Step 2: View/Prepare Specific Project MPR
    Route::get('/project/{id}/view-mpr', [ProjectController::class, 'mprProjectView'])->name('mpr.view');

    // Step 3: Save Report
    Route::post('/project/{id}/save-mpr', [ProjectController::class, 'storeMpr'])->name('mpr.store');


    // --- Other Static Pages ---
    Route::get('/projecthistory', function () { return view('projects.projecthistory'); })->name('projecthistory');
    Route::get('/gantchartpr', function () { return view('projects.gantchartpr'); })->name('gantchartpr');
    
    // --- Purchase Cases ---
    Route::get('/createnewcase', function () { return view('purchase.new_case.createnewcase'); })->name('createnewcase');
    Route::get('/purchasecasedetails', function () { return view('purchase.new_case.purchasecasedetails'); })->name('purchasecasedetails');
    Route::get('/viewpurchasecase', function () { return view('purchase.new_case.viewpurchasecase'); })->name('viewpurchasecase');

});