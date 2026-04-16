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
use App\Http\Controllers\PurItemsController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\SystemAdminAccountController;

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
// 2. PROTECTED ROUTES (LOGGED IN USERS)
// ====================================================
Route::middleware('auth')->group(function () {

    Route::get('/debug-user', function () {
        $u = Auth::user();

        $rawArea = (string) ($u?->acc_untarea ?? '');
        $rawAuth = (string) ($u?->acc_auth ?? '');

        return response()->json([
            'acc_id' => $u?->acc_id,
            'acc_username' => $u?->acc_username,
            'acc_status' => $u?->acc_status,
            'acc_unt_id' => $u?->acc_unt_id,
            'acc_level' => $u?->acc_level,
            'acc_untarea_raw' => $rawArea,
            'acc_untarea_norm' => strtolower(trim($rawArea)),
            'acc_auth_raw' => $rawAuth,
            'acc_auth_norm' => strtolower(trim($rawAuth)),
            'isSORD' => method_exists($u, 'isSORD') ? $u->isSORD() : null,
            'isDivision' => method_exists($u, 'isDivision') ? $u->isDivision() : null,
        ]);
    })->name('debug.user');

    Route::get('/password/change', [AuthController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/password/change', [AuthController::class, 'changePassword'])->name('password.update');

    Route::middleware('force_password_change')->group(function () {
        Route::get('/', function () {
            $u = Auth::user();
            if (method_exists($u, 'isSORD') && $u->isSORD()) {
                return redirect()->route('sord.dashboard');
            }

            $area = strtolower(trim((string) ($u?->acc_untarea ?? '')));

            return match ($area) {
                'hr' => redirect()->route('divhr.employelist'),
                'fin' => redirect()->route('nrdi.dashboard'), // High-level Fin lands on HQ Dashboard
                'it' => redirect()->route('admin.dashboard'),
                'nrdi' => redirect()->route('nrdi.dashboard'),
                'rdw' => redirect()->route('nrdi.dashboard'), // MD lands on HQ Dashboard
                'hqs' => redirect()->route('nrdi.dashboard'), // DDG lands on HQ Dashboard
                'proc' => redirect()->route('nrdi.dashboard'), // DProc lands on HQ Dashboard
                default => redirect()->route('dashboard'),
            };
        });

        Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
            ->name('dashboard')
            ->middleware('area:prj,rdwprj');

        Route::get('/sord/dashboard', [\App\Http\Controllers\DashboardController::class, 'sord'])
            ->name('sord.dashboard')
            ->middleware('area:rdwprj,rdw');

        Route::prefix('nrdi')->middleware('area:nrdi,rdw,hqs,proc,fin')->name('nrdi.')->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'nrdiDashboard'])->name('dashboard');
            Route::get('/dashboard-data', [\App\Http\Controllers\DashboardController::class, 'nrdiDashboardData'])->name('dashboard.data');
            Route::get('/contract-cases', [\App\Http\Controllers\DashboardController::class, 'contractCases'])->name('contract_cases.index');
            
            // Standard HQ Approval Routes (Generic)
            Route::get('/purchase-cases', [\App\Http\Controllers\PurchaseApprovalController::class, 'dashboard'])->name('purchase_cases.index');
            Route::get('/purchase-cases/{id}', [\App\Http\Controllers\PurchaseApprovalController::class, 'show'])->name('purchase_cases.show');
            Route::post('/purchase-cases/{id}/action', [\App\Http\Controllers\PurchaseApprovalController::class, 'action'])->name('purchase_cases.action');

            // Director Procurement Specialized Routes
            Route::prefix('procurement')->name('procurement.')->group(function () {
                Route::get('/dashboard', [\App\Http\Controllers\ProcurementDashboardController::class, 'index'])->name('purchase_cases.index');
                Route::get('/case/{id}', [\App\Http\Controllers\ProcurementDashboardController::class, 'show'])->name('purchase_cases.show');
            });

            // Director Finance Specialized Routes
            Route::prefix('finance')->name('finance.')->group(function () {
                Route::get('/dashboard', [\App\Http\Controllers\FinanceDashboardController::class, 'index'])->name('purchase_cases.index');
                Route::get('/case/{id}', [\App\Http\Controllers\FinanceDashboardController::class, 'show'])->name('purchase_cases.show');
            });

            // --- NEW DUPLICATED ROUTES FOR PURCHASE AND CONTRACT CASES ---
            Route::prefix('purchase-cases-new')->name('purchase_cases_new.')->group(function () {
                Route::get('/', [\App\Http\Controllers\PurchaseCaseController::class, 'index'])->name('index');

                Route::prefix('procurement')->name('procurement.')->group(function () {
                    Route::get('/', [\App\Http\Controllers\PurchaseProcurementController::class, 'index'])->name('index');
                    Route::get('/{id}', [\App\Http\Controllers\PurchaseProcurementController::class, 'show'])->name('show');
                });

                Route::prefix('finance')->name('finance.')->group(function () {
                    Route::get('/', [\App\Http\Controllers\PurchaseFinanceController::class, 'index'])->name('index');
                    Route::get('/{id}', [\App\Http\Controllers\PurchaseFinanceController::class, 'show'])->name('show');
                });

                // Wildcard routes must come last
                Route::get('/{id}', [\App\Http\Controllers\PurchaseCaseController::class, 'show'])->name('show');
                Route::post('/{id}/action', [\App\Http\Controllers\PurchaseCaseController::class, 'action'])->name('action');
            });

            Route::get('/contract-cases-new', [\App\Http\Controllers\ContractCaseController::class, 'index'])->name('contract_cases_new.index');

            Route::get('/projects', [ProjectController::class, 'nrdiIndex'])->name('projects.index');
            Route::get('/projects/{id}', [ProjectController::class, 'nrdiShow'])->name('projects.show');
        });

        Route::group([
            'middleware' => [
                function ($request, $next) {
                    if (Auth::user()->isSORD()) {
                        return redirect()->route('sord.dashboard');
                    }
                    return $next($request);
                },
                'area:prj,rdwprj',
            ],
        ], function () {

        // --- PROJECTS ---
        Route::get('/view-projects', [ProjectController::class, 'index'])->name('view-projects');
        Route::get('/addnewproject', [ProjectController::class, 'create'])
            ->name('addnewproject')
            ->middleware('approver');
        Route::post('/save-project', [ProjectController::class, 'store'])
            ->name('save-project')
            ->middleware('approver');
        Route::post('/finalize-project/{id}', [ProjectController::class, 'finalizeProject'])
            ->name('finalize-project')
            ->middleware('approver');
        Route::get('/openprojectdetails/{id}', [ProjectController::class, 'show'])->name('projects.show');

        // --- NEW MPR SYSTEM (USING DocMprController) ---
        // (Yeh line replace kar rahi hai purane ProjectController logic ko)
        Route::get('/project/{id}/view-mpr', [DocMprController::class, 'view'])->name('mpr.view');
        Route::get('/project/{id}/mpr-report', [DocMprController::class, 'generateReport'])->name('mpr.report');
        Route::post('/project/{id}/mpr/store', [DocMprController::class, 'store'])
            ->name('mpr.store')
            ->middleware('approver');

        // Old History Route (Keep if needed for other things)
        Route::get('/projecthistory', [ProjectController::class, 'projectHistory'])->name('projecthistory');

        // Attachments & Milestones (Same as before)
        Route::post('/project/{id}/upload-other', [ProjectController::class, 'storeOtherAttachment'])
            ->name('projects.upload-other')
            ->middleware('approver');
        Route::post('/project/{id}/upload-single', [ProjectController::class, 'uploadSingleAttachment'])
            ->name('projects.upload.single')
            ->middleware('approver');
        Route::get('/attachment/delete/{id}', [ProjectController::class, 'deleteAttachment'])->name('attachment.delete');
        Route::get('/attachment/view/{id}', [ProjectController::class, 'viewAttachment'])->name('attachment.view');
        Route::post('/milestone/mark-complete', [ProjectController::class, 'markMilestoneComplete'])
            ->name('milestone.complete')
            ->middleware('approver');
        Route::get('/project/{id}/add-milestone', [ProjectController::class, 'createMilestone'])
            ->name('projects.add-milestone')
            ->middleware('approver');
        Route::post('/project/{id}/save-milestone', [ProjectController::class, 'storeMilestone'])
            ->name('projects.store-milestone')
            ->middleware('approver');
        Route::get('/project/{id}/spendings', [ProjectController::class, 'projectSpendings'])->name('projects.spendings');
        Route::get('/milestone/{id}/edit', [ProjectController::class, 'editMilestone'])->name('milestone.edit');
        Route::post('/milestone/{id}/update', [ProjectController::class, 'updateMilestone'])->name('milestone.update');
        Route::get('/milestone/{id}/delete', [ProjectController::class, 'deleteMilestone'])->name('milestone.delete');
        Route::get('/gantchartpr', function () { return view('projects.gantchartpr'); })->name('gantchartpr');

        // --- PURCHASE & REPORTS (Project area) ---
        Route::get('/pc-initiation', [\App\Http\Controllers\PurchaseInitiationController::class, 'index'])->name('purchase.initiation.index');
        Route::get('/pc-initiation/case/{id}', [\App\Http\Controllers\PurchaseInitiationController::class, 'show'])->name('purchase.initiation.show');
        Route::get('/pc-initiation/statuses', [\App\Http\Controllers\PurchaseInitiationController::class, 'getStatuses'])->name('purchase.initiation.statuses');
        
        Route::get('/viewpurchasecase', [PurchaseController::class, 'index'])->name('viewpurchasecase');
        Route::get('/purchase/select', [PurchaseController::class, 'select'])
            ->name('purchase.select')
            ->middleware('approver');
        Route::get('/purchase/new/{type}', [PurchaseController::class, 'unifiedCreate'])
            ->name('purchase.unified.create')
            ->middleware('approver');
        Route::post('/purchase/store', [PurchaseController::class, 'store'])
            ->name('purchase.store')
            ->middleware('approver');
        Route::get('/purchase/details/{id}', [PurchaseController::class, 'show'])->name('purchasecasedetails');
        Route::post('/purchase/release/{id}', [PurchaseController::class, 'releaseCase'])
            ->name('purchase.release')
            ->middleware('approver');
        Route::post('/purchase/hold/{id}', [PurchaseController::class, 'holdCase'])
            ->name('purchase.hold')
            ->middleware('approver');
        Route::post('/purchase/update-core/{id}', [PurchaseController::class, 'updateCore'])
            ->name('purchase.update_core')
            ->middleware('approver');
        Route::get('/training', [TrainingController::class, 'index'])->name('training.index');
        Route::get('/training/create', [TrainingController::class, 'create'])->name('training.create');
        Route::get('/training/books', [TrainingController::class, 'indexBook'])->name('training.books.index');
        Route::get('/training/books/create', [TrainingController::class, 'createBook'])->name('training.books.create');
        Route::get('/training/license', [TrainingController::class, 'indexLicense'])->name('training.license.index');
        Route::get('/training/license/create', [TrainingController::class, 'createLicense'])->name('training.license.create');
        Route::get('/training/purchase/create', [TrainingController::class, 'createPurchase'])->name('training.purchase.create');
        Route::get('/training/{id}', [TrainingController::class, 'show'])->name('training.show');
        Route::post('/training/store', [TrainingController::class, 'store'])
            ->name('training.store')
            ->middleware('approver');
        Route::post('/training/books/store', [TrainingController::class, 'storeBook'])
            ->name('training.books.store')
            ->middleware('approver');
        Route::post('/training/license/store', [TrainingController::class, 'storeLicense'])
            ->name('training.license.store')
            ->middleware('approver');
        Route::post('/training/purchase/store', [TrainingController::class, 'storePurchase'])
            ->name('training.purchase.store')
            ->middleware('approver');
        Route::get('/get-last-minute/{headId}', [PurchaseController::class, 'getLastMinute'])->name('get.last.minute');
        Route::get('/get-next-minute/{headId}', [PurchaseController::class, 'getNextMinuteNumber'])->name('get.next.minute');
        Route::get('/minute-sheet', function () { return view('purchase.new_case.minutesheet'); })->name('minutesheet');
        Route::get('/purchase/case/{id}/minute-view', [PurchaseController::class, 'minuteView'])->name('purchase.minute_view');
        Route::get('/print-minute', function () { return view('purchase.new_case.print_minute'); })->name('purchase.new_case.print_minute');
        Route::post('/purchase/quote/store', [PurchaseController::class, 'storeQuote'])
            ->name('quotes.store')
            ->middleware('approver');
        Route::post('/purchase/upload', [PurchaseController::class, 'uploadAttachment'])
            ->name('purchase.upload')
            ->middleware('approver');
        Route::get('/purchase/it-reports', [ReportsController::class, 'index'])->name('purchase.reports.index');
        Route::post('/generate-comparative', [ReportsController::class, 'generateComparative'])->name('reports.generate.comparative');
        Route::post('/generate-it-letter', [ReportsController::class, 'generateITLetter'])->name('reports.generate.itletter');
        Route::get('/get-next-minute-training/{headId}', [TrainingController::class, 'getNextMinuteNumber'])->name('training.get.next.minute');

        // Puritems legacy routes removed in favor of purnew

        // Purnew (layout-integrated, uses DB RDWIS/DB/Individual Sql Files/purnew.sql)
        Route::prefix('purnew')->group(function () {
            Route::get('/create', [PurItemsController::class, 'indexLayout'])->name('purnew.create');
            Route::get('/groups', [PurItemsController::class, 'rfqListLayout'])->name('purnew.groups');
            Route::post('/item', [PurItemsController::class, 'createItem'])
                ->name('purnew.item.create')
                ->middleware('approver');
            Route::post('/rfq/preview', [PurItemsController::class, 'rfqPreview'])
                ->name('purnew.rfq.preview')
                ->middleware('approver');
            Route::post('/rfq/submit', [PurItemsController::class, 'rfqSubmit'])
                ->name('purnew.rfq.submit')
                ->middleware('approver');
            Route::get('/group/{id}', [PurItemsController::class, 'rfqShowLayout'])->name('purnew.group.show');
            Route::post('/setup', [PurItemsController::class, 'setupPurnew'])
                ->name('purnew.setup')
                ->middleware('approver');

            // Quotation System
            Route::get('/quotes/{rfqId}', [PurItemsController::class, 'getQuotationData'])->name('purnew.quotes.get');
            Route::get('/quotes/{rfqId}/items', [PurItemsController::class, 'rfqItemsJson'])->name('purnew.quotes.items');
            Route::post('/quotes/save', [PurItemsController::class, 'saveQuotation'])
                ->name('purnew.quotes.save')
                ->middleware('approver');
            Route::post('/quotes/delete-column', [PurItemsController::class, 'deleteQuotationColumn'])
                ->name('purnew.quotes.deleteColumn')
                ->middleware('approver');
            Route::post('/quotes/accept', [PurItemsController::class, 'acceptQuote'])
                ->name('purnew.quotes.accept')
                ->middleware('approver');
            Route::post('/quotes/accept-item', [PurItemsController::class, 'acceptItemQuote'])
                ->name('purnew.quotes.acceptItem')
                ->middleware('approver');
            Route::get('/vendors', [PurItemsController::class, 'vendorsJson'])->name('purnew.vendors');
            Route::delete('/group/{id}', [PurItemsController::class, 'deleteGroup'])->name('purnew.group.delete');
            Route::get('/group/{id}/details', [PurItemsController::class, 'groupDetailsJson'])->name('purnew.group.details');
        });

        // HR (hr area)
        Route::middleware('area:hr')->group(function () {

        Route::get('/divhr/employelist', [DivHrController::class, 'employeelist'])->name('divhr.employelist');
        Route::prefix('divhr')->group(function () {

            Route::get('/employeelist', [DivHrController::class, 'employeelist'])
                ->name('divhr.employelist');

            Route::get('/employee/{id}', [DivHrController::class, 'employeedetail'])
                ->name('divhr.employeedetail');

            Route::get('/attendance', [DivHrController::class, 'attendance'])
                ->name('divhr.attendance');
            Route::post('/attendance/save', [DivHrController::class, 'attendanceSave'])
                ->name('divhr.attendance.save')
                ->middleware('approver');

        });
        });

    }); // End Division Group



    // GROUP B: SORD / RDWPRJ AREA ROUTES
    // ====================================================
    Route::group([
        'prefix' => 'sord',
        'as' => 'sord.',
        'middleware' => [
            function ($request, $next) {
                if (Auth::user()->isDivision()) {
                    return redirect()->route('dashboard');
                }
                return $next($request);
            },
            'area:rdwprj,rdw',
        ],
    ], function () {

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

    // ====================================================
    // 3. SYSTEM ADMIN ACCOUNT MANAGEMENT (IT approver)
    // ====================================================
    Route::prefix('admin')
        ->name('admin.')
        ->middleware(['area:it', 'approver'])
        ->group(function () {
            Route::get('/', [SystemAdminAccountController::class, 'dashboard'])->name('dashboard');
            Route::get('/reversals', [SystemAdminAccountController::class, 'reversalsIndex'])->name('reversals.index');
            Route::get('/crypto-test', [SystemAdminAccountController::class, 'cryptoTest'])->name('crypto.test');
        });

    Route::prefix('admin/accounts')
        ->name('admin.accounts.')
        ->middleware(['area:it', 'approver'])
        ->group(function () {
            Route::get('/', [SystemAdminAccountController::class, 'index'])->name('index');
            Route::get('/create', [SystemAdminAccountController::class, 'create'])->name('create');
            Route::post('/', [SystemAdminAccountController::class, 'store'])->name('store');
            Route::post('/{account}/close', [SystemAdminAccountController::class, 'close'])->name('close');
            Route::post('/{account}/reopen', [SystemAdminAccountController::class, 'reopen'])->name('reopen');
            Route::post('/{account}/reset-password', [SystemAdminAccountController::class, 'resetPassword'])->name('reset_password');
            Route::get('/roles', [SystemAdminAccountController::class, 'fetchRoles'])->name('roles');
        });

    // ====================================================
    // FINANCE AREA ROUTES
    // ====================================================
    Route::prefix('fin')
        ->name('fin.')
        ->middleware(['area:fin'])
        ->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'finDashboard'])->name('dashboard');
        });

    // Unified Group for High-Level Approvals (DProc, DFin, MD, DDG, DG)
    Route::middleware(['area:proc,fin,rdw,hqs,nrdi'])->group(function () {
        Route::get('/approvals/dashboard', [\App\Http\Controllers\PurchaseApprovalController::class, 'dashboard'])->name('approvals.dashboard');
        Route::get('/approvals/show/{id}', [\App\Http\Controllers\PurchaseApprovalController::class, 'show'])->name('approvals.show');
        Route::post('/approvals/action/{id}', [\App\Http\Controllers\PurchaseApprovalController::class, 'action'])->name('approvals.action');
    });

    // Procurement Notifications
    Route::get('/notifications/unread', [\App\Http\Controllers\NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');

    }); // End force password change
}); // End Auth
