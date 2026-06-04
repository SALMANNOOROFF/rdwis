<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$itemsInput = [
    ["desc" => "MOUSE", "qty" => 1],
    ["desc" => "c88", "qty" => 1]
];

$itemIds = [];
foreach ($itemsInput as $idx => $item) {
    if (empty(trim($item['desc']))) continue;
    $itemIds[] = rand(100, 200);
}

$quotationsInput = [
    "57" => [ "101", "102" ],
    "180" => [ "103", "104" ]
];

foreach ($quotationsInput as $firmId => $itemPrices) {
    if (is_array($itemPrices)) {
        foreach ($itemPrices as $idx => $price) {
            echo "idx: $idx, price: $price, itemIds val: " . (isset($itemIds[$idx]) ? $itemIds[$idx] : "NOT SET") . "\n";
        }
    }
}
