<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\DivHrController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

$user = User::where('acc_untarea', 'hr')->first();
Auth::login($user);

$controller = new DivHrController();

echo "=== VERIFYING FROM-TO SPANS RENDERING IN VIEWS ===\n\n";

// 1. Employee List
$listView = $controller->employeelist(new Request());
$listHtml = $listView->render();

echo "1. Employee List Check:\n";
if (strpos($listHtml, 'From Sep 2026 To Oct 2026 (2 Mos)') !== false) {
    echo "✓ Found span 1: 'From Sep 2026 To Oct 2026 (2 Mos)'\n";
}
if (strpos($listHtml, 'From Nov 2026 To Dec 2026 (2 Mos)') !== false) {
    echo "✓ Found span 2: 'From Nov 2026 To Dec 2026 (2 Mos)'\n";
}
if (strpos($listHtml, 'From Jan 2027 To Aug 2027 (8 Mos)') !== false) {
    echo "✓ Found span 3: 'From Jan 2027 To Aug 2027 (8 Mos)'\n";
}

// 2. Employee Details
$detailView = $controller->employeedetail('14-26-09-1234');
$detailHtml = $detailView->render();

echo "\n2. Employee Details Check (14-26-09-1234):\n";
if (strpos($detailHtml, 'From Sep 2026 To Oct 2026 (2 Mos)') !== false) {
    echo "✓ Found span 1: 'From Sep 2026 To Oct 2026 (2 Mos)'\n";
}
if (strpos($detailHtml, 'From Nov 2026 To Dec 2026 (2 Mos)') !== false) {
    echo "✓ Found span 2: 'From Nov 2026 To Dec 2026 (2 Mos)'\n";
}
if (strpos($detailHtml, 'From Jan 2027 To Aug 2027 (8 Mos)') !== false) {
    echo "✓ Found span 3: 'From Jan 2027 To Aug 2027 (8 Mos)'\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";
