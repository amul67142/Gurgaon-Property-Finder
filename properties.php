<?php
require_once __DIR__ . '/includes/header.php';

// Build Query
$sql = "SELECT p.*, u.name as seller_name, u.seller_type, u.role, u.profile_image 
        FROM properties p 
        LEFT JOIN users u ON p.broker_id = u.id 
        WHERE p.is_approved = 1";
$params = [];

if (!empty($_GET['location'])) {
    $sql .= " AND location LIKE ?";
    $params[] = "%" . $_GET['location'] . "%";
}
if (!empty($_GET['type'])) {
    $sql .= " AND type = ?";
    $params[] = $_GET['type'];
}
if (!empty($_GET['status'])) {
    $sql .= " AND status = ?";
    $params[] = $_GET['status'];
}
if (!empty($_GET['bhk'])) {
    $bhk = intval($_GET['bhk']);
    $sql .= " AND (configurations LIKE ? OR title LIKE ?)";
    $params[] = "%$bhk BHK%";
    $params[] = "%$bhk BHK%";
}
if (!empty($_GET['max_price'])) {
    $sql .= " AND price <= ?";
    $params[] = $_GET['max_price'];
}
if (!empty($_GET['min_price'])) {
    $sql .= " AND price >= ?";
    $params[] = $_GET['min_price'];
}
if (isset($_GET['featured']) && $_GET['featured'] == '1') {
    $sql .= " AND is_featured = 1";
}
$sql .= " ORDER BY p.sort_order ASC, p.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $properties = $stmt->fetchAll();
} catch (PDOException $e) {
    $properties = [];
    $dbError = true;
}
?>

