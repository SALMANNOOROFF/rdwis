<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Finance\FinanceReportsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

try {
    $user = DB::table('cen.accounts')->where('acc_status', 'Active')->first();
    Auth::login(\App\Models\CenAccount::find($user->acc_id));
    
    $controller = app(FinanceReportsController::class);
    $req = new Request([
        'type' => 'inventory_assets',
        'category' => 'Assets',
        'limit' => 1
    ]);
    
    $res = $controller->getReportData($req);
    $data = $res->getData()->data;
    if (count($data) > 0) {
        echo "Item: " . $data[0]->desc . "\n";
        echo "Mapped Category: " . $data[0]->category . "\n";
    } else {
        echo "No assets found.\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
