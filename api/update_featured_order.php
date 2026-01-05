<?php
@session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Security check
if (!isAdmin()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['order']) && is_array($data['order'])) {
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("UPDATE properties SET sort_order = ? WHERE id = ?");
            foreach ($data['order'] as $index => $id) {
                // $index starts from 0, so 1st item gets sort_order 0, 2nd gets 1, etc.
                $stmt->execute([$index, intval($id)]);
            }
            
            $pdo->commit();
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
}

header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit;
