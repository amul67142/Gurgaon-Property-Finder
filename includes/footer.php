<?php if (!$isDashboard): ?>
</main>
<?php endif; ?>
<?php
$currentScript = $_SERVER['PHP_SELF'];
$isDashboard = strpos($currentScript, '/admin/') !== false || strpos($currentScript, '/broker/') !== false;
?>

<?php if (!$isDashboard): ?>
<footer class="bg-slate-900 text-white pt-16 pb-8">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
            <div data-aos="fade-up">
                <a href="<?php echo BASE_URL; ?>/index.php" class="mb-6 block">
                    <img src="<?php echo BASE_URL; ?>/assets/images/logo-white.png" alt="Gurgaon Property Finder" class="h-12 w-auto object-contain">
                </a>
                <p class="text-slate-400 leading-relaxed">
                    Premium real estate marketplace connecting buyers with their dream properties using modern technology and seamless experiences.
                </p>
            </div>
            
            <div data-aos="fade-up" data-aos-delay="100">
                <h4 class="text-lg font-semibold mb-6">Quick Links</h4>
                <ul class="space-y-3 text-slate-400">
                    <li><a href="<?php echo BASE_URL; ?>/index.php" class="hover:text-white transition">Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/properties.php" class="hover:text-white transition">Properties</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/about-us.php" class="hover:text-white transition">About Us</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/contact.php" class="hover:text-white transition">Contact Us</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/privacy-policy.php" class="hover:text-white transition">Privacy Policy</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/terms.php" class="hover:text-white transition">Terms & Conditions</a></li>
                </ul>
            </div>
            
            <div data-aos="fade-up" data-aos-delay="200">
                <h4 class="text-lg font-semibold mb-6">Contact</h4>
                <ul class="space-y-3 text-slate-400">
                    <li class="flex items-center gap-3"><i class="fa-solid fa-location-dot text-secondary"></i>303 JMD Galleria Sector 48, Gurgaon, Haryana, 122001</li>
                    
                    <li class="flex items-center gap-3"><i class="fa-solid fa-envelope text-secondary"></i> support@gurgaonpropertyfinder.com</li>
                </ul>
            </div>
            
            <div data-aos="fade-up" data-aos-delay="300">
                <h4 class="text-lg font-semibold mb-6">Newsletter</h4>
                <div class="relative">
                    <input type="email" placeholder="Your email address" class="w-full bg-slate-800 border-none rounded-lg py-3 px-4 focus:ring-2 focus:ring-secondary text-white">
                    <button class="absolute right-2 top-2 bg-secondary text-white p-1.5 rounded-md hover:bg-blue-600 transition">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="border-t border-slate-800 pt-8 text-center text-slate-500 text-sm">
            <p>&copy; <?php echo date('Y'); ?> Gurgaon Property Finder. All rights reserved.</p>
            <p class="mt-2 text-xs opacity-75">Owned and Operated by <span class="text-slate-400 font-medium tracking-wide">Real Vibe Digital Media</span></p>
        </div>
    </div>
</footer>
<?php endif; ?>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 800,
        once: true,
        offset: 100
    });

    // Toast Notification System
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) {
            const div = document.createElement('div');
            div.id = 'toast-container';
            div.className = 'fixed top-6 right-6 z-50 flex flex-col gap-3 pointer-events-none';
            document.body.appendChild(div);
        }
        
        const toast = document.createElement('div');
        const icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';
        const bgColor = type === 'success' ? 'bg-green-600' : 'bg-red-600';
        
        toast.className = `${bgColor} text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 transform translate-x-full transition-all duration-300 pointer-events-auto min-w-[300px] border border-white/10`;
        toast.innerHTML = `
            <i class="fa-solid ${icon} text-lg"></i>
            <span class="font-medium">${message}</span>
            <button class="ml-auto hover:opacity-75 transition-opacity" onclick="this.parentElement.remove()">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        `;
        
        document.getElementById('toast-container').appendChild(toast);
        
        // Triger slide in
        setTimeout(() => toast.classList.remove('translate-x-full'), 10);
        
        // Auto remove
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-x-full');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }
</script>
</body>
</html>
