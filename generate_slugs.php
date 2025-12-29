<?php
/**
 * Migration Script: Generate Slugs for Existing Properties
 * Run this once to add slugs to all properties that don't have them
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

echo "Starting slug generation for existing properties...\n\n";

try {
    // Get all properties without slugs
    $stmt = $pdo->query("SELECT id, title, slug FROM properties WHERE slug IS NULL OR slug = ''");
    $properties = $stmt->fetchAll();
    
    if (count($properties) == 0) {
        echo "No properties found without slugs. All done!\n";
        exit;
    }
    
    echo "Found " . count($properties) . " properties without slugs.\n\n";
    
    $updateStmt = $pdo->prepare("UPDATE properties SET slug = ? WHERE id = ?");
    $updated = 0;
    
    foreach ($properties as $prop) {
        $slug = generateSlug($prop['title']);
        
        // Check if slug already exists for another property
        $checkStmt = $pdo->prepare("SELECT id FROM properties WHERE slug = ? AND id != ?");
        $checkStmt->execute([$slug, $prop['id']]);
        
        if ($checkStmt->fetch()) {
            // Slug exists, append property ID to make it unique
            $slug = $slug . '-' . $prop['id'];
        }
        
        $updateStmt->execute([$slug, $prop['id']]);
        $updated++;
        
        echo "✓ Property #{$prop['id']}: '{$prop['title']}' → slug: '{$slug}'\n";
    }
    
    echo "\n✅ Successfully generated slugs for {$updated} properties!\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
