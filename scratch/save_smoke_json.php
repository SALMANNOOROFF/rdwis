<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\HrCtrCase;
use App\Services\HrForms\Extractors\AnnexMExtractor;
use App\Services\HrForms\Extractors\AnnexNExtractor;
use App\Services\HrForms\Extractors\AnnexBExtractor;

$c375 = HrCtrCase::find(375);
file_put_contents('scratch/smoke_c375_m.json', json_encode(app(AnnexMExtractor::class)->extract($c375), JSON_PRETTY_PRINT));
file_put_contents('scratch/smoke_c375_n.json', json_encode(app(AnnexNExtractor::class)->extract($c375), JSON_PRETTY_PRINT));

$c376 = HrCtrCase::find(376);
file_put_contents('scratch/smoke_c376_b.json', json_encode(app(AnnexBExtractor::class)->extract($c376), JSON_PRETTY_PRINT));
echo "Saved JSON files successfully." . PHP_EOL;
