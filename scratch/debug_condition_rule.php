<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\HrCtrCase;
use App\Models\HrForms\CaseExtra;
use App\Services\HrForms\FormGenerationService;

$case = HrCtrCase::first();
$rehCase = clone $case;
$rehCase->ctc_type = 'RH';

CaseExtra::updateOrCreate(
    ['case_id' => $rehCase->ctc_id],
    ['headcount_in_proposal' => false]
);

$gen = app(FormGenerationService::class);
$formsTrue = $gen->resolveRequiredForms($rehCase);
echo "formsTrue: " . json_encode($formsTrue, JSON_PRETTY_PRINT) . PHP_EOL;

$extra = CaseExtra::where('case_id', $rehCase->ctc_id)->first();
echo "headcount_in_proposal in DB: " . var_export($extra->headcount_in_proposal, true) . PHP_EOL;
