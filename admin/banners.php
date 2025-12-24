<?php
@session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

// Handle Form Submission
$message = '';
$error = '';

// Delete Banner
if (isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM ad_banners WHERE id = ?");
    if ($stmt->execute([$_POST['delete_id']])) {
        $message = "Banner deleted successfully.";
    } else {
        $error = "Failed to delete banner.";
    }
}

// Toggle Status
if (isset($_POST['toggle_id'])) {
    $stmt = $pdo->prepare("UPDATE ad_banners SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$_POST['toggle_id']]);
    header("Location: banners.php");
    exit;
}

// Add New Banner
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_banner'])) {
    $title = trim($_POST['title']);
    $linkType = $_POST['link_type'];
    $customLink = trim($_POST['custom_link']);
    
    // Image Upload
    if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            if ($_FILES['banner_image']['size'] > 2 * 1024 * 1024) {
                $error = "Banner image must be less than 2MB.";
            } else {
                $filename = 'ad_' . time() . '.' . $ext;
                $targetDir = __DIR__ . '/../assets/images/banners/';
                if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
                
                if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $targetDir . $filename)) {
                    $imagePath = 'assets/images/banners/' . $filename;
                    
                    $stmt = $pdo->prepare("INSERT INTO ad_banners (title, image_path, link_type, custom_link) VALUES (?, ?, ?, ?)");
                    if ($stmt->execute([$title, $imagePath, $linkType, $customLink])) {
                        $message = "Banner added successfully!";
                    } else {
                        $error = "Database error.";
                    }
                } else {
                    $error = "Failed to upload image.";
                }
            }
        } else {
            $error = "Invalid file type. Allowed: JPG, PNG, WEBP.";
        }
    } else {
        $error = "Please select an image.";
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="flex min-h-screen bg-slate-50">
    <!-- Sidebar -->
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 lg:ml-64 p-4 md:p-8">
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold font-display text-slate-800">Manage Banners</h1>
                <p class="text-slate-500">Upload and manage promotional banners</p>
            </div>
            <button onclick="document.getElementById('addBannerModal').classList.remove('hidden')" class="bg-slate-900 text-white px-5 py-2.5 rounded-lg hover:bg-secondary transition flex items-center gap-2 text-sm font-medium">
                <i class="fa-solid fa-plus"></i> Add New Banner
            </button>
        </div>

        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
                <i class="fa-solid fa-check-circle"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Banners Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <?php
            $banners = $pdo->query("SELECT * FROM ad_banners ORDER BY created_at DESC")->fetchAll();
            if (count($banners) > 0):
                foreach ($banners as $banner):
            ?>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden group">
                <div class="h-48 relative bg-slate-100">
                    <img src="<?php echo BASE_URL . '/' . $banner['image_path']; ?>" class="w-full h-full object-cover">
                    <div class="absolute top-2 right-2">
                        <span class="px-2 py-1 rounded text-xs font-bold <?php echo $banner['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                            <?php echo $banner['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                </div>
                <div class="p-5">
                    <h3 class="font-bold text-slate-800 mb-1"><?php echo htmlspecialchars($banner['title']); ?></h3>
                    <p class="text-xs text-slate-500 mb-4 flex items-center gap-1">
                        <i class="fa-solid <?php echo $banner['link_type'] == 'lead_form' ? 'fa-envelope-open-text' : 'fa-link'; ?>"></i>
                        <?php echo $banner['link_type'] == 'lead_form' ? 'Lead Form' : 'Custom Link'; ?>
                    </p>
                    
                    <div class="flex items-center gap-3 border-t border-slate-50 pt-4">
                        <form method="POST" class="flex-1">
                            <input type="hidden" name="toggle_id" value="<?php echo $banner['id']; ?>">
                            <button type="submit" class="w-full py-2 text-xs font-bold rounded-lg transition <?php echo $banner['is_active'] ? 'bg-yellow-50 text-yellow-600 hover:bg-yellow-100' : 'bg-green-50 text-green-600 hover:bg-green-100'; ?>">
                                <?php echo $banner['is_active'] ? 'Pause' : 'Activate'; ?>
                            </button>
                        </form>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this banner?');" class="flex-1">
                            <input type="hidden" name="delete_id" value="<?php echo $banner['id']; ?>">
                            <button type="submit" class="w-full py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 text-xs font-bold transition">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; else: ?>
                <div class="col-span-full py-12 text-center text-slate-400 bg-white rounded-2xl border border-dashed border-slate-200">
                    <i class="fa-regular fa-images text-4xl mb-3"></i>
                    <p>No banners uploaded yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Add Banner Modal -->
<div id="addBannerModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl transform transition-all scale-100">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-xl font-bold font-display text-slate-800">Add New Banner</h3>
            <button onclick="document.getElementById('addBannerModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Banner Title</label>
                <input type="text" name="title" required class="w-full px-4 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-secondary">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Banner Image (1200x300 recommended)</label>
                <input type="file" name="banner_image" required accept="image/*" class="w-full px-4 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-secondary file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-secondary file:text-white hover:file:bg-yellow-600">
            </div>

            <div>
                 <label class="block text-sm font-medium text-slate-700 mb-1">Action Type</label>
                 <select name="link_type" id="linkType" class="w-full px-4 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-secondary" onchange="toggleLinkInput()">
                     <option value="lead_form">Submit Lead Form (Default)</option>
                     <option value="custom">Open Custom Link</option>
                 </select>
            </div>

            <div id="customLinkDiv" class="hidden">
                <label class="block text-sm font-medium text-slate-700 mb-1">Destination URL</label>
                <input type="url" name="custom_link" placeholder="https://example.com/project" class="w-full px-4 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-secondary">
            </div>

            <div class="pt-4">
                <button type="submit" name="add_banner" class="w-full bg-slate-900 text-white py-3 rounded-xl font-bold hover:bg-secondary transition shadow-lg shadow-slate-900/20">
                    Upload Banner
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleLinkInput() {
    const type = document.getElementById('linkType').value;
    const linkDiv = document.getElementById('customLinkDiv');
    if (type === 'custom') {
        linkDiv.classList.remove('hidden');
    } else {
        linkDiv.classList.add('hidden');
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
