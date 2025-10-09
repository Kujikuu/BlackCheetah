<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get admin user
$admin = User::where('email', 'admin@blackcheetah.com')->first();

if (!$admin) {
    echo "Admin user not found!\n";
    exit(1);
}

// Create a token for the admin
$token = $admin->createToken('admin-test-token')->plainTextToken;

echo "Admin token created: $token\n\n";

// Test dashboard statistics endpoint
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/admin/dashboard/statistics');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json',
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Dashboard Statistics Response (HTTP $httpCode):\n";
echo $response . "\n\n";

// Test recent users endpoint
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/admin/dashboard/recent-users');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json',
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Recent Users Response (HTTP $httpCode):\n";
echo $response . "\n\n";

// Test franchisors list endpoint
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/admin/franchisors');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json',
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Franchisors List Response (HTTP $httpCode):\n";
echo $response . "\n\n";

// Clean up token
PersonalAccessToken::where('tokenable_id', $admin->id)->delete();
echo "Test completed and token cleaned up.\n";