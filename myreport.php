<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$title = "My Reports";
$user_id = $_SESSION['userID'];
$active_tab = isset($_GET['tab']) && $_GET['tab'] === 'found' ? 'found' : 'lost';

// ==========================================
// Handle Status Updates (Found/Claimed)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $update_post_id = (int)$_POST['post_id'];
    $new_status = $_POST['new_status'];

    // Security check: Verify the user owns this post before updating
    $check_stmt = $connection->prepare("SELECT user_id FROM posts WHERE post_id = ?");
    $check_stmt->bind_param("i", $update_post_id);
    $check_stmt->execute();
    $check_res = $check_stmt->get_result();

    if ($check_res->num_rows > 0) {
        $post_owner = $check_res->fetch_assoc()['user_id'];
        if ($post_owner === $user_id) {
            // Update the status in the database
            $update_stmt = $connection->prepare("UPDATE posts SET currentStatus = ? WHERE post_id = ?");
            $update_stmt->bind_param("si", $new_status, $update_post_id);
            $update_stmt->execute();
        }
    }
    
    // Redirect to avoid form resubmission on page refresh
    $redirect_url = "myreport.php?tab=" . urlencode($active_tab);
    if (isset($_GET['category_id'])) {
        $redirect_url .= "&category_id=" . (int)$_GET['category_id'];
    }
    header("Location: " . $redirect_url);
    exit();
}
// ==========================================

// Category filter
$selected_cat = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// Fetch categories
$categories = $connection->query("SELECT category_id, categoryName FROM category");
$cats = [];
while ($row = $categories->fetch_assoc()) {
    $cats[] = $row;
}

// Fetch user's posts based on active tab + optional category filter
if ($selected_cat > 0) {
    $stmt = $connection->prepare(
        "SELECT p.post_id, p.itemName, p.publicDescription, p.currentStatus, p.dateReported,
                c.categoryName
         FROM posts p
         JOIN category c ON p.category_id = c.category_id
         WHERE p.user_id = ? AND p.type = ? AND p.category_id = ?
         ORDER BY p.dateReported DESC"
    );
    $type = strtoupper($active_tab);
    $stmt->bind_param("isi", $user_id, $type, $selected_cat);
} else {
    $stmt = $connection->prepare(
        "SELECT p.post_id, p.itemName, p.publicDescription, p.currentStatus, p.dateReported,
                c.categoryName
         FROM posts p
         JOIN category c ON p.category_id = c.category_id
         WHERE p.user_id = ? AND p.type = ?
         ORDER BY p.dateReported DESC"
    );
    $type = strtoupper($active_tab);
    $stmt->bind_param("is", $user_id, $type);
}
$stmt->execute();
$posts = $stmt->get_result();

// Colors per tab
$is_lost   = $active_tab === 'lost';
$color     = $is_lost ? '#dc3545' : '#28a745';
$hover     = $is_lost ? '#b02a37' : '#1e7e34';
$label     = $is_lost ? 'LOST' : 'FOUND';
$empty_msg = $is_lost ? 'You have not reported any lost items yet.' : 'You have not reported any found items yet.';
?>

<?php require_once 'header_footer/main-header.php'; ?>

