<?php
@session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Define the secret key if not already defined in secrets.php
// (User should update secrets.php for actual security)
$secretKey = defined('ADMIN_SECRET_KEY') ? ADMIN_SECRET_KEY : 'default_fallback_change_me';

$key = isset($_GET['key']) ? $_GET['key'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : 'auth'; // 'auth' or 'login'

if (empty($key) || $key !== $secretKey) {
    die("Unauthorized Access. Invalid Secret Key.");
}

// 1. Generate the Secure Token
$token = hash('sha256', $secretKey);

// 2. Set the long-lived cookie (30 days)
setcookie('admin_device_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);

if ($action === 'login') {
    // 3. Magical Auto-Login
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
        $stmt->execute();
        $admin = $stmt->fetch();

        if ($admin) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['user_name'] = $admin['name'];
            $_SESSION['user_role'] = $admin['role'];
            $_SESSION['user_email'] = $admin['email'];
            
            header("Location: dashboard.php?msg=device_authorized_and_logged_in");
            exit;
        }
    } catch (Exception $e) {
        die("Login Error: " . $e->getMessage());
    }
}

// Default response: Just authorize the device
header("Location: ../login.php?msg=device_authorized");
exit;
