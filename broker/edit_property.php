<?php
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Check role
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'broker')) {
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
        exit;
    }
    redirect('../login.php');
}

if (!isset($_GET['id'])) {
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'Property ID missing.']);
        exit;
    }
    redirect('properties.php');
}

$id = intval($_GET['id']);
$brokerId = $_SESSION['user_id'];

// Fetch Property to ensure ownership (Admins can edit all)
if ($_SESSION['user_role'] === 'admin') {
    $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->execute([$id]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ? AND broker_id = ?");
    $stmt->execute([$id, $brokerId]);
}
$property = $stmt->fetch();

if (!$property) {
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'Property not found or unauthorized.']);
        exit;
    }
    redirect('properties.php');
}

$error = '';
$success = '';

// Check amenities
$propAmenities = $pdo->prepare("SELECT amenity_id FROM property_amenities WHERE property_id = ?");
$propAmenities->execute([$id]);
$currentAmenities = $propAmenities->fetchAll(PDO::FETCH_COLUMN);

// Fetch Floor Plans
$fpStmt = $pdo->prepare("SELECT * FROM property_floor_plans WHERE property_id = ?");
$fpStmt->execute([$id]);
$currentFloorPlans = $fpStmt->fetchAll();

// Fetch Property Images (Gallery)
$imgStmt = $pdo->prepare("SELECT * FROM property_images WHERE property_id = ? ORDER BY is_cover DESC, id ASC");
$imgStmt->execute([$id]);
$allImages = $imgStmt->fetchAll();

$coverImage = null;
$galleryImages = [];
foreach ($allImages as $img) {
    if ($img['is_cover']) {
        if (!$coverImage) $coverImage = $img;
        else $galleryImages[] = $img;
    } else {
        $galleryImages[] = $img;
    }
}

