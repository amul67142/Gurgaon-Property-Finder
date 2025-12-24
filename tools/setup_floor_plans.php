<?php
require_once __DIR__ . '/../config/db.php';

try {
    // Create property_floor_plans table
    $sql = "CREATE TABLE IF NOT EXISTS property_floor_plans (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_id INT NOT NULL,
        title VARCHAR(100) NOT NULL,
        size_sqft VARCHAR(50) DEFAULT NULL,
        image_path VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);
    echo "Table 'property_floor_plans' created successfully.<br>";

} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage());
}
?>
