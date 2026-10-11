<?php
/**
 * CraveHub - Built-in MongoDB Database Explorer
 */
$pageTitle = 'MongoDB Database Explorer';
$pageHeading = 'MongoDB Live Database Explorer';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/admin_header.php';

$db = getDB();
$dbStatus = $db->getStatusInfo();

$collectionsList = ['users', 'foods', 'orders', 'categories', 'coupons'];
$activeCollection = $_GET['collection'] ?? 'foods';

if (!in_array($activeCollection, $collectionsList)) {
    $activeCollection = 'foods';
}

$searchQuery = trim($_GET['search'] ?? '');
$filter = [];
if (!empty($searchQuery)) {
    $filter = [
        '$or' => [
            ['_id' => ['$regex' => $searchQuery, '$options' => 'i']],
            ['title' => ['$regex' => $searchQuery, '$options' => 'i']],
            ['name' => ['$regex' => $searchQuery, '$options' => 'i']],
            ['email' => ['$regex' => $searchQuery, '$options' => 'i']],
            ['customer_name' => ['$regex' => $searchQuery, '$options' => 'i']]
        ]
    ];
}

$documents = $db->find($activeCollection, $filter, ['limit' => 50, 'sort' => ['created_at' => -1]]);
$totalCount = $db->count($activeCollection);
?>

<!-- DB Connection Status Card -->
<div style="background: var(--admin-card); border: 1px solid var(--border-highlight); border-radius: var(--radius-lg); padding: 1.5rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(16, 185, 129, 0.15); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="fa-solid fa-database"></i>
        </div>
        <div>
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <h3 style="font-size: 1.15rem; font-weight: 800;">Database: <code>food_delivery_db</code></h3>
                <span class="badge-status badge-delivered" style="font-size: 0.72rem;">● Connected Live</span>
            </div>
            <div style="font-size: 0.85rem; color: var(--text-dim); margin-top: 0.2rem;">
                Host: <strong>mongodb://127.0.0.1:27017</strong> &bull; Engine: <strong><?php echo htmlspecialchars($dbStatus['engine']); ?></strong>
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 0.75rem;">
        <a href="<?php echo baseUrl('admin/index.php'); ?>" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Dashboard
        </a>
    </div>
</div>

<!-- Collections Navigation Tabs -->
<div style="display: flex; gap: 0.6rem; overflow-x: auto; padding-bottom: 0.75rem; margin-bottom: 1.5rem;">
    <?php foreach ($collectionsList as $col): ?>
        <?php $count = $db->count($col); ?>
        <a href="<?php echo baseUrl('admin/db_viewer.php?collection=' . $col); ?>" 
           class="btn <?php echo $activeCollection === $col ? 'btn-primary' : 'btn-secondary'; ?> btn-sm"
           style="border-radius: var(--radius-full); padding: 0.5rem 1.1rem;">
            <i class="fa-solid fa-folder"></i>
            <span><?php echo $col; ?></span>
            <span style="background: rgba(0,0,0,0.25); padding: 0.1rem 0.5rem; border-radius: 99px; font-size: 0.75rem;">
                <?php echo $count; ?>
            </span>
        </a>
    <?php endforeach; ?>
</div>

<!-- Search in Collection -->
<div style="background: var(--admin-card); border: 1px solid var(--admin-border); border-radius: var(--radius-md); padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <form action="" method="GET" style="display: flex; gap: 0.75rem; flex: 1; max-width: 450px;">
        <input type="hidden" name="collection" value="<?php echo htmlspecialchars($activeCollection); ?>">
        <input type="text" name="search" class="form-control" placeholder="Search in collection <?php echo htmlspecialchars($activeCollection); ?>..." value="<?php echo htmlspecialchars($searchQuery); ?>">
        <button type="submit" class="btn btn-secondary btn-sm">Search</button>
        <?php if (!empty($searchQuery)): ?>
            <a href="<?php echo baseUrl('admin/db_viewer.php?collection=' . $activeCollection); ?>" class="btn btn-outline btn-sm">Reset</a>
        <?php endif; ?>
    </form>

    <div style="font-size: 0.85rem; color: var(--text-dim);">
        Showing <strong><?php echo count($documents); ?></strong> of <strong><?php echo $totalCount; ?></strong> documents
    </div>
</div>

<!-- Documents Display Grid -->
<?php if (empty($documents)): ?>
    <div style="background: var(--admin-card); border: 1px solid var(--admin-border); border-radius: var(--radius-lg); padding: 3rem; text-align: center; color: var(--text-dim);">
        <i class="fa-solid fa-folder-open" style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--primary);"></i>
        <h4>No Documents Found</h4>
        <p style="font-size: 0.88rem; margin-top: 0.3rem;">No records matched your search query in collection <code><?php echo htmlspecialchars($activeCollection); ?></code>.</p>
    </div>
<?php else: ?>
    <div style="display: flex; flex-direction: column; gap: 1rem;">
        <?php foreach ($documents as $idx => $doc): ?>
            <?php
                $docId = (string)($doc['_id'] ?? $idx);
                $json = json_encode($doc, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            ?>
            <div style="background: var(--admin-card); border: 1px solid var(--admin-border); border-radius: var(--radius-md); overflow: hidden;">
                <!-- Document Header -->
                <div style="background: rgba(255,255,255,0.02); border-bottom: 1px solid var(--admin-border); padding: 0.75rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 0.6rem;">
                        <span style="color: var(--text-dim); font-size: 0.8rem; font-family: monospace;">#<?php echo $idx + 1; ?></span>
                        <span style="font-weight: 800; font-family: monospace; color: var(--primary); font-size: 0.95rem;">_id: "<?php echo htmlspecialchars($docId); ?>"</span>
                        <?php if (isset($doc['title'])): ?>
                            <span style="color: var(--text-muted); font-size: 0.85rem;">&bull; <strong><?php echo htmlspecialchars($doc['title']); ?></strong></span>
                        <?php elseif (isset($doc['name'])): ?>
                            <span style="color: var(--text-muted); font-size: 0.85rem;">&bull; <strong><?php echo htmlspecialchars($doc['name']); ?></strong> (<?php echo htmlspecialchars($doc['email'] ?? ''); ?>)</span>
                        <?php elseif (isset($doc['customer_name'])): ?>
                            <span style="color: var(--text-muted); font-size: 0.85rem;">&bull; <strong><?php echo htmlspecialchars($doc['customer_name']); ?></strong> (₹<?php echo number_format($doc['total_amount'] ?? 0, 2); ?>)</span>
                        <?php endif; ?>
                    </div>
                    
                    <button type="button" class="btn btn-secondary btn-sm" onclick="toggleJsonView('json-<?php echo $idx; ?>')" style="font-size: 0.75rem; padding: 0.25rem 0.65rem;">
                        <i class="fa-solid fa-code"></i> Toggle JSON
                    </button>
                </div>

                <!-- Document Body -->
                <div id="json-<?php echo $idx; ?>" style="padding: 1rem 1.25rem;">
                    <pre style="background: #060910; border: 1px solid rgba(255,255,255,0.05); border-radius: 8px; padding: 1rem; color: #a5b4fc; font-family: 'Consolas', 'Courier New', monospace; font-size: 0.85rem; overflow-x: auto; line-height: 1.5; margin: 0;"><?php echo htmlspecialchars($json); ?></pre>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
function toggleJsonView(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
