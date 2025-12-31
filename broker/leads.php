<?php
require_once __DIR__ . '/../includes/header.php';
requireBroker();

$brokerId = $_SESSION['user_id'];

// Get leads for properties owned by this broker
$sql = "SELECT l.*, p.title as property_title 
        FROM leads l
        JOIN properties p ON l.property_id = p.id
        WHERE p.broker_id = ?
        ORDER BY l.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$brokerId]);
$leads = $stmt->fetchAll();
?>
<div class="flex min-h-screen bg-slate-50">
    <!-- Sidebar -->
    <?php if (isAdmin()): ?>
        <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <?php else: ?>
     <aside class="w-64 bg-slate-900 text-white fixed top-0 bottom-0 left-0 overflow-y-auto z-30 hidden lg:block">
        <div class="p-6">
            <a href="<?php echo BASE_URL; ?>/index.php" class="text-2xl font-bold font-display text-white mb-10 block flex items-center gap-2">
                <i class="fa-solid fa-city text-secondary"></i> Gurgaon Property Finder
            </a>
            <nav class="space-y-2">
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800 hover:text-white transition"><i class="fa-solid fa-gauge-high w-5"></i> Dashboard</a>
                <a href="properties.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800 hover:text-white transition"><i class="fa-solid fa-list w-5"></i> My Listings</a>
                <a href="add_property.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800 hover:text-white transition"><i class="fa-solid fa-plus-circle w-5"></i> Add Property</a>
                <a href="leads.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-secondary text-white shadow-lg shadow-secondary/50"><i class="fa-solid fa-users w-5"></i> Leads</a>
                <div class="pt-8 mt-8 border-t border-slate-700">
                    <a href="<?php echo BASE_URL; ?>/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/10 transition"><i class="fa-solid fa-right-from-bracket w-5"></i> Logout</a>
                </div>
            </nav>
        </div>
    </aside>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="flex-1 lg:ml-64 p-4 md:p-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold font-display text-slate-800">Leads</h1>
                <p class="text-slate-500">Track inquiries on your properties</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                            <th class="py-4 pl-6 font-semibold">Date</th>
                            <th class="py-4 px-4 font-semibold">Property</th>
                            <th class="py-4 px-4 font-semibold">Lead Name</th>
                            <th class="py-4 px-4 font-semibold">Contact Info</th>
                            <th class="py-4 pr-6 font-semibold">Message</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        <?php if (count($leads) > 0): ?>
                            <?php foreach ($leads as $lead): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-4 pl-6 text-slate-500"><?php echo date('M d, Y', strtotime($lead['created_at'])); ?></td>
                                    <td class="py-4 px-4 font-medium text-slate-800"><?php echo htmlspecialchars($lead['property_title']); ?></td>
                                    <td class="py-4 px-4 text-slate-700"><?php echo htmlspecialchars($lead['name']); ?></td>
                                    <td class="py-4 px-4">
                                        <div class="flex flex-col">
                                            <span class="text-slate-700 flex items-center gap-2"><i class="fa-regular fa-envelope text-slate-400 text-xs"></i> <?php echo htmlspecialchars($lead['email']); ?></span>
                                            <span class="text-slate-500 flex items-center gap-2 mt-1"><i class="fa-solid fa-phone text-slate-400 text-xs"></i> <?php echo htmlspecialchars($lead['phone']); ?></span>
                                        </div>
                                    </td>
                                    <td class="py-4 pr-6 text-slate-600 italic">"<?php echo htmlspecialchars($lead['message']); ?>"</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fa-regular fa-paper-plane text-4xl mb-3 opacity-20"></i>
                                        <p>No leads yet. Promote your listings!</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
