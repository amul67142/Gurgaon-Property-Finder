<?php
require_once __DIR__ . '/../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $type = $data['type'] ?? 'Unknown';
    $property_id = $data['property_id'] ?? 0;
    $source = $data['source'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'];

    try {
        $stmt = $pdo->prepare("INSERT INTO cta_leads (cta_type, property_id, source_page, user_ip) VALUES (?, ?, ?, ?)");
        $stmt->execute([$type, $property_id, $source, $ip]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
