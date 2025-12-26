<?php
// TEMPORARY: Enable error display for debugging (REMOVE AFTER FIXING)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gurgaon Property Finder</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e293b',    // Slate 800
                        secondary: '#EAB308',  // Gold / Yellow-500
                        accent: '#f59e0b',     // Amber 500
                    },
                    fontFamily: {
                        sans: ['Montserrat', 'sans-serif'],
                        display: ['Playfair Display', 'serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <style>
        body { font-family: 'Montserrat', sans-serif !important; }
        h1, h2, h3, .font-display { font-family: 'Playfair Display', serif !important; font-weight: 500; }
        .glass { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="bg-gray-50 text-slate-800 antialiased selection:bg-secondary selection:text-white font-sans">

<?php
// Detect Dashboard Page
$currentScript = $_SERVER['PHP_SELF'];
$isDashboard = strpos($currentScript, '/admin/') !== false || strpos($currentScript, '/broker/') !== false;
$isTransparentPage = basename($_SERVER['PHP_SELF']) == 'index.php' || basename($_SERVER['PHP_SELF']) == 'property-details.php';
?>

<!-- Navigation (Public Only) -->
<?php if (!$isDashboard): ?>
<!-- Navigation  -->
<nav class="fixed w-full z-50 transition-all duration-300 <?php echo $isTransparentPage ? 'bg-transparent text-white border-transparent' : 'bg-white border-b border-gray-100 text-slate-900 shadow-sm'; ?>" id="navbar" data-transparent="<?php echo $isTransparentPage ? 'true' : 'false'; ?>">
    <div class="container mx-auto px-6 py-4 flex justify-between items-center">
        <a href="<?php echo BASE_URL; ?>/index.php" id="nav-logo" class="flex items-center gap-2">
            <img src="<?php echo BASE_URL; ?>/assets/images/<?php echo $isTransparentPage ? 'logo-white.png' : 'logo-dark.png'; ?>" 
                 alt="Gurgaon Property Finder" 
                 id="header-logo-img"
                 class="h-12 md:h-14 w-auto object-contain transition-all duration-300">
        </a>
        
        <div id="nav-menu" class="hidden lg:flex items-center space-x-8 font-medium <?php echo $isTransparentPage ? 'text-white' : 'text-slate-900'; ?>">
            <a href="<?php echo BASE_URL; ?>/index.php" class="hover:text-secondary transition">Home</a>
            
            <!-- Properties Dropdown -->
            <div class="relative group">
                <a href="<?php echo BASE_URL; ?>/properties.php" class="flex items-center gap-1 hover:text-secondary transition py-2">
                    Properties <i class="fa-solid fa-chevron-down text-[10px] mt-0.5 transition-transform group-hover:rotate-180"></i>
                </a>
                <div class="absolute left-0 mt-0 w-56 bg-white rounded-xl shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible group-hover:mt-2 transition-all duration-300 transform origin-top z-50 overflow-hidden text-slate-800">
                    <div class="p-2 space-y-1">
                        <a href="<?php echo BASE_URL; ?>/properties.php" class="block px-4 py-2.5 rounded-lg hover:bg-slate-50 hover:text-secondary transition flex items-center gap-3">
                            <i class="fa-solid fa-layer-group w-5 text-slate-400"></i> All Properties
                        </a>
                        <a href="<?php echo BASE_URL; ?>/properties.php?type=apartment" class="block px-4 py-2.5 rounded-lg hover:bg-slate-50 hover:text-secondary transition flex items-center gap-3">
                            <i class="fa-solid fa-building w-5 text-slate-400"></i> Apartments
                        </a>
                        <a href="<?php echo BASE_URL; ?>/properties.php?type=house" class="block px-4 py-2.5 rounded-lg hover:bg-slate-50 hover:text-secondary transition flex items-center gap-3">
                            <i class="fa-solid fa-house w-5 text-slate-400"></i> Independent Houses
                        </a>
                        <a href="<?php echo BASE_URL; ?>/properties.php?type=plot" class="block px-4 py-2.5 rounded-lg hover:bg-slate-50 hover:text-secondary transition flex items-center gap-3">
                            <i class="fa-solid fa-map-location-dot w-5 text-slate-400"></i> Plots / Land
                        </a>
                        <a href="<?php echo BASE_URL; ?>/properties.php?type=commercial" class="block px-4 py-2.5 rounded-lg hover:bg-slate-50 hover:text-secondary transition flex items-center gap-3">
                            <i class="fa-solid fa-shop w-5 text-slate-400"></i> Commercial
                        </a>
                    </div>
                </div>
            </div>

            <a href="<?php echo BASE_URL; ?>/about-us.php" class="hover:text-secondary transition">About Us</a>
            <a href="<?php echo BASE_URL; ?>/contact.php" class="hover:text-secondary transition">Contact</a>
            
            <?php if (isLoggedIn()): ?>
                <div class="relative group">
                    <button class="flex items-center gap-3 hover:text-secondary transition py-2">
                        <div class="w-8 h-8 rounded-full bg-secondary/10 flex items-center justify-center text-secondary">
                            <i class="fa-solid fa-user-circle"></i>
                        </div>
                        <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <i class="fa-solid fa-chevron-down text-[10px] mt-0.5"></i>
                    </button>
                    <!-- User Dropdown -->
                    <div class="absolute right-0 mt-0 w-56 bg-white rounded-xl shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible group-hover:mt-2 transition-all duration-300 transform origin-top-right z-50 overflow-hidden text-slate-800">
                        <div class="p-1.5 space-y-1">
                            <?php if (isAdmin()): ?>
                                <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="block px-4 py-2.5 rounded-lg hover:bg-slate-50 hover:text-secondary transition flex items-center gap-3">
                                    <i class="fa-solid fa-gauge-high w-5 text-slate-400"></i> Admin Panel
                                </a>
                            <?php elseif (isBroker()): ?>
                                <a href="<?php echo BASE_URL; ?>/broker/dashboard.php" class="block px-4 py-2.5 rounded-lg hover:bg-slate-50 hover:text-secondary transition flex items-center gap-3">
                                    <i class="fa-solid fa-gauge-high w-5 text-slate-400"></i> Dashboard
                                </a>
                            <?php endif; ?>
                            
                            <hr class="border-slate-100 mx-2">
                            
                            <a href="<?php echo BASE_URL; ?>/logout.php" class="block px-4 py-2.5 rounded-lg hover:bg-red-50 text-red-500 transition flex items-center gap-3">
                                <i class="fa-solid fa-right-from-bracket w-5"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="flex items-center gap-4">
                    <a href="<?php echo BASE_URL; ?>/login.php" class="hover:text-secondary transition">Login</a>
                    <a href="<?php echo BASE_URL; ?>/register.php" class="bg-secondary text-white px-6 py-2.5 rounded-full hover:bg-yellow-600 transition shadow-lg shadow-yellow-500/30 font-bold flex items-center gap-2">
                        Post Property <i class="fa-solid fa-plus-circle"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Mobile Menu Btn -->
        <button id="nav-mobile-btn" class="lg:hidden text-2xl <?php echo $isTransparentPage ? 'text-white' : 'text-slate-900'; ?>">
            <i class="fa-solid fa-bars-staggered"></i>
        </button>
    </div>

    <!-- Mobile Menu Side Drawer -->
    <div id="mobile-drawer" class="lg:hidden fixed inset-0 bg-slate-900/60 backdrop-blur-md z-[60] opacity-0 invisible transition-all duration-300">
        <div class="absolute right-0 top-0 bottom-0 w-[280px] bg-white shadow-2xl p-8 transform translate-x-full transition-transform duration-500 ease-in-out flex flex-col" id="mobile-drawer-content">
            <div class="flex justify-between items-center mb-12">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo-dark.png" alt="Logo" class="h-8 w-auto">
                <button id="close-mobile-btn" class="w-10 h-10 flex items-center justify-center rounded-full bg-slate-50 text-slate-400 hover:text-slate-900 transition">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <nav class="flex flex-col gap-2 overflow-y-auto flex-1 pr-2">
                <a href="<?php echo BASE_URL; ?>/index.php" class="flex items-center justify-between px-4 py-3.5 rounded-xl hover:bg-slate-50 text-slate-800 font-semibold transition group">
                    <span class="text-sm tracking-tight">Home</span>
                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-300 group-hover:text-secondary transition"></i>
                </a>
                
                <!-- Mobile Accordion for Properties -->
                <div class="flex flex-col">
                    <button class="flex items-center justify-between w-full px-4 py-3.5 rounded-xl hover:bg-slate-50 text-slate-800 font-semibold transition group" id="mobile-prop-toggle">
                        <span class="text-sm tracking-tight">Properties</span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-300 transition-transform duration-300" id="prop-chevron"></i>
                    </button>
                    <div class="flex flex-col pl-8 hidden overflow-hidden transition-all duration-300" id="mobile-prop-menu">
                        <a href="<?php echo BASE_URL; ?>/properties.php" class="py-2.5 px-4 text-xs font-medium text-slate-500 hover:text-secondary transition">All Properties</a>
                        <a href="<?php echo BASE_URL; ?>/properties.php?type=apartment" class="py-2.5 px-4 text-xs font-medium text-slate-500 hover:text-secondary transition">Apartments</a>
                        <a href="<?php echo BASE_URL; ?>/properties.php?type=house" class="py-2.5 px-4 text-xs font-medium text-slate-500 hover:text-secondary transition">Independent Houses</a>
                        <a href="<?php echo BASE_URL; ?>/properties.php?type=plot" class="py-2.5 px-4 text-xs font-medium text-slate-500 hover:text-secondary transition">Plots / Land</a>
                        <a href="<?php echo BASE_URL; ?>/properties.php?type=commercial" class="py-2.5 px-4 text-xs font-medium text-slate-500 hover:text-secondary transition">Commercial</a>
                    </div>
                </div>

                <a href="<?php echo BASE_URL; ?>/about-us.php" class="flex items-center justify-between px-4 py-3.5 rounded-xl hover:bg-slate-50 text-slate-800 font-semibold transition group">
                    <span class="text-sm tracking-tight">About Us</span>
                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-300 group-hover:text-secondary transition"></i>
                </a>
                <a href="<?php echo BASE_URL; ?>/contact.php" class="flex items-center justify-between px-4 py-3.5 rounded-xl hover:bg-slate-50 text-slate-800 font-semibold transition group">
                    <span class="text-sm tracking-tight">Contact</span>
                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-300 group-hover:text-secondary transition"></i>
                </a>
            </nav>

            <div class="mt-auto pt-8 border-t border-slate-50 flex flex-col gap-3">
                <?php if (isLoggedIn()): ?>
                    <div class="flex items-center gap-3 px-4 mb-4">
                        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 font-bold border border-slate-200">
                            <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-slate-800"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                            <span class="text-[10px] text-slate-400 uppercase tracking-wider"><?php echo htmlspecialchars($_SESSION['user_role']); ?></span>
                        </div>
                    </div>
                    <?php if (isAdmin()): ?>
                        <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="bg-slate-900 text-white w-full py-3.5 rounded-xl flex items-center justify-center gap-3 text-center text-xs font-bold transition shadow-lg shadow-slate-900/10">
                            <i class="fa-solid fa-gauge-high"></i> Admin Panel
                        </a>
                    <?php elseif (isBroker()): ?>
                        <a href="<?php echo BASE_URL; ?>/broker/dashboard.php" class="bg-slate-900 text-white w-full py-3.5 rounded-xl flex items-center justify-center gap-3 text-center text-xs font-bold transition shadow-lg shadow-slate-900/10">
                            <i class="fa-solid fa-gauge-high"></i> Dashboard
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>/logout.php" class="text-red-500 text-xs font-bold px-4 py-2 flex items-center justify-center gap-2 mt-2">
                        <i class="fa-solid fa-right-from-bracket"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/login.php" class="w-full py-3.5 text-center text-xs font-bold text-slate-700 border border-slate-100 rounded-xl hover:bg-slate-50 transition">Login</a>
                    <a href="<?php echo BASE_URL; ?>/register.php" class="bg-secondary text-white w-full py-3.5 rounded-xl text-center text-xs font-bold shadow-lg shadow-secondary/20 transition">Post Property</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const navbar = document.getElementById('navbar');
        const isTransparent = navbar ? navbar.dataset.transparent : 'false';
        const navLinks = document.getElementById('nav-menu');
        const mobileBtn = document.getElementById('nav-mobile-btn');
        const navLogoImg = document.getElementById('header-logo-img');
        
        function updateNavbar() {
            if (window.scrollY > 50) {
                // Scrolled State: Always White & Dark Text
                navbar.classList.remove('bg-transparent', 'border-transparent', 'text-white', 'shadow-sm');
                navbar.classList.add('bg-white', 'text-slate-900', 'shadow-md');
                
                // Ensure Dark Logo
                if (navLogoImg) navLogoImg.src = '<?php echo BASE_URL; ?>/assets/images/logo-dark.png';

                // Ensure Dark Nav Links
                if (navLinks) {
                     navLinks.classList.remove('text-white');
                     navLinks.classList.add('text-slate-900');
                }
                
                // Ensure Dark Mobile Btn
                if (mobileBtn) {
                    mobileBtn.classList.remove('text-white');
                    mobileBtn.classList.add('text-slate-900');
                }

            } else {
                // Top State
                if (isTransparent === 'true') {
                    // Transparent Home Page (Revert to Transparent)
                     navbar.classList.remove('bg-white', 'text-slate-900', 'shadow-md', 'shadow-sm');
                     navbar.classList.add('bg-transparent', 'text-white', 'border-transparent');
                     
                     // Revert to White Logo
                     if (navLogoImg) navLogoImg.src = '<?php echo BASE_URL; ?>/assets/images/logo-white.png';
                     
                     // Revert to White Links
                     if (navLinks) {
                        navLinks.classList.remove('text-slate-900');
                        navLinks.classList.add('text-white');
                     }
                     
                     if (mobileBtn) {
                        mobileBtn.classList.remove('text-slate-900');
                        mobileBtn.classList.add('text-white');
                     }

                } else {
                    // Standard Page (Stay White, just remove shadow)
                    navbar.classList.remove('shadow-md');
                    navbar.classList.add('shadow-sm'); // Optional default shadow
                }
            }
        }

        window.addEventListener('scroll', updateNavbar);
        updateNavbar(); // Initial check

        // Mobile Menu Logic
        const mobileDrawer = document.getElementById('mobile-drawer');
        const mobileDrawerContent = document.getElementById('mobile-drawer-content');
        const closeBtn = document.getElementById('close-mobile-btn');
        const mobilePropToggle = document.getElementById('mobile-prop-toggle');
        const mobilePropMenu = document.getElementById('mobile-prop-menu');
        const propChevron = document.getElementById('prop-chevron');

        function openDrawer() {
            mobileDrawer.classList.remove('invisible', 'opacity-0');
            mobileDrawer.classList.add('visible', 'opacity-100');
            mobileDrawerContent.classList.remove('translate-x-full');
            mobileDrawerContent.classList.add('translate-x-0');
            document.body.style.overflow = 'hidden'; // Prevent scroll
        }

        function closeDrawer() {
            mobileDrawerContent.classList.remove('translate-x-0');
            mobileDrawerContent.classList.add('translate-x-full');
            setTimeout(() => {
                mobileDrawer.classList.remove('visible', 'opacity-100');
                mobileDrawer.classList.add('invisible', 'opacity-0');
            }, 300);
            document.body.style.overflow = '';
        }

        if (mobileBtn) mobileBtn.addEventListener('click', openDrawer);
        if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
        if (mobileDrawer) {
            mobileDrawer.addEventListener('click', (e) => {
                if (e.target === mobileDrawer) closeDrawer();
            });
        }

        // Accordion Toggle
        if (mobilePropToggle) {
            mobilePropToggle.addEventListener('click', () => {
                mobilePropMenu.classList.toggle('hidden');
                propChevron.classList.toggle('rotate-180');
            });
        }
    });

    // Global Tracking Function
    function trackCTA(type, propertyId) {
        fetch('<?php echo BASE_URL; ?>/api/track_cta.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                type: type,
                property_id: propertyId,
                source: window.location.href
            })
        }).catch(err => console.error('Tracking Error:', err));
    }
</script>

<!-- Spacer for fixed nav -->
<?php if (!$isTransparentPage): ?>
<div id="nav-spacer" class="h-20"></div>
<?php endif; ?>
<?php endif; ?>

<?php if (!$isDashboard): ?>
<main class="min-h-screen">
<?php endif; ?>
