<?php
require_once __DIR__ . '/includes/header.php';
?>
<?php
// Fetch Active Banners
$banners = $pdo->query("SELECT * FROM ad_banners WHERE is_active = 1 ORDER BY created_at DESC")->fetchAll();
?>

<!-- Hero Section -->
<section class="relative min-h-screen flex items-center justify-center bg-slate-900 overflow-hidden">
    <div class="absolute inset-0 z-0">
        <img src="assets/images/hero-bg.jpg" alt="Luxury Home" class="w-full h-full object-cover opacity-40">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/50 to-transparent"></div>
    </div>
    
    <div class="relative z-10 container mx-auto px-6 text-center text-white" data-aos="fade-up">
        <h1 class="text-3xl md:text-6xl font-bold mb-6 font-display leading-[1.1] md:leading-tight px-4 md:px-0 mt-8 md:mt-0">
            Gurugram's Only <span class="text-secondary">Zero-Spam</span> Real Estate Platform.
        </h1>
        <p class="text-sm md:text-lg mb-10 text-slate-300 max-w-xl mx-auto font-light leading-relaxed px-6 md:px-0">
            Stop browsing fake listings. Start finding verified homes with AI-backed legal & investment checks.
        </p>

        <div class="bg-white/5 backdrop-blur-md p-2 md:p-3 rounded-2xl max-w-3xl mx-auto border border-white/10 shadow-2xl">
            <form action="properties.php" method="GET" class="flex flex-col md:flex-row gap-2 md:gap-3">
                <div class="flex-1 relative">
                    <i class="fa-solid fa-location-dot absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="location" placeholder="Location" class="w-full bg-slate-800/40 text-white py-3 md:py-2.5 pl-10 pr-4 rounded-xl focus:outline-none focus:ring-1 focus:ring-secondary border border-white/5 placeholder-slate-500 text-sm transition-all">
                </div>
                
                <div class="w-full md:w-48 relative">
                    <i class="fa-solid fa-house absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <select name="type" class="w-full bg-slate-800/40 text-white py-3 md:py-2.5 pl-10 pr-8 rounded-xl appearance-none focus:outline-none focus:ring-1 focus:ring-secondary cursor-pointer border border-white/5 text-sm transition-all">
                        <option value="" class="bg-slate-900">Any Type</option>
                        <option value="apartment" class="bg-slate-900">Apartment</option>
                        <option value="house" class="bg-slate-900">House</option>
                        <option value="plot" class="bg-slate-900">Plot</option>
                        <option value="commercial" class="bg-slate-900">Commercial</option>
                    </select>
                    <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-[9px] pointer-events-none"></i>
                </div>
                
                <button type="submit" class="bg-secondary text-white px-8 py-3 md:py-2.5 rounded-xl hover:bg-yellow-600 transition font-bold text-sm flex items-center justify-center gap-2 shadow-xl shadow-yellow-500/20 active:scale-95">
                    Search
                </button>
            </form>
        </div>

        <div class="mt-20 grid grid-cols-1 md:flex md:flex-row justify-center gap-4 md:gap-8 lg:gap-12 animate-fade-in-up delay-200">
            <!-- USP 1 -->
            <div class="flex items-center gap-4 bg-white/5 backdrop-blur-sm px-6 py-4 rounded-2xl border border-white/10 hover:bg-white/10 transition-all group">
                <div class="w-10 h-10 rounded-full bg-secondary/10 flex items-center justify-center text-lg shadow-inner">🛡️</div>
                <div class="text-left">
                    <p class="text-white text-[10px] md:text-xs font-bold uppercase tracking-widest mb-0.5">Your Number is Safe</p>
                    <p class="text-slate-400 text-[10px] font-medium opacity-80">We never sell your data.</p>
                </div>
            </div>

            <!-- USP 2 -->
             <div class="flex items-center gap-4 bg-white/5 backdrop-blur-sm px-6 py-4 rounded-2xl border border-white/10 hover:bg-white/10 transition-all group">
                <div class="w-10 h-10 rounded-full bg-secondary/10 flex items-center justify-center text-lg shadow-inner">🤖</div>
                <div class="text-left">
                    <p class="text-white text-[10px] md:text-xs font-bold uppercase tracking-widest mb-0.5">AI Legal Check</p>
                    <p class="text-slate-400 text-[10px] font-medium opacity-80">Builder track record verified.</p>
                </div>
            </div>

            <!-- USP 3 -->
             <div class="flex items-center gap-4 bg-white/5 backdrop-blur-sm px-6 py-4 rounded-2xl border border-white/10 hover:bg-white/10 transition-all group">
                <div class="w-10 h-10 rounded-full bg-secondary/10 flex items-center justify-center text-lg shadow-inner">📈</div>
                <div class="text-left">
                    <p class="text-white text-[10px] md:text-xs font-bold uppercase tracking-widest mb-0.5">Investment Data</p>
                    <p class="text-slate-400 text-[10px] font-medium opacity-80">Yield and appreciation forecasts.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sponsored Banners Slider -->
