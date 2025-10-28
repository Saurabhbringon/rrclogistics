<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=rdwalpaper', 'root', '');

    echo "=== Database Check ===\n";

    $stmt = $pdo->query('SELECT * FROM ip_addresses');
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "IP Records Count: " . count($records) . "\n";
    foreach ($records as $r) {
        echo "IP: " . $r['ip_address'] . " | Status: " . $r['status'] . " | Security: " . $r['security_status'] . "\n";
    }

    $stmt = $pdo->query('SELECT COUNT(*) as count FROM ip_addresses WHERE security_status = "active"');
    $active = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Security Active Records: " . $active . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>