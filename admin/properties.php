<?php
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

// Handle Approval / Disapproval / Deletion / Reordering
if (isset($_POST['action'])) {
    if (isset($_POST['id'])) {
        $id = intval($_POST['id']);
        if ($_POST['action'] === 'approve') {
            $stmt = $pdo->prepare("UPDATE properties SET is_approved = 1 WHERE id = ?");
            $stmt->execute([$id]);
            $success = "Property approved successfully.";
        } elseif ($_POST['action'] === 'disapprove') {
            $stmt = $pdo->prepare("UPDATE properties SET is_approved = 0 WHERE id = ?");
            $stmt->execute([$id]);
            $success = "Property disapproved successfully.";
        } elseif ($_POST['action'] === 'toggle_featured') {
            $stmt = $pdo->prepare("UPDATE properties SET is_featured = NOT is_featured WHERE id = ?");
            $stmt->execute([$id]);
            $success = "Featured status updated.";
        } elseif ($_POST['action'] === 'delete') {
            // Delete images before deleting the property
            deletePropertyImages($id, $pdo);
            
            $stmt = $pdo->prepare("DELETE FROM properties WHERE id = ?");
            $stmt->execute([$id]);
            $success = "Property and associated images deleted successfully.";
        }
    }
}

// Ensure database has sort_order column (Dynamic Migration)
try {
    $pdo->exec("ALTER TABLE properties ADD COLUMN IF NOT EXISTS sort_order INT DEFAULT 0");
} catch (PDOException $e) { /* Column might already exist */ }

// Fetch all properties with Broker info
$sql = "SELECT p.*, u.name as broker_name, u.email as broker_email 
        FROM properties p 
        JOIN users u ON p.broker_id = u.id";
$params = [];

if (isset($_GET['broker_id']) && !empty($_GET['broker_id'])) {
    $sql .= " WHERE p.broker_id = ?";
    $params[] = $_GET['broker_id'];
}

$sql .= " ORDER BY p.sort_order ASC, p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$properties = $stmt->fetchAll();
?>
<div class="flex min-h-screen bg-slate-50">
    <!-- Sidebar -->
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 lg:ml-64 p-4 md:p-8">
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold font-display text-slate-800">Manage Properties</h1>
                <p class="text-slate-500">Approve, Edit, or Delete listings</p>
            </div>
            <a href="../broker/add_property.php" class="bg-secondary text-white px-5 py-2.5 rounded-xl font-medium shadow-lg shadow-secondary/20 hover:bg-yellow-600 transition flex items-center gap-2">
                <i class="fa-solid fa-plus-circle"></i> Add Property
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
                            <th class="py-4 pl-6 font-semibold">ID</th>
                            <th class="py-4 px-4 font-semibold">Title</th>
                            <th class="py-4 px-4 font-semibold">Broker</th>
                            <th class="py-4 px-4 font-semibold">Price</th>
                            <th class="py-4 px-4 font-semibold">Status</th>
                            <th class="py-4 px-4 font-semibold">Featured</th>
                            <th class="py-4 pr-6 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        <?php if (count($properties) > 0): ?>
                            <?php foreach ($properties as $prop): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-4 pl-6 text-slate-500">#<?php echo $prop['id']; ?></td>
                                    <td class="py-4 px-4 font-medium text-slate-800"><?php echo htmlspecialchars($prop['title']); ?></td>
                                    <td class="py-4 px-4">
                                        <div class="flex flex-col">
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium text-slate-700"><?php echo htmlspecialchars($prop['broker_name']); ?></span>
                                                <?php if(!empty($prop['ad_broker_name'])): ?>
                                                    <span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-600 text-[10px] font-bold border border-blue-100 uppercase tracking-tighter" title="Ad Alias: <?php echo htmlspecialchars($prop['ad_broker_name']); ?>">Ad</span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="text-xs text-slate-400"><?php echo htmlspecialchars($prop['broker_email']); ?></span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-slate-600"><?php echo formatPrice($prop['price']); ?></td>
                                    <td class="py-4 px-4">
                                        <?php if ($prop['is_approved']): ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-600">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Approved
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-600">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-4">
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $prop['id']; ?>">
                                            <input type="hidden" name="action" value="toggle_featured">
                                            <button type="submit" class="focus:outline-none">
                                                <?php if ($prop['is_featured']): ?>
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-600 border border-purple-100">
                                                        <i class="fa-solid fa-star"></i> Featured
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-50 text-slate-400 border border-slate-100 opacity-60 hover:opacity-100 transition">
                                                        <i class="fa-regular fa-star"></i> Standard
                                                    </span>
                                                <?php endif; ?>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="py-4 pr-6 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <form method="POST" style="display:inline;" class="flex items-center gap-2">
                                                <input type="hidden" name="id" value="<?php echo $prop['id']; ?>">
                                                
                                                <?php if (!$prop['is_approved']): ?>
                                                    <button type="submit" name="action" value="approve" class="bg-emerald-50 text-emerald-600 hover:bg-emerald-100 px-3 py-1.5 rounded-md text-xs font-bold transition">
                                                        Approve
                                                    </button>
                                                <?php else: ?>
                                                    <button type="submit" name="action" value="disapprove" class="bg-amber-50 text-amber-600 hover:bg-amber-100 px-3 py-1.5 rounded-md text-xs font-bold transition">
                                                        Disapprove
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <a href="../broker/edit_property.php?id=<?php echo $prop['id']; ?>" class="p-2 text-slate-400 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>

                                                <button type="submit" name="action" value="delete" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition" onclick="return confirm('Are you sure?')" title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                            <a href="<?php echo BASE_URL; ?>/property-details.php?id=<?php echo $prop['id']; ?>" target="_blank" class="p-2 text-slate-400 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition" title="View Property">
                                                <i class="fa-solid fa-external-link"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <p>No properties found.</p>
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