<?php if (count($banners) > 0): ?>
<section class="py-12 bg-slate-50 border-b border-slate-200">
    <div class="container mx-auto px-6">
        <div class="swiper bannerSwiper rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
            <div class="swiper-wrapper">
                <?php foreach ($banners as $banner): ?>
                <div class="swiper-slide cursor-pointer group" onclick="handleBannerClick(<?php echo htmlspecialchars(json_encode($banner)); ?>)">
                    <div class="relative w-full h-48 md:h-64 lg:h-96 bg-white overflow-hidden">
                        <img src="<?php echo htmlspecialchars(BASE_URL . '/' . $banner['image_path']); ?>" alt="<?php echo htmlspecialchars($banner['title']); ?>" class="w-full h-full object-cover transition duration-700 group-hover:scale-105">
                        
                        <!-- Hover Overlay with Button -->
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-all duration-500 flex items-center justify-center backdrop-blur-[2px]">
                            <button class="bg-white text-slate-900 px-8 py-3.5 rounded-full font-bold shadow-2xl transform translate-y-4 group-hover:translate-y-0 transition-all duration-500 flex items-center gap-2 hover:bg-secondary hover:text-white">
                                Know More
                                <i class="fa-solid fa-arrow-right text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <!-- Pagination -->
            <div class="swiper-pagination"></div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- What We Do / Services -->
<section class="py-24 bg-slate-950 relative overflow-hidden">
    <!-- Subtle Dark Grid -->
    <div class="absolute inset-0 z-0 opacity-[0.05] pointer-events-none">
        <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="darkGridPattern" width="50" height="50" patternUnits="userSpaceOnUse">
                    <path d="M 50 0 L 0 0 0 50" fill="none" stroke="white" stroke-width="0.5"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#darkGridPattern)" />
        </svg>
    </div>

    <div class="container mx-auto px-6 relative z-10">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6" data-aos="fade-up">
            <div class="max-w-xl">
                <h2 class="text-4xl font-bold text-white font-display mb-4">Our Value Proposition</h2>
                <div class="w-12 h-1 bg-secondary rounded-full mb-6"></div>
                <p class="text-slate-400 leading-relaxed text-sm">
                    Gurugram’s most trusted zero-spam ecosystem. We don't just list properties; we provide a high-integrity bridge between serious seekers and verified owners.
                </p>
            </div>
            <a href="about-us.php" class="text-secondary text-xs font-bold uppercase tracking-widest hover:text-white transition-colors flex items-center gap-2 group">
                Learn our story <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Card 1 -->
            <div class="bg-slate-900/50 backdrop-blur-sm border border-slate-800 p-8 rounded-2xl hover:border-secondary/50 hover:bg-slate-900 transition-all duration-300 group" data-aos="fade-up" data-aos-delay="100">
                <div class="text-secondary text-2xl mb-6">
                    <i class="fa-solid fa-house-chimney-user"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-3 font-display">Buy Properties</h3>
                <p class="text-slate-500 text-sm leading-relaxed">
                    Access premium listings with AI legal checks. We ensure your investment is safe and verified.
                </p>
            </div>

            <!-- Card 2 -->
            <div class="bg-slate-900/50 backdrop-blur-sm border border-slate-800 p-8 rounded-2xl hover:border-secondary/50 hover:bg-slate-900 transition-all duration-300 group" data-aos="fade-up" data-aos-delay="200">
                <div class="text-secondary text-2xl mb-6">
                    <i class="fa-solid fa-handshake"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-3 font-display">List Your Property</h3>
                <p class="text-slate-500 text-sm leading-relaxed">
                    Showcase your property to Gurugram's largest network of verified tenants and buyers. Maximum reach, zero friction.
                </p>
            </div>

            <!-- Card 3 -->
            <div class="bg-slate-900/50 backdrop-blur-sm border border-slate-800 p-8 rounded-2xl hover:border-secondary/50 hover:bg-slate-900 transition-all duration-300 group" data-aos="fade-up" data-aos-delay="300">
                <div class="text-secondary text-2xl mb-6">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-3 font-display">Investment Data</h3>
                <p class="text-slate-500 text-sm leading-relaxed">
                    AI-powered rental yield forecasts and appreciation reports to maximize your real estate ROI.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Benefits / Why Choose Us -->
