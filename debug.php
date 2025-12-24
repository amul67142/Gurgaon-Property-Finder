<?php
/**
 * Debug Script - Upload to root directory
 * Access via: yourdomain.com/debug.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug Information</h1>";

// 1. Check PHP Version
echo "<h2>1. PHP Version</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Required: 7.4 or higher<br><br>";

// 2. Test Database Connection
echo "<h2>2. Database Connection Test</h2>";
$host = 'localhost';
$dbname = 'u650869678_gurugaonpro';
$username = 'u650869678_gurugaonpro';
$password = 'Amul@123#';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    echo "✅ <strong style='color:green'>Database Connected Successfully!</strong><br>";
} catch (PDOException $e) {
    echo "❌ <strong style='color:red'>Database Connection Failed:</strong> " . $e->getMessage() . "<br>";
}

// 3. Check File Paths
echo "<h2>3. File Path Check</h2>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Path: " . __DIR__ . "<br>";

// 4. Check if config files exist
echo "<h2>4. Config Files</h2>";
$configPath = __DIR__ . '/config/db.php';
echo "Looking for: $configPath<br>";
echo file_exists($configPath) ? "✅ config/db.php exists<br>" : "❌ config/db.php NOT found<br>";

$configPhp = __DIR__ . '/config/config.php';
echo "Looking for: $configPhp<br>";
echo file_exists($configPhp) ? "✅ config/config.php exists<br>" : "❌ config/config.php NOT found<br>";

// 5. Test including db.php
echo "<h2>5. Test Loading db.php</h2>";
try {
    require_once __DIR__ . '/config/db.php';
    echo "✅ db.php loaded successfully<br>";
    echo "BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'NOT DEFINED') . "<br>";
} catch (Exception $e) {
    echo "❌ Error loading db.php: " . $e->getMessage() . "<br>";
}

// 6. Check Required Extensions
echo "<h2>6. Required PHP Extensions</h2>";
$extensions = ['pdo', 'pdo_mysql', 'mbstring', 'json'];
foreach ($extensions as $ext) {
    echo extension_loaded($ext) ? "✅ $ext loaded<br>" : "❌ $ext NOT loaded<br>";
}

echo "<br><hr><p><strong>DELETE THIS FILE after debugging!</strong></p>";
?>
