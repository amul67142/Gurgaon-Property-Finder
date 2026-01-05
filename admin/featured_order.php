<?php
@session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
require_once __DIR__ . '/../includes/header.php';

// Fetch all featured properties
$stmt = $pdo->prepare("SELECT id, title, location, type, price, is_featured, sort_order 
                       FROM properties 
                       WHERE is_featured = 1 
                       ORDER BY sort_order ASC, created_at DESC");
$stmt->execute();
$featured = $stmt->fetchAll();
?>

<div class="flex min-h-screen bg-slate-50">
    <!-- Sidebar -->
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 lg:ml-64 p-4 md:p-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold font-display text-slate-800">Featured Order</h1>
            <p class="text-slate-500">Drag and drop properties to set their display order on the homepage slider.</p>
        </div>

        <div class="max-w-3xl">
            <div id="sortable-list" class="space-y-4">
                <?php if (count($featured) > 0): ?>
                    <?php foreach ($featured as $prop): 
                        $imagePath = get_property_cover($prop['id'], $pdo);
                    ?>
                        <div class="sortable-item bg-white p-4 rounded-xl shadow-sm border border-slate-100 flex items-center gap-4 cursor-move active:shadow-lg transition-all" data-id="<?php echo $prop['id']; ?>">
                            <div class="flex-shrink-0 text-slate-300">
                                <i class="fa-solid fa-grip-vertical text-xl"></i>
                            </div>
                            <div class="w-20 h-14 rounded-lg overflow-hidden border border-slate-100 flex-shrink-0">
                                <img src="<?php echo htmlspecialchars($imagePath); ?>" class="w-full h-full object-cover" onerror="this.onerror=null; this.src='<?php echo BASE_URL; ?>/assets/images/placeholder.jpg'">
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-slate-800 truncate"><?php echo htmlspecialchars($prop['title']); ?></h3>
                                <p class="text-xs text-slate-500 truncate"><?php echo htmlspecialchars($prop['location']); ?> • <?php echo formatPrice($prop['price']); ?></p>
                            </div>
                            <div class="hidden md:block">
                                <span class="bg-purple-100 text-purple-600 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">Featured</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="bg-white p-12 text-center rounded-2xl border-2 border-dashed border-slate-200">
                        <i class="fa-solid fa-star text-slate-200 text-5xl mb-4"></i>
                        <p class="text-slate-400">No featured properties found. Mark some properties as featured first.</p>
                        <a href="properties.php" class="mt-4 inline-block text-secondary font-bold hover:underline">Manage Properties</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (count($featured) > 0): ?>
                <div id="save-indicator" class="fixed bottom-8 right-8 bg-slate-900 text-white px-6 py-3 rounded-full shadow-2xl flex items-center gap-3 opacity-0 translate-y-4 transition-all duration-300 pointer-events-none z-50">
                    <i class="fa-solid fa-check-circle text-green-400"></i>
                    <span class="text-sm font-bold">Order Saved Automatically</span>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const list = document.getElementById('sortable-list');
    if (!list) return;

    new Sortable(list, {
        animation: 150,
        ghostClass: 'bg-slate-50',
        onEnd: function() {
            const order = Array.from(list.querySelectorAll('.sortable-item')).map(item => item.dataset.id);
            saveOrder(order);
        }
    });

    function saveOrder(order) {
        fetch('<?php echo BASE_URL; ?>/api/update_featured_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ order: order })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSaveIndicator();
            } else {
                alert('Error saving order: ' + data.message);
            }
        })
        .catch(err => {
            console.error('Fetch error:', err);
            alert('A connection error occurred.');
        });
    }

    function showSaveIndicator() {
        const indicator = document.getElementById('save-indicator');
        indicator.classList.remove('opacity-0', 'translate-y-4');
        indicator.classList.add('opacity-100', 'translate-y-0');
        
        setTimeout(() => {
            indicator.classList.remove('opacity-100', 'translate-y-0');
            indicator.classList.add('opacity-0', 'translate-y-4');
        }, 2000);
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
