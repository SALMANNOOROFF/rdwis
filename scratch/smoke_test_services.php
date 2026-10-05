<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\HrCtrCase;
use App\Services\HrForms\Extractors\AnnexAExtractor;
use App\Services\HrForms\Extractors\AnnexBExtractor;
use App\Services\HrForms\Extractors\AnnexNExtractor;
use App\Services\HrForms\Extractors\AnnexMExtractor;

echo "=================================================================" . PHP_EOL;
echo "SMOKE TEST 1: CASE #375 (RENEWAL, PROJECT ILRD)" . PHP_EOL;
echo "=================================================================" . PHP_EOL;
$case375 = HrCtrCase::find(375);

echo "--- ANNEX M (RDW/HR/F-08: Performance Appraisal Form) ---" . PHP_EOL;
$annexM = app(AnnexMExtractor::class)->extract($case375);
echo json_encode($annexM, JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL;

echo "--- ANNEX N (RDW/HR/F-09: Renewal / Extension of Contract(s)) ---" . PHP_EOL;
$annexN = app(AnnexNExtractor::class)->extract($case375);
echo json_encode($annexN, JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL;

echo "=================================================================" . PHP_EOL;
echo "SMOKE TEST 2: CASE #376 (FRESH HIRING)" . PHP_EOL;
echo "=================================================================" . PHP_EOL;
$case376 = HrCtrCase::find(376);

echo "--- ANNEX B (RDW/HR/F-02: Hiring Board for Selection of Contract Employee) ---" . PHP_EOL;
$annexB = app(AnnexBExtractor::class)->extract($case376);
echo json_encode($annexB, JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL;

echo "=================================================================" . PHP_EOL;
echo "SMOKE TEST 3: REAL ALLOCATIONS CASE (CASE #364, HEAD 350011)" . PHP_EOL;
echo "=================================================================" . PHP_EOL;
$case364 = HrCtrCase::find(364);

echo "--- ANNEX A (RDW/HR/F-01: In-Principle Approval for Hiring) ---" . PHP_EOL;
$annexA_364 = app(AnnexAExtractor::class)->extract($case364);
echo json_encode($annexA_364, JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL;

echo "--- ANNEX N (RDW/HR/F-09: Renewal / Extension) ---" . PHP_EOL;
$annexN_364 = app(AnnexNExtractor::class)->extract($case364);
echo json_encode($annexN_364, JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL;
