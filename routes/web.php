<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\PurchaseController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- DEBUG USER ROUTE (For checking data) ---
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
// 1. GUEST ROUTES (Login/Logout)
// ====================================================

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// ====================================================
// 2. PROTECTED ROUTES (Logged In Users)
// ====================================================
Route::middleware('auth')->group(function () {

    // --- MAIN REDIRECT (Jab banda '/' pe aye) ---
    Route::get('/', function () {
        if (Auth::user()->isSORD()) {
            return redirect()->route('sord.dashboard');
        }
        return redirect()->route('dashboard');
    });

    // --- 1. SORD DASHBOARD ROUTE ---
    Route::get('/sord/dashboard', function () {
        // Security: Division wala yahan na aa sake
        if (Auth::user()->isDivision()) {
            return redirect()->route('dashboard');
        }
        // FIX: Yahan 'sord.index' ki bajaye 'index' use kar rahe hain
        return view('index'); 
    })->name('sord.dashboard');

    // --- 2. DIVISION DASHBOARD ROUTE ---
    Route::get('/dashboard', function () {
        // Security: SORD wala yahan na aa sake
        if (Auth::user()->isSORD()) {
            return redirect()->route('sord.dashboard');
        }
        return view('index'); // Same view use hoga
    })->name('dashboard');


    // ====================================================
    // GROUP A: DIVISION SPECIFIC ROUTES
    // (Project, Purchase, Reports - Inko SORD access nahi kar sakega)
    // ====================================================
    
    Route::group(['middleware' => function ($request, $next) {
        // Agar SORD user ghalti se yahan aye, to 403 Forbidden ya Redirect kardo
        if (Auth::user()->isSORD()) {
            // Option 1: Wapis dashboard bhej do
            return redirect()->route('sord.dashboard')->with('error', 'Access Denied');
            // Option 2: Error dikhao (Uncomment to use)
            // abort(403, 'Unauthorized Access: Division Area Only');
        }
        return $next($request);
    }], function () {

        // --- PROJECT ROUTES ---
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
        Route::post('/milestone/mark-complete', [ProjectController::class, 'markMilestoneComplete'])->name('milestone.complete');   
        Route::get('/project/{id}/add-milestone', [ProjectController::class, 'createMilestone'])->name('projects.add-milestone');
        Route::post('/project/{id}/save-milestone', [ProjectController::class, 'storeMilestone'])->name('projects.store-milestone');
        Route::get('/project/{id}/spendings', [ProjectController::class, 'projectSpendings'])->name('projects.spendings');
        Route::get('/milestone/{id}/edit', [ProjectController::class, 'editMilestone'])->name('milestone.edit');
        Route::post('/milestone/{id}/update', [ProjectController::class, 'updateMilestone'])->name('milestone.update');
        Route::get('/milestone/{id}/delete', [ProjectController::class, 'deleteMilestone'])->name('milestone.delete');

        // MPR & History
        Route::get('/project/{id}/view-mpr', [ProjectController::class, 'mprProjectView'])->name('mpr.view');
        Route::post('/project/{id}/save-mpr', [ProjectController::class, 'storeMpr'])->name('mpr.store');
        Route::get('/projecthistory', [ProjectController::class, 'projectHistory'])->name('projecthistory');
        Route::get('/gantchartpr', function () { return view('projects.gantchartpr'); })->name('gantchartpr');

        // --- PURCHASE ROUTES ---
        Route::get('/purchase/create', [PurchaseController::class, 'create'])->name('createnewcase');
        Route::post('/purchase/store', [PurchaseController::class, 'store'])->name('purchase.store');
        Route::get('/viewpurchasecase', [PurchaseController::class, 'index'])->name('viewpurchasecase');
        Route::get('/purchase/details/{id}', [PurchaseController::class, 'show'])->name('purchasecasedetails');
        Route::post('/purchase/release/{id}', [PurchaseController::class, 'releaseCase'])->name('purchase.release');
        Route::get('/get-last-minute/{headId}', [PurchaseController::class, 'getLastMinute'])->name('get.last.minute');
        Route::get('/get-next-minute/{headId}', [PurchaseController::class, 'getNextMinuteNumber'])->name('get.next.minute');
        Route::get('/minute-sheet', function () { return view('purchase.new_case.minutesheet'); })->name('minutesheet');
        Route::get('/print-minute', function () { return view('purchase.new_case.print_minute'); })->name('purchase.new_case.print_minute');
        Route::post('/purchase/quote/store', [PurchaseController::class, 'storeQuote'])->name('quotes.store');
        Route::post('/purchase/upload', [PurchaseController::class, 'uploadAttachment'])->name('purchase.upload');

        // --- REPORTS ---
        Route::get('/purchase/it-reports', [ReportsController::class, 'index'])->name('purchase.reports.index');
        Route::post('/generate-comparative', [ReportsController::class, 'generateComparative'])->name('reports.generate.comparative');
        Route::post('/generate-it-letter', [ReportsController::class, 'generateITLetter'])->name('reports.generate.itletter');

    }); // End of Division Group


    // ====================================================
    // GROUP B: SORD SPECIFIC ROUTES
    // ====================================================
    
    Route::group(['prefix' => 'sord', 'as' => 'sord.', 'middleware' => function ($request, $next) {
        // Agar Division user yahan aye, to roko
        if (Auth::user()->isDivision()) {
            return redirect()->route('dashboard');
        }
        return $next($request);
    }], function () {
        Route::get('/all-projects', [ProjectController::class, 'sordIndex'])->name('all_projects');
        Route::get('/approvals', function() { return "Approvals Page Coming Soon"; })->name('approvals');
        Route::get('/rates', function() { return "SOR Page Coming Soon"; })->name('rates');

    }); // End of SORD Group

}); // End of Auth Middleware