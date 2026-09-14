<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Purchase;
use App\Models\CenAccount;
use App\Http\Controllers\PurchaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$p = Purchase::first();
if (!$p) {
    echo "No purchase case found.\n";
    exit(0);
}

$u = CenAccount::where('acc_username', 'superadminrdw')->first() ?? CenAccount::first();
Auth::login($u);

$controller = app(PurchaseController::class);
$req = new Request();
$res = $controller->createItLetter($req, $p->pcs_id);

echo "createItLetter response: " . $res->getContent() . "\n";

$saveReq = new Request([
    'ref_no' => 'R&D/TEST/' . $p->pcs_id,
    'letter_date' => date('d F Y'),
    'subject' => 'TEST RFQ SUBJECT',
    'see_distribution' => 'See distribution list',
    'para1' => 'Para 1 content',
    'para2' => 'Para 2 content',
    'para3' => 'Para 3 content',
    'signatory_name' => 'MUHAMMAD MUDASSIR',
    'signatory_rank' => 'Cdr (R)',
    'signatory_dept' => 'R&D Wing',
]);

$saveRes = $controller->saveItLetter($saveReq, $p->pcs_id);
echo "saveItLetter response: " . $saveRes->getContent() . "\n";
echo "SUCCESS! Both createItLetter and saveItLetter executed without error.\n";