$allAmenities = $pdo->query("SELECT * FROM amenities ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = clean_input($_POST['title']);
    $description = clean_input($_POST['description']);
    $price = clean_input($_POST['price']);
    $location = clean_input($_POST['location']);
    $type = clean_input($_POST['type']);
    $status = clean_input($_POST['status']);
    
    // New Fields
    $developer = clean_input($_POST['developer']);
    $rera_no = clean_input($_POST['rera_no']);
    $brochure_url = clean_input($_POST['brochure_url']);
    $size_range = clean_input($_POST['size_range']);
    $configurations = clean_input($_POST['configurations']);
    $map_url = clean_input($_POST['map_url']);
    $video_url = clean_input($_POST['video_url']);
    
    $is_featured = $property['is_featured']; // Default to current
    if ($_SESSION['user_role'] === 'admin') {
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    }

    $ad_broker_name = clean_input($_POST['ad_broker_name'] ?? '');
    $ad_broker_type = clean_input($_POST['ad_broker_type'] ?? '');

    // Dynamic Points
    $highlight_points = $_POST['highlight_points_json'];
    $location_advantages = $_POST['location_advantages_json'];

    $selectedAmenities = isset($_POST['amenities']) ? $_POST['amenities'] : [];

    if (empty($title) || empty($price) || empty($location)) {
        $error = 'Please fill in required fields.';
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => $error]);
            exit;
        }
    } else {
        try {
            error_log("Starting property update for ID: " . $id);
            $pdo->beginTransaction();

            // Determine Approval Status (Admins = 1, Brokers = 0)
            $newStatus = 1; // Auto-approve all property edits

            $slug = slugify($title);

            // Update Property
            $stmt = $pdo->prepare("UPDATE properties SET 
                title=?, slug=?, description=?, price=?, location=?, type=?, status=?, 
                developer=?, rera_no=?, brochure_url=?, size_range=?, configurations=?, 
                highlight_points=?, location_advantages=?, map_url=?, video_url=?,
                is_approved=?, is_featured=?, ad_broker_name=?, ad_broker_type=? 
                WHERE id=?");
            $stmt->execute([
                $title, $slug, $description, $price, $location, $type, $status,
                $developer, $rera_no, $brochure_url, $size_range, $configurations,
                $highlight_points, $location_advantages, $map_url, $video_url,
                $newStatus,
                $is_featured,
                $ad_broker_name,
                $ad_broker_type,
                $id
            ]);

            // Update Amenities
            $pdo->prepare("DELETE FROM property_amenities WHERE property_id = ?")->execute([$id]);
            if (!empty($selectedAmenities)) {
                $st = $pdo->prepare("INSERT INTO property_amenities (property_id, amenity_id) VALUES (?, ?)");
                foreach ($selectedAmenities as $amId) {
                    $st->execute([$id, $amId]);
                }
            }

            // Image Upload Helper
            $uploadDir = __DIR__ . '/../assets/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            // Upload Highlights Image
            $highlightsImgPath = uploadFile('highlights_image', $uploadDir);
            if ($highlightsImgPath) {
                $pdo->prepare("UPDATE properties SET highlights_image = ? WHERE id = ?")->execute([$highlightsImgPath, $id]);
            }

            // Upload Location Image
            $locImgPath = uploadFile('location_advantages_image', $uploadDir);
            if ($locImgPath) {
                $pdo->prepare("UPDATE properties SET location_advantages_image = ? WHERE id = ?")->execute([$locImgPath, $id]);
            }

            // Upload Ad Broker Image
            $adBrokerImgPath = uploadFile('ad_broker_image', $uploadDir);
            if ($adBrokerImgPath) {
                $pdo->prepare("UPDATE properties SET ad_broker_image = ? WHERE id = ?")->execute([$adBrokerImgPath, $id]);
            }

            // 1. Handle Dedicated Cover Photo Upload
            $newCoverPath = uploadFile('cover_image', $uploadDir);
            if ($newCoverPath) {
                // Unset current cover
                $pdo->prepare("UPDATE property_images SET is_cover = 0 WHERE property_id = ? AND is_cover = 1")->execute([$id]);
                // Insert new cover
                $pdo->prepare("INSERT INTO property_images (property_id, image_path, is_cover) VALUES (?, ?, 1)")->execute([$id, $newCoverPath]);
            }

            // 2. Upload New Gallery Images (No automatic cover assignment)
            if (isset($_FILES['images']) && $_FILES['images']['error'][0] !== UPLOAD_ERR_NO_FILE) {
                $files = $_FILES['images'];
                for ($i = 0; $i < count($files['name']); $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        if ($files['size'][$i] > 2 * 1024 * 1024) {
                            throw new Exception("Gallery image " . $files['name'][$i] . " exceeds 2MB limit.");
                        }
                        $tmpName = $files['tmp_name'][$i];
                        $name = time() . '_eg_' . $i . '_' . basename($files['name'][$i]);
                        $destination = $uploadDir . $name;
                        
                        if (move_uploaded_file($tmpName, $destination)) {
                            $stmt = $pdo->prepare("INSERT INTO property_images (property_id, image_path, is_cover) VALUES (?, ?, ?)");
                            $stmt->execute([$id, 'assets/uploads/' . $name, 0]);
                        }
                    }
                }
            }

            $pdo->commit();
            error_log("Successfully updated property ID: " . $id);
            $success = 'Property updated successfully! It may require approval again if sensitive details were changed.';
            
            if ($isAjax) {
                echo json_encode(['success' => true, 'message' => $success, 'redirect' => 'properties.php']);
                exit;
            }

            // Refresh Data
            $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
            $stmt->execute([$id]);
            $property = $stmt->fetch();
            
            $propAmenities = $pdo->prepare("SELECT amenity_id FROM property_amenities WHERE property_id = ?");
            $propAmenities->execute([$id]);
            $currentAmenities = $propAmenities->fetchAll(PDO::FETCH_COLUMN);

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Error updating property: ' . $e->getMessage();
            
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => $error]);
                exit;
            }
        }
    }
    // Handle Floor Plans (New)
    if (isset($_POST['fp_titles']) && is_array($_POST['fp_titles'])) {
        $fpTitles = $_POST['fp_titles'];
        $fpSizes = $_POST['fp_sizes'];
        $fpFiles = $_FILES['fp_images'];

        $stmt = $pdo->prepare("INSERT INTO property_floor_plans (property_id, title, size_sqft, image_path) VALUES (?, ?, ?, ?)");

        for ($i = 0; $i < count($fpTitles); $i++) {
            $title = clean_input($fpTitles[$i]);
            $size = clean_input($fpSizes[$i]);
            
            if (!empty($title)) {
                $imagePath = 'assets/images/blurred-map-placeholder.jpg'; // default
                
                // Upload Image if present
                if (isset($fpFiles['name'][$i]) && $fpFiles['error'][$i] === UPLOAD_ERR_OK) {
                    if ($fpFiles['size'][$i] > 2 * 1024 * 1024) {
                        throw new Exception("Floor plan " . $fpFiles['name'][$i] . " exceeds 2MB limit.");
                    }
                    $tmpName = $fpFiles['tmp_name'][$i];
                    $name = time() . '_fp_' . $i . '_' . basename($fpFiles['name'][$i]);
                    $destination = $uploadDir . $name;
                    if (move_uploaded_file($tmpName, $destination)) {
                        $imagePath = 'assets/uploads/' . $name;
                    }
                }
                
                $stmt->execute([$id, $title, $size, $imagePath]);
            }
        }
        // Refresh floor plans
        $fpStmt = $pdo->prepare("SELECT * FROM property_floor_plans WHERE property_id = ?");
        $fpStmt->execute([$id]);
        $currentFloorPlans = $fpStmt->fetchAll();
        $success = "Property and floor plans updated successfully.";
    }

    // Handle Floor Plan Deletion
    if (isset($_POST['delete_fp_id'])) {
        $delFpId = intval($_POST['delete_fp_id']);
        $pdo->prepare("DELETE FROM property_floor_plans WHERE id = ? AND property_id = ?")->execute([$delFpId, $id]);
        
        $fpStmt = $pdo->prepare("SELECT * FROM property_floor_plans WHERE property_id = ?");
        $fpStmt->execute([$id]);
        $currentFloorPlans = $fpStmt->fetchAll();
        $success = "Floor plan deleted.";
    }

    // Handle Image Deletion
    if (isset($_POST['delete_img_id'])) {
        $delImgId = intval($_POST['delete_img_id']);
        $stmt = $pdo->prepare("SELECT image_path, is_cover FROM property_images WHERE id = ? AND property_id = ?");
        $stmt->execute([$delImgId, $id]);
        $imgData = $stmt->fetch();
        
        if ($imgData) {
            $pdo->prepare("DELETE FROM property_images WHERE id = ?")->execute([$delImgId]);
            
            // If the deleted image was the cover, pick another one
            if ($imgData['is_cover']) {
                $nextImg = $pdo->prepare("SELECT id FROM property_images WHERE property_id = ? ORDER BY id ASC LIMIT 1");
                $nextImg->execute([$id]);
                $nextId = $nextImg->fetchColumn();
                if ($nextId) {
                    $pdo->prepare("UPDATE property_images SET is_cover = 1 WHERE id = ?")->execute([$nextId]);
                }
            }

            $imgStmt = $pdo->prepare("SELECT * FROM property_images WHERE property_id = ? ORDER BY is_cover DESC, id ASC");
            $imgStmt->execute([$id]);
            $currentImages = $imgStmt->fetchAll();
            $success = "Image deleted successfully.";
        }
    }

    // Handle Set Cover
    if (isset($_POST['set_cover_id'])) {
        $setCoverId = intval($_POST['set_cover_id']);
        // Remove existing cover
        $pdo->prepare("UPDATE property_images SET is_cover = 0 WHERE property_id = ?")->execute([$id]);
        // Set new cover
        $pdo->prepare("UPDATE property_images SET is_cover = 1 WHERE id = ? AND property_id = ?")->execute([$setCoverId, $id]);
        
        $imgStmt = $pdo->prepare("SELECT * FROM property_images WHERE property_id = ? ORDER BY is_cover DESC, id ASC");
        $imgStmt->execute([$id]);
        $currentImages = $imgStmt->fetchAll();
        $success = "Cover image updated.";
    }

    // Handle Section Image Deletion
    if (isset($_POST['delete_highlights_image'])) {
        $pdo->prepare("UPDATE properties SET highlights_image = NULL WHERE id = ?")->execute([$id]);
        $property['highlights_image'] = null; // Update local variable
        $success = "Highlights image removed.";
    }
    if (isset($_POST['delete_location_image'])) {
        $pdo->prepare("UPDATE properties SET location_advantages_image = NULL WHERE id = ?")->execute([$id]);
        $property['location_advantages_image'] = null; // Update local variable
        $success = "Location image removed.";
    }
    if (isset($_POST['delete_ad_broker_image'])) {
        $pdo->prepare("UPDATE properties SET ad_broker_image = NULL WHERE id = ?")->execute([$id]);
        $property['ad_broker_image'] = null; // Update local variable
        $success = "Display image removed.";
    }
}

