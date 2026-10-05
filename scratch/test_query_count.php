<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

config(['hrforms.enabled' => true]);
\Illuminate\Support\Facades\DB::enableQueryLog();

$case = \App\Models\HrCtrCase::where('ctc_status', 'Draft')->where('ctc_type', 'Hg')->first() 
    ?? \App\Models\HrCtrCase::where('ctc_type', 'Hg')->first();

echo "Testing Case: #{$case->ctc_id} Type: {$case->ctc_type} Status: {$case->ctc_status}\n";

\App\Models\HrForms\CaseForm::where('case_id', $case->ctc_id)->delete();
\App\Models\HrForms\FormAuditLog::where('case_id', $case->ctc_id)->delete();

\Illuminate\Support\Facades\DB::flushQueryLog();

$gen = app(\App\Services\HrForms\FormGenerationService::class);
$result = $gen->syncForms($case);

$queries = \Illuminate\Support\Facades\DB::getQueryLog();
echo "Total queries during fresh syncForms: " . count($queries) . "\n";