<section class="py-20 bg-slate-900 text-white relative overflow-hidden">
    <!-- Decor -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-secondary/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2"></div>

    <div class="container mx-auto px-6 relative z-10">
        <div class="flex flex-col lg:flex-row items-center gap-16">
            <div class="lg:w-1/2" data-aos="fade-right">
                <span class="text-secondary font-bold tracking-widest uppercase text-xs">Why Choose Us</span>
                <h2 class="text-3xl md:text-5xl font-bold mt-2 font-display leading-tight">
                    Experience the <span class="text-secondary">Next Level</span> of Real Estate
                </h2>
                <p class="text-slate-400 mt-6 text-lg leading-relaxed">
                    We are Gurgaon's premier property listing platform, dedicated to transparency and trust. Instead of selling directly, we empower you with data-driven insights to find the perfect property among verified listings.
                </p>
                
                <div class="mt-8 space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-secondary/20 flex items-center justify-center text-secondary shrink-0 mt-1">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold mb-1">100% Verified Listings</h4>
                            <p class="text-slate-500 text-sm">Every property is physically verified by our experts.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-secondary/20 flex items-center justify-center text-secondary shrink-0 mt-1">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold mb-1">Safe & Secure Transactions</h4>
                            <p class="text-slate-500 text-sm">We ensure complete legal transparency and documentation.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-secondary/20 flex items-center justify-center text-secondary shrink-0 mt-1">
                            <i class="fa-solid fa-headset"></i>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold mb-1">Dedicated Support</h4>
                            <p class="text-slate-500 text-sm">Relationship managers assigned to you for a seamless journey.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="lg:w-1/2" data-aos="fade-left">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-4 translate-y-8">
                        <img src="assets/images/gurgaon_skyline.png" class="rounded-2xl shadow-2xl w-full h-64 object-cover">
                        <img src="assets/images/gurgaon_residential.png" class="rounded-2xl shadow-2xl w-full h-48 object-cover">
                    </div>
                    <div class="space-y-4">
                        <img src="assets/images/gurgaon_commercial.png" class="rounded-2xl shadow-2xl w-full h-48 object-cover">
                        <img src="assets/images/gurgaon_skyline.png" class="rounded-2xl shadow-2xl w-full h-64 object-cover">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Properties -->
