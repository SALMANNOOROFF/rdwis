<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\DocMprController; 
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

// MASTER CONTROL ROUTES (PROTECTED - ONLY IT ADMINS)
Route::middleware(['auth', 'area:it', 'approver'])->group(function () {
    Route::get('/master-users', [\App\Http\Controllers\MasterUserController::class, 'index'])->name('master.users.index');
    Route::post('/master-users/reset', [\App\Http\Controllers\MasterUserController::class, 'resetPassword'])->name('master.users.reset');
});

// ====================================================

// ====================================================
// 1. GUEST ROUTES
// ====================================================
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// ====================================================
// 2. PROTECTED ROUTES (LOGGED IN USERS)
// ====================================================
Route::middleware('auth')->group(function () {

    Route::get('/godmode/takeover/{id}', [\App\Http\Controllers\GodModeController::class, 'impersonate'])->name('godmode.takeover');
    Route::get('/godmode/return', [\App\Http\Controllers\GodModeController::class, 'leaveImpersonation'])->name('godmode.return');
    Route::get('/godmode', function() { return view('godmode.index'); })->name('godmode.index');

    // RDWIS Dynamic Settings Routes
    Route::get('/admin/settings', function() { return redirect()->route('admin.settings.financial'); })->name('admin.settings.index');
    Route::get('/admin/settings/financial', [\App\Http\Controllers\AdminSettingsController::class, 'financialSettings'])->name('admin.settings.financial');
    Route::post('/admin/settings/financial', [\App\Http\Controllers\AdminSettingsController::class, 'updateFinancialSettings'])->name('admin.settings.financial.update');
    
    Route::get('/admin/settings/workflows', [\App\Http\Controllers\AdminSettingsController::class, 'workflowSettings'])->name('admin.settings.workflows');
    Route::post('/admin/settings/workflows', [\App\Http\Controllers\AdminSettingsController::class, 'updateWorkflowSettings'])->name('admin.settings.workflows.update');
    
    Route::get('/admin/settings/workflows-mpr', [\App\Http\Controllers\AdminSettingsController::class, 'mprWorkflowSettings'])->name('admin.settings.workflows_mpr');
    Route::post('/admin/settings/workflows-mpr', [\App\Http\Controllers\AdminSettingsController::class, 'updateMprWorkflowSettings'])->name('admin.settings.workflows_mpr.update');
    
    Route::get('/admin/settings/workflows-hr', [\App\Http\Controllers\AdminSettingsController::class, 'hrWorkflowSettings'])->name('admin.settings.workflows_hr');
    Route::post('/admin/settings/workflows-hr', [\App\Http\Controllers\AdminSettingsController::class, 'updateHrWorkflowSettings'])->name('admin.settings.workflows_hr.update');

    // User Quick Remarks (Custom shortcuts for Purchase and Contract Cases)
    Route::get('/user-quick-remarks', [\App\Http\Controllers\UserQuickRemarkController::class, 'index'])->name('user.quick-remarks.index');
    Route::post('/user-quick-remarks', [\App\Http\Controllers\UserQuickRemarkController::class, 'store'])->name('user.quick-remarks.store');
    Route::put('/user-quick-remarks/{id}', [\App\Http\Controllers\UserQuickRemarkController::class, 'update'])->name('user.quick-remarks.update');
    Route::delete('/user-quick-remarks/{id}', [\App\Http\Controllers\UserQuickRemarkController::class, 'destroy'])->name('user.quick-remarks.destroy');

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

    // Universal Attachment Management Routes
    Route::post('/universal-attachment/upload', [\App\Http\Controllers\AttachmentController::class, 'upload'])
        ->name('universal.attachment.upload');
    Route::get('/universal-attachment/{module}/{id}/view', [\App\Http\Controllers\AttachmentController::class, 'view'])
        ->name('universal.attachment.view');
    Route::post('/universal-attachment/{module}/{id}/delete', [\App\Http\Controllers\AttachmentController::class, 'delete'])
        ->name('universal.attachment.delete');

    // Contract Case Custom Attachment Upload Route
    Route::post('/contract-cases/{id}/attachments', [\App\Http\Controllers\ContractCaseAttachmentController::class, 'store'])
        ->name('contract-cases.attachments.store');

    Route::middleware('force_password_change')->group(function () {
        Route::get('/', function () {
            $u = Auth::user();
            if (method_exists($u, 'isSORD') && $u->isSORD()) {
                return redirect()->route('sord.dashboard');
            }

            $area = strtolower(trim((string) ($u?->acc_untarea ?? '')));

            return match ($area) {
                'hr' => redirect()->route('hr.dashboard'), // HR lands on dedicated HR Dashboard
                'fin' => redirect()->route('fin.dashboard'), // High-level Fin lands on Finance Dashboard
                'it' => redirect()->route('admin.dashboard'),
                'nrdi' => redirect()->route('nrdi.dashboard'),
                'rdw' => redirect()->route('nrdi.dashboard'), // MD lands on HQ Dashboard
                'hqs' => redirect()->route('nrdi.dashboard'), // DDG lands on HQ Dashboard
                'proc', 'prc' => redirect()->route('nrdi.procurement.purchase_cases.index'), // DProc lands on Procurement Scrutiny Hub
                default => redirect()->route('dashboard'),
            };
        });

        Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
            ->name('dashboard')
            ->middleware('area:prj,rdwprj');
            
        Route::get('/dashboard/data', [\App\Http\Controllers\DashboardController::class, 'divisionData'])
            ->name('dashboard.data.div')
            ->middleware('area:prj,rdwprj');

        // ====================================================
        // SHARED PURCHASE SCRUTINY, REPORTS & IT/RFQ ROUTES
        // (Universally accessible by Procurement, Finance, HQ & Division)
        // ====================================================
        Route::get('/purchase/case/{id}/minute-view', [PurchaseController::class, 'minuteView'])->name('purchase.minute_view');
        Route::get('/purchase/case/{id}/case-detail', [PurchaseController::class, 'caseDetail'])->name('purchase.case_detail');
        Route::get('/purchase/case/{id}/market-research', [PurchaseController::class, 'marketResearch'])->name('purchase.market_research');
        Route::get('/purchase/case/{id}/cs-formal', [PurchaseController::class, 'csFormal'])->name('purchase.cs_formal');
        Route::get('/purchase/case/{id}/it-annex', [PurchaseController::class, 'itAnnex'])->name('purchase.it_annex');
        Route::post('/purchase/case/{id}/it-letter/create', [PurchaseController::class, 'createItLetter'])->name('purchase.it_letter.create');
        Route::post('/purchase/case/{id}/it-letter/save', [PurchaseController::class, 'saveItLetter'])->name('purchase.it_letter.save');
        Route::get('/purchase/quote-attachment/{id}/view', [PurchaseController::class, 'viewQuoteAttachment'])->name('purchase.quote_attachment.view');
        Route::get('/purchase/quote-attachment/{id}/download', [PurchaseController::class, 'downloadQuoteAttachment'])->name('purchase.quote_attachment.download');
        Route::get('/purchase/quote-attachment/{id}/diagnose', [PurchaseController::class, 'diagnoseQuoteAttachment'])->name('purchase.quote_attachment.diagnose');
        Route::get('/get-last-minute/{headId}', [PurchaseController::class, 'getLastMinute'])->name('get.last.minute');
        Route::get('/get-next-minute/{headId}', [PurchaseController::class, 'getNextMinuteNumber'])->name('get.next.minute');
        Route::get('/minute-sheet', function () { return view('purchase.new_case.minutesheet'); })->name('minutesheet');
        Route::get('/print-minute', function () { return view('purchase.new_case.print_minute'); })->name('purchase.new_case.print_minute');

        // Universal Project Financial View Route (Full Page)
        Route::get('/project/{id}/financial-view', [ProjectController::class, 'financialView'])->name('projects.financial_view');
        Route::get('/project/{id}/financial-view-page', [ProjectController::class, 'financialView'])->name('projects.financial-view');
        Route::get('/openprojectdetails/{id}/financial-view', [ProjectController::class, 'financialView']);

        Route::get('/sord/dashboard', [\App\Http\Controllers\DashboardController::class, 'sord'])
            ->name('sord.dashboard')
            ->middleware('area:rdwprj,rdw');

        Route::get('/hr/dashboard', [\App\Http\Controllers\DashboardController::class, 'hrDashboard'])
            ->name('hr.dashboard')
            ->middleware('area:hr,nrdi,hqs,rdw');

        // HR Modules Under Development (Navy Civilians, PN Officers, PN CPO/Sailors)
        Route::get('/hr/navy-civilians', function() {
            return view('hr.under_development', [
                'title' => 'Navy Civilians',
                'category' => 'Civilian Personnel Management',
                'description' => 'Navy Civilians personnel rosters, service records, and civilian establishment listings are currently under active development.'
            ]);
        })->name('hr.navy_civilians');

        Route::get('/hr/pn-officers', function() {
            return view('hr.under_development', [
                'title' => 'PN Officers',
                'category' => 'Naval Officers Roster',
                'description' => 'PN Officers posted strength, ranks, branches, and establishment details module is currently under active development.'
            ]);
        })->name('hr.pn_officers');

        Route::get('/hr/pn-sailors', function() {
            return view('hr.under_development', [
                'title' => 'PN CPO / Sailors',
                'category' => 'Naval Sailors & CPOs',
                'description' => 'PN CPO / Sailors rosters, rates, branches, and postings management module is currently under active development.'
            ]);
        })->name('hr.pn_sailors');

        Route::prefix('nrdi')->middleware('area:nrdi,rdw,hqs,proc,prc,fin,hr,prj,rdwprj,it')->name('nrdi.')->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'nrdiDashboard'])->name('dashboard');
            Route::get('/dashboard-data', [\App\Http\Controllers\DashboardController::class, 'nrdiDashboardData'])->name('dashboard.data');
            // Redirect old routes to modern hubs
            Route::get('/contract-cases', function() {
                return redirect()->route('nrdi.contract_cases_new.index');
            })->name('contract_cases.index');
            
            Route::get('/purchase-cases', function() {
                return redirect()->route('nrdi.purchase_cases_new.index');
            })->name('purchase_cases.index');
            Route::get('/purchase-cases/{id}', [\App\Http\Controllers\PurchaseApprovalController::class, 'show'])->name('purchase_cases.show');
            Route::post('/purchase-cases/{id}/action', [\App\Http\Controllers\PurchaseApprovalController::class, 'action'])->name('purchase_cases.action');

            // Director Procurement Specialized Routes
            Route::prefix('procurement')->name('procurement.')->group(function () {
                Route::get('/dashboard', [\App\Http\Controllers\ProcurementDashboardController::class, 'index'])->name('purchase_cases.index');
                Route::get('/case/{id}', [\App\Http\Controllers\ProcurementDashboardController::class, 'show'])->name('purchase_cases.show');
                Route::post('/case/{id}/close', [\App\Http\Controllers\ProcurementDashboardController::class, 'closeCase'])->name('purchase_cases.close');
                
                // Dedicated Procurement Reports (Custom Inventory & Assets, Purchase Cases by Firms, etc.)
                Route::get('/reports', [\App\Http\Controllers\ProcurementReportsController::class, 'index'])->name('reports.index');
                Route::get('/reports/data', [\App\Http\Controllers\ProcurementReportsController::class, 'getReportData'])->name('reports.data');
                Route::get('/reports/export', [\App\Http\Controllers\ProcurementReportsController::class, 'exportExcel'])->name('reports.export');
            });

            // Firms Search, Registration, and Directory Routes
            Route::prefix('firms')->name('firms.')->group(function () {
                Route::get('/', function() {
                    return redirect()->route('nrdi.firms.list');
                })->name('index');
                Route::get('/list', [\App\Http\Controllers\FirmController::class, 'list'])->name('list');
                Route::post('/store', [\App\Http\Controllers\FirmController::class, 'store'])->name('store');
                Route::get('/data', [\App\Http\Controllers\FirmController::class, 'searchData'])->name('data');
                Route::get('/{id}', [\App\Http\Controllers\FirmController::class, 'show'])->name('show');
            });

            // Director Finance Specialized Routes
            Route::prefix('finance')->name('finance.')->group(function () {
                Route::get('/purchase-cases', function() {
                    return redirect()->route('nrdi.purchase_cases_new.finance.index');
                })->name('purchase_cases.index');
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
                // Common Scrutiny Reports (Accessible by all modules)
                Route::get('/case/{id}/case-detail', [\App\Http\Controllers\PurchaseController::class, 'caseDetail']);
                Route::get('/case/{id}/market-research', [\App\Http\Controllers\PurchaseController::class, 'marketResearch']);
                Route::get('/case/{id}/cs-formal', [\App\Http\Controllers\PurchaseController::class, 'csFormal']);
                Route::get('/case/{id}/it-annex', [\App\Http\Controllers\PurchaseController::class, 'itAnnex']);
                Route::post('/case/{id}/it-letter/save', [\App\Http\Controllers\PurchaseController::class, 'saveItLetter']);
            });

            Route::get('/contract-cases-new', [\App\Http\Controllers\ContractCaseController::class, 'index'])->name('contract_cases_new.index');

            Route::get('/projects', [ProjectController::class, 'nrdiIndex'])->name('projects.index');
            Route::get('/projects/{id}', [ProjectController::class, 'nrdiShow'])->name('projects.show');
        });

        // --- DIVISION CONTRACT CASE ROUTES ---
        Route::prefix('division')->middleware('area:prj,rdwprj')->group(function () {
            Route::get('/contract-cases', [\App\Http\Controllers\Division\ContractCaseController::class, 'index'])
                ->name('division.contract-cases.index');
            Route::get('/contract-cases/create', [\App\Http\Controllers\Division\ContractCaseController::class, 'create'])
                ->name('division.contract-cases.create');
            Route::get('/contract-cases/employee-contract/{empId}', [\App\Http\Controllers\Division\ContractCaseController::class, 'getEmployeeContractDetails'])
                ->name('division.contract-cases.employee-contract');
            Route::get('/contract-cases/{id}', [\App\Http\Controllers\Division\ContractCaseController::class, 'show'])
                ->name('division.contract-cases.show');
            Route::get('/contract-cases/{id}/edit', [\App\Http\Controllers\Division\ContractCaseController::class, 'edit'])
                ->name('division.contract-cases.edit');
            Route::put('/contract-cases/{id}', [\App\Http\Controllers\Division\ContractCaseController::class, 'update'])
                ->name('division.contract-cases.update');
            Route::post('/contract-cases', [\App\Http\Controllers\Division\ContractCaseController::class, 'store'])
                ->name('division.contract-cases.store');
            Route::post('/contract-cases/{id}/release', [\App\Http\Controllers\Division\ContractCaseController::class, 'release'])
                ->name('division.contract-cases.release');
            Route::post('/contract-cases/{id}/forward', [\App\Http\Controllers\Division\ContractCaseController::class, 'forward'])
                ->name('division.contract-cases.forward');
            Route::post('/contract-cases/{id}/cancel', [\App\Http\Controllers\Division\ContractCaseController::class, 'cancel'])
                ->name('division.contract-cases.cancel');
        });

        // --- HR CONTRACT CASE ROUTES & REPORTS ---
        Route::prefix('hr')->middleware('area:hr,prj,rdwprj,hqs,rdw,nrdi,it')->group(function () {
            Route::get('/contract-cases', [\App\Http\Controllers\HR\ContractCaseController::class, 'index'])
                ->name('hr.contract-cases.index');
            Route::get('/contract-cases/{id}', [\App\Http\Controllers\HR\ContractCaseController::class, 'show'])
                ->name('hr.contract-cases.show');
            Route::post('/contract-cases/{id}/forward', [\App\Http\Controllers\HR\ContractCaseController::class, 'forward'])
                ->name('hr.contract-cases.forward');
            Route::post('/contract-cases/{id}/return', [\App\Http\Controllers\HR\ContractCaseController::class, 'return'])
                ->name('hr.contract-cases.return');
            Route::post('/contract-cases/{id}/fulfill', [\App\Http\Controllers\HR\ContractCaseController::class, 'fulfill'])
                ->name('hr.contract-cases.fulfill');
            Route::post('/contract-cases/{id}/add-employee', [\App\Http\Controllers\HR\ContractCaseController::class, 'addEmployee'])
                ->name('hr.contract-cases.add-employee');
            Route::get('/contract-cases/preview-emp-id', [\App\Http\Controllers\HR\ContractCaseController::class, 'previewEmployeeId'])
                ->name('hr.contract-cases.preview-emp-id');
            Route::post('/contract-cases/{id}/reject', [\App\Http\Controllers\HR\ContractCaseController::class, 'reject'])
                ->name('hr.contract-cases.reject');

            // HR Reports (Single Page like Finance Reports)
            Route::get('/reports', [DivHrController::class, 'hrReportsIndex'])->name('hr.reports.index');
            Route::get('/reports/data', [DivHrController::class, 'hrReportsData'])->name('hr.reports.data');
        });

        // --- FINANCE CONTRACT CASE ROUTES ---
        Route::prefix('finance')->middleware('area:fin')->group(function () {
            Route::get('/contract-cases', [\App\Http\Controllers\Finance\ContractCaseController::class, 'index'])
                ->name('finance.contract-cases.index');
            Route::get('/contract-cases/{id}', [\App\Http\Controllers\Finance\ContractCaseController::class, 'show'])
                ->name('finance.contract-cases.show');
            Route::post('/contract-cases/{id}/forward', [\App\Http\Controllers\Finance\ContractCaseController::class, 'forward'])
                ->name('finance.contract-cases.forward');
            Route::post('/contract-cases/{id}/return', [\App\Http\Controllers\Finance\ContractCaseController::class, 'return'])
                ->name('finance.contract-cases.return');
            Route::post('/contract-cases/{id}/reject', [\App\Http\Controllers\Finance\ContractCaseController::class, 'reject'])
                ->name('finance.contract-cases.reject');
        });

        // --- MD CONTRACT CASE ROUTES ---
        Route::prefix('md')->middleware('area:rdw')->group(function () {
            Route::get('/contract-cases', [\App\Http\Controllers\MD\ContractCaseController::class, 'index'])
                ->name('md.contract-cases.index');
            Route::get('/contract-cases/{id}', [\App\Http\Controllers\MD\ContractCaseController::class, 'show'])
                ->name('md.contract-cases.show');
            Route::post('/contract-cases/{id}/approve', [\App\Http\Controllers\MD\ContractCaseController::class, 'approve'])
                ->name('md.contract-cases.approve');
            Route::post('/contract-cases/{id}/forward', [\App\Http\Controllers\MD\ContractCaseController::class, 'forward'])
                ->name('md.contract-cases.forward');
            Route::post('/contract-cases/{id}/return', [\App\Http\Controllers\MD\ContractCaseController::class, 'return'])
                ->name('md.contract-cases.return');
            Route::post('/contract-cases/{id}/reject', [\App\Http\Controllers\MD\ContractCaseController::class, 'reject'])
                ->name('md.contract-cases.reject');
        });

        // --- DDG CONTRACT CASE ROUTES ---
        Route::prefix('ddg')->middleware('area:hqs')->group(function () {
            Route::get('/contract-cases', [\App\Http\Controllers\DDG\ContractCaseController::class, 'index'])
                ->name('ddg.contract-cases.index');
            Route::get('/contract-cases/{id}', [\App\Http\Controllers\DDG\ContractCaseController::class, 'show'])
                ->name('ddg.contract-cases.show');
            Route::post('/contract-cases/{id}/approve', [\App\Http\Controllers\DDG\ContractCaseController::class, 'approve'])
                ->name('ddg.contract-cases.approve');
            Route::post('/contract-cases/{id}/forward', [\App\Http\Controllers\DDG\ContractCaseController::class, 'forward'])
                ->name('ddg.contract-cases.forward');
            Route::post('/contract-cases/{id}/return', [\App\Http\Controllers\DDG\ContractCaseController::class, 'return'])
                ->name('ddg.contract-cases.return');
            Route::post('/contract-cases/{id}/reject', [\App\Http\Controllers\DDG\ContractCaseController::class, 'reject'])
                ->name('ddg.contract-cases.reject');
        });

        // --- DG CONTRACT CASE ROUTES ---
        Route::prefix('dg')->middleware('area:nrdi')->group(function () {
            Route::get('/contract-cases', [\App\Http\Controllers\DG\ContractCaseController::class, 'index'])
                ->name('dg.contract-cases.index');
            Route::get('/contract-cases/{id}', [\App\Http\Controllers\DG\ContractCaseController::class, 'show'])
                ->name('dg.contract-cases.show');
            Route::post('/contract-cases/{id}/approve', [\App\Http\Controllers\DG\ContractCaseController::class, 'approve'])
                ->name('dg.contract-cases.approve');
            Route::post('/contract-cases/{id}/forward', [\App\Http\Controllers\DG\ContractCaseController::class, 'forward'])
                ->name('dg.contract-cases.forward');
            Route::post('/contract-cases/{id}/return', [\App\Http\Controllers\DG\ContractCaseController::class, 'return'])
                ->name('dg.contract-cases.return');
            Route::post('/contract-cases/{id}/reject', [\App\Http\Controllers\DG\ContractCaseController::class, 'reject'])
                ->name('dg.contract-cases.reject');
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

        // --- FINANCE OF PROJECT (Division-level head status with Pcc/CSRF drill-down) ---
        Route::get('/finance-of-project', [\App\Http\Controllers\Division\FinanceOfProjectController::class, 'index'])
            ->name('division.finance-of-project.index');
        Route::get('/finance-of-project/{head_id}/{scope}/{figure}/{subhead?}', [\App\Http\Controllers\Division\FinanceOfProjectController::class, 'drillDown'])
            ->name('division.finance-of-project.drilldown');
        Route::get('/finance-of-project/ajax/projects-by-division/{unit_id?}', [\App\Http\Controllers\Division\FinanceOfProjectController::class, 'getProjectsByDivision'])
            ->name('division.finance-of-project.projects-by-division');

        Route::get('/milestone/{id}/edit', [ProjectController::class, 'editMilestone'])->name('milestone.edit');
        Route::post('/milestone/{id}/update', [ProjectController::class, 'updateMilestone'])->name('milestone.update');
        Route::get('/milestone/{id}/delete', [ProjectController::class, 'deleteMilestone'])->name('milestone.delete');
        Route::get('/gantchartpr', function () { return view('projects.gantchartpr'); })->name('gantchartpr');

        // --- PURCHASE & REPORTS (Project area) ---
        Route::get('/purchase/receipts', [\App\Http\Controllers\PurchaseReceiptController::class, 'index'])->name('purchase.receipts.index');
        Route::get('/purchase/receipts/case/{pcs_id}', [\App\Http\Controllers\PurchaseReceiptController::class, 'create'])->name('purchase.receipts.create');
        Route::post('/purchase/receipts/case/{pcs_id}', [\App\Http\Controllers\PurchaseReceiptController::class, 'store'])->name('purchase.receipts.store');
        Route::post('/purchase/receipts/case/{pcs_id}/cancel', [\App\Http\Controllers\PurchaseReceiptController::class, 'cancelCase'])->name('purchase.receipts.cancel');
        Route::get('/inventory/assets', [\App\Http\Controllers\PurchaseReceiptController::class, 'assetsIndex'])->name('inventory.assets.index');
        Route::post('/inventory/assets/transition/{iac_id}', [\App\Http\Controllers\PurchaseReceiptController::class, 'updateAssetStatus'])->name('inventory.assets.update_status');

        Route::get('/pc-initiation', [\App\Http\Controllers\PurchaseInitiationController::class, 'index'])->name('purchase.initiation.index');
        Route::get('/pc-initiation/case/{id}', [\App\Http\Controllers\PurchaseInitiationController::class, 'show'])->name('purchase.initiation.show');
        Route::post('/pc-initiation/case/{id}/save', [\App\Http\Controllers\PurchaseInitiationController::class, 'save'])->name('purchase.initiation.save');
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
        Route::post('/purchase/select-firm/{id}', [PurchaseController::class, 'selectFirm'])
            ->name('purchase.select_firm');
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
        Route::middleware('area:hr,prj,rdwprj,fin,rdw,hqs,nrdi,it')->group(function () {

            Route::get('/divhr/employelist', [DivHrController::class, 'employeelist'])->name('divhr.employelist');
            Route::prefix('divhr')->group(function () {

                Route::get('/employeelist', [DivHrController::class, 'employeelist'])
                    ->name('divhr.employelist');

                Route::get('/employee/{id}', [DivHrController::class, 'employeedetail'])
                    ->name('divhr.employeedetail');
                Route::get('/employee/{id}/edit', [DivHrController::class, 'employeeEdit'])
                    ->name('divhr.employee.edit');
                Route::post('/employee/{id}/update', [DivHrController::class, 'employeeUpdate'])
                    ->name('divhr.employee.update');
                Route::post('/employee/{id}/photo', [DivHrController::class, 'uploadPhoto'])
                    ->name('divhr.employee.upload_photo');

                Route::get('/attendance', [DivHrController::class, 'attendance'])
                    ->name('divhr.attendance');
                Route::post('/attendance/save', [DivHrController::class, 'attendanceSave'])
                    ->name('divhr.attendance.save')
                    ->middleware('approver');

                Route::get('/initiate-contract', [DivHrController::class, 'initiateContract'])
                    ->name('divhr.contract.initiate');

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
        ->group(function () {
            // Finance Dashboard
            Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'finDashboard'])
                ->middleware(['area:fin,nrdi'])
                ->name('dashboard');

            // COMMITMENTS & PAYMENTS - STRICTLY FINANCE ONLY
            Route::middleware(['area:fin'])->group(function () {
                Route::get('/commitments', function () {
                    return redirect()->route('fin.payments.index');
                })->name('commitments.landing');
                Route::get('/commitments/salary-orders', [\App\Http\Controllers\Finance\PaymentController::class, 'salaryPlaceholder'])->name('commitments.salary.placeholder');
                Route::get('/payments', [\App\Http\Controllers\Finance\PaymentController::class, 'index'])->name('payments.index');
                Route::get('/payments/{cmt_id}', [\App\Http\Controllers\Finance\PaymentController::class, 'show'])->name('payments.show');
                Route::post('/payments/{cmt_id}/transaction', [\App\Http\Controllers\Finance\PaymentController::class, 'storeTransaction'])->name('payments.store_transaction');
            });
            
            // Finance Reports (Accessible to HQ, Divisions, Fin)
            Route::middleware(['area:fin,prj,rdwprj,hqs,rdw,nrdi,it'])->group(function () {
                Route::get('/reports', [\App\Http\Controllers\Finance\FinanceReportsController::class, 'index'])->name('reports.index');
                Route::get('/reports/data', [\App\Http\Controllers\Finance\FinanceReportsController::class, 'getReportData'])->name('reports.data');
                Route::get('/reports/export', [\App\Http\Controllers\Finance\FinanceReportsController::class, 'exportExcel'])->name('reports.export');
            });
        });

    // Unified Group for Approvals & Scrutiny (Redirect old routes to modern purchase_cases_new hub)
    Route::middleware(['area:proc,prc,fin,rdw,hqs,nrdi,prj,rdwprj,it'])->group(function () {
        Route::get('/approvals/dashboard', function() {
            return redirect()->route('nrdi.purchase_cases_new.index');
        })->name('approvals.dashboard');
        Route::get('/approvals/show/{id}', function($id) {
            return redirect()->route('nrdi.purchase_cases_new.show', $id);
        })->name('approvals.show');
        Route::post('/approvals/action/{id}', [\App\Http\Controllers\PurchaseCaseController::class, 'action'])->name('approvals.action');
    });

    // Procurement Notifications
    Route::get('/notifications/unread', [\App\Http\Controllers\NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');

    // =========================================================
    // COMPLAINTS & SUGGESTIONS SUPPORT TICKETING SYSTEM
    // =========================================================
    Route::get('/support/tickets', [\App\Http\Controllers\SupportTicketController::class, 'index'])->name('support.tickets.index');
    Route::post('/support/tickets', [\App\Http\Controllers\SupportTicketController::class, 'store'])->name('support.tickets.store');
    Route::get('/support/tickets/{id}', [\App\Http\Controllers\SupportTicketController::class, 'show'])->name('support.tickets.show');
    Route::post('/support/tickets/{id}/reply', [\App\Http\Controllers\SupportTicketController::class, 'reply'])->name('support.tickets.reply');
    Route::post('/support/tickets/{id}/status', [\App\Http\Controllers\SupportTicketController::class, 'updateStatus'])->name('support.tickets.status');

    }); // End force password change
}); // End Auth

// Direct Storage File Serving Route (Handles previews, streaming, and cross-network requests)
Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    if (!file_exists($fullPath) || is_dir($fullPath)) {
        $altPath = public_path('storage/' . $path);
        if (file_exists($altPath) && !is_dir($altPath)) {
            $fullPath = $altPath;
        } else {
            abort(404, 'Requested document not found.');
        }
    }

    $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
    $mimeTypes = [
        'pdf' => 'application/pdf',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'doc' => 'application/msword',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xls' => 'application/vnd.ms-excel',
        'txt' => 'text/plain',
    ];
    $mime = $mimeTypes[$ext] ?? (mime_content_type($fullPath) ?: 'application/octet-stream');

    return response()->file($fullPath, [
        'Content-Type' => $mime,
        'Content-Disposition' => 'inline; filename="' . basename($fullPath) . '"',
        'X-Frame-Options' => 'SAMEORIGIN',
        'Access-Control-Allow-Origin' => '*',
    ]);
})->where('path', '.*')->name('storage.serve');

