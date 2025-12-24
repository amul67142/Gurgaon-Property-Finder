<?php
require_once __DIR__ . '/../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $leadType = trim($_POST['type'] ?? 'General');
    // property_id might come from a hidden field if you add it, or we can infer it
    // For now getting property_id from session or referrer is hard, better if form sends it.
    // Assuming forms might not send property_id yet, but let's check.
    $property_id = isset($_POST['property_id']) ? (int)$_POST['property_id'] : null;

    // Validate
    if (empty($name) || empty($phone)) {
        // Handle error - maybe redirect back with error?
        // For now, just go back
        if(isset($_SERVER['HTTP_REFERER'])) {
             header("Location: " . $_SERVER['HTTP_REFERER']);
        } else {
             header("Location: ../index.php");
        }
        exit;
    }

    // Check for source_ad_id
    $source_ad_id = isset($_POST['source_ad_id']) && !empty($_POST['source_ad_id']) ? (int)$_POST['source_ad_id'] : null;

    try {
        $stmt = $pdo->prepare("INSERT INTO leads (name, email, phone, message, property_id, source_ad_id) VALUES (?, ?, ?, ?, ?, ?)");
        // Using 'message' field to store Lead Type for now if no specific column
        $stmt->execute([$name, $email, $phone, "Type: " . $leadType, $property_id, $source_ad_id]);

        // Redirect to Thank You
        header("Location: ../thank-you.php");
        exit;
    } catch (PDOException $e) {
        // Log error
        error_log("Lead Error: " . $e->getMessage());
        header("Location: ../index.php"); // Fallback
        exit;
    }
} else {
    header("Location: ../index.php");
    exit;
}
