<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Http\Controllers\Finance\ContractCaseController as FinanceController;
use App\Http\Controllers\MD\ContractCaseController as MdController;
use App\Http\Controllers\DDG\ContractCaseController as DdgController;
use App\Http\Controllers\DG\ContractCaseController as DgController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

echo "════════════════════════════════════════════════════════════════════\n";
echo "AUDIT: TABS & VIEWS ACROSS ALL AUTHORITIES (FINANCE, MD, DDG, DG)\n";
echo "════════════════════════════════════════════════════════════════════\n\n";

// 1. Finance
$finUser = User::find(27);
Auth::setUser($finUser);
$finCtrl = app(FinanceController::class);
$viewFin = $finCtrl->index();
$htmlFin = $viewFin->render();
echo "1. FINANCE HUB:\n";
echo "   - Pending: " . $viewFin->getData()['actionReqCases']->count() . "\n";
echo "   - In Pipeline: " . $viewFin->getData()['initiatedCases']->count() . "\n";
echo "   - Closed: " . $viewFin->getData()['completedCases']->count() . "\n";
echo "   - Total: " . $viewFin->getData()['cases']->count() . "\n";
echo "   - Render: " . strlen($htmlFin) . " bytes [✓ SUCCESS]\n\n";

// 2. MD
$mdUser = User::find(21);
Auth::setUser($mdUser);
$mdCtrl = app(MdController::class);
$viewMd = $mdCtrl->index();
$htmlMd = $viewMd->render();
echo "2. MD HUB:\n";
echo "   - Pending: " . $viewMd->getData()['actionReqCases']->count() . "\n";
echo "   - In Pipeline: " . $viewMd->getData()['initiatedCases']->count() . "\n";
echo "   - Closed: " . $viewMd->getData()['completedCases']->count() . "\n";
echo "   - Total: " . $viewMd->getData()['cases']->count() . "\n";
echo "   - Render: " . strlen($htmlMd) . " bytes [✓ SUCCESS]\n\n";

// 3. DDG
$ddgUser = User::find(22);
Auth::setUser($ddgUser);
$ddgCtrl = app(DdgController::class);
$viewDdg = $ddgCtrl->index();
$htmlDdg = $viewDdg->render();
echo "3. DDG HUB:\n";
echo "   - Pending: " . $viewDdg->getData()['actionReqCases']->count() . "\n";
echo "   - In Pipeline: " . $viewDdg->getData()['initiatedCases']->count() . "\n";
echo "   - Closed: " . $viewDdg->getData()['completedCases']->count() . "\n";
echo "   - Total: " . $viewDdg->getData()['cases']->count() . "\n";
echo "   - Render: " . strlen($htmlDdg) . " bytes [✓ SUCCESS]\n\n";

// 4. DG
$dgUser = User::where('acc_untarea', 'nrdi')->first() ?? User::first();
Auth::setUser($dgUser);
$dgCtrl = app(DgController::class);
$viewDg = $dgCtrl->index();
$htmlDg = $viewDg->render();
echo "4. DG HUB:\n";
echo "   - Pending: " . $viewDg->getData()['actionReqCases']->count() . "\n";
echo "   - In Pipeline: " . $viewDg->getData()['initiatedCases']->count() . "\n";
echo "   - Closed: " . $viewDg->getData()['completedCases']->count() . "\n";
echo "   - Total: " . $viewDg->getData()['cases']->count() . "\n";
echo "   - Render: " . strlen($htmlDg) . " bytes [✓ SUCCESS]\n\n";

echo "════════════════════════════════════════════════════════════════════\n";
echo "ALL EXECUTIVE HUBS SUCCESSFULLY UPGRADED AND VERIFIED!\n";
echo "════════════════════════════════════════════════════════════════════\n";
