<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Http\Controllers\HR\ContractCaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$hrUser = User::find(24);
Auth::setUser($hrUser);

$controller = app(ContractCaseController::class);
$req = Request::create('/hr/contract-cases', 'GET');
$view = $controller->index($req);

echo "View Name: " . $view->name() . "\n";
echo "Pending Action Count: " . $view->getData()['actionReqCases']->count() . "\n";
echo "Open In Pipeline Count: " . $view->getData()['initiatedCases']->count() . "\n";
echo "Closed/Fulfilled Count: " . $view->getData()['completedCases']->count() . "\n";
echo "Total Master Count: " . $view->getData()['cases']->count() . "\n";

$html = $view->render();
echo "Rendered HTML length: " . strlen($html) . " bytes [✓ SUCCESS]\n";
