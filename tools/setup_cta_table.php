<?php
require_once __DIR__ . '/../config/db.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS cta_leads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cta_type VARCHAR(50),
        property_id INT,
        source_page VARCHAR(255),
        user_ip VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Table 'cta_leads' created or already exists.";
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
