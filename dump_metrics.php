<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$finService = app(\App\Services\FinancialIntelligenceService::class);
$head = \Illuminate\Support\Facades\DB::table('cen.heads')->first();
$res = $finService->getHeadStatus($head->hed_id);
echo json_encode($res, JSON_PRETTY_PRINT);
