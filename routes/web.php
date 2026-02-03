<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\DocMprController; // <--- YE IMPORT ZAROORI HAI
use App\Http\Controllers\MprController;
use App\Http\Controllers\DivHrController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- DEBUG USER ROUTE ---
Route::get('/debug-user', function() {
    $u = Auth::user();
    if(!$u) return "Not Logged In";
    return dd([
        'Username' => $u->acc_username,
        'Is SORD?' => $u->isSORD(),
        'Is Division?' => $u->isDivision(),
        'Unit Area' => $u->unit ? $u->unit->unt_area : 'No Unit',
    ]);
});

// ====================================================
// 1. GUEST ROUTES
// ====================================================
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// ====================================================
// 2. PROTECTED ROUTES (LOGGED IN USERS)
// ====================================================
Route::middleware('auth')->group(function () {

    // --- MAIN REDIRECT ---
    Route::get('/', function () {
        if (Auth::user()->isSORD()) {
            return redirect()->route('sord.dashboard');
        }
        return redirect()->route('dashboard');
    });

    // --- DASHBOARDS ---
    Route::get('/dashboard', function () {
        if (Auth::user()->isSORD()) return redirect()->route('sord.dashboard');
        return view('index');
    })->name('dashboard');

    Route::get('/sord/dashboard', function () {
        if (Auth::user()->isDivision()) return redirect()->route('dashboard');
        return view('index'); // Same view, sidebar logic handles menu
    })->name('sord.dashboard');


    // ====================================================
    // GROUP A: DIVISION SPECIFIC ROUTES
    // ====================================================
    
    Route::group(['middleware' => function ($request, $next) {
        if (Auth::user()->isSORD()) {
            return redirect()->route('sord.dashboard');
        }
        return $next($request);
    }], function () {

        // --- PROJECTS ---
        Route::get('/view-projects', [ProjectController::class, 'index'])->name('view-projects');
        Route::get('/addnewproject', [ProjectController::class, 'create'])->name('addnewproject');
        Route::post('/save-project', [ProjectController::class, 'store'])->name('save-project');
        Route::post('/finalize-project/{id}', [ProjectController::class, 'finalizeProject'])->name('finalize-project');
        Route::get('/openprojectdetails/{id}', [ProjectController::class, 'show'])->name('projects.show');

        // --- NEW MPR SYSTEM (USING DocMprController) ---
        // (Yeh line replace kar rahi hai purane ProjectController logic ko)
        Route::get('/project/{id}/view-mpr', [DocMprController::class, 'view'])->name('mpr.view');
        Route::get('/project/{id}/mpr-report', [DocMprController::class, 'generateReport'])->name('mpr.report');
        Route::post('/project/{id}/mpr/store', [DocMprController::class, 'store'])->name('mpr.store');

        // Old History Route (Keep if needed for other things)
        Route::get('/projecthistory', [ProjectController::class, 'projectHistory'])->name('projecthistory');

        // Attachments & Milestones (Same as before)
        Route::post('/project/{id}/upload-other', [ProjectController::class, 'storeOtherAttachment'])->name('projects.upload-other');
        Route::post('/project/{id}/upload-single', [ProjectController::class, 'uploadSingleAttachment'])->name('projects.upload.single');
        Route::get('/attachment/delete/{id}', [ProjectController::class, 'deleteAttachment'])->name('attachment.delete');
        Route::get('/attachment/view/{id}', [ProjectController::class, 'viewAttachment'])->name('attachment.view');
        Route::post('/milestone/mark-complete', [ProjectController::class, 'markMilestoneComplete'])->name('milestone.complete');   
        Route::get('/project/{id}/add-milestone', [ProjectController::class, 'createMilestone'])->name('projects.add-milestone');
        Route::post('/project/{id}/save-milestone', [ProjectController::class, 'storeMilestone'])->name('projects.store-milestone');
        Route::get('/project/{id}/spendings', [ProjectController::class, 'projectSpendings'])->name('projects.spendings');
        Route::get('/milestone/{id}/edit', [ProjectController::class, 'editMilestone'])->name('milestone.edit');
        Route::post('/milestone/{id}/update', [ProjectController::class, 'updateMilestone'])->name('milestone.update');
        Route::get('/milestone/{id}/delete', [ProjectController::class, 'deleteMilestone'])->name('milestone.delete');
        Route::get('/gantchartpr', function () { return view('projects.gantchartpr'); })->name('gantchartpr');

        // --- PURCHASE & REPORTS (Same as before) ---
        Route::get('/viewpurchasecase', [PurchaseController::class, 'index'])->name('viewpurchasecase');
        Route::get('/purchase/create', [PurchaseController::class, 'create'])->name('createnewcase');
        Route::post('/purchase/store', [PurchaseController::class, 'store'])->name('purchase.store');
        Route::get('/purchase/details/{id}', [PurchaseController::class, 'show'])->name('purchasecasedetails');
        Route::post('/purchase/release/{id}', [PurchaseController::class, 'releaseCase'])->name('purchase.release');
        Route::get('/get-last-minute/{headId}', [PurchaseController::class, 'getLastMinute'])->name('get.last.minute');
        Route::get('/get-next-minute/{headId}', [PurchaseController::class, 'getNextMinuteNumber'])->name('get.next.minute');
        Route::get('/minute-sheet', function () { return view('purchase.new_case.minutesheet'); })->name('minutesheet');
        Route::get('/print-minute', function () { return view('purchase.new_case.print_minute'); })->name('purchase.new_case.print_minute');
        Route::post('/purchase/quote/store', [PurchaseController::class, 'storeQuote'])->name('quotes.store');
        Route::post('/purchase/upload', [PurchaseController::class, 'uploadAttachment'])->name('purchase.upload');
        Route::get('/purchase/it-reports', [ReportsController::class, 'index'])->name('purchase.reports.index');
        Route::post('/generate-comparative', [ReportsController::class, 'generateComparative'])->name('reports.generate.comparative');
        Route::post('/generate-it-letter', [ReportsController::class, 'generateITLetter'])->name('reports.generate.itletter');

         // HR 
        Route::get('/divhr/employelist', [DivHrController::class, 'employeelist'])->name('divhr.employelist');
        Route::prefix('divhr')->group(function () {

            Route::get('/employeelist', [DivHrController::class, 'employeelist'])
                ->name('divhr.employelist');

            Route::get('/employee/{id}', [DivHrController::class, 'employeedetail'])
                ->name('divhr.employeedetail');

        });

    }); // End Division Group



    // GROUP B: SORD SPECIFIC ROUTES
    // ====================================================
    Route::group(['prefix' => 'sord', 'as' => 'sord.', 'middleware' => function ($request, $next) {
        if (Auth::user()->isDivision()) {
            return redirect()->route('dashboard');
        }
        return $next($request);
    }], function () {

        Route::get('/dashboard', function () { return view('index'); })->name('dashboard');
        
        Route::get('/all-projects', [ProjectController::class, 'sordIndex'])->name('all_projects');
        
        // --- CORRECTED LINES ---
        
        // Inbox
        // URL Banega: /sord/inbox | Name Banega: sord.inbox
        Route::get('/inbox', [MprController::class, 'sordInbox'])->name('inbox');
        
        // Review Page
        // URL Banega: /sord/review/{id} | Name Banega: sord.review_mpr
        Route::get('/review/{doc_id}', [MprController::class, 'reviewMpr'])->name('review_mpr');
        
        // Actions
        // URL Banega: /sord/action | Name Banega: sord.action
        Route::post('/action', [MprController::class, 'sordAction'])->name('action');
        Route::get('/compile-report', [MprController::class, 'compileMprReport'])->name('compile_report');
        
        // SORD Project Details (Read Only)
        Route::get('/project-details/{id}', [ProjectController::class, 'sordShow'])->name('project_details');

        // Global MPR Log
        Route::get('/mpr-log', [MprController::class, 'sordLog'])->name('mpr_log');

    });
}); // End Auth