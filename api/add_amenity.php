<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Ensure only logged in users (brokers/admin) can add amenities
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $name = clean_input($data['name'] ?? '');

    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Name is required']);
        exit;
    }

    // Check if exists
    $stmt = $pdo->prepare("SELECT id FROM amenities WHERE name = ?");
    $stmt->execute([$name]);
    $existing = $stmt->fetch();

    if ($existing) {
        echo json_encode(['success' => true, 'id' => $existing['id'], 'name' => $name, 'message' => 'Amenity already exists']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO amenities (name, icon) VALUES (?, 'fa-check')");
        $stmt->execute([$name]);
        $id = $pdo->lastInsertId();
        
        echo json_encode(['success' => true, 'id' => $id, 'name' => $name]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}
?>
