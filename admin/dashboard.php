<?php
@session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
require_once __DIR__ . '/../includes/header.php';

// Analytics Queries
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$totalBrokers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'broker'")->fetchColumn();
$totalProperties = $pdo->query("SELECT COUNT(*) FROM properties")->fetchColumn();
$totalLeads = $pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn();
$ctaClicks = $pdo->query("SELECT COUNT(*) FROM cta_leads")->fetchColumn();

// Fetch leads by type for chart
$leadsByType = $pdo->query("SELECT lead_type, COUNT(*) as count FROM leads GROUP BY lead_type")->fetchAll();
$labels = [];
$data = [];
foreach ($leadsByType as $row) {
    $labels[] = ucfirst($row['lead_type']);
    $data[] = $row['count'];
}
?>
<div class="flex min-h-screen bg-slate-50">
    <!-- Sidebar -->
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 lg:ml-64 p-4 md:p-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold font-display text-slate-800">Admin Dashboard</h1>
            <p class="text-slate-500">Overview of platform activity</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Stats Cards -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-slate-500 text-sm font-medium">Total Users</h3>
                    <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-500"><i class="fa-solid fa-user"></i></div>
                </div>
                <div class="text-3xl font-bold text-slate-800"><?php echo number_format($totalUsers); ?></div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-slate-500 text-sm font-medium">Brokers</h3>
                    <div class="w-10 h-10 rounded-full bg-orange-50 flex items-center justify-center text-orange-500"><i class="fa-solid fa-user-tie"></i></div>
                </div>
                <div class="text-3xl font-bold text-slate-800"><?php echo number_format($totalBrokers); ?></div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-slate-500 text-sm font-medium">Properties</h3>
                    <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500"><i class="fa-solid fa-building"></i></div>
                </div>
                <div class="text-3xl font-bold text-slate-800"><?php echo number_format($totalProperties); ?></div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-slate-500 text-sm font-medium">Total Leads</h3>
                    <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center text-purple-500"><i class="fa-solid fa-users"></i></div>
                </div>
                <div class="text-3xl font-bold text-slate-800"><?php echo number_format($totalLeads + $ctaClicks); ?></div>
            </div>
        </div>

            <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-100 mb-8">
                <h3 class="text-lg font-bold text-slate-800 mb-6">Recent Leads</h3>
                 <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs text-slate-400 uppercase tracking-wider border-b border-slate-100">
                                <th class="pb-3 font-medium">Name</th>
                                <th class="pb-3 font-medium">Contact</th>
                                <th class="pb-3 font-medium">Date</th>
                                <th class="pb-3 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <?php 
                            $recentLeads = $pdo->query("SELECT * FROM leads ORDER BY created_at DESC LIMIT 5")->fetchAll();
                            if(count($recentLeads) > 0):
                                foreach($recentLeads as $lead): 
                            ?>
                            <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50 transition">
                                <td class="py-3 font-medium text-slate-800"><?php echo htmlspecialchars($lead['name']); ?></td>
                                <td class="py-3 text-slate-500">
                                    <div class="flex flex-col">
                                        <span><?php echo htmlspecialchars($lead['phone']); ?></span>
                                        <span class="text-xs text-slate-400"><?php echo htmlspecialchars($lead['email']); ?></span>
                                    </div>
                                </td>
                                <td class="py-3 text-slate-500"><?php echo date('M d, H:i', strtotime($lead['created_at'])); ?></td>
                                <td class="py-3"><span class="bg-green-100 text-green-600 px-2 py-1 rounded text-xs font-bold">New</span></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="4" class="py-4 text-center text-slate-400">No leads found yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>


            
             <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 h-fit">
                <h3 class="text-lg font-bold text-slate-800 mb-6">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="properties.php" class="block w-full text-center py-3 rounded-xl bg-slate-50 text-slate-600 hover:bg-slate-100 transition font-medium">Manage Properties</a>
                    <a href="leads.php" class="block w-full text-center py-3 rounded-xl bg-secondary text-white hover:bg-yellow-600 transition font-medium shadow-lg shadow-yellow-500/20">View All Leads</a>
                </div>
            </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('leadsChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            data: <?php echo json_encode($data); ?>,
            backgroundColor: ['#3b82f6', '#f59e0b', '#10b981', '#6366f1'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        },
        cutout: '70%'
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
