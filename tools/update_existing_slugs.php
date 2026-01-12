<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if running from CLI or authorized
if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.");
}

echo "Starting SEO Slug Backfill...\n";

try {
    $stmt = $pdo->query("SELECT id, title FROM properties");
    $properties = $stmt->fetchAll();

    $count = 0;
    foreach ($properties as $prop) {
        $slug = slugify($prop['title']);
        $update = $pdo->prepare("UPDATE properties SET slug = ? WHERE id = ?");
        $update->execute([$slug, $prop['id']]);
        $count++;
        echo "Updated Project [{$prop['id']}]: {$prop['title']} -> {$slug}\n";
    }

    echo "Successfully updated $count project(s).\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
