<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$case = App\Models\HrCtrCase::find(376);
app(App\Services\HrForms\FormGenerationService::class)->syncForms($case);
echo "Forms for 376: " . DB::table('hrforms.case_forms')->where('case_id', 376)->count() . PHP_EOL;
