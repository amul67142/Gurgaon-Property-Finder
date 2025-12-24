<?php
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ctaType = $_POST['cta_type'] ?? 'Unknown';
    $propertyId = $_POST['property_id'] ?? null;
    $sourcePage = $_POST['source_page'] ?? '';
    $userIp = $_SERVER['REMOTE_ADDR'];

    try {
        $stmt = $pdo->prepare("INSERT INTO cta_leads (cta_type, property_id, source_page, user_ip) VALUES (?, ?, ?, ?)");
        $stmt->execute([$ctaType, $propertyId, $sourcePage, $userIp]);
    } catch (Exception $e) {
        // Silent fail for tracking
    }
}
?>
