<!DOCTYPE html>
<html>

<head>
    <title>IP Security Debug</title>
</head>

<body>
    <h1>IP Security Debug</h1>

    <?php
    try {
        // Direct database connection
        $pdo = new PDO('mysql:host=localhost;dbname=rdwalpaper_db', 'root', '');

        // Get current IP (better detection)
        $currentIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        // Try to get real IP if behind proxy
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $currentIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $currentIp = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
            $currentIp = $_SERVER['HTTP_X_REAL_IP'];
        }

        // For local testing, show common local IPs
        $possibleIps = [$currentIp];
        if ($currentIp === '127.0.0.1' || $currentIp === '::1') {
            // Add common local network IPs for testing
            $possibleIps[] = '192.168.1.4';
            $possibleIps[] = 'fe80::98c:448c:891f:71d9%14';
        }
        echo "<h2>Current Information</h2>";
        echo "<p><strong>Detected IP:</strong> " . htmlspecialchars($currentIp) . "</p>";
        if (count($possibleIps) > 1) {
            echo "<p><strong>Possible IPs to check:</strong> " . implode(', ', array_map('htmlspecialchars', $possibleIps)) . "</p>";
        }

        // Check all IP records
        $stmt = $pdo->query('SELECT * FROM ip_addresses ORDER BY id');
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<h2>All IP Records in Database (" . count($records) . " total)</h2>";

        if (empty($records)) {
            echo "<p style='color: orange;'>No IP records found in database</p>";
        } else {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>IP Address</th><th>Status</th><th>Security Status</th><th>Description</th></tr>";

            foreach ($records as $record) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($record['id']) . "</td>";
                echo "<td>" . htmlspecialchars($record['ip_address']) . "</td>";
                echo "<td>" . htmlspecialchars($record['status']) . "</td>";
                echo "<td>" . htmlspecialchars($record['security_status']) . "</td>";
                echo "<td>" . htmlspecialchars($record['description'] ?? '') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }

        // Check security status
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM ip_addresses WHERE security_status = "active" OR security_status = "1"');
        $securityActiveCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        $securityActive = $securityActiveCount > 0;

        echo "<h2>Security Status</h2>";
        echo "<p><strong>Security Active:</strong> " . ($securityActive ? 'YES' : 'NO') . " ($securityActiveCount records with 'active' or '1' status)</p>";

        // Check current IP in database
        $currentIpRecord = null;
        $matchedIp = null;

        foreach ($possibleIps as $testIp) {
            $stmt = $pdo->prepare('SELECT * FROM ip_addresses WHERE ip_address = ?');
            $stmt->execute([$testIp]);
            $ipRecord = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($ipRecord) {
                $currentIpRecord = $ipRecord;
                $matchedIp = $testIp;
                break;
            }
        }

        echo "<h2>Your IP Status</h2>";
        if ($currentIpRecord) {
            echo "<p style='color: green;'><strong>Found matching IP in database: " . htmlspecialchars($matchedIp) . "</strong></p>";
            echo "<p>Status: " . htmlspecialchars($currentIpRecord['status']) . "</p>";
            echo "<p>Security Status: " . htmlspecialchars($currentIpRecord['security_status']) . "</p>";
            echo "<p>Description: " . htmlspecialchars($currentIpRecord['description'] ?? '') . "</p>";
        } else {
            echo "<p style='color: red;'><strong>None of your possible IPs are in database</strong></p>";
            echo "<p>Tested IPs: " . implode(', ', array_map('htmlspecialchars', $possibleIps)) . "</p>";
        }

        // Login decision logic
        echo "<h2>Login Decision</h2>";

        // Check for admin users in database
        $stmt = $pdo->query('SELECT email, role FROM users WHERE LOWER(role) = "admin"');
        $adminUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<h3>Admin Users (Bypass IP Security)</h3>";
        if (!empty($adminUsers)) {
            echo "<p style='color: blue;'>Admin users found - they will bypass IP security:</p>";
            echo "<ul>";
            foreach ($adminUsers as $admin) {
                echo "<li>" . htmlspecialchars($admin['email']) . " (role: " . htmlspecialchars($admin['role']) . ")</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No admin users found in database</p>";
        }

        echo "<h3>Regular Users IP Security</h3>";
        if ($securityActive) {
            if (!$currentIpRecord) {
                echo "<p style='color: red; font-weight: bold;'>❌ LOGIN SHOULD BE BLOCKED: Security is active but your IP is not in database</p>";
            } elseif ($currentIpRecord['status'] !== '1') {
                echo "<p style='color: red; font-weight: bold;'>❌ LOGIN SHOULD BE BLOCKED: Security is active and your IP status is not '1' (current: " . htmlspecialchars($currentIpRecord['status']) . ")</p>";
            } else {
                echo "<p style='color: green; font-weight: bold;'>✅ LOGIN SHOULD BE ALLOWED: Security is active and your IP is authorized</p>";
            }
        } else {
            if ($currentIpRecord && $currentIpRecord['status'] === '0') {
                echo "<p style='color: red; font-weight: bold;'>❌ LOGIN SHOULD BE BLOCKED: Your IP is specifically blocked</p>";
            } else {
                echo "<p style='color: green; font-weight: bold;'>✅ LOGIN SHOULD BE ALLOWED: Security is not active" . ($currentIpRecord ? " and your IP is not blocked" : " and your IP is not in database") . "</p>";
            }
        }

    } catch (Exception $e) {
        echo "<p style='color: red;'>Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    ?>

    <h2>Testing Instructions</h2>
    <ol>
        <li><strong>Admin Users:</strong> Users with role = 'admin' will ALWAYS bypass IP security</li>
        <li><strong>Regular Users:</strong> IP security applies based on the rules below:
            <ul>
                <li>If security is active and user's IP is not in the database, login will fail</li>
                <li>If security is active and user's IP status is '0', login will fail</li>
                <li>If security is not active, only specifically blocked IPs (status '0') will fail</li>
            </ul>
        </li>
        <li><strong>To test admin bypass:</strong>
            <ul>
                <li>Create a user with role = 'admin'</li>
                <li>Login with admin credentials - should work regardless of IP security</li>
            </ul>
        </li>
        <li><strong>To test IP security:</strong>
            <ul>
                <li>Use a regular user (role != 'admin')</li>
                <li>Set security_status = '1' for any IP record</li>
                <li>Remove your IP from database or set status to '0'</li>
                <li>Login should fail with "Your IP address is not authorized"</li>
            </ul>
        </li>
    </ol>
</body>

</html>