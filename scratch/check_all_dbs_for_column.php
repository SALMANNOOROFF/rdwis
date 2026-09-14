<?php

$dbs = ['updatedrdwV1', 'rdw', 'liverdw', 'accessdev', 'postgres'];
$user = 'postgres';
$pass = '12345678';
$host = '127.0.0.1';
$port = 5433;

foreach ($dbs as $db) {
    try {
        $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass);
        $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_schema = 'pur' AND table_name = 'pur_it_letters'");
        $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Database: $db (port $port)\n";
        echo "Has pur_it_letters: " . (count($cols) > 0 ? 'YES' : 'NO') . "\n";
        echo "Has pit_distribution_label: " . (in_array('pit_distribution_label', $cols) ? 'YES' : 'NO') . "\n";
        echo "Has pit_paragraphs: " . (in_array('pit_paragraphs', $cols) ? 'YES' : 'NO') . "\n";
        echo "Columns: " . implode(', ', $cols) . "\n\n";
    } catch (Exception $e) {
        echo "Database: $db (port $port) ERROR: " . $e->getMessage() . "\n\n";
    }
}

// Also check port 5432 just in case
$port = 5432;
foreach ($dbs as $db) {
    try {
        $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass);
        $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_schema = 'pur' AND table_name = 'pur_it_letters'");
        $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Database: $db (port $port)\n";
        echo "Has pur_it_letters: " . (count($cols) > 0 ? 'YES' : 'NO') . "\n";
        echo "Has pit_distribution_label: " . (in_array('pit_distribution_label', $cols) ? 'YES' : 'NO') . "\n";
        echo "Has pit_paragraphs: " . (in_array('pit_paragraphs', $cols) ? 'YES' : 'NO') . "\n";
        echo "Columns: " . implode(', ', $cols) . "\n\n";
    } catch (Exception $e) {
        // Silent or error
    }
}
