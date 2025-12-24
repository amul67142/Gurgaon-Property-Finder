<?php
require_once __DIR__ . '/config/db.php';
$stmt = $pdo->query("SELECT id, name, email, role FROM users WHERE role = 'admin'");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($admins) > 0) {
    echo "Found Admins:\n";
    foreach ($admins as $admin) {
        echo "ID: " . $admin['id'] . " | Name: " . $admin['name'] . " | Email: " . $admin['email'] . "\n";
    }
} else {
    echo "No admin users found.\n";
}
?>
