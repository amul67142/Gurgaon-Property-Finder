<?php
require_once __DIR__ . '/../includes/header.php';
requireBroker();

// Fetch specific broker stats
$userId = $_SESSION['user_id'];
$totalProperties = $pdo->prepare("SELECT COUNT(*) FROM properties WHERE broker_id = ?");
$totalProperties->execute([$userId]);
$totalProperties = $totalProperties->fetchColumn();

// Leads count (simplified for now, assuming leads table has broker logic or property relation)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM leads l 
                       JOIN properties p ON l.property_id = p.id 
                       WHERE p.broker_id = ?");
$stmt->execute([$userId]);
$totalLeads = $stmt->fetchColumn();

// Recent Properties
$stmt = $pdo->prepare("SELECT * FROM properties WHERE broker_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$userId]);
$recentProperties = $stmt->fetchAll();
?>
<div class="flex min-h-screen bg-slate-50">
    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-white fixed top-0 bottom-0 left-0 overflow-y-auto transition-transform z-30 hidden lg:block" id="sidebar">
        <div class="p-6">
            <a href="<?php echo BASE_URL; ?>/index.php" class="mb-10 block px-2">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo-white.png" alt="Gurgaon Property Finder" class="h-14 w-auto object-contain">
            </a>
            
            <nav class="space-y-2">
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-secondary text-white shadow-lg shadow-secondary/50">
                    <i class="fa-solid fa-gauge-high w-5"></i> Dashboard
                </a>
                <a href="properties.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800 hover:text-white transition">
                    <i class="fa-solid fa-list w-5"></i> My Listings
                </a>
                <a href="add_property.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800 hover:text-white transition">
                    <i class="fa-solid fa-plus-circle w-5"></i> Add Property
                </a>
                <a href="leads.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800 hover:text-white transition">
                    <i class="fa-solid fa-users w-5"></i> Leads
                </a>
                
                <div class="pt-8 mt-8 border-t border-slate-700">
                    <a href="<?php echo BASE_URL; ?>/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/10 transition">
                        <i class="fa-solid fa-right-from-bracket w-5"></i> Logout
                    </a>
                </div>
            </nav>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 lg:ml-64 p-4 md:p-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold font-display text-slate-800">Broker Dashboard</h1>
            <p class="text-slate-500">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-xl shadow-blue-500/20">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-blue-100 font-medium mb-1">My Properties</p>
                        <h3 class="text-3xl font-bold"><?php echo $totalProperties; ?></h3>
                    </div>
                    <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                        <i class="fa-solid fa-building text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-orange-400 to-orange-500 rounded-2xl p-6 text-white shadow-xl shadow-orange-500/20">
                 <div class="flex justify-between items-start">
                    <div>
                        <p class="text-orange-100 font-medium mb-1">Total Leads</p>
                        <h3 class="text-3xl font-bold"><?php echo $totalLeads; ?></h3>
                    </div>
                    <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                        <i class="fa-solid fa-user-group text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-slate-800">Recent Listings</h3>
                <a href="properties.php" class="text-secondary text-sm font-medium hover:underline">View All</a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-slate-400 text-xs uppercase tracking-wider border-b border-slate-100">
                            <th class="pb-3 pl-4">Property</th>
                            <th class="pb-3">Price</th>
                            <th class="pb-3">Status</th>
                            <th class="pb-3">Date</th>
                            <th class="pb-3 text-right pr-4">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php if (count($recentProperties) > 0): ?>
                            <?php foreach ($recentProperties as $prop): ?>
                                <tr class="border-b border-slate-50 hover:bg-slate-50 transition">
                                    <td class="py-4 pl-4 font-medium text-slate-700"><?php echo htmlspecialchars($prop['title']); ?></td>
                                    <td class="py-4 px-4 text-slate-600"><?php echo formatPrice($prop['price']); ?></td>
                                    <td class="py-4">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold 
                                            <?php echo $prop['status'] === 'ready_to_move' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                                            <?php echo ucwords(str_replace('_', ' ', $prop['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="py-4 text-slate-400"><?php echo date('M j, Y', strtotime($prop['created_at'])); ?></td>
                                    <td class="py-4 text-right pr-4">
                                        <a href="edit_property.php?id=<?php echo $prop['id']; ?>" class="text-slate-400 hover:text-secondary"><i class="fa-solid fa-pen-to-square"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">No properties listed yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Mobile Sidebar Toggle Script -->
<script>
    const mobileBtn = document.querySelector('.fa-bars').parentElement;
    const sidebar = document.getElementById('sidebar');
    // Mobile logic would go here if needed
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