<section class="py-20 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 relative overflow-hidden">
    <!-- Luxury Background Pattern -->
    <div class="absolute inset-0 opacity-5">
        <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, gold 1px, transparent 0); background-size: 40px 40px;"></div>
    </div>
    
    <!-- Accent Glow Effects -->
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-secondary/10 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-secondary/5 rounded-full blur-3xl"></div>
    <div class="container mx-auto px-6">
        <?php
        try {
            // Fetch Featured
            $stmt = $pdo->query("SELECT p.*, u.name as broker_name, u.seller_type, u.profile_image 
                                 FROM properties p 
                                 LEFT JOIN users u ON p.broker_id = u.id 
                                 WHERE p.is_approved = 1 AND p.is_featured = 1 
                                 ORDER BY p.sort_order ASC, p.created_at DESC LIMIT 6");
            $featured = $stmt->fetchAll();

            if (count($featured) > 0): ?>
                <div class="flex justify-between items-end mb-10 relative z-10" data-aos="fade-up">
                    <div>
                        <span class="text-secondary font-bold tracking-widest uppercase text-xs flex items-center gap-2">
                            <i class="fa-solid fa-crown"></i> Gurugram's Finest
                        </span>
                        <h2 class="text-4xl font-bold text-white mt-2 font-display uppercase tracking-tight">Premium Projects</h2>
                        <div class="w-24 h-1 bg-gradient-to-r from-secondary to-transparent mt-3"></div>
                    </div>
                    <a href="properties.php?featured=1" class="hidden md:flex items-center gap-2 text-slate-300 hover:text-secondary transition text-sm font-medium group">
                        View All <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform text-xs"></i>
                    </a>
                </div>

                <div class="relative group/swiper" data-aos="fade-up">
                    <!-- Navigation Arrows -->
                    <div class="premium-next swiper-button-next !text-secondary !w-12 !h-12 !bg-slate-800/80 !backdrop-blur-md !rounded-full !border !border-white/10 hover:!bg-secondary hover:!text-white transition-all shadow-2xl !-right-2 md:!-right-6 !z-50 opacity-0 group-hover/swiper:opacity-100 flex items-center justify-center after:!content-none">
                        <i class="fa-solid fa-chevron-right text-xl"></i>
                    </div>
                    <div class="premium-prev swiper-button-prev !text-secondary !w-12 !h-12 !bg-slate-800/80 !backdrop-blur-md !rounded-full !border !border-white/10 hover:!bg-secondary hover:!text-white transition-all shadow-2xl !-left-2 md:!-left-6 !z-50 opacity-0 group-hover/swiper:opacity-100 flex items-center justify-center after:!content-none">
                        <i class="fa-solid fa-chevron-left text-xl"></i>
                    </div>

                    <div class="swiper premiumSwiper pb-16">
                        <div class="swiper-wrapper">
                            <?php foreach ($featured as $prop): 
                                $imagePath = get_property_cover($prop['id'], $pdo);
                                $sImg = get_seller_logo($prop);
                            ?>
                                <div class="swiper-slide h-auto">
                                    <div class="bg-white rounded-2xl overflow-hidden border-2 border-secondary/20 shadow-xl shadow-secondary/5 hover:shadow-secondary/20 transition-all duration-500 group relative flex flex-col h-full bg-gradient-to-b from-white to-secondary/[0.02]">
                                        <!-- Premium Badge -->
                                        <div class="absolute top-4 left-4 z-30 flex items-center gap-1.5 bg-secondary text-white px-3 py-1 rounded-full text-[9px] font-bold uppercase tracking-widest shadow-lg animate-pulse-slow">
                                            <i class="fa-solid fa-crown"></i> Premium
                                        </div>

                                        <div class="relative overflow-hidden h-56">
                                            <img src="<?php echo htmlspecialchars($imagePath); ?>" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700">
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                            
                                            <div class="absolute top-4 right-4 bg-white/95 backdrop-blur-md px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-slate-800 border border-slate-100 z-10 rounded">
                                                <?php echo str_replace('_', ' ', $prop['status']); ?>
                                            </div>

                                            <div class="absolute bottom-4 right-4 z-20 flex items-center gap-2 group/seller">
                                                <div class="bg-white/95 backdrop-blur-md px-2.5 py-1.5 rounded-full shadow-lg opacity-0 -translate-x-2 group-hover/seller:opacity-100 group-hover/seller:translate-x-0 transition-all duration-300 invisible group-hover/seller:visible border border-secondary/10">
                                                    <p class="text-[9px] font-bold text-slate-800 whitespace-nowrap"><?php echo htmlspecialchars($prop['ad_broker_name'] ?: ($prop['broker_name'] ?: 'Seller')); ?></p>
                                                </div>
                                                <div class="w-9 h-9 rounded-full border-2 border-secondary/30 shadow-xl overflow-hidden bg-white group-hover:border-secondary transition-colors">
                                                    <?php if (!empty($sImg)): ?>
                                                        <img src="<?php echo htmlspecialchars($sImg); ?>" class="w-full h-full object-cover">
                                                    <?php else: ?>
                                                        <div class="w-full h-full flex items-center justify-center bg-slate-50 text-slate-300 text-[10px]">
                                                            <i class="fa-solid fa-user"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="p-6 flex-1 flex flex-col">
                                            <div class="flex-1">
                                                <h3 class="text-xl font-bold font-display text-slate-900 mb-2 line-clamp-1 group-hover:text-secondary transition-colors duration-300">
                                                    <?php echo htmlspecialchars($prop['title']); ?>
                                                </h3>
                                                <div class="flex items-center gap-1.5 text-slate-400 text-xs mb-4 font-medium">
                                                    <i class="fa-solid fa-location-dot text-secondary/60"></i>
                                                    <?php echo htmlspecialchars($prop['location']); ?>
                                                </div>
                                                
                                                <div class="flex items-center gap-3 mb-6 p-3 bg-slate-50 rounded-xl border border-slate-100 group-hover:bg-white group-hover:border-secondary/20 transition-all">
                                                    <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center overflow-hidden border border-slate-100">
                                                         <?php if (!empty($sImg)): ?>
                                                             <img src="<?php echo htmlspecialchars($sImg); ?>" class="w-full h-full object-cover">
                                                         <?php else: ?>
                                                             <i class="fa-solid fa-user text-slate-400 text-[10px]"></i>
                                                         <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-[10px] font-bold text-slate-800 leading-tight"><?php echo htmlspecialchars($prop['ad_broker_name'] ?: ($prop['broker_name'] ?: 'Seller')); ?></h4>
                                                        <span class="text-[8px] uppercase font-bold text-secondary tracking-widest block"><?php echo htmlspecialchars($prop['ad_broker_type'] ?: ($prop['seller_type'] ?: 'Premium Broker')); ?></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                                                 <div>
                                                     <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest mb-0.5">Starting From</p>
                                                     <p class="text-secondary font-black text-2xl">
                                                        <?php echo formatPrice($prop['price']); ?>
                                                     </p>
                                                 </div>
                                                 <a href="property-details.php?slug=<?php echo htmlspecialchars($prop['slug'] ?? ''); ?>&id=<?php echo $prop['id']; ?>" class="bg-secondary text-white px-5 py-2.5 rounded-lg text-xs font-bold uppercase tracking-wider hover:bg-slate-900 transition-all shadow-lg shadow-secondary/20 hover:shadow-slate-900/20 active:scale-95">
                                                     Details <i class="fa-solid fa-arrow-right ml-1 text-[10px]"></i>
                                                 </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <!-- Pagination -->
                        <div class="swiper-pagination premium-pagination"></div>
                    </div>
                </div>
            <?php endif;

            // Fetch Other Listings (Not Featured)
            $stmt = $pdo->query("SELECT p.*, u.name as broker_name, u.seller_type, u.profile_image 
                                 FROM properties p 
                                 LEFT JOIN users u ON p.broker_id = u.id 
                                 WHERE p.is_approved = 1 AND p.is_featured = 0 
                                 ORDER BY p.sort_order ASC, p.created_at DESC LIMIT 6");
            $others = $stmt->fetchAll();
            ?>

<!-- Property Listings Section -->
<section class="py-20 bg-gradient-to-br from-slate-800 via-slate-900 to-slate-800 relative overflow-hidden border-t border-slate-700/50">
    <!-- Subtle Grid Pattern -->
    <div class="absolute inset-0 opacity-[0.03]">
        <div class="absolute inset-0" style="background-image: linear-gradient(rgba(148, 163, 184, 0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(148, 163, 184, 0.1) 1px, transparent 1px); background-size: 50px 50px;"></div>
    </div>
    
    <!-- Subtle Accent Lights -->
    <div class="absolute top-1/3 left-0 w-96 h-96 bg-blue-500/5 rounded-full blur-3xl"></div>
    <div class="absolute bottom-1/3 right-0 w-96 h-96 bg-cyan-500/5 rounded-full blur-3xl"></div>
    
    <div class="container mx-auto px-6 relative z-10">
            <div class="flex justify-between items-end mb-10" data-aos="fade-up">
                <div>
                    <span class="text-cyan-400 font-bold tracking-widest uppercase text-xs flex items-center gap-2">
                        <i class="fa-solid fa-sparkles"></i> New Arrivals
                    </span>
                    <h2 class="text-4xl font-bold text-white mt-2 font-display">Property Listings</h2>
                    <div class="w-24 h-1 bg-gradient-to-r from-cyan-400 to-transparent mt-3"></div>
                </div>
                <a href="properties.php" class="hidden md:flex items-center gap-2 text-slate-300 hover:text-cyan-400 transition text-sm font-medium group">
                    View All <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform text-xs"></i>
                </a>
            </div>

            <div class="relative group/swiper" data-aos="fade-up">
                <!-- Navigation Arrows -->
                <div class="listings-next swiper-button-next !text-cyan-400 !w-10 !h-10 !bg-slate-900/80 !backdrop-blur-md !rounded-full !border !border-white/10 hover:!bg-cyan-400 hover:!text-white transition-all shadow-2xl !-right-2 md:!-right-5 !z-50 opacity-0 group-hover/swiper:opacity-100 flex items-center justify-center after:!content-none">
                    <i class="fa-solid fa-chevron-right text-lg"></i>
                </div>
                <div class="listings-prev swiper-button-prev !text-cyan-400 !w-10 !h-10 !bg-slate-900/80 !backdrop-blur-md !rounded-full !border !border-white/10 hover:!bg-cyan-400 hover:!text-white transition-all shadow-2xl !-left-2 md:!-left-5 !z-50 opacity-0 group-hover/swiper:opacity-100 flex items-center justify-center after:!content-none">
                    <i class="fa-solid fa-chevron-left text-lg"></i>
                </div>

                <div class="swiper listingsSwiper pb-16">
                    <div class="swiper-wrapper">
                        <?php if (count($others) > 0): ?>
                            <?php foreach ($others as $prop): 
                                $imagePath = get_property_cover($prop['id'], $pdo);
                                $sImg = get_seller_logo($prop);
                            ?>
                                <div class="swiper-slide h-auto">
                                    <div class="bg-white/95 backdrop-blur-sm rounded-xl overflow-hidden border border-white/20 shadow-lg hover:shadow-2xl hover:shadow-cyan-500/10 transition-all duration-500 group flex flex-col h-full">
                                        <div class="relative overflow-hidden h-48">
                                            <img src="<?php echo htmlspecialchars($imagePath); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-all duration-700">
                                            <div class="absolute top-3 left-3 bg-slate-900/90 backdrop-blur-md px-3 py-1 text-[9px] font-bold uppercase tracking-widest text-white border border-white/10 z-10 rounded">
                                                <?php echo str_replace('_', ' ', $prop['status']); ?>
                                            </div>
                                        </div>
                                        <div class="p-5 flex-1 flex flex-col bg-white">
                                            <h3 class="text-lg font-bold font-display text-slate-900 mb-1 line-clamp-1 group-hover:text-cyan-600 transition">
                                                <?php echo htmlspecialchars($prop['title']); ?>
                                            </h3>
                                            <div class="flex items-center gap-1.5 text-slate-500 text-xs mb-4 font-medium">
                                                <i class="fa-solid fa-map-pin text-slate-400"></i>
                                                <?php echo htmlspecialchars($prop['location']); ?>
                                            </div>
                                            
                                            <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
                                                 <p class="text-slate-900 font-bold text-lg">
                                                    <?php echo formatPrice($prop['price']); ?>
                                                 </p>
                                                 <a href="<?php echo BASE_URL; ?>/property/<?php echo ($prop['slug'] ?? $prop['id']); ?>" class="text-xs font-bold text-slate-600 uppercase tracking-wider hover:text-cyan-600 transition flex items-center gap-1">
                                                     View Details <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                                 </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="swiper-slide text-center py-16">
                                <p class="text-slate-400 text-sm">No properties found.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- Pagination -->
                    <div class="swiper-pagination listings-pagination"></div>
                </div>
            </div>
        </div>
    </section>
        <?php
        } catch (PDOException $e) { }
        ?>
    </div>
</section>

<!-- Call to Action -->
<section class="py-16 bg-slate-900 border-t border-slate-800">
    <div class="container mx-auto px-6 text-center" data-aos="fade-up">
        <h2 class="text-3xl font-bold font-display text-white mb-4">Ready to Find Your Home?</h2>
        <p class="text-slate-400 text-base max-w-xl mx-auto mb-8">
            Join thousands of satisfied customers who found their perfect property.
        </p>
        <div class="flex justify-center gap-4">
            <a href="register.php" class="bg-secondary text-white px-8 py-3 rounded-lg font-bold hover:bg-yellow-600 transition text-sm">Get Started</a>
            <a href="properties.php" class="bg-transparent border border-slate-700 text-white px-8 py-3 rounded-lg font-bold hover:bg-slate-800 transition text-sm">Browse Listings</a>
        </div>
    </div>
</section>

<!-- Footer -->
<?php require_once __DIR__ . '/includes/footer.php'; ?>

<!-- Ad Lead Modal -->
<div id="adLeadModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl relative overflow-hidden transform scale-95 opacity-0 transition-all duration-300" id="adModalContent">
        <div class="absolute top-0 left-0 w-full h-1.5 bg-secondary"></div>
        <button onclick="closeAdModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>
        
        <div class="p-8">
            <div class="text-center mb-6">
                <h3 class="text-2xl font-display font-bold text-slate-900 mb-2">Interested?</h3>
                <p class="text-slate-500 text-sm">Fill in your details to know more about <span id="adTitle" class="font-bold text-slate-800">this project</span>.</p>
            </div>
            
            <form action="api/submit_lead.php" method="POST" class="space-y-4">
                <input type="hidden" name="type" value="Sponsored Ad Lead">
                <input type="hidden" name="source_ad_id" id="sourceAdId">
                
                <div>
                    <input type="text" name="name" required placeholder="Your Name" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary transition">
                </div>
                <div>
                    <input type="tel" name="phone" required placeholder="Phone Number" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary transition">
                </div>
                <div>
                    <input type="email" name="email" placeholder="Email Address (Optional)" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary transition">
                </div>
                
                <button type="submit" class="w-full bg-secondary text-white font-bold py-3.5 rounded-xl hover:bg-yellow-600 transition shadow-lg shadow-yellow-500/20">
                    Request Call Back
                </button>
                <p class="text-xs text-center text-slate-400 mt-4">We respect your privacy. No spam.</p>
            </form>
        </div>
    </div>
</div>

<script>
    // Initialize Swiper
    document.addEventListener('DOMContentLoaded', function() {
        const bannerSwiper = new Swiper('.bannerSwiper', {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
                dynamicBullets: true,
            }
        });

        const premiumSwiper = new Swiper('.premiumSwiper', {
            slidesPerView: 1,
            spaceBetween: 24,
            pagination: {
                el: '.premium-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.premium-next',
                prevEl: '.premium-prev',
            },
            breakpoints: {
                768: {
                    slidesPerView: 2,
                },
                1024: {
                    slidesPerView: 3,
                },
            }
        });

        const listingsSwiper = new Swiper('.listingsSwiper', {
            slidesPerView: 1,
            spaceBetween: 24,
            pagination: {
                el: '.listings-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.listings-next',
                prevEl: '.listings-prev',
            },
            breakpoints: {
                768: {
                    slidesPerView: 2,
                },
                1024: {
                    slidesPerView: 3,
                },
            }
        });
    });

    const adModal = document.getElementById('adLeadModal');
    const adModalContent = document.getElementById('adModalContent');
    const sourceAdIdInput = document.getElementById('sourceAdId');
    const adTitleSpan = document.getElementById('adTitle');

    function handleBannerClick(banner) {
        if (banner.link_type === 'custom' && banner.custom_link) {
            // Open custom link
            window.open(banner.custom_link, '_blank');
        } else {
            // Open Capture Modal
            openAdModal(banner.id, banner.title);
        }
    }

    function openAdModal(adId, title) {
        sourceAdIdInput.value = adId;
        adTitleSpan.textContent = title;
        adModal.classList.remove('hidden');
        // Animation
        setTimeout(() => {
            adModalContent.classList.remove('scale-95', 'opacity-0');
            adModalContent.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeAdModal() {
        adModalContent.classList.remove('scale-100', 'opacity-100');
        adModalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            adModal.classList.add('hidden');
        }, 300);
    }
    
    // Close on outside click
    adModal.addEventListener('click', (e) => {
        if (e.target === adModal) closeAdModal();
    });
</script>
