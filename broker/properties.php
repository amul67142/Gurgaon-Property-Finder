<?php
require_once __DIR__ . '/../includes/header.php';
requireBroker();

$brokerId = $_SESSION['user_id'];

// Delete Property
if (isset($_POST['delete_id'])) {
    $deleteId = intval($_POST['delete_id']);
    
    // Delete images before deleting the property
    deletePropertyImages($deleteId, $pdo);
    
    $stmt = $pdo->prepare("DELETE FROM properties WHERE id = ? AND broker_id = ?");
    $stmt->execute([$deleteId, $brokerId]);
    $success = "Property and associated images deleted successfully.";
}

// Fetch properties
$stmt = $pdo->prepare("SELECT * FROM properties WHERE broker_id = ? ORDER BY created_at DESC");
$stmt->execute([$brokerId]);
$properties = $stmt->fetchAll();
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
                <a href="properties.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-secondary text-white shadow-lg shadow-secondary/50"><i class="fa-solid fa-list w-5"></i> My Listings</a>
                <a href="add_property.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800 hover:text-white transition"><i class="fa-solid fa-plus-circle w-5"></i> Add Property</a>
                <a href="leads.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-800 hover:text-white transition"><i class="fa-solid fa-users w-5"></i> Leads</a>
                <div class="pt-8 mt-8 border-t border-slate-700">
                    <a href="<?php echo BASE_URL; ?>/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/10 transition"><i class="fa-solid fa-right-from-bracket w-5"></i> Logout</a>
                </div>
            </nav>
        </div>
    </aside>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="flex-1 lg:ml-64 p-4 md:p-8">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold font-display text-slate-800">My Listings</h1>
                <p class="text-slate-500">Manage your property portfolio</p>
            </div>
            <a href="add_property.php" class="bg-secondary text-white px-6 py-3 rounded-xl hover:bg-blue-600 transition shadow-lg shadow-blue-500/30 flex items-center gap-2">
                <i class="fa-solid fa-plus-circle"></i> Add New Property
            </a>
        </div>

        <?php if (isset($success)): ?>
            <div class="bg-green-50 text-green-600 p-4 rounded-xl mb-6 border border-green-100 flex items-center gap-3"><i class="fa-solid fa-check-circle"></i> <?php echo $success; ?></div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                            <th class="py-4 pl-6 font-semibold">Title</th>
                            <th class="py-4 px-4 font-semibold">Price</th>
                            <th class="py-4 px-4 font-semibold">Type</th>
                            <th class="py-4 px-4 font-semibold">Status</th>
                            <th class="py-4 px-4 font-semibold">Approval</th>
                            <th class="py-4 pr-6 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        <?php if (count($properties) > 0): ?>
                            <?php foreach ($properties as $prop): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-4 pl-6 font-medium text-slate-800"><?php echo htmlspecialchars($prop['title']); ?></td>
                                    <td class="py-4 px-4 text-slate-600"><?php echo formatPrice($prop['price']); ?></td>
                                    <td class="py-4 px-4 text-slate-500 capitalize"><?php echo ucwords($prop['type']); ?></td>
                                    <td class="py-4 px-4">
                                         <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold 
                                            <?php echo $prop['status'] === 'ready_to_move' ? 'bg-emerald-50 text-emerald-600' : 'bg-blue-50 text-blue-600'; ?>">
                                            <span class="w-1.5 h-1.5 rounded-full <?php echo $prop['status'] === 'ready_to_move' ? 'bg-emerald-500' : 'bg-blue-500'; ?>"></span>
                                            <?php echo ucwords(str_replace('_', ' ', $prop['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <?php if ($prop['is_approved']): ?>
                                            <span class="text-emerald-600 font-medium text-xs bg-emerald-50 px-2 py-1 rounded-md">Approved</span>
                                        <?php else: ?>
                                            <span class="text-amber-600 font-medium text-xs bg-amber-50 px-2 py-1 rounded-md">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 pr-6 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="edit_property.php?id=<?php echo $prop['id']; ?>" class="p-2 text-slate-400 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this property?');" class="inline-block">
                                                <input type="hidden" name="delete_id" value="<?php echo $prop['id']; ?>">
                                                <button type="submit" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition" title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fa-regular fa-folder-open text-4xl mb-3 opacity-20"></i>
                                        <p>No listings found. Add your first property!</p>
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
