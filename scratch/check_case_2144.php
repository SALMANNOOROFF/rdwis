<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\CenAccount::whereIn('acc_untarea', ['rdw', 'nrdi', 'rdwprj', 'hqs'])->first() ?: \App\Models\CenAccount::first();
\Illuminate\Support\Facades\Auth::login($user);

$controller = app(\App\Http\Controllers\Division\FinanceOfProjectController::class);

$resEq = $controller->drillDown(200018, 'subhead', 'in-process', 'Equipment');
$dataEq = $resEq->getData();

echo "=== DRILLDOWN FOR EQUIPMENT (NDB 200018) ===\n";
echo "Total items: " . count($dataEq['items'] ?? []) . "\n";
foreach ($dataEq['items'] ?? [] as $it) {
    echo "Ref: {$it->ref_no} | Title: {$it->title} | Subhead: {$it->subhead} | Amount: {$it->amount}\n";
}

$resMisc = $controller->drillDown(200018, 'subhead', 'in-process', 'Misc');
$dataMisc = $resMisc->getData();

echo "\n=== DRILLDOWN FOR MISC (NDB 200018) ===\n";
echo "Total items: " . count($dataMisc['items'] ?? []) . "\n";
foreach ($dataMisc['items'] ?? [] as $it) {
    echo "Ref: {$it->ref_no} | Title: {$it->title} | Subhead: {$it->subhead} | Amount: {$it->amount}\n";
}
