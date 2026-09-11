<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CenAccount;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;

$user = CenAccount::whereIn('acc_untarea', ['proc', 'prc'])->first();
Auth::guard('web')->login($user);

$controller = app(\App\Http\Controllers\PurchaseCaseController::class);
$c = Purchase::find(2322);
$resp = $controller->show($c->pcs_id);
$html = $resp->render();

echo "Case #2322 rendered successfully.\n";
if (strpos($html, '<span class="text-dark font-weight-bold" style="color: #0f172a !important;">' . $c->subhead_display . '</span>') !== false) {
    echo "PASS: Subhead rendered as plain bold text: '{$c->subhead_display}' (No badge)!\n";
} else {
    echo "FAIL: Expected plain text not found in HTML\n";
}
