<?php
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in (admin or broker)
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'broker')) {
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
        exit;
    }
    redirect('../login.php');
}

$error = '';
$success = '';

// Handle new amenity addition
if (isset($_POST['add_amenity'])) {
    $newAmenity = clean_input($_POST['new_amenity_name']);
    if (!empty($newAmenity)) {
        // Basic check if exists
        $check = $pdo->prepare("SELECT id FROM amenities WHERE name = ?");
        $check->execute([$newAmenity]);
        if (!$check->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO amenities (name, icon) VALUES (?, 'fa-check')");
            $stmt->execute([$newAmenity]);
        }
    }
}

// Get amenities
$amenities = $pdo->query("SELECT * FROM amenities ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['add_amenity'])) {
    // Sanitize inputs
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
    
    // Dynamic Points (Coming from hidden JSON inputs)
    $highlight_points = $_POST['highlight_points_json']; // Expecting comma separated string from JS
    $location_advantages = $_POST['location_advantages_json'];

    $is_featured = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') ? (isset($_POST['is_featured']) ? 1 : 1) : 0; // Default to 1 for admin
    $is_approved = 1; // Auto-approve all property posts
    $ad_broker_name = clean_input($_POST['ad_broker_name'] ?? '');
    $ad_broker_type = clean_input($_POST['ad_broker_type'] ?? '');

    $selectedAmenities = isset($_POST['amenities']) ? $_POST['amenities'] : [];

    if (empty($title) || empty($price) || empty($location)) {
        $error = 'Please fill in required fields.';
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => $error]);
            exit;
        }
    } else {
        try {
            $pdo->beginTransaction();

            $slug = slugify($title);

            $stmt = $pdo->prepare("INSERT INTO properties (
                broker_id, title, slug, description, price, location, type, status, 
                developer, rera_no, brochure_url, size_range, configurations, 
                highlight_points, location_advantages, map_url, video_url, is_approved, is_featured,
                ad_broker_name, ad_broker_type
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $_SESSION['user_id'], $title, $slug, $description, $price, $location, $type, $status,
                $developer, $rera_no, $brochure_url, $size_range, $configurations,
                $highlight_points, $location_advantages, $map_url, $video_url,
                $is_approved,
                $is_featured,
                $ad_broker_name,
                $ad_broker_type
            ]);
            
            $propertyId = $pdo->lastInsertId();

            // Amenities
            if (!empty($selectedAmenities)) {
                $stmt = $pdo->prepare("INSERT INTO property_amenities (property_id, amenity_id) VALUES (?, ?)");
                foreach ($selectedAmenities as $amenityId) {
                    $stmt->execute([$propertyId, $amenityId]);
                }
            }

            // Image Upload Helper
            $uploadDir = __DIR__ . '/../assets/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            // Upload Highlights Image
            $highlightsImgPath = uploadFile('highlights_image', $uploadDir);
            if ($highlightsImgPath) {
                $pdo->prepare("UPDATE properties SET highlights_image = ? WHERE id = ?")->execute([$highlightsImgPath, $propertyId]);
            }

            // Upload Location Image
            $locImgPath = uploadFile('location_advantages_image', $uploadDir);
            if ($locImgPath) {
                $pdo->prepare("UPDATE properties SET location_advantages_image = ? WHERE id = ?")->execute([$locImgPath, $propertyId]);
            }
            // Upload Ad Broker Image
            $adBrokerImgPath = uploadFile('ad_broker_image', $uploadDir);
            if ($adBrokerImgPath) {
                $pdo->prepare("UPDATE properties SET ad_broker_image = ? WHERE id = ?")->execute([$adBrokerImgPath, $propertyId]);
            }

            // 1. Upload Main Cover Photo (Dedicated Field)
            $coverImgPath = uploadFile('cover_image', $uploadDir);
            if ($coverImgPath) {
                $stmt = $pdo->prepare("INSERT INTO property_images (property_id, image_path, is_cover) VALUES (?, ?, ?)");
                $stmt->execute([$propertyId, $coverImgPath, 1]);
            }

            // 2. Upload Gallery (No automatic cover assignment)
            if (isset($_FILES['images'])) {
                $files = $_FILES['images'];
                for ($i = 0; $i < count($files['name']); $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        if ($files['size'][$i] > 2 * 1024 * 1024) {
                            throw new Exception("Gallery image " . $files['name'][$i] . " exceeds 2MB limit.");
                        }
                        $tmpName = $files['tmp_name'][$i];
                        $name = time() . '_g_' . $i . '_' . basename($files['name'][$i]);
                        $destination = $uploadDir . $name;
                        
                        if (move_uploaded_file($tmpName, $destination)) {
                            $stmt = $pdo->prepare("INSERT INTO property_images (property_id, image_path, is_cover) VALUES (?, ?, ?)");
                            $stmt->execute([$propertyId, 'assets/uploads/' . $name, 0]);
                        }
                    }
                }
            }

            // Process Floor Plans
            if (isset($_POST['fp_titles']) && is_array($_POST['fp_titles'])) {
                $fpTitles = $_POST['fp_titles'];
                $fpSizes = $_POST['fp_sizes']; 
                $fpFiles = $_FILES['fp_images'];

                $stmt = $pdo->prepare("INSERT INTO property_floor_plans (property_id, title, size_sqft, image_path) VALUES (?, ?, ?, ?)");

                for ($i = 0; $i < count($fpTitles); $i++) {
                    $title = clean_input($fpTitles[$i]);
                    $size = clean_input($fpSizes[$i]);
                    $imagePath = 'assets/images/blurred-map-placeholder.jpg'; // Default

                    if (!empty($title)) {
                        // Handle File Upload for this row
                        if (isset($fpFiles['name'][$i]) && $fpFiles['error'][$i] === UPLOAD_ERR_OK) {
                            $tmpName = $fpFiles['tmp_name'][$i];
                            $name = 'fp_' . time() . '_' . $i . '_' . basename($fpFiles['name'][$i]);
                            $destination = $uploadDir . $name;
                            if (move_uploaded_file($tmpName, $destination)) {
                                $imagePath = 'assets/uploads/' . $name;
                            }
                        }
                        $stmt->execute([$propertyId, $title, $size, $imagePath]);
                    }
                }
            }

            $pdo->commit();
            $success = 'Property added successfully!';
            
            if ($isAjax) {
                echo json_encode(['success' => true, 'message' => $success, 'redirect' => 'dashboard.php']);
                exit;
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Error adding property: ' . $e->getMessage();
            
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => $error]);
                exit;
            }
        }
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
                <a href="properties.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800 hover:text-white transition"><i class="fa-solid fa-list w-5"></i> My Listings</a>
                <a href="add_property.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-secondary text-white shadow-lg shadow-secondary/50"><i class="fa-solid fa-plus-circle w-5"></i> Add Property</a>
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
                    <h1 class="text-2xl font-bold font-display text-slate-800">Add Property</h1>
                    <p class="text-slate-500">List a new property on the marketplace</p>
                </div>
                <a href="dashboard.php" class="text-secondary hover:underline text-sm font-medium">Back to request</a>
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
                            <input type="text" name="title" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 focus:border-secondary outline-none transition" placeholder="e.g. Luxury Apartment in Golf Course Road" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Location</label>
                            <input type="text" name="location" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 focus:border-secondary outline-none transition" placeholder="e.g. Sector 42, Gurgaon" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Price (₹)</label>
                            <input type="number" name="price" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 focus:border-secondary outline-none transition" placeholder="e.g. 15000000" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Price Text</label>
                            <input type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 outline-none transition" placeholder="e.g. ₹ 1.5 Cr onwards">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Type</label>
                            <select name="type" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 outline-none transition cursor-pointer">
                                <option value="apartment">Apartment</option>
                                <option value="house">House</option>
                                <option value="plot">Plot</option>
                                <option value="commercial">Commercial</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                            <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 outline-none transition cursor-pointer">
                                <option value="ready_to_move">Ready to Move</option>
                                <option value="under_construction">Under Construction</option>
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
                                <input type="checkbox" name="is_featured" value="1" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-secondary"></div>
                            </label>
                        </div>
                    </div>

                    <div class="mt-8 pt-8 border-t border-slate-100 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Display Name (Alias)</label>
                            <input type="text" name="ad_broker_name" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-secondary/50 outline-none transition text-sm" placeholder="e.g. Authorized Partner">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Display Type</label>
                            <input type="text" name="ad_broker_type" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-secondary/50 outline-none transition text-sm" placeholder="e.g. DEVELOPER">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Display Logo/Image</label>
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
                            <input type="text" name="developer" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 focus:border-secondary outline-none transition" placeholder="e.g. DLF">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">RERA No.</label>
                            <input type="text" name="rera_no" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 focus:border-secondary outline-none transition" placeholder="e.g. GGM/123/2024">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Configurations</label>
                            <input type="text" name="configurations" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 outline-none transition" placeholder="e.g. 2 BHK, 3 BHK, Penthouses">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Size Range</label>
                            <input type="text" name="size_range" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 outline-none transition" placeholder="e.g. 1200 - 2500 Sq. Ft.">
                        </div>
                        <div class="col-span-2">
                             <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
                             <textarea name="description" rows="4" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 outline-none transition" placeholder="Detailed property description..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Project Highlights (Dynamic Points + Image) -->
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-blue-50 text-secondary flex items-center justify-center text-sm">3</span> Project Highlights
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                             <label class="block text-sm font-medium text-slate-700 mb-2">Highlights Image</label>
                             <input type="file" name="highlights_image" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 outline-none transition" accept="image/*">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Key Points</label>
                            <div id="highlights-container" class="space-y-3 mb-3">
                                <div class="flex gap-2">
                                    <input type="text" class="highlight-input w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-4 focus:ring-2 focus:ring-secondary/50 outline-none text-sm" placeholder="Add a highlight point">
                                </div>
                            </div>
                            <button type="button" onclick="addPoint('highlights-container')" class="text-sm text-secondary font-medium hover:underline flex items-center gap-1"><i class="fa-solid fa-plus-circle"></i> Add another point</button>
                            <input type="hidden" name="highlight_points_json" id="highlight_points_json">
                        </div>
                    </div>
                </div>

                <!-- Section 4: Location Advantages (Dynamic Points + Image) -->
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-blue-50 text-secondary flex items-center justify-center text-sm">4</span> Location Advantages
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                             <label class="block text-sm font-medium text-slate-700 mb-2">Location Map/Image</label>
                             <input type="file" name="location_advantages_image" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 outline-none transition" accept="image/*">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Key Locations</label>
                            <div id="location-container" class="space-y-3 mb-3">
                                <div class="flex gap-2">
                                    <input type="text" class="location-input w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-4 focus:ring-2 focus:ring-secondary/50 outline-none text-sm" placeholder="Add location advantage">
                                </div>
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
                            <?php foreach ($amenities as $am): ?>
                            <label class="flex items-center gap-3 p-2 hover:bg-white rounded-lg transition cursor-pointer">
                                <input type="checkbox" name="amenities[]" value="<?php echo $am['id']; ?>" class="w-4 h-4 text-secondary rounded focus:ring-secondary border-gray-300">
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
                    <div id="floor-plans-container" class="space-y-6">
                        <!-- Initial Row -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-slate-50 rounded-xl border border-slate-200 relative group">
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Plan Title</label>
                                <input type="text" name="fp_titles[]" class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 focus:outline-none focus:border-secondary text-sm" placeholder="e.g. 3BHK Luxury">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Size (Sq. Ft.)</label>
                                <input type="text" name="fp_sizes[]" class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 focus:outline-none focus:border-secondary text-sm" placeholder="e.g. 2100">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Plan Image</label>
                                <input type="file" name="fp_images[]" class="w-full bg-white border border-slate-200 rounded-lg py-1.5 px-3 focus:outline-none focus:border-secondary text-sm" accept="image/*">
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="addFloorPlan()" class="mt-4 text-sm text-secondary font-medium hover:underline flex items-center gap-1">
                        <i class="fa-solid fa-plus-circle"></i> Add another floor plan
                    </button>
                    <p class="text-xs text-slate-400 mt-2">Default blurred placeholder used if no image uploaded.</p>
                </div>

                <!-- Section 7: Media & Links -->
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-blue-50 text-secondary flex items-center justify-center text-sm">7</span> Media & Links
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="col-span-2">
                             <label class="block text-sm font-medium text-slate-700 mb-2">Main Cover Photo <span class="text-red-500">*</span></label>
                             <div class="border-2 border-dashed border-secondary/30 rounded-xl p-8 text-center hover:bg-secondary/5 transition cursor-pointer relative bg-secondary/5 mb-8">
                                 <input type="file" name="cover_image" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*" required onchange="previewCoverImage(this)">
                                 <div id="cover-preview-placeholder">
                                     <i class="fa-solid fa-image text-3xl text-secondary mb-2"></i>
                                     <p class="text-sm font-medium text-slate-700">Select Main Cover Photo</p>
                                     <p class="text-xs text-slate-500 mt-1">This will be the primary image for the listing</p>
                                 </div>
                                 <div id="cover-preview-container" class="hidden">
                                      <img id="cover-preview" src="#" class="mx-auto h-40 w-auto rounded-lg object-cover shadow-md border-2 border-white">
                                      <p class="text-xs text-secondary mt-2 font-medium">Click or Drag to change cover photo</p>
                                 </div>
                             </div>

                            <label class="block text-sm font-medium text-slate-700 mb-2">Gallery Images</label>
                            
                            <!-- Preview Container -->
                            <div id="image-preview-container" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4 hidden"></div>

                            <div class="border-2 border-dashed border-slate-300 rounded-xl p-8 text-center hover:bg-slate-50 transition cursor-pointer relative bg-slate-50">
                                <input type="file" name="images[]" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*" onchange="previewImages(this)">
                                <i class="fa-solid fa-images text-3xl text-slate-400 mb-2"></i>
                                <p class="text-sm font-medium text-slate-700">Click to select multiple photos</p>
                                <p class="text-xs text-slate-500 mt-1">Accepts JPG, PNG, WEBP</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Brochure URL</label>
                            <input type="text" name="brochure_url" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 outline-none transition" placeholder="Link to PDF">
                        </div>
                         <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Video URL</label>
                            <input type="text" name="video_url" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 outline-none transition" placeholder="YouTube/Vimeo Link">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Map Embed URL</label>
                            <input type="text" name="map_url" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-secondary/50 outline-none transition" placeholder="Google Maps Embed Link">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4">
                     <a href="dashboard.php" class="px-8 py-4 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition">Cancel</a>
                     <button type="submit" class="px-8 py-4 rounded-xl bg-secondary text-white font-bold hover:bg-blue-600 shadow-lg shadow-secondary/30 transition transform hover:-translate-y-0.5">Submit Property</button>
                </div>

            </form>
        </div>
    </main>
</div>

<script>
function addPoint(containerId) {
    const container = document.getElementById(containerId);
    const div = document.createElement('div');
    div.className = 'flex gap-2';
    
    // Determine input class based on container
    const inputClass = containerId === 'highlights-container' ? 'highlight-input' : 'location-input';
    
    div.innerHTML = `
        <input type="text" class="${inputClass} w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-4 focus:ring-2 focus:ring-secondary/50 outline-none text-sm" placeholder="Add another point">
        <button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600 px-2"><i class="fa-solid fa-trash"></i></button>
    `;
    container.appendChild(div);
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

    // AJAX Form Submission
    e.preventDefault();
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    
    // Show Loading State
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Processing...';

    const formData = new FormData(form);

    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            // Clear storage
            localStorage.removeItem(FORM_STORAGE_KEY);
            // Redirect after delay
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1500);
        } else {
            showToast(data.message, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    })
    .catch(error => {
        showToast('Something went wrong. Please try again.', 'error');
        console.error('Submission Error:', error);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
});

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
    div.className = 'grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-slate-50 rounded-xl border border-slate-200 relative group animate-fade-up';
    div.innerHTML = `
        <button type="button" onclick="this.parentElement.remove()" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-xs shadow-md hover:bg-red-600 transition"><i class="fa-solid fa-times"></i></button>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Plan Title</label>
            <input type="text" name="fp_titles[]" class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 focus:outline-none focus:border-secondary text-sm" placeholder="e.g. 3BHK Luxury">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Size (Sq. Ft.)</label>
            <input type="text" name="fp_sizes[]" class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 focus:outline-none focus:border-secondary text-sm" placeholder="e.g. 2100">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Plan Image</label>
            <input type="file" name="fp_images[]" class="w-full bg-white border border-slate-200 rounded-lg py-1.5 px-3 focus:outline-none focus:border-secondary text-sm" accept="image/*">
        </div>
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

// Form Persistence Logic
const FORM_STORAGE_KEY = 'add_property_form_data';

function saveFormData() {
    const form = document.getElementById('propertyForm');
    const formData = new FormData(form);
    const data = {};
    
    formData.forEach((value, key) => {
        // Don't save files or sensitive data
        if (!(value instanceof File) && !key.includes('points_json')) {
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
            } else if (input.type !== 'file') {
                input.value = data[key];
            }
        }
    });
}

// Clear storage on successful submit or cancel
document.getElementById('propertyForm').addEventListener('submit', () => {
    // We clear after a short delay to ensure valid submission
    setTimeout(() => {
        localStorage.removeItem(FORM_STORAGE_KEY);
    }, 1000);
});

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
