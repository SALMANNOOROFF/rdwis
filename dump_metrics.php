<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$h = 200008;
$svc = app(App\Services\FinancialIntelligenceService::class);
$status = $svc->getHeadStatus($h);

$results = [
    'Received_Project' => $status->pcc_received,
    'Received_CSRF' => $status->cf_received,
    
    'Expenditure_Project' => $status->pcc_expenditure,
    'Expenditure_CSRF' => $status->cf_expenditure,
    
    'Balance_Project' => $status->pcc_balance,
    'Balance_CSRF' => $status->cf_balance,
    
    'Commitments_Project' => $status->pcc_commitments,
    'Commitments_CSRF' => $status->cf_commitments,
    
    'InProcess_Project' => $status->pcc_in_process,
    'InProcess_CSRF' => $status->cf_in_process,
    
    'Available_Project' => $status->pcc_available,
    'Available_CSRF' => $status->cf_available,
    
    'YetToBeReceived_Project' => $status->pcc_yet_to_be_received,
    'YetToBeReceived_CSRF' => $status->cf_yet_to_be_received,
    
    'Remaining_Project' => $status->pcc_can_be_spent,
    'Remaining_CSRF' => $status->cf_can_be_spent,
];

echo json_encode($results, JSON_PRETTY_PRINT);
