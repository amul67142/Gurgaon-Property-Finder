<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/xml; charset=utf-8');

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// 1. Static Pages
$staticPages = [
    'index.php',
    'properties.php',
    'about-us.php',
    'contact.php'
];

foreach ($staticPages as $page) {
    echo '<url>';
    echo '<loc>' . BASE_URL . '/' . $page . '</loc>';
    echo '<changefreq>weekly</changefreq>';
    echo '<priority>0.8</priority>';
    echo '</url>';
}

// 2. Dynamic Property Pages
$stmt = $pdo->query("SELECT id, slug, updated_at FROM properties WHERE is_approved = 1 ORDER BY updated_at DESC");
while ($row = $stmt->fetch()) {
    $slug = !empty($row['slug']) ? $row['slug'] : '';
    $lastMod = date('Y-m-d', strtotime($row['updated_at']));
    
    echo '<url>';
    echo '<loc>' . BASE_URL . '/property/' . $slug . '</loc>';
    echo '<lastmod>' . $lastMod . '</lastmod>';
    echo '<changefreq>monthly</changefreq>';
    echo '<priority>0.6</priority>';
    echo '</url>';
}

echo '</urlset>';
