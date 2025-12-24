<?php
require_once __DIR__ . '/../config/db.php';
session_start();

// Basic Admin/Broker Check
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'broker')) {
    header('Location: ../login.php');
    exit();
}

// Build Query
$params = [];
$query = "SELECT l.created_at, l.name, l.email, l.phone, l.lead_type, l.message, p.title as project_title 
          FROM leads l 
          LEFT JOIN properties p ON l.property_id = p.id";

if (isset($_GET['project_id']) && !empty($_GET['project_id'])) {
    $query .= " WHERE l.property_id = ?";
    $params[] = $_GET['project_id'];
}

$query .= " ORDER BY l.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Headers for Download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="leads_export_' . date('Y-m-d') . '.csv"');

// Output CSV
$output = fopen('php://output', 'w');

// CSV Column Headers
fputcsv($output, ['Date', 'Name', 'Email', 'Phone', 'Type', 'Message', 'Project']);

foreach ($leads as $lead) {
    fputcsv($output, [
        $lead['created_at'],
        $lead['name'],
        $lead['email'],
        $lead['phone'],
        $lead['lead_type'],
        $lead['message'],
        $lead['project_title'] ? $lead['project_title'] : 'General Inquiry'
    ]);
}

fclose($output);
exit();
?>
