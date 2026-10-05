<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\HrCtrCase;
use App\Models\HrForms\CaseExtra;
use App\Models\HrForms\CaseForm;
use App\Services\HrForms\FormGenerationService;
use Illuminate\Support\Facades\Config;

Config::set('hrforms.enabled', true);

$fresh = HrCtrCase::whereIn(\Illuminate\Support\Facades\DB::raw('UPPER(ctc_type)'), ['HG', 'CF', 'FRESH'])->first();
$fresh->ctc_status = 'Draft';
$fresh->save();

CaseExtra::updateOrCreate(
    ['case_id' => $fresh->ctc_id],
    ['headcount_in_proposal' => false]
);

app(FormGenerationService::class)->syncForms($fresh);

$formA = CaseForm::where('case_id', $fresh->ctc_id)->where('form_code', 'RDW/HR/F-01')->first();
$formB = CaseForm::where('case_id', $fresh->ctc_id)->where('form_code', 'RDW/HR/F-02')->first();

file_put_contents('scratch/sample_form_a.json', json_encode($formA->form_data, JSON_PRETTY_PRINT));
file_put_contents('scratch/sample_form_b.json', json_encode($formB->form_data, JSON_PRETTY_PRINT));

echo "Saved sample JSONs for Case ID: {$fresh->ctc_id}" . PHP_EOL;
