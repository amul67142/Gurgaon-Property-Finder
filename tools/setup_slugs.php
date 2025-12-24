<?php
require_once __DIR__ . '/../config/db.php';

try {
    // 1. Check if column exists
    $check = $pdo->query("SHOW COLUMNS FROM properties LIKE 'slug'");
    if ($check->rowCount() == 0) {
        echo "Adding slug column...\n";
        $pdo->exec("ALTER TABLE properties ADD COLUMN slug VARCHAR(255) AFTER title");
        $pdo->exec("ALTER TABLE properties ADD INDEX (slug)");
    } else {
        echo "Slug column exists.\n";
    }

    // 2. Fetch all properties to update (Regenerate all for consistency)
    $stmt = $pdo->query("SELECT p.id, p.title, u.name as broker_name 
                         FROM properties p 
                         LEFT JOIN users u ON p.broker_id = u.id");
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($properties) . " properties to update.\n";

    $updateStmt = $pdo->prepare("UPDATE properties SET slug = :slug WHERE id = :id");

    foreach ($properties as $prop) {
        $base = $prop['title'];
        if (!empty($prop['broker_name'])) {
            $base .= ' ' . $prop['broker_name'];
        }
        
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $base)));
        
        // Remove duplicate hyphens
        $slug = preg_replace('/-+/', '-', $slug);
        
        // Ensure unique
        $checkSlug = $pdo->prepare("SELECT id FROM properties WHERE slug = ? AND id != ?");
        $checkSlug->execute([$slug, $prop['id']]);
        if($checkSlug->rowCount() > 0) {
            $slug .= '-' . $prop['id'];
        }

        $updateStmt->execute([':slug' => $slug, ':id' => $prop['id']]);
        echo "Updated {$prop['id']} -> $slug\n";
    }

    echo "Done!";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
