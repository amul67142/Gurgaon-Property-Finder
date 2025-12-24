<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="w-64 bg-slate-900 text-white fixed top-0 bottom-0 left-0 overflow-y-auto z-30 hidden lg:block">
    <div class="p-6">
        <a href="<?php echo BASE_URL; ?>/index.php" class="mb-10 block px-2">
            <img src="<?php echo BASE_URL; ?>/assets/images/logo-white.png" alt="Gurgaon Property Finder" class="h-14 w-auto object-contain">
        </a>
        <div class="flex items-center gap-3 mb-8 text-slate-400 text-sm font-medium uppercase tracking-wider">
            Admin Panel
        </div>
        <nav class="space-y-2">
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl <?php echo $current_page == 'dashboard.php' ? 'bg-secondary text-white shadow-lg shadow-secondary/50' : 'text-slate-300 hover:bg-slate-800 hover:text-white transition'; ?>">
                <i class="fa-solid fa-gauge-high w-5"></i> Dashboard
            </a>
            <a href="users.php" class="flex items-center gap-3 px-4 py-3 rounded-xl <?php echo $current_page == 'users.php' ? 'bg-secondary text-white shadow-lg shadow-secondary/50' : 'text-slate-300 hover:bg-slate-800 hover:text-white transition'; ?>">
                <i class="fa-solid fa-users-gear w-5"></i> Users
            </a>
            <a href="properties.php" class="flex items-center gap-3 px-4 py-3 rounded-xl <?php echo $current_page == 'properties.php' ? 'bg-secondary text-white shadow-lg shadow-secondary/50' : 'text-slate-300 hover:bg-slate-800 hover:text-white transition'; ?>">
                <i class="fa-solid fa-building w-5"></i> Properties
            </a>
            <a href="leads.php" class="flex items-center gap-3 px-4 py-3 rounded-xl <?php echo $current_page == 'leads.php' ? 'bg-secondary text-white shadow-lg shadow-secondary/50' : 'text-slate-300 hover:bg-slate-800 hover:text-white transition'; ?>">
                <i class="fa-solid fa-users w-5"></i> Leads
            </a>
            <a href="banners.php" class="flex items-center gap-3 px-4 py-3 rounded-xl <?php echo $current_page == 'banners.php' ? 'bg-secondary text-white shadow-lg shadow-secondary/50' : 'text-slate-300 hover:bg-slate-800 hover:text-white transition'; ?>">
                <i class="fa-solid fa-rectangle-ad w-5"></i> Banners
            </a>
            <div class="pt-8 mt-8 border-t border-slate-700">
                <a href="<?php echo BASE_URL; ?>/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/10 transition">
                    <i class="fa-solid fa-right-from-bracket w-5"></i> Logout
                </a>
            </div>
        </nav>
    </div>
</aside>
