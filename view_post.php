<?php
require_once 'connect.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$title = "View Post";
$current_user_id = $_SESSION['userID'];

// Get post_id from URL
if (!isset($_GET['post_id'])) {
    header("Location: dashboard.php");
    exit();
}

$post_id = (int)$_GET['post_id'];

// Fetch post details
$stmt = $connection->prepare(
    "SELECT p.*, c.categoryName, l.locationName, l.zone, u.full_name, u.user_id as poster_id, u.is_admin as poster_is_admin
     FROM posts p
     JOIN category c ON p.category_id = c.category_id
     JOIN location l ON p.location_id = l.location_id
     JOIN users u ON p.user_id = u.user_id
     WHERE p.post_id = ?"
);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) {
    header("Location: dashboard.php");
    exit();
}

// Check if current user is the poster or an admin
$stmt = $connection->prepare("SELECT is_admin FROM users WHERE user_id = ?");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$current_user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$is_owner = ($current_user_id === $post['poster_id']);
$is_admin = ($current_user['is_admin'] == 1);
$can_see_private = $is_owner || $is_admin;

$is_lost  = ($post['type'] === 'LOST');
$is_found = ($post['type'] === 'FOUND');
$type_label  = $is_lost ? 'LOST' : 'FOUND';
$type_color  = $is_lost ? '#dc3545' : '#28a745';
$back_page   = $is_lost ? 'lost_items.php' : 'found_items.php';
?>

<?php require_once 'header_footer/pre-header.php'; ?>

<style>
    .view-wrapper {
        padding: 30px 20px;
        max-width: 680px;
        margin: 0 auto;
    }
    .view-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
    }
    .view-header h2 {
        font-weight: 700;
        margin: 0;
        font-size: 1.5rem;
        flex: 1;
    }
    .type-badge {
        border-radius: 20px;
        padding: 4px 14px;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        color: #fff;
        background: <?= $type_color ?>;
    }
    .view-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 16px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    .view-image {
        background: #e0e0e0;
        height: 220px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #999;
        font-size: 0.9rem;
        font-weight: 500;
        letter-spacing: 0.05em;
    }
    .view-body {
        padding: 1.5rem;
    }
    .detail-row {
        display: flex;
        flex-direction: column;
        margin-bottom: 1.1rem;
    }
    .detail-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #aaa;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 4px;
    }
    .detail-value {
        font-size: 0.95rem;
        color: #222;
        line-height: 1.5;
    }
    .detail-value.private {
        background: #fff8e1;
        border-left: 3px solid #ffc107;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 0.9rem;
        color: #555;
    }
    .private-notice {
        background: #fff8e1;
        border-left: 3px solid #ffc107;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 0.85rem;
        color: #888;
        font-style: italic;
    }
    .divider {
        border-top: 1px solid #eee;
        margin: 1rem 0;
    }
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        color: #fff;
        background: <?= $type_color ?>;
    }
    .view-footer {
        padding: 1rem 1.5rem;
        background: #f9f9f9;
        border-top: 1px solid #eee;
        font-size: 0.8rem;
        color: #aaa;
    }
</style>

<div class="view-wrapper">
    <!-- Header -->
    <div class="view-header">
        <a href="<?= $back_page ?>" class="btn btn-outline-secondary btn-sm">← Back</a>
        <h2><?= $type_label === 'LOST' ? 'Lost Item Report' : 'Found Item Report' ?></h2>
        <span class="type-badge"><?= $type_label ?></span>
    </div>

    <div class="view-card">
        <!-- Image Placeholder -->
        <div class="view-image">NO IMAGE</div>

        <div class="view-body">

            <!-- Item Name -->
            <div class="detail-row">
                <span class="detail-label">Item Name</span>
                <span class="detail-value"><?= htmlspecialchars($post['itemName']) ?></span>
            </div>
            <!-- Category -->
            <div class="detail-row">
                <span class="detail-label">Category</span>
                <span class="detail-value"><?= htmlspecialchars($post['categoryName']) ?></span>
            </div>

            <!-- Description -->
            <div class="detail-row">
                <span class="detail-label">Description</span>
                <span class="detail-value"><?= htmlspecialchars($post['publicDescription']) ?></span>
            </div>

            <!-- Private Details -->
            <div class="detail-row">
                <span class="detail-label">Private Details
                    <?php if ($can_see_private): ?>
                        <span style="color:#ffc107;">★ Only visible to you and admins</span>
                    <?php endif; ?>
                </span>
                <?php if ($can_see_private): ?>
                    <span class="detail-value private">
                        <?= !empty($post['privateDetails']) ? htmlspecialchars($post['privateDetails']) : 'None provided.' ?>
                    </span>
                <?php else: ?>
                    <span class="private-notice">🔒 Private details are only visible to the reporter and admins.</span>
                <?php endif; ?>
            </div>

            <div class="divider"></div>

            <!-- Location -->
            <div class="detail-row">
                <span class="detail-label"><?= $is_lost ? 'Lost At' : 'Found At' ?></span>
                <span class="detail-value"><?= htmlspecialchars($post['locationName']) ?> — <?= htmlspecialchars($post['zone']) ?></span>
            </div>

            <!-- Drop-off Point -->
            <div class="detail-row">
                <span class="detail-label">Drop-off Point</span>
                <span class="detail-value"><?= htmlspecialchars($post['dropOff_id']) ?></span>
            </div>

            <div class="divider"></div>

            <!-- Status -->
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="status-badge"><?= htmlspecialchars($post['currentStatus']) ?></span>
            </div>

            <!-- Reported By -->
            <div class="detail-row" style="margin-top: 1rem;">
                <span class="detail-label">Reported By</span>
                <span class="detail-value"><?= htmlspecialchars($post['full_name'] ?? 'Unknown') ?></span>
            </div>
        </div>

        <!-- Footer: date -->
        <div class="view-footer">
            Reported on <?= date('F j, Y \a\t g:i A', strtotime($post['dateReported'])) ?>
        </div>
    </div>
</div>

<?php require_once 'header_footer/footer.php'; ?>