<?php
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

// Handle Delete
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    // Prevent deleting self (assuming session user id is set)
    if ($id != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $success = "User deleted successfully.";
    } else {
        $error = "You cannot delete your own account.";
    }
}

// Fetch Users
// Filter Logic
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$sql = "SELECT * FROM users";
$params = [];

if ($filter === 'broker') {
    $sql .= " WHERE role = 'broker' AND (seller_type IS NULL OR seller_type = 'Broker')";
} elseif ($filter === 'developer') {
    $sql .= " WHERE role = 'broker' AND seller_type = 'Developer'";
} elseif ($filter === 'user') {
    $sql .= " WHERE role = 'user'";
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<div class="flex min-h-screen bg-slate-50">
    <!-- Sidebar -->
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 lg:ml-64 p-4 md:p-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold font-display text-slate-800">User Management</h1>
            <p class="text-slate-500">Manage all registered users and brokers.</p>
        </div>

        <!-- Filter Tabs -->
        <div class="flex flex-wrap gap-2 mb-6">
            <a href="?filter=all" class="px-5 py-2.5 rounded-full text-sm font-bold transition <?php echo $filter === 'all' ? 'bg-slate-900 text-white shadow-lg' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-100'; ?>">
                All Users
            </a>
            <a href="?filter=developer" class="px-5 py-2.5 rounded-full text-sm font-bold transition <?php echo $filter === 'developer' ? 'bg-secondary text-white shadow-lg shadow-secondary/30' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-100'; ?>">
                <i class="fa-solid fa-helmet-safety mr-1"></i> Developers
            </a>
            <a href="?filter=broker" class="px-5 py-2.5 rounded-full text-sm font-bold transition <?php echo $filter === 'broker' ? 'bg-orange-500 text-white shadow-lg shadow-orange-500/30' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-100'; ?>">
                <i class="fa-solid fa-user-tie mr-1"></i> Brokers
            </a>
            <a href="?filter=user" class="px-5 py-2.5 rounded-full text-sm font-bold transition <?php echo $filter === 'user' ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/30' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-100'; ?>">
                <i class="fa-solid fa-users mr-1"></i> Users
            </a>
        </div>

        <?php if (isset($success)): ?>
            <div class="bg-green-50 text-green-600 p-4 rounded-xl mb-6 border border-green-100 flex items-center gap-3"><i class="fa-solid fa-check-circle"></i> <?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 border border-red-100 flex items-center gap-3"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                            <th class="py-4 pl-6 font-semibold">ID</th>
                            <th class="py-4 px-4 font-semibold">Name</th>
                            <th class="py-4 px-4 font-semibold">Email</th>
                            <th class="py-4 px-4 font-semibold">Role</th>
                            <th class="py-4 px-4 font-semibold">Joined</th>
                            <th class="py-4 px-4 font-semibold">Listings</th>
                            <th class="py-4 pr-6 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        <?php foreach ($users as $user): 
                            // Count listings
                            $listCount = $pdo->query("SELECT COUNT(*) FROM properties WHERE broker_id = " . $user['id'])->fetchColumn();
                        ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-4 pl-6 text-slate-500">#<?php echo $user['id']; ?></td>
                            <td class="py-4 px-4 font-medium text-slate-800"><?php echo htmlspecialchars($user['name']); ?></td>
                            <td class="py-4 px-4 text-slate-600"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="py-4 px-4">
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800">Admin</span>
                                <?php elseif ($user['role'] === 'broker'): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-orange-100 text-orange-800">Broker</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">User</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-4 text-slate-500"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td class="py-4 px-4">
                                <?php if ($listCount > 0): ?>
                                <a href="properties.php?broker_id=<?php echo $user['id']; ?>" class="text-xs font-bold text-secondary hover:text-blue-700 hover:underline">
                                    <?php echo $listCount; ?> Properties
                                </a>
                                <?php else: ?>
                                <span class="text-xs text-slate-400">None</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 pr-6 text-right">
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user? This cannot be undone.');">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="text-slate-400 hover:text-red-500 transition px-2 py-1 rounded hover:bg-red-50">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
