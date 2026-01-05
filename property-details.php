<?php
@session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (!isset($_GET['id']) && !isset($_GET['slug'])) {
    redirect('properties.php');
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

try {
    // Fetch Property
    if ($slug) {
        $stmt = $pdo->prepare("SELECT p.*, u.name as broker_name, u.phone as broker_phone, u.email as broker_email, u.seller_type, u.profile_image 
                               FROM properties p 
                               JOIN users u ON p.broker_id = u.id 
                               WHERE p.slug = ? AND p.is_approved = 1");
        $stmt->execute([$slug]);
    } else {
        $stmt = $pdo->prepare("SELECT p.*, u.name as broker_name, u.phone as broker_phone, u.email as broker_email, u.seller_type, u.profile_image 
                               FROM properties p 
                               JOIN users u ON p.broker_id = u.id 
                               WHERE p.id = ? AND p.is_approved = 1");
        $stmt->execute([$id]);
    }
    
    $property = $stmt->fetch();

    if (!$property) throw new Exception("Property not found");
    
    $id = $property['id'];

    // SEO Variables for Header
    $page_title = htmlspecialchars($property['title']) . " in " . htmlspecialchars($property['location']) . " | Gurgaon Property Finder";
    $page_description = substr(strip_tags($property['description']), 0, 160) . "...";
    $page_image = get_property_cover($id, $pdo);

    // Fetch Images
    $imgStmt = $pdo->prepare("SELECT * FROM property_images WHERE property_id = ? ORDER BY is_cover DESC");
    $imgStmt->execute([$id]);
    $allImages = $imgStmt->fetchAll();

    $coverImage = null;
    $galleryImages = [];
    foreach ($allImages as $img) {
        if ($img['is_cover']) {
            if (!$coverImage) $coverImage = $img;
            else $galleryImages[] = $img; // Fallback for multiple covers (shouldn't happen)
        } else {
            $galleryImages[] = $img;
        }
    }

    // Fetch Amenities
    $amenities = $pdo->prepare("SELECT a.name, a.icon FROM amenities a 
                                JOIN property_amenities pa ON a.id = pa.amenity_id 
                                WHERE pa.property_id = ?");
    $amenities->execute([$id]);
    $amenities = $amenities->fetchAll();

    // Fetch Floor Plans
    $floorPlans = $pdo->prepare("SELECT * FROM property_floor_plans WHERE property_id = ?");
    $floorPlans->execute([$id]);
    $floorPlans = $floorPlans->fetchAll();
    
    // Parse Dynamic Points
    $highlights = !empty($property['highlight_points']) ? explode(',', $property['highlight_points']) : [];
    $locationPoints = !empty($property['location_advantages']) ? explode(',', $property['location_advantages']) : [];

    // Fetch Related Projects (Same Location OR Similar Price +/- 20%)
    $minPrice = $property['price'] * 0.8;
    $maxPrice = $property['price'] * 1.2;
    $relatedStmt = $pdo->prepare("SELECT p.*, u.name as broker_name, u.seller_type, u.profile_image 
                                  FROM properties p 
                                  LEFT JOIN users u ON p.broker_id = u.id 
                                  WHERE p.is_approved = 1 AND p.id != ? AND (p.location LIKE ? OR p.price BETWEEN ? AND ?) 
                                  LIMIT 3");
    $relatedStmt->execute([$id, '%' . $property['location'] . '%', $minPrice, $maxPrice]);
    $relatedProperties = $relatedStmt->fetchAll();

    // Determine Hero Image
    $heroImage = $coverImage ? $coverImage['image_path'] : (!empty($galleryImages) ? $galleryImages[0]['image_path'] : '/assets/images/hero-bg.jpg');
    if (strpos($heroImage, 'http') === false) {
        $heroImage = BASE_URL . '/' . ltrim($heroImage, '/');
    }

} catch (Exception $e) {
    // Error handling
    echo '<div class="bg-black min-h-screen text-white flex items-center justify-center">Property not found.</div>';
    exit();
}
?>

<style>
    body { background-color: #050505; color: #e5e5e5; font-family: 'Inter', sans-serif; }
    
    /* Immersive Header Overrides */
    /* Immersive Header Overrides Removed - Using Standard Header */
    /* Navbar styling handled by header.php script */
    .text-gold { color: #d4af37; }
    .bg-gold { background-color: #d4af37; }
    .border-gold { border-color: #d4af37; }
    .font-heading { font-weight: 700; letter-spacing: -0.02em; }
    
    /* Animations */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-up { animation: fadeUp 0.8s ease-out forwards; }
    .delay-100 { animation-delay: 0.1s; }
    .delay-200 { animation-delay: 0.2s; }
    .delay-300 { animation-delay: 0.3s; }
    
    /* Background Pattern */
    .bg-pattern {
        background-color: #ffffff;
        background-image: url('<?php echo BASE_URL; ?>/assets/images/bg-pattern.png');
        background-repeat: repeat;
        background-size: 400px; /* Adjust size as needed */
    }

    /* Custom Animations for Stamp */
    @keyframes spin-slow {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .animate-spin-slow { animation: spin-slow 10s linear infinite; }
    
    @keyframes pulse-slow {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.9; transform: scale(1.05); }
    }
    .animate-pulse-slow { animation: pulse-slow 4s ease-in-out infinite; }
</style>

<!-- Hero Section -->
<div class="relative w-full h-screen flex items-end bg-cover bg-center group" 
     style="background-image: url('<?php echo htmlspecialchars($heroImage); ?>');">
    
    <!-- Gradient Overlay -->
    <div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-black/40"></div>

    <!-- Main Content (Bottom Left) -->
    <div class="absolute bottom-32 md:bottom-28 left-0 w-full z-20 container mx-auto px-6 pb-6">
        <h1 class="text-3xl md:text-5xl lg:text-6xl text-white font-heading mb-3 leading-tight md:leading-none drop-shadow-2xl opacity-0 animate-fade-up delay-100 mt-12 md:mt-0">
            <?php echo htmlspecialchars($property['title']); ?>
        </h1>
        <p class="text-base md:text-xl text-white/90 font-medium drop-shadow-md flex items-center gap-2 opacity-0 animate-fade-up delay-200">
            <i class="fa-solid fa-location-dot text-gold text-lg"></i> <?php echo htmlspecialchars($property['location']); ?>
        </p>
    </div>

    <!-- Bottom Info Bar -->
    <div class="absolute bottom-0 left-0 w-full bg-black/90 backdrop-blur-md border-t border-white/10 z-30 opacity-0 animate-fade-up delay-300">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row items-center justify-between py-5 gap-6">
                
                <!-- Info Grid -->
                <div class="flex-1 grid grid-cols-2 md:grid-cols-5 gap-y-6 gap-x-4 md:gap-8 text-sm w-full md:w-auto">
                    <div class="border-r border-white/10 pr-4 md:pr-6 last:border-0">
                        <span class="block text-gold font-bold uppercase text-[9px] md:text-[10px] tracking-[0.2em] mb-1">Price</span>
                        <span class="block text-white font-bold text-base md:text-lg whitespace-nowrap">
                            <?php echo formatPrice($property['price']); ?>
                        </span>
                    </div>

                    <div class="border-r border-white/10 pr-4 md:pr-6 last:border-0">
                        <span class="block text-gold font-bold uppercase text-[9px] md:text-[10px] tracking-[0.2em] mb-1">Sizes</span>
                        <span class="block text-white font-bold text-base md:text-lg whitespace-nowrap"><?php echo htmlspecialchars($property['size_range'] ?? 'N/A'); ?></span>
                    </div>
                    
                    <div class="border-r border-white/10 pr-4 md:pr-6 last:border-0">
                        <span class="block text-gold font-bold uppercase text-[9px] md:text-[10px] tracking-[0.2em] mb-1">Status</span>
                        <span class="block text-white font-bold text-base md:text-lg whitespace-nowrap"><?php echo ucwords(str_replace('_', ' ', $property['status'])); ?></span>
                    </div>

                    <div class="border-r border-white/10 pr-4 md:pr-6 last:border-0">
                        <span class="block text-gold font-bold uppercase text-[9px] md:text-[10px] tracking-[0.2em] mb-1">Config</span>
                        <span class="block text-white font-bold text-sm md:text-base leading-tight"><?php echo htmlspecialchars($property['configurations'] ?? 'N/A'); ?></span>
                    </div>
                    
                    <div class="last:border-0 flex flex-col justify-center min-w-0">
                        <span class="block text-gold font-bold uppercase text-[9px] md:text-[10px] tracking-[0.2em] mb-1">RERA No.</span>
                        <span class="block text-white font-bold text-[10px] md:text-xs bg-white/5 md:bg-white/10 px-2 py-0.5 rounded border border-white/10 w-fit truncate max-w-full" title="<?php echo htmlspecialchars($property['rera_no'] ?? ''); ?>">
                            <?php echo htmlspecialchars($property['rera_no'] ?? 'N/A'); ?>
                        </span>
                    </div>
                </div>

                <!-- CTA Button -->
                <button onclick="openModal('site_visit')" class="bg-gold text-black font-bold py-3.5 px-10 hover:bg-white transition-all duration-300 shadow-xl shadow-gold/20 uppercase text-[10px] md:text-xs tracking-[0.2em] whitespace-nowrap w-full md:w-auto transform hover:-translate-y-1 active:scale-[0.98]">
                    Schedule Site Visit
                </button>
            </div>
        </div>
    </div>

    <!-- AI Report Stamp (Right Corner) -->
    <div class="absolute bottom-40 md:bottom-32 right-4 md:right-20 z-40 transform scale-75 md:scale-100">
        <button onclick="getInvestmentReport(<?php echo $property['id']; ?>)" class="group relative w-20 h-20 md:w-36 md:h-36 bg-white/10 backdrop-blur-lg rounded-full border border-white/20 shadow-2xl flex items-center justify-center cursor-pointer hover:bg-slate-900/90 hover:scale-105 transition-all duration-500 animate-pulse-slow">
             <!-- Rotating Text Ring -->
             <div class="absolute inset-0 rounded-full border border-gold/30 animate-spin-slow"></div>
             
             <div class="text-center p-2">
                 <div class="text-gold text-lg md:text-3xl mb-1"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
                 <div class="text-[7px] md:text-[10px] uppercase tracking-widest text-white font-bold leading-tight">
                     AI<br>Investment<br>Report
                 </div>
             </div>
             
             <!-- Tooltip style badge -->
             <div class="absolute -top-1 -right-1 md:-top-2 md:-right-2 bg-secondary text-white text-[7px] md:text-[10px] font-bold px-2 md:px-3 py-0.5 md:py-1 rounded-full shadow-lg uppercase tracking-wide">
                CHECK
             </div>
        </button>
    </div>
</div>

<!-- Content Container (Cream Theme) -->
<div class="bg-pattern relative z-10 -mt-10 rounded-t-[40px] overflow-hidden shadow-[0_-10px_40px_rgba(0,0,0,0.2)]">
    
    <!-- About & Lead Form Section -->
    <div class="pt-20 pb-4 relative overflow-hidden">
         <!-- Decorative Background Elements (Simple Grid Only) -->
         <div class="absolute top-0 left-0 w-full h-full bg-grid-slate-100 opacity-40 pointer-events-none"></div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 text-black">
                
                <!-- Left: About Text -->
                <div class="lg:col-span-2">
                    <span class="text-secondary font-bold tracking-[0.2em] uppercase text-[10px] mb-2 block">Project Details</span>
                    <h2 class="text-3xl md:text-4xl font-display font-medium mb-6 text-black tracking-tight">About the Project</h2>
                    <div class="prose prose-stone prose-sm md:prose-lg text-slate-700 font-light leading-relaxed mb-8 relative">
                        <div id="project-description" class="line-clamp-[8] md:line-clamp-none transition-all duration-500">
                            <?php echo nl2br(htmlspecialchars($property['description'])); ?>
                        </div>
                        <button id="description-toggle" class="mt-4 text-secondary font-bold text-xs uppercase tracking-widest flex items-center gap-2 hover:gap-3 transition-all md:hidden">
                            <span>Read More</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                    
                    <!-- Quick Download Button -->
                    <button onclick="openModal('brochure')" class="flex items-center gap-3 text-slate-900 font-bold hover:text-secondary transition group">
                        <span class="w-12 h-12 rounded-full border border-slate-200 flex items-center justify-center group-hover:border-secondary group-hover:bg-secondary group-hover:text-white transition">
                            <i class="fa-solid fa-download"></i>
                        </span>
                        <span>Download Brochure</span>
                    </button>
                    
                    <!-- Amenities (Moved here for better flow or keep separate? Keeping separate as requested, but maybe minimal preview?) -->
                </div>

                <!-- Right: Sticky Lead Form -->
                <div class="lg:col-span-1">
                    <div class="bg-white md:bg-stone-50 border border-stone-100 p-6 md:p-8 rounded-[2rem] sticky top-24 shadow-xl md:shadow-lg shadow-gray-200/50">
                        <!-- Listed By Info -->
                        <div class="flex items-center gap-4 mb-6 pb-6 md:mb-8 md:pb-8 border-b border-stone-100 md:border-stone-200">
                              <div class="w-14 h-14 md:w-16 md:h-16 rounded-full overflow-hidden border-2 border-white shadow-sm bg-white flex items-center justify-center flex-shrink-0">
                                  <?php 
                                      $sImg = get_seller_logo($property);
                                      $pType = !empty($property['ad_broker_type']) ? $property['ad_broker_type'] : ($property['seller_type'] ?? 'Broker');
                                      $displayName = !empty($property['ad_broker_name']) ? $property['ad_broker_name'] : $property['broker_name'];
                                  ?>
                                  <?php if($sImg): ?>
                                      <img src="<?php echo htmlspecialchars($sImg); ?>" class="w-full h-full object-cover">
                                  <?php else: ?>
                                      <i class="fa-solid fa-user-tie text-2xl text-slate-300"></i>
                                  <?php endif; ?>
                             </div>
                             <div class="flex-1">
                                  <h4 class="font-bold text-slate-900 text-sm md:text-base leading-tight mb-1"><?php echo htmlspecialchars($displayName); ?></h4>
                                  <span class="text-[9px] md:text-xs font-bold uppercase tracking-wider text-secondary bg-secondary/5 px-2 py-0.5 rounded border border-secondary/10">
                                      <?php echo htmlspecialchars($pType); ?>
                                  </span>
                             </div>
                        </div>

                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Inquiry</span>
                        <h3 class="text-2xl font-bold font-display mb-2 text-slate-800">Interested?</h3>
                        <p class="text-slate-500 text-sm mb-6">Request a callback from our experts.</p>
                        
                        <form method="POST" action="api/submit_lead.php" class="space-y-4">  
                            <input type="hidden" name="type" value="Sidebar Inquiry">
                            <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                             <div>
                                 <input type="text" name="name" placeholder="Name" class="w-full bg-white border border-stone-200 rounded-lg px-4 py-3 text-slate-800 placeholder-slate-400 focus:border-secondary focus:ring-1 focus:ring-secondary outline-none transition" required>
                             </div>
                             
                             <div class="flex gap-2">
                                <span class="bg-stone-100 border border-stone-200 text-slate-500 px-3 py-3 rounded-lg font-medium text-sm flex items-center">+91</span>
                                <input type="text" name="phone" placeholder="Phone Number" class="flex-1 bg-white border border-stone-200 rounded-lg px-4 py-3 text-slate-800 placeholder-slate-400 focus:border-secondary focus:ring-1 focus:ring-secondary outline-none transition" required>
                             </div>
                             
                             <div>
                                 <input type="email" name="email" placeholder="Email Address" class="w-full bg-white border border-stone-200 rounded-lg px-4 py-3 text-slate-800 placeholder-slate-400 focus:border-secondary focus:ring-1 focus:ring-secondary outline-none transition" required>
                             </div>

                             <div class="flex items-start gap-2 pt-1">
                                <input type="checkbox" id="side_disclaimer" checked class="mt-0.5 accent-secondary w-3 h-3 pointer-events-none">
                                <label for="side_disclaimer" class="text-[9px] text-slate-400 leading-tight">
                                    I authorize Gurgaon Property Finder to contact me.
                                </label>
                            </div>

                             <div class="pt-2">
                                 <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3.5 rounded-lg hover:bg-secondary hover:text-white transition shadow-lg shadow-slate-900/10 uppercase tracking-wide text-xs">
                                     Get a Callback
                                 </button>
                             </div>
                             <p class="text-[10px] text-slate-400 text-center mt-4 opacity-70">Strictly confidential.</p>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Section Divider -->
    <div class="flex items-center justify-center py-8 bg-white">
        <div class="w-32 h-px bg-gradient-to-r from-transparent via-stone-300 to-transparent"></div>
    </div>

    <!-- Highlights Section (Stone/Gold Theme) -->
    <?php if(!empty($highlights)): ?>
    <div class="pt-4 pb-16 bg-white">
        <div class="container mx-auto px-6">
            <div class="bg-stone-50 border border-stone-100 rounded-[40px] p-8 md:p-12 shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                    <div>
                        <h3 class="text-3xl font-bold font-display text-slate-800 mb-8">Project Highlights</h3>
                        <ul class="space-y-4">
                            <?php foreach($highlights as $point): if(trim($point) == '') continue; ?>
                            <li class="flex items-start gap-4 text-slate-700 leading-relaxed group">
                                <span class="w-2 h-2 rounded-full bg-secondary mt-2.5 shrink-0 group-hover:scale-125 transition"></span>
                                <span class="flex-1 font-medium"><?php echo htmlspecialchars(trim($point)); ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <button onclick="openModal('enquiry')" class="mt-10 bg-secondary text-white px-10 py-3.5 rounded-full hover:bg-slate-900 transition font-bold shadow-lg shadow-yellow-500/20 text-sm uppercase tracking-wider">
                            Download Brochure
                        </button>
                    </div>
                    <div class="h-full min-h-[400px]">
                         <?php 
                            $hImg = !empty($property['highlights_image']) ? $property['highlights_image'] : '';
                            if (!empty($hImg) && strpos($hImg, 'http') === false) $hImg = BASE_URL . '/' . ltrim($hImg, '/');
                         ?>
                         <img src="<?php echo htmlspecialchars($hImg); ?>" class="w-full h-full object-cover rounded-[30px] shadow-lg">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Section Divider -->
    <div class="flex items-center justify-center py-8 bg-white">
         <div class="w-24 h-px bg-stone-200"></div>
         <div class="mx-4 text-secondary"><i class="fa-solid fa-key text-xs opacity-50"></i></div>
         <div class="w-24 h-px bg-stone-200"></div>
    </div>

    <!-- Location Section (Stone/Gold Theme) -->
    <?php if(!empty($locationPoints)): ?>
    <div class="py-16 bg-white">
        <div class="container mx-auto px-6">
            <div class="bg-slate-900 rounded-[40px] p-8 md:p-12 shadow-xl text-white relative overflow-hidden">
                <!-- Decorative Circle -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-secondary/10 rounded-full blur-3xl translate-x-1/2 -translate-y-1/2"></div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center relative z-10">
                     <div class="order-2 md:order-1 h-full min-h-[300px] md:min-h-[400px]">
                         <?php 
                            $lImg = !empty($property['location_advantages_image']) ? $property['location_advantages_image'] : '';
                            if (!empty($lImg) && strpos($lImg, 'http') === false) $lImg = BASE_URL . '/' . ltrim($lImg, '/');
                         ?>
                         <img src="<?php echo htmlspecialchars($lImg); ?>" class="w-full h-full object-cover rounded-[30px] shadow-2xl border border-white/10">
                     </div>
                     <div class="order-1 md:order-2">
                        <span class="text-secondary font-bold tracking-widest uppercase text-xs mb-2 block">Connectivity</span>
                        <h3 class="text-3xl font-bold font-display text-white mb-8">Location Advantages</h3>
                        <ul class="space-y-4">
                            <?php foreach($locationPoints as $point): if(trim($point) == '') continue; ?>
                            <li class="flex items-start gap-3.5 text-gray-300 leading-relaxed">
                                <i class="fa-solid fa-location-dot text-secondary mt-1"></i>
                                <span class="flex-1 font-medium"><?php echo htmlspecialchars(trim($point)); ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <button onclick="openModal('enquiry')" class="mt-10 bg-white text-slate-900 px-10 py-3.5 rounded-full hover:bg-secondary hover:text-white transition font-bold shadow-lg">
                            View Location Map
                        </button>
                     </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Section Divider -->
    <div class="flex items-center justify-center py-8">
        <div class="w-32 h-px bg-gradient-to-r from-transparent via-stone-300 to-transparent"></div>
    </div>

    <!-- Amenities Section -->
    <div class="py-20">
        <div class="container mx-auto px-6">
            <div class="bg-gradient-to-br from-white to-amber-50 border border-amber-100/50 rounded-[40px] p-8 md:p-12 shadow-sm relative overflow-hidden">
                <!-- Decorative Corner -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-amber-100/20 rounded-full blur-3xl -mr-32 -mt-32 pointer-events-none"></div>
                <div class="text-center mb-16">
                     <h2 class="text-4xl font-bold font-display text-slate-900 mb-4">World-Class Amenities</h2>
                     <p class="text-slate-500 max-w-2xl mx-auto">Experience a life of luxury with handpicked amenities designed for your comfort.</p>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 md:gap-8 justify-items-center">
                    <?php foreach ($amenities as $am): ?>
                        <div class="bg-white p-4 md:p-6 rounded-2xl text-center w-full hover:-translate-y-1 transition-all duration-300 border border-stone-200 hover:border-stone-300 hover:shadow-md group">
                            <div class="w-12 h-12 md:w-16 md:h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3 md:mb-4 text-slate-500 group-hover:text-black group-hover:bg-amber-50 transition-colors duration-300">
                                <?php if($am['icon']): ?><i class="fa-solid <?php echo $am['icon']; ?> text-xl md:text-2xl"></i><?php else: ?><i class="fa-solid fa-check text-xl md:text-2xl"></i><?php endif; ?>
                            </div>
                            <h4 class="font-semibold text-black text-[11px] md:text-sm tracking-wide line-clamp-1"><?php echo htmlspecialchars($am['name']); ?></h4>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Floor Plans Section (Dynamic) -->
    <?php if(!empty($floorPlans)): ?>
    <div class="py-16">
        <div class="container mx-auto px-6">
            <!-- Wrapped Container like Amenities -->
            <!-- Wrapped Container (Dark Slate Theme) -->
            <div class="bg-slate-900 border border-slate-800 rounded-[40px] p-8 md:p-12 shadow-xl relative overflow-hidden">
                 <!-- Pattern Overlay -->
                 <div class="absolute inset-0 bg-dots-pattern opacity-5 pointer-events-none"></div>
                 
                 <div class="relative z-10">
                     <div class="text-center mb-12">
                         <h2 class="text-3xl font-bold font-display text-white mb-4">Floor Plans</h2>
                         <p class="text-slate-400 max-w-2xl mx-auto">Layouts available for this property.</p>
                     </div>
                     
                     <div class="flex flex-wrap justify-center gap-8">
                         <?php foreach($floorPlans as $fp): 
                            $hasImage = !empty(trim($fp['image_path'] ?? ''));
                            $fpImg = $hasImage ? trim($fp['image_path']) : '';
                            if ($hasImage && strpos($fpImg, 'http') === false) $fpImg = BASE_URL . '/' . ltrim($fpImg, '/');
                         ?>
                         <div class="w-full md:w-72 bg-white p-4 rounded-[30px] border border-stone-200 shadow-sm hover:shadow-md transition-all duration-300 group">
                             <div class="h-48 bg-slate-200 rounded-2xl overflow-hidden relative flex items-center justify-center border border-slate-300">
                                 <?php if($hasImage): ?>
                                     <img src="<?php echo htmlspecialchars($fpImg); ?>" 
                                          class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                          onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                     >
                                     <!-- Fallback if Image Fails -->
                                     <div class="absolute inset-0 hidden flex-col items-center justify-center text-center p-6 bg-slate-200">
                                        <div class="w-16 h-16 bg-white text-secondary rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg">
                                            <i class="fa-solid fa-ruler-combined text-2xl"></i>
                                        </div>
                                        <span class="text-slate-500 text-xs font-bold uppercase tracking-widest">Preview</span>
                                     </div>
        
                                     <!-- View Button Overlay -->
                                      <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/40 backdrop-blur-sm">
                                          <button onclick="openModal('enquiry')" class="bg-white text-slate-900 px-6 py-2 rounded-full font-bold text-xs shadow-xl hover:bg-secondary hover:text-white transition uppercase tracking-wider">View</button>
                                      </div>
                                 <?php else: ?>
                                     <!-- Default Icon/Vector (No Image Set) -->
                                     <div class="text-center p-6 group-hover:scale-110 transition duration-500">
                                        <div class="w-16 h-16 bg-white text-secondary rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg">
                                            <i class="fa-solid fa-ruler-combined text-2xl"></i>
                                        </div>
                                        <span class="text-slate-500 text-xs font-bold uppercase tracking-widest">Layout</span>
                                     </div>
                                 <?php endif; ?>
                             </div>
                             <div class="mt-4 text-center">
                                 <h3 class="text-lg font-bold text-slate-800"><?php echo htmlspecialchars($fp['title']); ?></h3>
                                 <?php if(!empty($fp['size_sqft'])): ?>
                                 <p class="text-slate-500 text-xs mt-1 font-medium bg-slate-50 py-1 px-3 rounded-full inline-block border border-stone-100"><?php echo htmlspecialchars($fp['size_sqft']); ?> Sq. Ft.</p>
                                 <?php endif; ?>
                             </div>
                         </div>
                         <?php endforeach; ?>
                     </div>
                 </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div> <!-- End Content Container -->

<!-- Gallery (Dark) -->
<div class="py-16 bg-[#0a0a0a] border-t border-white/5">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <div class="container mx-auto px-6 relative group">
         <div class="flex items-end justify-between mb-8">
            <h2 class="text-3xl text-white font-heading">Property Gallery</h2>
            <!-- Navigation Buttons -->
            <div class="flex gap-2">
                <button class="swiper-button-prev-custom w-12 h-12 rounded-full border border-white/20 text-white flex items-center justify-center hover:bg-white hover:text-black transition duration-300">
                    <i class="fa-solid fa-arrow-left"></i>
                </button>
                <button class="swiper-button-next-custom w-12 h-12 rounded-full border border-white/20 text-white flex items-center justify-center hover:bg-white hover:text-black transition duration-300">
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
         </div>

         <?php if (count($galleryImages) > 0): ?>
            <div class="swiper gallery-slider overflow-hidden rounded-[30px]">
                <div class="swiper-wrapper">
                    <?php foreach($galleryImages as $i => $img): 
                         $imgSrc = $img['image_path'];
                         if (strpos($imgSrc, 'http') === false) $imgSrc = BASE_URL . '/' . ltrim($imgSrc, '/');
                    ?>
                    <div class="swiper-slide h-[500px] w-full relative group/slide">
                        <img src="<?php echo htmlspecialchars($imgSrc); ?>" class="w-full h-full object-cover transition duration-700 group-hover/slide:scale-110">
                        <div class="absolute inset-0 bg-black/10 group-hover/slide:bg-transparent transition duration-500"></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
         <?php else: ?>
            <p class="text-gray-400">No additional images available in gallery.</p>
         <?php endif; ?>
    </div>
</div>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    const swiper = new Swiper('.gallery-slider', {
        loop: true,
        autoplay: {
            delay: 4000,
            disableOnInteraction: false,
        },
        speed: 800,
        spaceBetween: 20,
        slidesPerView: 1,
        breakpoints: {
            640: {
                slidesPerView: 2,
            },
            1024: {
                slidesPerView: 3, // Shows part of next slide
            },
        },
        navigation: {
            nextEl: '.swiper-button-next-custom',
            prevEl: '.swiper-button-prev-custom',
        },
    });
</script>

<!-- Disclaimer (Dark) -->
<div class="py-8 bg-black border-t border-white/5 text-center">
    <div class="container mx-auto px-6 max-w-4xl">
         <p class="text-[10px] text-gray-600 uppercase tracking-widest">
            Disclaimer: Images are for representation purposes only. Prices subject to change.
         </p>
    </div>
</div>

<!-- Modal (Dark Theme) -->
<!-- Modal (Soft Theme) -->
<div id="contactModal" class="fixed inset-0 bg-black/60 hidden z-[100] items-center justify-center p-4 backdrop-blur-md">
    <div class="bg-white w-full max-w-4xl rounded-[30px] overflow-hidden flex shadow-2xl relative animate-fade-up">
        <button onclick="closeModal()" class="absolute top-4 right-4 z-20 w-10 h-10 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center text-slate-800 hover:bg-white transition shadow-sm"><i class="fa-solid fa-times"></i></button>
        
        <!-- Left: Image -->
        <div class="hidden md:block w-5/12 relative">
             <?php 
                $mImg = !empty($property['images'][0]['image_path']) ? $property['images'][0]['image_path'] : 'assets/images/hero-bg.jpg';
                if (!empty($mImg) && strpos($mImg, 'http') === false) $mImg = BASE_URL . '/' . ltrim($mImg, '/');
             ?>
             <img src="<?php echo htmlspecialchars($mImg); ?>" class="w-full h-full object-cover">
             <div class="absolute inset-0 bg-black/20"></div>
             <div class="absolute bottom-8 left-8 text-white p-4">
                 <h4 class="font-display text-2xl mb-1"><?php echo htmlspecialchars($property['title']); ?></h4>
                 <p class="text-white/80 text-sm"><i class="fa-solid fa-location-dot mr-1"></i> <?php echo htmlspecialchars($property['location']); ?></p>
             </div>
        </div>

        <!-- Right: Form -->
        <div class="w-full md:w-7/12 p-8 md:p-12 bg-stone-50">
            <h3 class="text-3xl font-display font-medium text-slate-900 mb-2">Get in Touch</h3>
            <p class="text-slate-500 text-sm mb-8">Register your interest and download the brochure.</p>
            
            <form method="POST" action="api/submit_lead.php" class="space-y-5">
                <input type="hidden" name="type" id="leadType">
                <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                
                <div>
                     <input type="text" name="name" class="w-full bg-white border border-stone-200 rounded-xl px-5 py-3.5 text-slate-800 placeholder-slate-400 focus:border-secondary focus:ring-1 focus:ring-secondary outline-none transition" placeholder="Name" required value="<?php echo $_SESSION['user_name'] ?? ''; ?>">
                </div>
                <div>
                     <input type="email" name="email" class="w-full bg-white border border-stone-200 rounded-xl px-5 py-3.5 text-slate-800 placeholder-slate-400 focus:border-secondary focus:ring-1 focus:ring-secondary outline-none transition" placeholder="Email" required value="<?php echo $_SESSION['user_email'] ?? ''; ?>">
                </div>
                
                <div class="flex items-center gap-3">
                     <span class="bg-white border border-stone-200 text-slate-500 px-4 py-3.5 rounded-xl font-medium">+91</span>
                     <input type="text" name="phone" class="w-full bg-white border border-stone-200 rounded-xl px-5 py-3.5 text-slate-800 placeholder-slate-400 focus:border-secondary focus:ring-1 focus:ring-secondary outline-none transition" placeholder="Phone Number" required>
                </div>
                
                <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">

                <div class="flex items-start gap-3 mt-2">
                    <input type="checkbox" id="disclaimer" name="disclaimer" checked class="mt-1 accent-secondary pointer-events-none">
                    <label for="disclaimer" class="text-[10px] text-slate-500 leading-tight">
                        I authorize Gurgaon Property Finder to contact me via Call, SMS, WhatsApp or Email. This will override the registry on DND / NDNC.
                    </label>
                </div>
                
                <button type="submit" class="w-full bg-slate-900 text-white font-bold py-4 rounded-xl hover:bg-secondary hover:text-white transition shadow-xl shadow-slate-900/10 mt-4 tracking-wide uppercase text-sm">
                    Submit Enquiry
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function openModal(type) {
    document.getElementById('contactModal').style.display = 'flex';
    document.getElementById('leadType').value = type;
}
function closeModal() {
    document.getElementById('contactModal').style.display = 'none';
}
window.onclick = function(event) {
    if (event.target == document.getElementById('contactModal')) {
        closeModal();
    }
    if (event.target == document.getElementById('reportModal')) {
        closeReportModal();
    }
}

// Description Toggle
document.getElementById('description-toggle')?.addEventListener('click', function() {
    const desc = document.getElementById('project-description');
    const isExpanded = !desc.classList.contains('line-clamp-[8]');
    
    if (isExpanded) {
        desc.classList.add('line-clamp-[8]');
        this.querySelector('span').textContent = 'Read More';
        this.querySelector('i').className = 'fa-solid fa-arrow-right';
    } else {
        desc.classList.remove('line-clamp-[8]');
        this.querySelector('span').textContent = 'Show Less';
        this.querySelector('i').className = 'fa-solid fa-arrow-up';
    }
});


// Investment Report Logic
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

<!-- Related Projects -->
<?php if (!empty($relatedProperties)): ?>
<div class="py-20 bg-stone-50 border-t border-stone-200">
    <div class="container mx-auto px-6">
        <div class="text-center mb-12">
             <span class="text-secondary font-bold tracking-widest uppercase text-xs">Similar Options</span>
             <h2 class="text-3xl font-bold font-display text-slate-800 mt-2">Related Projects</h2>
        </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
            <?php foreach ($relatedProperties as $rp): 
                $bgImg = get_property_cover($rp['id'], $pdo);
                $sImg = get_seller_logo($rp);
            ?>
            <a href="property-details.php?slug=<?php echo htmlspecialchars($rp['slug'] ?? ''); ?>&id=<?php echo $rp['id']; ?>" class="group flex flex-col bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-stone-100 h-full mb-6 md:mb-0">
                <div class="relative h-64 shrink-0 overflow-hidden">
                    <img src="<?php echo htmlspecialchars($bgImg); ?>" alt="<?php echo htmlspecialchars($rp['title']); ?>" class="w-full h-full object-cover transition duration-700 group-hover:scale-110">
                    <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-lg text-xs font-bold text-slate-900 shadow-sm">
                        <?php echo ucwords(str_replace('_', ' ', $rp['status'])); ?>
                    </div>
                </div>
                <div class="p-6 flex-1 flex flex-col">
                    <h3 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-secondary transition line-clamp-1"><?php echo htmlspecialchars($rp['title']); ?></h3>
                    <p class="text-slate-500 text-sm mb-4"><i class="fa-solid fa-location-dot mr-1"></i> <?php echo htmlspecialchars($rp['location']); ?></p>
                    
                    <div class="flex items-center gap-3 mt-auto mb-4">
                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center overflow-hidden border border-slate-200 shrink-0">
                             <?php if (!empty($sImg)): ?>
                                 <img src="<?php echo htmlspecialchars($sImg); ?>" class="w-full h-full object-cover">
                             <?php else: ?>
                                 <i class="fa-solid fa-user text-slate-400 text-xs"></i>
                             <?php endif; ?>
                        </div>
                        <div>
                             <h4 class="text-xs font-bold text-slate-800 leading-tight"><?php echo htmlspecialchars($rp['ad_broker_name'] ?: ($rp['broker_name'] ?: 'Seller')); ?></h4>
                             <span class="text-[10px] uppercase font-bold text-secondary tracking-wider block"><?php echo htmlspecialchars($rp['ad_broker_type'] ?: ($rp['seller_type'] ?: 'Broker')); ?></span>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between pt-4 border-t border-slate-50">
                        <div>
                            <span class="block text-xs text-gray-400 font-bold uppercase tracking-wider">Price</span>
                            <span class="font-bold text-slate-800">
                                <?php echo formatPrice($rp['price']); ?><span class="text-[10px] text-slate-400 font-normal ml-0.5">onwards</span>
                            </span>
                        </div>
                        <div class="text-right">
                             <div class="text-[10px] font-bold text-slate-500 group-hover:text-secondary uppercase tracking-wider transition">
                                 Details <i class="fa-solid fa-arrow-right ml-1"></i>
                             </div>
                        </div>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