if (!$isAjax) {
    require_once __DIR__ . '/../includes/header.php';
}
?>
<div class="flex min-h-screen bg-slate-50">
    <!-- Sidebar -->
    <?php if (isAdmin()): ?>
        <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <?php else: ?>
    <aside class="w-64 bg-slate-900 text-white fixed top-0 bottom-0 left-0 overflow-y-auto z-30 hidden lg:block">
        <div class="p-6">
            <a href="<?php echo BASE_URL; ?>/index.php" class="mb-10 block px-2">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo-white.png" alt="Gurgaon Property Finder" class="h-14 w-auto object-contain">
            </a>
            <nav class="space-y-2">
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800 hover:text-white transition"><i class="fa-solid fa-gauge-high w-5"></i> Dashboard</a>
                <a href="properties.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-800 text-white shadow-lg shadow-slate-900/50"><i class="fa-solid fa-list w-5"></i> My Listings</a>
                <a href="add_property.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800 hover:text-white transition"><i class="fa-solid fa-plus-circle w-5"></i> Add Property</a>
                <a href="leads.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800 hover:text-white transition"><i class="fa-solid fa-users w-5"></i> Leads</a>
                <div class="pt-8 mt-8 border-t border-slate-700">
                    <a href="<?php echo BASE_URL; ?>/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/10 transition"><i class="fa-solid fa-right-from-bracket w-5"></i> Logout</a>
                </div>
            </nav>
        </div>
    </aside>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="flex-1 lg:ml-64 p-4 md:p-8">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold font-display text-slate-800">Edit Property</h1>
                    <p class="text-slate-500">Update property details and information</p>
                </div>
                <a href="properties.php" class="text-secondary hover:underline text-sm font-medium">Back to listings</a>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 border border-red-100 flex items-center gap-3"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="bg-green-50 text-green-600 p-4 rounded-xl mb-6 border border-green-100 flex items-center gap-3"><i class="fa-solid fa-check-circle"></i> <?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-8" id="propertyForm">
                
                <!-- Section 1: Basic Info -->
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-blue-50 text-secondary flex items-center justify-center text-sm">1</span> Basic Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Property Name</label>
                            <input type="text" name="title" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 focus:border-secondary outline-none transition" value="<?php echo htmlspecialchars($property['title']); ?>" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Location</label>
                            <input type="text" name="location" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 focus:border-secondary outline-none transition" value="<?php echo htmlspecialchars($property['location']); ?>" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Price (₹)</label>
                            <input type="number" name="price" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 focus:border-secondary outline-none transition" value="<?php echo htmlspecialchars($property['price']); ?>" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Type</label>
                            <select name="type" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 outline-none transition cursor-pointer">
                                <option value="apartment" <?php if($property['type'] == 'apartment') echo 'selected'; ?>>Apartment</option>
                                <option value="house" <?php if($property['type'] == 'house') echo 'selected'; ?>>House</option>
                                <option value="plot" <?php if($property['type'] == 'plot') echo 'selected'; ?>>Plot</option>
                                <option value="commercial" <?php if($property['type'] == 'commercial') echo 'selected'; ?>>Commercial</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                            <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 outline-none transition cursor-pointer">
                                <option value="ready_to_move" <?php if($property['status'] == 'ready_to_move') echo 'selected'; ?>>Ready to Move</option>
                                <option value="under_construction" <?php if($property['status'] == 'under_construction') echo 'selected'; ?>>Under Construction</option>
                            </select>
                        </div>
                </div>

                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <!-- Admin Only: Featured Toggle -->
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-slate-100 border-l-4 border-l-secondary">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-yellow-50 text-secondary flex items-center justify-center text-xl">
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Featured Listing</h3>
                            <p class="text-slate-500 text-sm">Should this property be highlighted on the homepage?</p>
                        </div>
                        <div class="ml-auto">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_featured" value="1" class="sr-only peer" <?php if($property['is_featured']) echo 'checked'; ?>>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-secondary"></div>
                            </label>
                        </div>
                    </div>

                    <div class="mt-8 pt-8 border-t border-slate-100 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Display Name (Alias)</label>
                            <input type="text" name="ad_broker_name" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-secondary/50 outline-none transition text-sm" value="<?php echo htmlspecialchars($property['ad_broker_name'] ?? ''); ?>" placeholder="e.g. Authorized Partner">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Display Type</label>
                            <input type="text" name="ad_broker_type" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-secondary/50 outline-none transition text-sm" value="<?php echo htmlspecialchars($property['ad_broker_type'] ?? ''); ?>" placeholder="e.g. DEVELOPER">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Display Logo/Image</label>
                            <?php if(!empty($property['ad_broker_image'])): ?>
                                <div class="relative w-20 h-20 mb-3 group">
                                    <img src="<?php echo BASE_URL . '/' . htmlspecialchars($property['ad_broker_image']); ?>" class="w-full h-full rounded-xl object-cover border border-slate-200 shadow-sm">
                                    <button type="submit" name="delete_ad_broker_image" class="absolute -top-2 -right-2 bg-red-500 text-white w-6 h-6 rounded-full flex items-center justify-center shadow-lg hover:bg-red-600 transition" onclick="return confirm('Remove display image?')" title="Remove Image">
                                        <i class="fa-solid fa-xmark text-[10px]"></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="ad_broker_image" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-4 focus:ring-2 focus:ring-secondary/50 outline-none transition text-xs" accept="image/*">
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Section 2: Property Details -->
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-blue-50 text-secondary flex items-center justify-center text-sm">2</span> Property Details
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Developer Name</label>
                            <input type="text" name="developer" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 focus:border-secondary outline-none transition" value="<?php echo htmlspecialchars($property['developer'] ?? ''); ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">RERA No.</label>
                            <input type="text" name="rera_no" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 focus:border-secondary outline-none transition" value="<?php echo htmlspecialchars($property['rera_no'] ?? ''); ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Configurations</label>
                            <input type="text" name="configurations" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 outline-none transition" value="<?php echo htmlspecialchars($property['configurations'] ?? ''); ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Size Range</label>
                            <input type="text" name="size_range" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 outline-none transition" value="<?php echo htmlspecialchars($property['size_range'] ?? ''); ?>">
                        </div>
                        <div class="col-span-2">
                             <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
                             <textarea name="description" rows="4" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 outline-none transition"><?php echo htmlspecialchars($property['description']); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Project Highlights (Dynamic) -->
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-blue-50 text-secondary flex items-center justify-center text-sm">3</span> Project Highlights
                    </h3>
                    <!-- /DEBUG (Removed) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                             <label class="block text-sm font-medium text-slate-700 mb-2">Update Highlights Image (Single)</label>
                             
                             <div class="relative mb-3 group">
                                 <?php if(!empty($property['highlights_image'])): ?>
                                    <div class="relative rounded-xl overflow-hidden shadow-sm border border-slate-200">
                                        <img id="preview-highlights-img" src="<?php echo BASE_URL . '/' . htmlspecialchars($property['highlights_image']); ?>" class="w-full h-48 object-cover">
                                        <button type="submit" name="delete_highlights_image" value="1" class="absolute top-2 right-2 bg-red-500 text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-red-600 transition shadow-sm" onclick="return confirm('Remove highlights image?')" title="Remove Image">
                                            <i class="fa-solid fa-xmark text-xs"></i>
                                        </button>
                                    </div>
                                 <?php else: ?>
                                    <div id="preview-highlights-container" class="hidden relative rounded-xl overflow-hidden shadow-sm border border-slate-200 mb-2">
                                        <img id="preview-highlights-new" src="" class="w-full h-48 object-cover">
                                    </div>
                                 <?php endif; ?>
                             </div>

                             <div class="border-2 border-dashed border-slate-300 rounded-xl p-4 text-center hover:bg-slate-50 transition cursor-pointer relative bg-slate-50">
                                <input type="file" name="highlights_image" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*" onchange="previewSingle(this, 'preview-highlights')">
                                <div class="space-y-1">
                                    <i class="fa-solid fa-image text-slate-400 text-xl"></i>
                                    <p class="text-xs font-medium text-slate-600">Click to replace/upload</p>
                                </div>
                             </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Key Points</label>
                            <div id="highlights-container" class="space-y-3 mb-3">
                                <?php 
                                    $hPoints = explode(',', $property['highlight_points'] ?? '');
                                    foreach($hPoints as $point): if(trim($point) == '') continue;
                                ?>
                                <div class="flex gap-2">
                                    <input type="text" class="highlight-input w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-4 focus:ring-2 focus:ring-secondary/50 outline-none text-sm" value="<?php echo htmlspecialchars(trim($point)); ?>">
                                    <button type="button" onclick="removePoint(this, 'highlights-container')" class="text-red-400 hover:text-red-600 px-2"><i class="fa-solid fa-trash"></i></button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" onclick="addPoint('highlights-container')" class="text-sm text-secondary font-medium hover:underline flex items-center gap-1"><i class="fa-solid fa-plus-circle"></i> Add another point</button>
                            <input type="hidden" name="highlight_points_json" id="highlight_points_json">
                        </div>
                    </div>
                </div>

                <!-- Section 4: Location Advantages (Dynamic) -->
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-blue-50 text-secondary flex items-center justify-center text-sm">4</span> Location Advantages
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                             <label class="block text-sm font-medium text-slate-700 mb-2">Update Location Image (Single)</label>
                             
                             <div class="relative mb-3 group">
                                 <?php if(!empty($property['location_advantages_image'])): ?>
                                    <div class="relative rounded-xl overflow-hidden shadow-sm border border-slate-200">
                                        <img id="preview-location-img" src="<?php echo BASE_URL . '/' . htmlspecialchars($property['location_advantages_image']); ?>" class="w-full h-48 object-cover">
                                        <button type="submit" name="delete_location_image" value="1" class="absolute top-2 right-2 bg-red-500 text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-red-600 transition shadow-sm" onclick="return confirm('Remove location image?')" title="Remove Image">
                                            <i class="fa-solid fa-xmark text-xs"></i>
                                        </button>
                                    </div>
                                 <?php else: ?>
                                    <div id="preview-location-container" class="hidden relative rounded-xl overflow-hidden shadow-sm border border-slate-200 mb-2">
                                        <img id="preview-location-new" src="" class="w-full h-48 object-cover">
                                    </div>
                                 <?php endif; ?>
                             </div>

                             <div class="border-2 border-dashed border-slate-300 rounded-xl p-4 text-center hover:bg-slate-50 transition cursor-pointer relative bg-slate-50">
                                <input type="file" name="location_advantages_image" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*" onchange="previewSingle(this, 'preview-location')">
                                <div class="space-y-1">
                                    <i class="fa-solid fa-map-location-dot text-slate-400 text-xl"></i>
                                    <p class="text-xs font-medium text-slate-600">Click to replace/upload</p>
                                </div>
                             </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Key Locations</label>
                            <div id="location-container" class="space-y-3 mb-3">
                                <?php 
                                    $lPoints = explode(',', $property['location_advantages'] ?? '');
                                    foreach($lPoints as $point): if(trim($point) == '') continue;
                                ?>
                                <div class="flex gap-2">
                                    <input type="text" class="location-input w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-4 focus:ring-2 focus:ring-secondary/50 outline-none text-sm" value="<?php echo htmlspecialchars(trim($point)); ?>">
                                    <button type="button" onclick="removePoint(this, 'location-container')" class="text-red-400 hover:text-red-600 px-2"><i class="fa-solid fa-trash"></i></button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" onclick="addPoint('location-container')" class="text-sm text-secondary font-medium hover:underline flex items-center gap-1"><i class="fa-solid fa-plus-circle"></i> Add another location</button>
                            <input type="hidden" name="location_advantages_json" id="location_advantages_json">
                        </div>
                    </div>
                </div>

                <!-- Section 5: Amenities -->
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-blue-50 text-secondary flex items-center justify-center text-sm">5</span> Amenities
                    </h3>
                    
                     <!-- Scrollable Amenities List -->
                     <div class="h-64 overflow-y-auto border border-slate-200 rounded-xl p-4 mb-4 bg-slate-50 custom-scrollbar">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <?php foreach ($allAmenities as $am): ?>
                            <label class="flex items-center gap-3 p-2 hover:bg-white rounded-lg transition cursor-pointer">
                                <input type="checkbox" name="amenities[]" value="<?php echo $am['id']; ?>" class="w-4 h-4 text-secondary rounded focus:ring-secondary border-gray-300" <?php if(in_array($am['id'], $currentAmenities)) echo 'checked'; ?>>
                                <span class="text-sm text-slate-700 flex items-center gap-2">
                                    <?php if(!empty($am['icon'])): ?><i class="fa-solid <?php echo $am['icon']; ?> text-slate-400 w-5"></i><?php endif; ?>
                                    <?php echo htmlspecialchars($am['name']); ?>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Add Custom Amenities -->
                    <div class="flex gap-3 items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-slate-500 mb-1">Custom Amenities (comma separated)</label>
                            <input type="text" id="new_amenity_name" class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-secondary/50 outline-none" placeholder="e.g. Golf Simulator, Cigar Lounge, Mini Theater">
                        </div>
                        <button type="button" onclick="addCustomAmenity()" class="bg-slate-800 text-white px-4 py-2 rounded-lg text-sm hover:bg-slate-700 transition">Add to List</button>
                    </div>
                </div>

                <!-- Section 6: Floor Plans -->
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-blue-50 text-secondary flex items-center justify-center text-sm">6</span> Floor Plans
                    </h3>
                    
                    <!-- Existing Floor Plans -->
                    <?php if(!empty($currentFloorPlans)): ?>
                    <div class="mb-6 space-y-3">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Existing Floor Plans</label>
                        <?php foreach($currentFloorPlans as $fp): ?>
                        <div class="flex items-center justify-between p-3 bg-slate-50 border border-slate-200 rounded-xl">
                            <div class="flex items-center gap-4">
                                <img src="<?php echo strpos($fp['image_path'], 'http') === 0 ? $fp['image_path'] : BASE_URL . '/' . $fp['image_path']; ?>" class="w-12 h-12 object-cover rounded-lg">
                                <div>
                                    <p class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($fp['title']); ?></p>
                                    <p class="text-xs text-slate-500"><?php echo htmlspecialchars($fp['size_sqft']); ?></p>
                                </div>
                            </div>
                            <button type="submit" name="delete_fp_id" value="<?php echo $fp['id']; ?>" class="bg-red-50 text-red-500 w-8 h-8 rounded-full flex items-center justify-center hover:bg-red-100 transition shadow-sm" onclick="return confirm('Delete this floor plan?')" title="Delete Floor Plan">
                                <i class="fa-solid fa-xmark text-xs"></i>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <hr class="border-slate-100 my-6">
                    <?php endif; ?>

                    <!-- Add New Floor Plans -->
                    <div id="floor-plans-container" class="space-y-6">
                         <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-slate-50 rounded-xl border border-slate-200 relative group">
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Plan Title</label>
                                <input type="text" name="fp_titles[]" class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 focus:outline-none focus:border-secondary text-sm" placeholder="e.g. 3BHK Luxury">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Size (e.g. 2500 Sq. Ft.)</label>
                                <input type="text" name="fp_sizes[]" class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 focus:outline-none focus:border-secondary text-sm" placeholder="e.g. 1200 Sq. Ft.">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Image</label>
                                <input type="file" name="fp_images[]" class="w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300" accept="image/*">
                            </div>
                            <button type="button" onclick="this.parentElement.remove()" class="absolute -top-2 -right-2 bg-red-50 text-red-500 w-6 h-6 rounded-full flex items-center justify-center shadow-sm opacity-0 group-hover:opacity-100 transition"><i class="fa-solid fa-xmark text-xs"></i></button>
                        </div>
                    </div>
                    <button type="button" onclick="addFloorPlan()" class="mt-4 text-sm text-secondary font-medium hover:underline flex items-center gap-1"><i class="fa-solid fa-plus-circle"></i> Add another floor plan</button>
                </div>

                <!-- Section 7: Media & Links -->
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-blue-50 text-secondary flex items-center justify-center text-sm">7</span> Media & Links
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                        <!-- Update Cover Photo -->
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-slate-700 mb-3">Update Main Cover Photo</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Current -->
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-slate-400 mb-2 tracking-wider">Current Cover</p>
                                    <?php if($coverImage): ?>
                                        <div class="relative aspect-video rounded-xl overflow-hidden border border-slate-200 shadow-sm bg-slate-50">
                                            <img src="<?php echo BASE_URL . '/' . htmlspecialchars($coverImage['image_path']); ?>" class="w-full h-full object-cover">
                                        </div>
                                    <?php else: ?>
                                        <div class="aspect-video bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl flex items-center justify-center text-slate-400 text-xs">
                                            No cover set
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <!-- Upload New -->
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-secondary mb-2 tracking-wider">Select New Cover</p>
                                    <div class="border-2 border-dashed border-secondary/20 rounded-xl p-4 text-center hover:bg-secondary/5 transition cursor-pointer relative bg-secondary/5 aspect-video flex flex-col items-center justify-center">
                                        <input type="file" name="cover_image" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*" onchange="previewCoverImage(this)">
                                        <div id="cover-preview-placeholder">
                                            <i class="fa-solid fa-cloud-arrow-up text-xl text-secondary mb-1"></i>
                                            <p class="text-[10px] font-bold text-slate-600">Click to Upload</p>
                                        </div>
                                        <div id="cover-preview-container" class="hidden w-full h-full">
                                            <img id="cover-preview" src="#" class="w-full h-full object-cover rounded-lg border border-white">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gallery Upload -->
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-slate-700 mb-3">Add to Gallery Images</label>
                            <div id="image-preview-container" class="grid grid-cols-3 gap-2 mb-4 hidden"></div>
                            <div class="border-2 border-dashed border-slate-300 rounded-xl p-4 text-center hover:bg-slate-50 transition cursor-pointer relative bg-slate-50 aspect-video flex flex-col items-center justify-center">
                                <input type="file" name="images[]" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*" onchange="previewImages(this)">
                                <i class="fa-solid fa-images text-xl text-slate-400 mb-1"></i>
                                <p class="text-[10px] font-bold text-slate-600">Add Gallery Photos</p>
                            </div>
                        </div>

                         <!-- Existing Gallery Images -->
                        <?php if(!empty($galleryImages)): ?>
                        <div class="col-span-2 mt-4">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Gallery Images (Excludes Cover)</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                                <?php foreach($galleryImages as $img): ?>
                                <div class="relative group rounded-xl overflow-hidden aspect-square border border-slate-200 bg-slate-100">
                                    <img src="<?php echo BASE_URL . '/' . htmlspecialchars($img['image_path']); ?>" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition flex flex-col items-center justify-center gap-3">
                                        <button type="submit" name="set_cover_id" value="<?php echo $img['id']; ?>" class="bg-white/20 hover:bg-white/30 text-white text-[10px] font-bold py-1.5 px-3 rounded-full border border-white/50 backdrop-blur-sm transition">
                                            Set as Cover
                                        </button>
                                        
                                        <button type="submit" name="delete_img_id" value="<?php echo $img['id']; ?>" class="bg-red-500 text-white w-9 h-9 rounded-full flex items-center justify-center hover:bg-red-600 transition shadow-lg scale-90 group-hover:scale-100 duration-300" onclick="return confirm('Delete this image?')">
                                            <i class="fa-solid fa-trash-can text-sm"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Brochure URL</label>
                            <input type="text" name="brochure_url" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 outline-none transition" value="<?php echo htmlspecialchars($property['brochure_url'] ?? ''); ?>">
                        </div>
                         <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Video URL</label>
                            <input type="text" name="video_url" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 outline-none transition" value="<?php echo htmlspecialchars($property['video_url'] ?? ''); ?>">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Map Embed URL</label>
                            <input type="text" name="map_url" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 outline-none transition" value="<?php echo htmlspecialchars($property['map_url'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4">
                     <a href="properties.php" class="px-8 py-4 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition">Cancel</a>
                     <button type="submit" class="px-8 py-4 rounded-xl bg-secondary text-white font-bold hover:bg-blue-600 shadow-lg shadow-secondary/30 transition transform hover:-translate-y-0.5">Update Property</button>
                </div>

            </form>
        </div>
    </main>
</div>

<script>
function addPoint(containerId) {
    const container = document.getElementById(containerId);
    // Remove "No points" message if present
    const msg = container.querySelector('.no-points-msg');
    if(msg) msg.remove();

    const div = document.createElement('div');
    div.className = 'flex gap-2';
    
    // Determine input class based on container
    const inputClass = containerId === 'highlights-container' ? 'highlight-input' : 'location-input';
    
    div.innerHTML = `
        <input type="text" class="${inputClass} w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-4 focus:ring-2 focus:ring-secondary/50 outline-none text-sm" placeholder="Add another point">
        <button type="button" onclick="removePoint(this, '${containerId}')" class="text-red-400 hover:text-red-600 px-2"><i class="fa-solid fa-trash"></i></button>
    `;
    container.appendChild(div);
}

function removePoint(btn, containerId) {
    btn.parentElement.remove();
    checkEmpty(containerId);
}

document.getElementById('propertyForm').addEventListener('submit', function(e) {
    // Process Highlights
    const highlightInputs = document.querySelectorAll('.highlight-input');
    const highlights = [];
    highlightInputs.forEach(input => {
        if(input.value.trim() !== '') highlights.push(input.value.trim());
    });
    document.getElementById('highlight_points_json').value = highlights.join(',');

    // Process Location Advantages
    const locationInputs = document.querySelectorAll('.location-input');
    const locations = [];
    locationInputs.forEach(input => {
        if(input.value.trim() !== '') locations.push(input.value.trim());
    });
    document.getElementById('location_advantages_json').value = locations.join(',');

    // Only use AJAX for the main Update button
    const submitBtn = e.submitter && (e.submitter.name ? !e.submitter.name.startsWith('delete_') : true) ? e.submitter : null;
    
    if (submitBtn) {
        e.preventDefault();
        const originalBtnText = submitBtn.innerHTML;
        const formElement = e.currentTarget;
        
        // Show Loading State
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Updating...';

        console.log('Starting property update AJAX fetch...');
        const formData = new FormData(formElement);

        // Abort controller for timeout
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 60000); // 60s timeout

        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData,
            signal: controller.signal
        })
        .then(response => {
            clearTimeout(timeoutId);
            return response.json();
        })
        .then(data => {
            console.log('Update Response:', data);
            if (data.success) {
                showToast(data.message, 'success');
                // Clear storage
                localStorage.removeItem(FORM_STORAGE_KEY);
                // Redirect after delay
                setTimeout(() => {
                    window.location.href = data.redirect || 'properties.php';
                }, 1000);
            } else {
                showToast(data.message, 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        })
        .catch(error => {
            clearTimeout(timeoutId);
            const errorMsg = error.name === 'AbortError' ? 'Update timed out. Large files may take longer.' : 'Something went wrong. Please try again.';
            showToast(errorMsg, 'error');
            console.error('Submission Error:', error);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        });
    }
});
</script>

<script>
// Initial logic: Show "No points" message if empty, instead of adding input
function checkEmpty(containerId) {
    const container = document.getElementById(containerId);
    if(container.children.length === 0) {
        container.innerHTML = '<p class="text-sm text-slate-400 italic no-points-msg">No points added yet.</p>';
    }
}

checkEmpty('highlights-container');
checkEmpty('location-container');

function addPoint(containerId) {
    const container = document.getElementById(containerId);
    // Remove "No points" message if present
    const msg = container.querySelector('.no-points-msg');
    if(msg) msg.remove();

    const div = document.createElement('div');
    div.className = 'flex gap-2';
    
    // Determine input class based on container
    const inputClass = containerId === 'highlights-container' ? 'highlight-input' : 'location-input';
    
    div.innerHTML = `
        <input type="text" class="${inputClass} w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-4 focus:ring-2 focus:ring-secondary/50 outline-none text-sm" placeholder="Add another point">
        <button type="button" onclick="removePoint(this, '${containerId}')" class="text-red-400 hover:text-red-600 px-2"><i class="fa-solid fa-trash"></i></button>
    `;
    container.appendChild(div);
}

function addCustomAmenity() {
    const input = document.getElementById('new_amenity_name');
    const inputVal = input.value.trim();
    if (!inputVal) return;

    // Split by comma and handle each one
    const names = inputVal.split(',').map(n => n.trim()).filter(n => n !== '');
    
    if (names.length === 0) return;

    // Button loading state
    const btn = document.querySelector('button[onclick="addCustomAmenity()"]');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';

    // Process all amenities recursively or via loop
    const promises = names.map(name => {
        return fetch('<?php echo BASE_URL; ?>/api/add_amenity.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ name: name })
        }).then(r => r.json());
    });

    Promise.all(promises)
    .then(results => {
        results.forEach(data => {
            if (data.success) {
                // Check if already in the list to avoid duplicates
                const existingCheckbox = document.querySelector(`input[name="amenities[]"][value="${data.id}"]`);
                if (!existingCheckbox) {
                    const container = document.querySelector('.custom-scrollbar .grid');
                    const label = document.createElement('label');
                    label.className = 'flex items-center gap-3 p-2 hover:bg-white rounded-lg transition cursor-pointer';
                    label.innerHTML = `
                        <input type="checkbox" name="amenities[]" value="${data.id}" class="w-4 h-4 text-secondary rounded focus:ring-secondary border-gray-300" checked>
                        <span class="text-sm text-slate-700 flex items-center gap-2">
                            <i class="fa-solid fa-check text-slate-400 w-5"></i>
                            ${data.name}
                        </span>
                    `;
                    container.appendChild(label);
                } else {
                    existingCheckbox.checked = true;
                }
            } else {
                console.error('Error adding ' + data.name + ':', data.message);
            }
        });
        
        input.value = '';
        btn.disabled = false;
        btn.innerHTML = originalText;
        
        // Scroll to bottom
        const scrollContainer = document.querySelector('.custom-scrollbar');
        scrollContainer.scrollTop = scrollContainer.scrollHeight;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding amenities.');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function addFloorPlan() {
    const container = document.getElementById('floor-plans-container');
    const div = document.createElement('div');
    div.className = 'grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-slate-50 rounded-xl border border-slate-200 relative group';
    div.innerHTML = `
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Plan Title</label>
            <input type="text" name="fp_titles[]" class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 focus:outline-none focus:border-secondary text-sm" placeholder="e.g. 3BHK Luxury">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Size (e.g. 2500 Sq. Ft.)</label>
            <input type="text" name="fp_sizes[]" class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 focus:outline-none focus:border-secondary text-sm" placeholder="e.g. 1200 Sq. Ft.">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Image</label>
            <input type="file" name="fp_images[]" class="w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300" accept="image/*">
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="absolute -top-2 -right-2 bg-red-50 text-red-500 w-6 h-6 rounded-full flex items-center justify-center shadow-sm opacity-0 group-hover:opacity-100 transition"><i class="fa-solid fa-xmark text-xs"></i></button>
    `;
    container.appendChild(div);
}
</script>

<script>
let selectedFiles = new DataTransfer();

function previewCoverImage(input) {
    const placeholder = document.getElementById('cover-preview-placeholder');
    const container = document.getElementById('cover-preview-container');
    const preview = document.getElementById('cover-preview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            placeholder.classList.add('hidden');
            container.classList.remove('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        placeholder.classList.remove('hidden');
        container.classList.add('hidden');
    }
}

function previewImages(input) {
    const container = document.getElementById('image-preview-container');
    const files = input.files;
    
    // Add new files to our DataTransfer object
    for (let i = 0; i < files.length; i++) {
        selectedFiles.items.add(files[i]);
    }
    
    // Sync the input with our DataTransfer object
    input.files = selectedFiles.files;
    
    renderPreviews();
}

function renderPreviews() {
    const container = document.getElementById('image-preview-container');
    container.innerHTML = '';
    
    if (selectedFiles.files.length > 0) {
        container.classList.remove('hidden');
        Array.from(selectedFiles.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative rounded-xl overflow-hidden aspect-square border border-slate-200 bg-slate-50 group';
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-full object-cover">
                    <button type="button" onclick="removeFile(${index})" class="absolute top-2 right-2 bg-red-500 text-white w-6 h-6 rounded-full flex items-center justify-center shadow-lg hover:bg-red-600 transition">
                        <i class="fa-solid fa-xmark text-[10px]"></i>
                    </button>
                    <div class="absolute inset-x-0 bottom-0 bg-black/50 text-white text-[8px] py-1 text-center opacity-0 group-hover:opacity-100 transition">New</div>
                `;
                container.appendChild(div);
            }
            reader.readAsDataURL(file);
        });
    } else {
        container.classList.add('hidden');
    }
}

function removeFile(index) {
    const dt = new DataTransfer();
    const { files } = selectedFiles;
    
    for (let i = 0; i < files.length; i++) {
        if (i !== index) dt.items.add(files[i]);
    }
    
    selectedFiles = dt;
    document.querySelector('input[name="images[]"]').files = selectedFiles.files;
    renderPreviews();
}

function previewSingle(input, prefix) {
    // prefix is 'preview-highlights' or 'preview-location'
    const imgExisting = document.getElementById(prefix + '-img');
    const containerNew = document.getElementById(prefix + '-container');
    const imgNew = document.getElementById(prefix + '-new');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // If existing image is present, update its src
            if (imgExisting) {
                imgExisting.src = e.target.result;
            } 
            // Else show the new container
            else if (containerNew && imgNew) {
                containerNew.classList.remove('hidden');
                imgNew.src = e.target.result;
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Form Persistence Logic
const FORM_STORAGE_KEY = 'edit_property_form_data_' + <?php echo $property['id']; ?>;

function saveFormData() {
    const form = document.getElementById('propertyForm');
    const formData = new FormData(form);
    const data = {};
    
    formData.forEach((value, key) => {
        // Don't save files or sensitive data, or delete triggers
        if (!(value instanceof File) && !key.includes('points_json') && !key.startsWith('delete_')) {
            data[key] = value;
        }
    });
    
    localStorage.setItem(FORM_STORAGE_KEY, JSON.stringify(data));
}

function restoreFormData() {
    const savedData = localStorage.getItem(FORM_STORAGE_KEY);
    if (!savedData) return;
    
    const data = JSON.parse(savedData);
    const form = document.getElementById('propertyForm');
    
    Object.keys(data).forEach(key => {
        const input = form.querySelector(`[name="${key}"]`);
        if (input) {
            if (input.type === 'checkbox') {
                input.checked = data[key] === '1';
            } else if (input.type !== 'file' && input.tagName !== 'BUTTON') {
                input.value = data[key];
            }
        }
    });
}

// FORM_STORAGE_KEY is already defined above

// Event listeners for persistence
document.addEventListener('DOMContentLoaded', () => {
    restoreFormData();
    
    const form = document.getElementById('propertyForm');
    form.addEventListener('input', saveFormData);
});
</script>

<style>
/* Custom scrollbar for amenities */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1; 
    border-radius: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8; 
}
</style>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
