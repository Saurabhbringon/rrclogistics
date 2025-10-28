<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

// Get the kernel
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create a test request
$request = Illuminate\Http\Request::create('/api/check-ip', 'GET');

// Get the user's IP
$userIp = $request->ip();

echo "=== IP Security Debug ===\n";
echo "Current IP: " . $userIp . "\n";

// Check database connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=rdwalpaper', 'root', '');
    echo "Database: Connected ✓\n";

    // Check IP records
    $stmt = $pdo->query("SELECT * FROM ip_addresses");
    $ipRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "IP Records in Database: " . count($ipRecords) . "\n";
    foreach ($ipRecords as $record) {
        echo "- IP: " . $record['ip_address'] .
            " | Status: " . $record['status'] .
            " | Security: " . $record['security_status'] . "\n";
    }

    // Check if security is active
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM ip_addresses WHERE security_status = 'active'");
    $securityActive = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;

    echo "Security Active: " . ($securityActive ? "YES" : "NO") . "\n";

    // Check if current IP is in database
    $stmt = $pdo->prepare("SELECT * FROM ip_addresses WHERE ip_address = ?");
    $stmt->execute([$userIp]);
    $ipRecord = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "Current IP in Database: " . ($ipRecord ? "YES" : "NO") . "\n";

    if ($ipRecord) {
        echo "Current IP Status: " . $ipRecord['status'] . "\n";
        echo "Current IP Security Status: " . $ipRecord['security_status'] . "\n";
    }

    // Expected behavior
    echo "\n=== Expected Behavior ===\n";
    if ($securityActive) {
        if (!$ipRecord) {
            echo "Should BLOCK login: Security active but IP not in database\n";
        } elseif ($ipRecord['status'] !== '1') {
            echo "Should BLOCK login: Security active and IP status is not 1\n";
        } else {
            echo "Should ALLOW login: Security active and IP authorized\n";
        }
    } else {
        if ($ipRecord && $ipRecord['status'] === '0') {
            echo "Should BLOCK login: IP is specifically blocked\n";
        } else {
            echo "Should ALLOW login: Security not active\n";
        }
    }

} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