<style>
    /* ── Outer shell: sidebar + content side by side ── */
    .page-wrapper {
        display: flex;
        align-items: flex-start;
        padding: 30px 20px;
        max-width: 1200px;
        margin: 0 auto;
        gap: 24px;
    }

    /* ── Main content area ── */
    .main-content {
        flex: 1;
        min-width: 0;
    }

    .page-top {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
    }
    .page-top h2 {
        font-weight: 700;
        margin: 0;
        font-size: 1.6rem;
    }

    /* Tabs */
    .tab-bar {
        display: flex;
        gap: 0;
        border-bottom: 2px solid #eee;
        margin-bottom: 24px;
    }
    .tab-btn {
        padding: 10px 28px;
        font-size: 0.9rem;
        font-weight: 600;
        text-decoration: none;
        color: #888;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        transition: all 0.15s;
        letter-spacing: 0.03em;
    }
    .tab-btn:hover { text-decoration: none; color: #444; }
    .tab-btn.active-lost  { color: #dc3545; border-bottom-color: #dc3545; }
    .tab-btn.active-found { color: #28a745; border-bottom-color: #28a745; }

    /* Category Filter */
    .category-filter {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 24px;
    }
    .cat-btn {
        border: 1.5px solid #ccc;
        background: #fff;
        border-radius: 20px;
        padding: 5px 16px;
        font-size: 0.85rem;
        font-weight: 500;
        color: #555;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.15s;
    }
    .cat-btn:hover { text-decoration: none; }
    .cat-btn.active { color: #fff; }

    /* Card Grid */
    .card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
    }
    .item-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .item-card-image {
        background: #e0e0e0;
        height: 160px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #999;
        font-size: 0.85rem;
        font-weight: 500;
        letter-spacing: 0.05em;
    }
    .item-card-body {
        padding: 12px 14px;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .item-card-name {
        font-weight: 600;
        font-size: 0.95rem;
        color: #222;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .item-card-meta {
        font-size: 0.78rem;
        color: #999;
    }
    .item-card-status {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .item-card-footer {
        padding: 0 14px 14px 14px;
    }

    /* Buttons */
    .btn-view {
        display: block;
        text-align: center;
        color: #fff;
        border-radius: 6px;
        padding: 7px;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        letter-spacing: 0.04em;
        transition: background 0.15s;
    }
    .btn-view:hover { color: #fff; text-decoration: none; }

    /* Action Button CSS */
    .btn-action {
        display: block;
        width: 100%;
        text-align: center;
        color: #fff;
        border-radius: 6px;
        padding: 7px;
        font-size: 0.85rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        letter-spacing: 0.04em;
        transition: opacity 0.15s;
    }
    .btn-action:hover { opacity: 0.8; }

    .empty-state {
        text-align: center;
        color: #aaa;
        padding: 60px 0;
        font-size: 1rem;
    }

    /* Collapse sidebar when closed */
    #sidebar.closed {
        display: none;
    }
</style>

    <?php include 'side-menu.php'; ?>
<div class="page-wrapper">

    <div class="main-content">

        <div class="page-top">
            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">← Back</a>
            <h2>My Reports</h2>
        </div>

        <div class="tab-bar">
            <a href="myreport.php?tab=lost"
               class="tab-btn <?php print($active_tab === 'lost' ? 'active-lost' : ''); ?>">
                Lost Items
            </a>
            <a href="myreport.php?tab=found"
               class="tab-btn <?php print($active_tab === 'found' ? 'active-found' : ''); ?>">
                Found Items
            </a>
        </div>

        <div class="category-filter">
            <a href="myreport.php?tab=<?php print($active_tab); ?>"
               class="cat-btn <?php print($selected_cat === 0 ? 'active' : ''); ?>"
               style="<?php print($selected_cat === 0 ? "background:{$color};border-color:{$color};" : "border-color:#ccc;"); ?>">
                All
            </a>
            <?php foreach ($cats as $cat): ?>
                <a href="myreport.php?tab=<?php print($active_tab); ?>&category_id=<?php print($cat['category_id']); ?>"
                   class="cat-btn <?php print($selected_cat === (int)$cat['category_id'] ? 'active' : ''); ?>"
                   style="<?php print($selected_cat === (int)$cat['category_id']
                       ? "background:{$color};border-color:{$color};"
                       : "border-color:#ccc;"); ?>"
                   onmouseover="this.style.borderColor='<?php print($color); ?>'; this.style.color='<?php print($selected_cat === (int)$cat['category_id'] ? '#fff' : $color); ?>'"
                   onmouseout="this.style.borderColor='<?php print($selected_cat === (int)$cat['category_id'] ? $color : '#ccc'); ?>'; this.style.color='<?php print($selected_cat === (int)$cat['category_id'] ? '#fff' : '#555'); ?>'">
                    <?php print(htmlspecialchars($cat['categoryName'])); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($posts->num_rows === 0): ?>
            <div class="empty-state"><?php print($empty_msg); ?></div>
        <?php else: ?>
            <div class="card-grid">
                <?php while ($post = $posts->fetch_assoc()): ?>
                    <div class="item-card">
                        <div class="item-card-image">NO IMAGE</div>
                        <div class="item-card-body">
                            <div class="item-card-name">
                                <?php print(htmlspecialchars($post['itemName'] ?? 'No item name')); ?>
                            </div>
                            <div class="item-card-meta" style="color:#888; font-size:0.8rem;">
                                <?php print(htmlspecialchars($post['categoryName'])); ?>
                            </div>
                            <div class="item-card-meta">
                                <?php print(date('M j, Y', strtotime($post['dateReported']))); ?>
                            </div>
                            <div class="item-card-status" style="color:<?php print($color); ?>">
                                <?php print(htmlspecialchars($post['currentStatus'])); ?>
                            </div>
                        </div>

                        <div class="item-card-footer">
                            <div style="display: flex; gap: 8px;">
                                <a href="view_post.php?post_id=<?php print($post['post_id']); ?>"
                                   class="btn-view"
                                   style="flex: 1; background:<?php print($color); ?>"
                                   onmouseover="this.style.background='<?php print($hover); ?>'"
                                   onmouseout="this.style.background='<?php print($color); ?>'">
                                    VIEW
                                </a>

                                <?php 
                                // Show "FOUND?" if tab is Lost and status isn't Found already
                                if ($is_lost && strtolower($post['currentStatus']) !== 'found'): 
                                ?>
                                    <form method="POST" style="flex: 1; margin: 0;">
                                        <input type="hidden" name="post_id" value="<?php print($post['post_id']); ?>">
                                        <input type="hidden" name="new_status" value="Found">
                                        <button type="submit" name="update_status" class="btn-action" style="background: #28a745;">
                                            FOUND?
                                        </button>
                                    </form>

                                <?php 
                                // Show "CLAIMED?" if tab is Found and status isn't Claimed already
                                elseif (!$is_lost && strtolower($post['currentStatus']) !== 'claimed'): 
                                ?>
                                    <form method="POST" style="flex: 1; margin: 0;">
                                        <input type="hidden" name="post_id" value="<?php print($post['post_id']); ?>">
                                        <input type="hidden" name="new_status" value="Claimed">
                                        <button type="submit" name="update_status" class="btn-action" style="background: #17a2b8;">
                                            CLAIMED?
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                            </div>
                        </div>

                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

    </div></div><?php require_once 'header_footer/footer.php'; ?>