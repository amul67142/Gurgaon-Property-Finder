<?php
// Database Configuration - AUTO ENVIRONMENT DETECTION
// Automatically switches between local and production credentials
if (file_exists(__DIR__ . '/secrets.php')) {
    require_once __DIR__ . '/secrets.php';
}

// Detect environment based on hostname
$isLocal = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', 'localhost:80', 'localhost:8080']);

if ($isLocal) {
    // LOCAL DEVELOPMENT CREDENTIALS
    $host = 'localhost';
    $dbname = 'ggn';
    $username = 'root';
    $password = '';
} else {
    // PRODUCTION CREDENTIALS (Hostinger)
    $host = 'localhost';
    $dbname = 'u650869678_gurugaonpro';
    $username = 'u650869678_gurugaonpro';
    $password = 'Amul@123#';
}

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

/* ==========================
   Base URL Configuration
   ========================== */

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$hostName = $_SERVER['HTTP_HOST'];

// Project root (parent of config folder)
$project_root = dirname(__DIR__);
$doc_root = $_SERVER['DOCUMENT_ROOT'];

// Normalize slashes
$project_root = str_replace('\\', '/', $project_root);
$doc_root = str_replace('\\', '/', $doc_root);

// Calculate subpath
// If project_root equals doc_root, we're in the root (no subpath)
// Otherwise, calculate the relative path
if ($project_root === $doc_root) {
    $url_subpath = '';
} else {
    $url_subpath = str_replace($doc_root, '', $project_root);
}

$base_url = $protocol . "://" . $hostName . $url_subpath;

if (!defined('BASE_URL')) {
    define('BASE_URL', $base_url);
}