<div class="bg-gradient-to-b from-slate-50 to-white py-10">
    <div class="container mx-auto px-6">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Filters Toggle (Mobile Only) -->
            <div class="lg:hidden mb-4">
                <button id="filter-toggle-btn" class="w-full bg-white border border-slate-200 text-slate-700 font-bold py-3 rounded-xl flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition-all">
                    <i class="fa-solid fa-sliders text-secondary text-sm"></i>
                    <span class="text-sm">Filter Properties</span>
                </button>
            </div>

            <!-- Sidebar Filters -->
            <div id="filter-sidebar" class="w-full lg:w-1/4 hidden lg:block" data-aos="fade-right">
                <div class="bg-white p-6 rounded-2xl shadow-sm sticky top-24 border border-slate-100">
                    <h3 class="text-xl font-bold mb-6 font-display flex items-center gap-2">
                        <i class="fa-solid fa-filter text-secondary"></i> Filters
                    </h3>
                    <form action="" method="GET" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Location</label>
                            <input type="text" name="location" value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>" placeholder="Enter Sector/Area" class="w-full bg-slate-50 border border-slate-100 rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Property Type</label>
                            <select name="type" class="w-full bg-slate-50 border border-slate-100 rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none appearance-none cursor-pointer transition-all">
                                <option value="">All Types</option>
                                <option value="apartment" <?php echo (isset($_GET['type']) && $_GET['type'] == 'apartment') ? 'selected' : ''; ?>>Apartments</option>
                                <option value="house" <?php echo (isset($_GET['type']) && $_GET['type'] == 'house') ? 'selected' : ''; ?>>Independent Houses</option>
                                <option value="plot" <?php echo (isset($_GET['type']) && $_GET['type'] == 'plot') ? 'selected' : ''; ?>>Plots / Land</option>
                                <option value="commercial" <?php echo (isset($_GET['type']) && $_GET['type'] == 'commercial') ? 'selected' : ''; ?>>Commercial</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Status</label>
                            <select name="status" class="w-full bg-slate-50 border border-slate-100 rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none appearance-none cursor-pointer transition-all">
                                <option value="">Any Status</option>
                                <option value="ready_to_move" <?php echo (isset($_GET['status']) && $_GET['status'] == 'ready_to_move') ? 'selected' : ''; ?>>Ready to Move</option>
                                <option value="under_construction" <?php echo (isset($_GET['status']) && $_GET['status'] == 'under_construction') ? 'selected' : ''; ?>>Under Construction</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Price Range (₹)</label>
                            <div class="grid grid-cols-2 gap-3">
                                <input type="number" name="min_price" value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>" class="w-full bg-slate-50 border border-slate-100 rounded-xl py-3 px-4 text-sm outline-none placeholder:text-slate-300" placeholder="Min">
                                <input type="number" name="max_price" value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>" class="w-full bg-slate-50 border border-slate-100 rounded-xl py-3 px-4 text-sm outline-none placeholder:text-slate-300" placeholder="Max">
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-slate-900 text-white font-bold py-4 rounded-xl hover:bg-secondary transition shadow-lg shadow-slate-900/10 active:scale-[0.98] text-xs uppercase tracking-widest mt-4">Apply Filters</button>
                        
                        <?php if(!empty($_GET)): ?>
                            <a href="properties.php" class="block text-center text-[10px] font-bold text-slate-400 hover:text-red-500 uppercase tracking-widest mt-2 transition">Clear All</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Listings Grid -->
            <div class="w-full lg:w-3/4">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold font-display">Properties <span class="text-slate-400 text-lg font-normal ml-2">(<?php echo count($properties); ?> Found)</span></h2>
                </div>

                <?php if (isset($dbError)): ?>
                    <div class="bg-red-50 text-red-600 p-6 rounded-xl border border-red-200 text-center">
                        <i class="fa-solid fa-triangle-exclamation text-3xl mb-3"></i>
                        <h3 class="font-bold">Database Error</h3>
                        <p>Table 'properties' not found. Please import schema.</p>
                    </div>
                <?php elseif (count($properties) > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach ($properties as $prop): ?>
                            <?php
                            $isFeatured = $prop['is_featured'] == 1;
                            $imagePath = get_property_cover($prop['id'], $pdo);
                            $sImg = get_seller_logo($prop);
                            ?>
                            <div class="bg-white rounded-xl overflow-hidden <?php echo $isFeatured ? 'border-2 border-secondary/20 shadow-xl shadow-secondary/5 hover:shadow-secondary/20 bg-gradient-to-b from-white to-secondary/[0.02]' : 'border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-1'; ?> transition-all duration-<?php echo $isFeatured ? '500' : '300'; ?> group relative flex flex-col h-full" data-aos="fade-up">
                                <?php if ($isFeatured): ?>
                                <!-- Premium Badge -->
                                <div class="absolute top-4 left-4 z-30 flex items-center gap-1.5 bg-secondary text-white px-3 py-1 rounded-full text-[9px] font-bold uppercase tracking-widest shadow-lg animate-pulse-slow">
                                    <i class="fa-solid fa-crown"></i> Premium
                                </div>
                                <?php endif; ?>
                                <div class="relative overflow-hidden <?php echo $isFeatured ? 'h-64' : 'h-56'; ?>">
                                    <img src="<?php echo htmlspecialchars($imagePath); ?>" class="w-full h-full object-cover <?php echo $isFeatured ? 'transform group-hover:scale-110' : 'grayscale-[20%] group-hover:grayscale-0'; ?> transition-all duration-700">
                                    <?php if ($isFeatured): ?>
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <?php endif; ?>
                                    <!-- Status Badge -->
                                    <div class="absolute <?php echo $isFeatured ? 'top-14 right-4' : 'top-4 left-4'; ?> bg-white/<?php echo $isFeatured ? '95' : '90'; ?> backdrop-blur-md px-3 py-<?php echo $isFeatured ? '1' : '0.5'; ?> text-[<?php echo $isFeatured ? '10px' : '9px'; ?>] font-bold uppercase tracking-widest text-slate-<?php echo $isFeatured ? '800' : '600'; ?> border border-slate-100 z-10 rounded">
                                        <?php echo str_replace('_', ' ', $prop['status']); ?>
                                    </div>
                                    
                                    <!-- AI Stamp -->
                                    <!-- AI Stamp -->
                                    <div class="absolute top-4 right-4 group/ai z-20">
                                        <button onclick="getInvestmentReport(<?php echo $prop['id']; ?>); event.preventDefault();" class="bg-white/10 backdrop-blur-md border border-white/20 text-white w-8 h-8 rounded-full flex items-center justify-center transition-all duration-300 hover:w-auto hover:px-4 hover:bg-slate-900/90 shadow-lg overflow-hidden group-hover/ai:w-auto group-hover/ai:px-4 group-hover/ai:bg-slate-900/90">
                                            <i class="fa-solid fa-wand-magic-sparkles text-sm text-gold"></i>
                                            <span class="w-0 overflow-hidden opacity-0 group-hover/ai:w-auto group-hover/ai:opacity-100 group-hover/ai:ml-2 transition-all duration-300 whitespace-nowrap text-[10px] font-bold uppercase tracking-wider">AI Report Available</span>
                                        </button>
                                    </div>

                                    <!-- Seller Floating Logo -->
                                    <div class="absolute bottom-4 right-4 z-20 flex items-center gap-2 group/seller">
                                        <div class="bg-white/90 backdrop-blur-md px-3 py-1 rounded-full shadow-lg opacity-0 -translate-x-2 group-hover/seller:opacity-100 group-hover/seller:translate-x-0 transition-all duration-300 invisible group-hover/seller:visible">
                                            <p class="text-[10px] font-bold text-slate-800 whitespace-nowrap"><?php echo htmlspecialchars($prop['ad_broker_name'] ?: ($prop['seller_name'] ?: 'Seller')); ?></p>
                                        </div>
                                        <div class="w-10 h-10 rounded-full border-2 <?php echo $isFeatured ? 'border-secondary/30 group-hover:border-secondary transition-colors' : 'border-white'; ?> shadow-xl overflow-hidden bg-white">
                                            <?php if (!empty($sImg)): ?>
                                                <img src="<?php echo htmlspecialchars($sImg); ?>" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center bg-slate-100 text-slate-400 text-xs">
                                                    <i class="fa-solid fa-user"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="<?php echo $isFeatured ? 'p-6' : 'p-5'; ?> flex-1 flex flex-col">
                                    <h3 class="text-<?php echo $isFeatured ? '2xl' : 'lg'; ?> font-bold font-display text-slate-<?php echo $isFeatured ? '900' : '800'; ?> mb-2 line-clamp-1 <?php echo $isFeatured ? 'group-hover:text-secondary transition-colors duration-300' : 'group-hover:text-secondary transition'; ?>">
                                        <?php echo htmlspecialchars($prop['title']); ?>
                                    </h3>
                                    <div class="flex items-center gap-1.5 text-slate-<?php echo $isFeatured ? '400' : '400'; ?> text-<?php echo $isFeatured ? 'sm' : '[10px]'; ?> mb-4 font-<?php echo $isFeatured ? 'medium' : 'medium'; ?>">
                                        <i class="fa-solid fa-<?php echo $isFeatured ? 'location-dot' : 'map-pin'; ?> text-<?php echo $isFeatured ? 'secondary/60' : 'slate-300'; ?>"></i>
                                        <?php echo htmlspecialchars($prop['location']); ?>
                                    </div>
                                    
                                    <?php if ($isFeatured): ?>
                                    <div class="flex-1">
                                        <!-- Seller Info Card for Featured -->
                                        <div class="flex items-center gap-3 mb-6 p-3 bg-slate-50 rounded-xl border border-slate-100 group-hover:bg-white group-hover:border-secondary/20 transition-all">
                                             <div class="w-10 h-10 rounded-full bg-<?php echo $isFeatured ? 'white' : 'slate-100'; ?> flex items-center justify-center overflow-hidden border border-slate-<?php echo $isFeatured ? '100' : '200'; ?>">
                                                 <?php if (!empty($sImg)): ?>
                                                     <img src="<?php echo htmlspecialchars($sImg); ?>" class="w-full h-full object-cover">
                                                 <?php else: ?>
                                                     <i class="fa-solid fa-user text-slate-400 text-sm"></i>
                                                 <?php endif; ?>
                                             </div>
                                             <div>
                                                 <h4 class="text-xs font-bold text-slate-800 leading-tight">
                                                     <?php echo htmlspecialchars($prop['ad_broker_name'] ?: ($prop['seller_name'] ?: 'Verified Seller')); ?>
                                                 </h4>
                                                 <span class="text-[10px] uppercase font-bold text-secondary tracking-widest block <?php echo $isFeatured ? 'mt-0.5' : 'mt-0.5'; ?>">
                                                     <?php echo htmlspecialchars($prop['ad_broker_type'] ?: ($prop['seller_type'] ?: ($prop['role'] == 'admin' ? 'Admin' : 'Broker'))); ?>
                                                 </span>
                                             </div>
                                         </div>
                                    </div>
                                    
                                    <!-- Price Section -->
                                    <div class="<?php echo $isFeatured ? 'flex items-center justify-between pt-6 border-t border-slate-100' : 'mt-auto pt-4 border-t border-slate-50 flex items-center justify-between'; ?>">
                                         <?php if ($isFeatured): ?>
                                         <div>
                                             <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest mb-1">Starting From</p>
                                             <p class="text-secondary font-black text-2xl">
                                                <?php echo formatPrice($prop['price']); ?>
                                             </p>
                                         </div>
                                         <a href="<?php echo BASE_URL; ?>/property/<?php echo ($prop['slug'] ?? $prop['id']); ?>" class="bg-secondary text-white px-6 py-3 rounded-lg text-xs font-bold uppercase tracking-wider hover:bg-slate-900 transition-all shadow-lg shadow-secondary/20 hover:shadow-slate-900/20 active:scale-95">
                                             Details <i class="fa-solid fa-arrow-right ml-1 text-[10px]"></i>
                                         </a>
                                         <?php else: ?>
                                         <p class="text-slate-900 font-bold text-lg">
                                            <?php echo formatPrice($prop['price']); ?>
                                         </p>
                                         <a href="<?php echo BASE_URL; ?>/property/<?php echo ($prop['slug'] ?? $prop['id']); ?>" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest hover:text-secondary transition">View Details</a>
                                         <?php endif; ?>
                                    </div>
                                    <?php else: ?>
                                    <!-- Standard Layout: Price at bottom -->
                                    <div class="mt-auto pt-4 border-t border-slate-50 flex items-center justify-between">
                                         <p class="text-slate-900 font-bold text-lg">
                                            <?php echo formatPrice($prop['price']); ?>
                                         </p>
                                         <a href="<?php echo BASE_URL; ?>/property/<?php echo ($prop['slug'] ?? $prop['id']); ?>" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest hover:text-secondary transition">View Details</a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-2xl p-12 text-center border-2 border-dashed border-slate-200">
                        <div class="text-6xl text-slate-200 mb-4"><i class="fa-regular fa-folder-open"></i></div>
                        <h3 class="text-xl font-semibold text-slate-600">No properties match your filters</h3>
                        <a href="properties.php" class="text-secondary font-semibold hover:underline mt-2 inline-block">Reset Filters</a>
                    </div>

<script>
// Filter Toggle Logic
document.getElementById('filter-toggle-btn')?.addEventListener('click', function() {
    const sidebar = document.getElementById('filter-sidebar');
    const isHidden = sidebar.classList.contains('hidden');
    sidebar.classList.toggle('hidden');
    if (isHidden) {
        sidebar.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
});

// Investment Report Logic (Reused)
function getInvestmentReport(propertyId) {
    document.getElementById('reportModal').style.display = 'flex';
    const contentDiv = document.getElementById('reportContent');
    contentDiv.innerHTML = '<div class="text-center py-10"><i class="fa-solid fa-circle-notch fa-spin text-4xl text-secondary mb-4"></i><p class="text-slate-600 animate-pulse">Analyzing Gurugram Market Data...</p><p class="text-xs text-slate-400 mt-2">Checking Amenities, Connectivity & Price Trends</p></div>';

    const formData = new FormData();
    formData.append('property_id', propertyId);

    fetch('api/generate_investment_report.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            contentDiv.innerHTML = '<div class="text-center text-red-500 py-10"><i class="fa-solid fa-triangle-exclamation text-4xl mb-4"></i><p>' + data.error + '</p></div>';
        } else {
            contentDiv.innerHTML = data.html;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        contentDiv.innerHTML = '<div class="text-center text-red-500 py-10"><p>Failed to generate report. Please try again.</p></div>';
    });
}

function closeReportModal() {
    document.getElementById('reportModal').style.display = 'none';
}

window.onclick = function(event) {
    if (event.target == document.getElementById('reportModal')) {
        closeReportModal();
    }
}
</script>

<!-- Investment Report Modal -->
<div id="reportModal" class="fixed inset-0 bg-black/80 hidden z-[110] items-center justify-center p-4 backdrop-blur-md">
    <div class="bg-white w-full max-w-2xl rounded-[20px] overflow-hidden shadow-2xl relative animate-fade-up max-h-[90vh] flex flex-col">
        <!-- Header -->
        <div class="bg-slate-900 p-6 flex justify-between items-center shrink-0">
            <h3 class="text-xl font-display font-medium text-white flex items-center gap-2">
                <i class="fa-solid fa-robot text-secondary"></i> AI Investment Memo
            </h3>
            <button onclick="closeReportModal()" class="w-8 h-8 bg-white/10 rounded-full flex items-center justify-center text-white hover:bg-white/20 transition">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        
        <!-- Content -->
        <div id="reportContent" class="p-8 overflow-y-auto custom-scrollbar">
            <!-- Content Injected Here -->
        </div>

        <!-- Footer -->
        <div class="p-4 border-t border-stone-100 bg-stone-50 text-center shrink-0">
            <p class="text-[10px] text-slate-400 uppercase tracking-widest">
                Generated by AI • Verify details independently
            </p>
        </div>
    </div>
</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
