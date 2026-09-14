<?php

$pdo = new PDO("pgsql:host=127.0.0.1;port=5433;dbname=rdw", "postgres", "12345678");
$stmt = $pdo->query("SELECT pcs_id, pcs_title, pcs_date FROM pur.purcases WHERE pcs_id = 2349");
$r = $stmt->fetch(PDO::FETCH_ASSOC);
if ($r) {
    echo "Found in 'rdw': ID: {$r['pcs_id']} | Date: {$r['pcs_date']} | Title: {$r['pcs_title']}\n";
} else {
    echo "Not in 'rdw'\n";
}

$stmt2 = $pdo->query("SELECT pcs_id, pcs_title, pcs_date FROM pur.purcases ORDER BY pcs_id DESC LIMIT 5");
while ($r = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    echo "Recent in 'rdw': {$r['pcs_id']} - {$r['pcs_title']}\n";
}
