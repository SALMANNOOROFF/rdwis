<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::select("
    SELECT 
        t.title,
        t.annex,
        t.form_code,
        m.hiring_type,
        m.requirement,
        m.condition_rule
    FROM hrforms.form_templates t
    JOIN hrforms.form_matrix m ON t.form_code = m.form_code
    ORDER BY t.id, m.hiring_type
");

$matrix = [];
$formMeta = [];
foreach ($rows as $r) {
    $key = $r->form_code;
    $formMeta[$key] = [
        'title' => $r->title,
        'annex' => $r->annex,
        'form_code' => $r->form_code,
    ];
    $matrix[$key][$r->hiring_type] = $r->requirement . ($r->condition_rule ? " ({$r->condition_rule})" : "");
}

$types = ['Fresh', 'Extension', 'Renewal', 'Rehiring', 'Internship'];

echo str_pad("Form", 42) . " | " . str_pad("Annex", 6) . " | " . str_pad("Form No.", 14) . " | ";
foreach ($types as $t) {
    echo str_pad($t, 16) . " | ";
}
echo "\n" . str_repeat("-", 155) . "\n";

foreach ($formMeta as $code => $meta) {
    echo str_pad($meta['title'], 42) . " | " . str_pad($meta['annex'], 6) . " | " . str_pad($meta['form_code'], 14) . " | ";
    foreach ($types as $t) {
        $val = $matrix[$code][$t] ?? 'not_required';
        echo str_pad($val, 16) . " | ";
    }
    echo "\n";
}
