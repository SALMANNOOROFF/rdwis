<?php

$dbs = ['rdw', 'liverdw', 'accessdev', 'postgres', 'updatedrdwV1'];
$user = 'postgres';
$pass = '12345678';
$host = '127.0.0.1';

foreach ([5433, 5432] as $port) {
    foreach ($dbs as $db) {
        try {
            $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass);
            $stmt = $pdo->query("SELECT pcs_id, pcs_title, pcs_status FROM pur.purcases WHERE pcs_id = 2349");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                echo ">>> FOUND IN db: $db on port $port: ID: {$row['pcs_id']}, Title: {$row['pcs_title']}\n";
                // Check if pit_distribution_label exists in this db
                $colStmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_schema = 'pur' AND table_name = 'pur_it_letters'");
                $cols = $colStmt->fetchAll(PDO::FETCH_COLUMN);
                echo "pur_it_letters columns in $db: " . implode(', ', $cols) . "\n";
            }
        } catch (Exception $e) {
            // ignore
        }
    }
}
