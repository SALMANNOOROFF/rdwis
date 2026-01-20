<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportsController;
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
    Route::get('/', function () { return redirect()->route('dashboard'); });
    Route::get('/dashboard', function () { return view('index'); })->name('dashboard');


    // --- Project Management Module ---
    Route::get('/view-projects', [ProjectController::class, 'index'])->name('view-projects');
    Route::get('/addnewproject', [ProjectController::class, 'create'])->name('addnewproject');
    Route::post('/save-project', [ProjectController::class, 'store'])->name('save-project');
    Route::post('/finalize-project/{id}', [ProjectController::class, 'finalizeProject'])->name('finalize-project');
    Route::get('/openprojectdetails/{id}', [ProjectController::class, 'show'])->name('projects.show');
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

// ... baki routes upar rehne dein ...
// --- Other Static Pages ---
Route::get('/projecthistory', function () {
    return view('projects.projecthistory');
})->name('projecthistory');

Route::get('/gantchartpr', function () {
    return view('projects.gantchartpr');
})->name('gantchartpr');

// --- Purchase Management Module ---

// 1. Create New Case Form
// SAHI: Sirf yeh wala rasta rakhein jo Controller ko point kare
Route::get('/purchase/create', [PurchaseController::class, 'create'])->name('createnewcase');

Route::get('/get-last-minute/{headId}', [PurchaseController::class, 'getLastMinute'])->name('get.last.minute');

// 2. View All Purchase Cases (dynamic data load)
Route::get('/viewpurchasecase', [PurchaseController::class, 'index'])->name('viewpurchasecase');

// 3. Purchase Case Details (dynamic ID)
Route::get('/purchase/details/{id}', [PurchaseController::class, 'show'])->name('purchasecasedetails');

// 4. Minute Sheet & Printing
Route::get('/minute-sheet', function () {
    return view('purchase.new_case.minutesheet');
})->name('minutesheet');

Route::get('/print-minute', function () {
    return view('purchase.new_case.print_minute');
})->name('purchase.new_case.print_minute');

// 5. Quote Save (POST)
Route::post('/purchase/quote/store', [PurchaseController::class, 'storeQuote'])->name('quotes.store');




// Sidebar link aur Form page ke liye
Route::get('/purchase/it-reports', [ReportsController::class, 'index'])->name('purchase.reports.index');

// Form Submit karne ke liye
Route::post('/generate-comparative', [ReportsController::class, 'generateComparative'])->name('reports.generate.comparative');
Route::post('/generate-it-letter', [ReportsController::class, 'generateITLetter'])->name('reports.generate.itletter');

// controller for attachments upload
Route::post('/purchase/upload', [PurchaseController::class, 'uploadAttachment'])->name('purchase.upload');

Route::get('/get-next-minute/{headId}', [PurchaseController::class, 'getNextMinuteNumber'])->name('get.next.minute');


Route::post('/purchase/store', [App\Http\Controllers\PurchaseController::class, 'store'])->name('purchase.store');

// Route for releasing the case (Changing status to Under Scrutiny)
Route::post('/purchase/release/{id}', [PurchaseController::class, 'releaseCase'])->name('purchase.release');
}); // group close