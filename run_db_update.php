<?php
require_once __DIR__ . '/config/db.php';

try {
    $sql = file_get_contents(__DIR__ . '/config/db_update_v2.sql');
    $pdo->exec($sql);
    echo "Database updated successfully!";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Database already updated (Duplicate column error ignored).";
    } else {
        echo "Error updating database: " . $e->getMessage();
    }
}
?>
