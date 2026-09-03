<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\DivHrController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;

$user = User::where('acc_untarea', 'hr')->first();
Auth::login($user);

$controller = new DivHrController();

// Find an employee with 65000 contract salary
$contracts65k = DB::table('hr.contracts')->where('ctr_salary', 65000)->get();
echo "Found " . count($contracts65k) . " contracts with 65k salary.\n";

foreach ($contracts65k->take(3) as $c) {
    echo "\n--- Testing Employee: {$c->ctr_num} (Contract #{$c->ctr_id}) ---\n";
    $view = $controller->employeedetail($c->ctr_num);
    $rendered = $view->render();

    if (strpos($rendered, '780,000') !== false) {
        echo "✓ Annual Salary [780,000] rendered successfully!\n";
    } else {
        echo "✗ Annual Salary 780,000 NOT found in rendered HTML.\n";
    }

    if (strpos($rendered, '65,000 / mo') !== false) {
        echo "✓ Monthly breakdown [65,000 / mo] rendered successfully!\n";
    } else {
        echo "✗ Monthly breakdown 65,000 / mo NOT found.\n";
    }
}
