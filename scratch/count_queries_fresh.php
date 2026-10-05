<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\HrCtrCase;
use App\Models\HrForms\CaseForm;
use App\Services\HrForms\FormGenerationService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

Config::set('hrforms.enabled', true);

$fresh = HrCtrCase::whereIn(DB::raw('UPPER(ctc_type)'), ['HG', 'CF', 'FRESH'])->where('ctc_status', 'Draft')->first();
if (!$fresh) {
    $fresh = HrCtrCase::whereIn(DB::raw('UPPER(ctc_type)'), ['HG', 'CF', 'FRESH'])->first();
    $fresh->ctc_status = 'Draft';
    $fresh->save();
}

// Clear any existing forms for clean first-time count
CaseForm::where('case_id', $fresh->ctc_id)->forceDelete();

DB::enableQueryLog();
$start = microtime(true);
$gen = app(FormGenerationService::class);
$result = $gen->syncForms($fresh);
$duration = round((microtime(true) - $start) * 1000, 2);
$queries = DB::getQueryLog();
DB::disableQueryLog();

echo "Total queries for fresh hiring sync: " . count($queries) . PHP_EOL;
echo "Execution time: {$duration} ms" . PHP_EOL;

$hrformsQueries = 0;
$legacyQueries = 0;
foreach ($queries as $q) {
    if (str_contains(strtolower($q['query']), 'hrforms')) {
        $hrformsQueries++;
    } else {
        $legacyQueries++;
    }
}

echo "hrforms queries: {$hrformsQueries}" . PHP_EOL;
echo "legacy read queries (cen/fin/prj/hr): {$legacyQueries}" . PHP_EOL;
