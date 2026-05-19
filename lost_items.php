<?php
require_once 'connect.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$title = "Lost Items";

// Fetch categories
$categories = $connection->query("SELECT category_id, categoryName FROM category");
$cats = [];
while ($row = $categories->fetch_assoc()) {
    $cats[] = $row;
}

// Selected category filter
$selected_cat = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// Fetch lost posts
if ($selected_cat > 0) {
    $stmt = $connection->prepare(
        "SELECT p.post_id, p.itemName, p.publicDescription, p.currentStatus, p.dateReported,
            c.categoryName, u.full_name
         FROM posts p
         JOIN category c ON p.category_id = c.category_id
         JOIN users u ON p.user_id = u.user_id
         WHERE p.type = 'LOST' AND p.category_id = ?
         ORDER BY p.dateReported DESC"
    );
    $stmt->bind_param("i", $selected_cat);
    $stmt->execute();
    $posts = $stmt->get_result();
} else {
    $posts = $connection->query(
        "SELECT p.post_id, p.itemName, p.publicDescription, p.currentStatus, p.dateReported,
            c.categoryName, u.full_name
         FROM posts p
         JOIN category c ON p.category_id = c.category_id
         JOIN users u ON p.user_id = u.user_id
         WHERE p.type = 'LOST'
         ORDER BY p.dateReported DESC"
    );
}
?>

<?php require_once 'header_footer/main-header.php'; ?>

<style>
    .page-wrapper {
        padding: 30px 20px;
        max-width: 1100px;
        margin: 0 auto;
    }
    .page-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
    }
    .page-header h2 {
        font-weight: 700;
        margin: 0;
        font-size: 1.6rem;
    }
    .badge-lost {
        background: #dc3545;
        color: #fff;
        border-radius: 20px;
        padding: 4px 14px;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.04em;
    }

    /* Category Filter */
    .category-filter {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 28px;
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
    .cat-btn:hover {
        border-color: #dc3545;
        color: #dc3545;
        text-decoration: none;
    }
    .cat-btn.active {
        background: #dc3545;
        border-color: #dc3545;
        color: #fff;
    }

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
        color: #dc3545;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .item-card-footer {
        padding: 0 14px 14px 14px;
    }
    .btn-view {
        display: block;
        text-align: center;
        background: #dc3545;
        color: #fff;
        border-radius: 6px;
        padding: 7px;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.15s;
        letter-spacing: 0.04em;
    }
    .btn-view:hover {
        background: #b02a37;
        color: #fff;
        text-decoration: none;
    }

    .empty-state {
        text-align: center;
        color: #aaa;
        padding: 60px 0;
        font-size: 1rem;
    }
</style>

<div class="page-wrapper">
    <div class="page-header" style="gap: 12px; align-items: center; display: flex; margin-bottom: 24px;">
    <a href="dashboard.php" class="btn btn-outline-secondary btn-sm" style="margin-right: 8px;">← Back</a>
    <h2 style="margin: 0;">Found Items</h2>
    </div>

    <!-- Category Filter -->
    <div class="category-filter">
        <a href="lost_items.php" class="cat-btn <?= $selected_cat === 0 ? 'active' : '' ?>">All</a>
        <?php foreach ($cats as $cat): ?>
            <a href="lost_items.php?category_id=<?= $cat['category_id'] ?>"
               class="cat-btn <?= $selected_cat === (int)$cat['category_id'] ? 'active' : '' ?>">
                <?= htmlspecialchars($cat['categoryName']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Card Grid -->
    <?php if ($posts->num_rows === 0): ?>
        <div class="empty-state">No lost items reported yet.</div>
    <?php else: ?>
        <div class="card-grid">
            <?php while ($post = $posts->fetch_assoc()): ?>
                <div class="item-card">
                    <div class="item-card-image">NO IMAGE</div>
                    <div class="item-card-body">
                        <div class="item-card-name">
                            <?= htmlspecialchars($post['itemName'] ?? 'No item name') ?>
                        </div>
                        <div class="item-card-meta" style="color:#888; font-size:0.8rem;"><?= htmlspecialchars($post['categoryName']) ?></div>
                        <div class="item-card-meta">By: <?= htmlspecialchars($post['full_name'] ?? 'Unknown') ?></div>
                        <div class="item-card-status"><?= htmlspecialchars($post['currentStatus']) ?></div>
                    </div>
                    <div class="item-card-footer">
                        <a href="view_post.php?post_id=<?= $post['post_id'] ?>" class="btn-view">VIEW</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'header_footer/footer.php'; ?>