<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\HrCtrCase;
use App\Services\HrForms\Extractors\AnnexAExtractor;
use App\Services\HrForms\Extractors\AnnexBExtractor;
use Illuminate\Support\Facades\DB;

$fresh = HrCtrCase::whereIn(DB::raw('UPPER(ctc_type)'), ['HG', 'CF', 'FRESH'])->orderBy('ctc_id', 'desc')->first();

echo "=== FRESH CASE: ID {$fresh->ctc_id}, {$fresh->ctc_newjobtitle}, Grade: {$fresh->ctc_newgrade} ===" . PHP_EOL;

echo "--- ANNEX A (F-01: In-Principle Approval for Hiring of HR) ---" . PHP_EOL;
echo json_encode(app(AnnexAExtractor::class)->extract($fresh), JSON_PRETTY_PRINT) . PHP_EOL;

echo "--- ANNEX B (F-02: Hiring Board for Selection of Contract Employee) ---" . PHP_EOL;
echo json_encode(app(AnnexBExtractor::class)->extract($fresh), JSON_PRETTY_PRINT) . PHP_EOL;
