<?php
require_once __DIR__ . '/config/db.php';

try {
    $pdo->exec("ALTER TABLE properties ADD COLUMN is_featured BOOLEAN DEFAULT FALSE");
    echo "Successfully added 'is_featured' column!";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Column 'is_featured' already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
