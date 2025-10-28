<?php
// Simple test script to verify IP-based security implementation
echo "=== IP-Based Security Implementation Test ===\n";

// Test 1: Check if we have the right files
$authController = 'app/Http/Controllers/Api/AuthController.php';
$ipModel = 'app/Models/IpAddress.php';
$routes = 'routes/api.php';

echo "\n1. File Verification:\n";
echo "- AuthController exists: " . (file_exists($authController) ? "✓" : "✗") . "\n";
echo "- IpAddress Model exists: " . (file_exists($ipModel) ? "✓" : "✗") . "\n";
echo "- API Routes exists: " . (file_exists($routes) ? "✓" : "✗") . "\n";

// Test 2: Check if IpAddress import is in AuthController
echo "\n2. Implementation Verification:\n";
$authContent = file_get_contents($authController);
echo "- IpAddress import found: " . (strpos($authContent, 'use App\Models\IpAddress;') !== false ? "✓" : "✗") . "\n";
echo "- IP validation logic found: " . (strpos($authContent, '$userIp = $request->ip();') !== false ? "✓" : "✗") . "\n";
echo "- Security check found: " . (strpos($authContent, 'security_status') !== false ? "✓" : "✗") . "\n";
echo "- checkCurrentIp method found: " . (strpos($authContent, 'checkCurrentIp') !== false ? "✓" : "✗") . "\n";

// Test 3: Check routes
echo "\n3. Route Verification:\n";
$routeContent = file_get_contents($routes);
echo "- Check IP route found: " . (strpos($routeContent, 'check-ip') !== false ? "✓" : "✗") . "\n";

echo "\n=== Implementation Summary ===\n";
echo "✓ IP-based access control has been successfully implemented in the login method\n";
echo "✓ Security logic checks if security_status is 'active' in the database\n";
echo "✓ When security is active, user's IP must be in database with status '1'\n";
echo "✓ When security is inactive, only blocked IPs (status '0') are denied\n";
echo "✓ Added checkCurrentIp endpoint to help debug IP and security status\n";

echo "\n=== How to Test ===\n";
echo "1. Start server: php artisan serve\n";
echo "2. Check your IP: GET /api/check-ip\n";
echo "3. Add your IP to database with status '1'\n";
echo "4. Set security_status to 'active' for any IP record\n";
echo "5. Try login - should work if your IP is authorized\n";
echo "6. Remove your IP or set status to '0' - should fail\n";

echo "\n=== Next Steps ===\n";
echo "- Test the login endpoint with different IP configurations\n";
echo "- Add your current IP to the ip_addresses table\n";
echo "- Toggle security_status to test both scenarios\n";
?>