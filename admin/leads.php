<?php
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$ctaLeads = $pdo->query("SELECT * FROM cta_leads ORDER BY timestamp DESC")->fetchAll();
?>
<div class="flex min-h-screen bg-slate-50">
    <!-- Sidebar -->
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 lg:ml-64 p-4 md:p-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold font-display text-slate-800">Lead Management</h1>
            <p class="text-slate-500">Track and manage all platform inquiries</p>
        </div>

        <div class="mb-10">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-envelope-open-text text-secondary"></i> Inquiry Leads
                </h2>
                
                <div class="flex items-center gap-3">
                    <!-- Filter Form -->
                    <form method="GET" class="flex items-center gap-2">
                        <select name="project_id" class="bg-white border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-secondary focus:border-secondary block p-2.5 outline-none" onchange="this.form.submit()">
                            <option value="">All Projects</option>
                            <?php 
                            // Fetch all properties for filter
                            $allProps = $pdo->query("SELECT id, title FROM properties ORDER BY title ASC")->fetchAll();
                            foreach($allProps as $p): 
                            ?>
                            <option value="<?php echo $p['id']; ?>" <?php echo (isset($_GET['project_id']) && $_GET['project_id'] == $p['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['title']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </form>

                    <!-- Export Button -->
                    <a href="export_leads.php<?php echo isset($_GET['project_id']) ? '?project_id=' . $_GET['project_id'] : ''; ?>" class="bg-emerald-500 hover:bg-emerald-600 text-white font-medium rounded-lg text-sm px-4 py-2.5 text-center inline-flex items-center gap-2 transition shadow-lg shadow-emerald-500/20">
                        <i class="fa-solid fa-file-excel"></i> Export Excel
                    </a>
                </div>
            </div>

            <?php
            // Build Query
            $params = [];
            $query = "SELECT l.*, p.title as project_title 
                      FROM leads l 
                      LEFT JOIN properties p ON l.property_id = p.id";
            
            if (isset($_GET['project_id']) && !empty($_GET['project_id'])) {
                $query .= " WHERE l.property_id = ?";
                $params[] = $_GET['project_id'];
            }
            
            $query .= " ORDER BY l.created_at DESC";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $leads = $stmt->fetchAll();
            ?>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                                <th class="py-4 pl-6 font-semibold">Date</th>
                                <th class="py-4 px-4 font-semibold">Name</th>
                                <th class="py-4 px-4 font-semibold">Project</th>
                                <th class="py-4 px-4 font-semibold">Contact</th>
                                <th class="py-4 px-4 font-semibold">Type</th>
                                <th class="py-4 pr-6 font-semibold">Message</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            <?php if (count($leads) > 0): ?>
                                <?php foreach ($leads as $lead): ?>
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="py-4 pl-6 text-slate-500 whitespace-nowrap"><?php echo date('M d, Y', strtotime($lead['created_at'])); ?></td>
                                        <td class="py-4 px-4 font-medium text-slate-800"><?php echo htmlspecialchars($lead['name']); ?></td>
                                        <td class="py-4 px-4">
                                            <?php if($lead['project_title']): ?>
                                                <span class="text-slate-800 font-medium block truncate max-w-[200px]" title="<?php echo htmlspecialchars($lead['project_title']); ?>">
                                                    <?php echo htmlspecialchars($lead['project_title']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-slate-400 italic">General Inquiry</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="flex flex-col">
                                                <span class="text-slate-700 flex items-center gap-2"><i class="fa-regular fa-envelope text-slate-400 text-xs"></i> <?php echo htmlspecialchars($lead['email']); ?></span>
                                                <?php if($lead['phone']): ?>
                                                    <span class="text-slate-500 flex items-center gap-2 mt-1"><i class="fa-solid fa-phone text-slate-400 text-xs"></i> <?php echo htmlspecialchars($lead['phone']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 whitespace-nowrap">
                                                <?php echo htmlspecialchars($lead['lead_type']); ?>
                                            </span>
                                        </td>
                                        <td class="py-4 pr-6 text-slate-600 italic truncate max-w-xs" title="<?php echo htmlspecialchars($lead['message']); ?>">
                                            "<?php echo htmlspecialchars($lead['message']); ?>"
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-400">No leads found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
