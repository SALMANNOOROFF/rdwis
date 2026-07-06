<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\FinancialIntelligenceService;

$service = new FinancialIntelligenceService();

// Test HeadId 200012
$headId = 200012;
$status = $service->getHeadStatus($headId);

echo "=== Testing HeadId: $headId ===\n";
echo "Head Name: " . $status->head_name . "\n";
echo "Total Expenditure: " . number_format($status->expenditure, 2) . "\n";
echo "  Project Expenditure: " . number_format($status->prj_expenditure, 2) . "\n";
echo "  CSRF Expenditure: " . number_format($status->cf_expenditure, 2) . "\n";
echo "Total Commitments: " . number_format($status->commitments, 2) . "\n";
echo "  Project Commitments: " . number_format($status->prj_commitments, 2) . "\n";
echo "  CSRF Commitments: " . number_format($status->cf_commitments, 2) . "\n";
echo "Total In Process: " . number_format($status->in_process, 2) . "\n";
echo "  Project In Process: " . number_format($status->prj_in_process, 2) . "\n";
echo "  CSRF In Process: " . number_format($status->cf_in_process, 2) . "\n";
