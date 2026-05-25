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

// ==========================================
// 1. HANDLE NEW COMMENT SUBMISSION
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_text'])) {
    $comment_text = trim($_POST['comment_text']);
    
    if (!empty($comment_text)) {
        $insert_stmt = $connection->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("iis", $post_id, $current_user_id, $comment_text);
        $insert_stmt->execute();
        $insert_stmt->close();
        
        // Redirect to the same page to prevent form resubmission on refresh (PRG pattern)
        header("Location: view_post.php?post_id=" . $post_id);
        exit();
    }
}

// ==========================================
// 2. FETCH POST DETAILS
// ==========================================
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

// ==========================================
// 3. FETCH COMMENTS
// ==========================================
$comments_stmt = $connection->prepare(
    "SELECT c.comment, c.created_at, u.full_name 
     FROM comments c
     JOIN users u ON c.user_id = u.user_id
     WHERE c.post_id = ?
     ORDER BY c.created_at ASC"
);
$comments_stmt->bind_param("i", $post_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();
$comments = $comments_result->fetch_all(MYSQLI_ASSOC);
$comments_stmt->close();
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
        margin-bottom: 2rem;
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

    /* --- Comments Section Styles --- */
    .comments-section {
        background: #fff;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 16px rgba(0,0,0,0.08);
    }
    .comments-header {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        color: #333;
        border-bottom: 1px solid #eee;
        padding-bottom: 0.5rem;
    }
    .comment-list {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        margin-bottom: 2rem;
    }
    .comment-item {
        background: #f9f9f9;
        padding: 1rem;
        border-radius: 8px;
        border-left: 3px solid #ddd;
    }
    .comment-meta {
        font-size: 0.8rem;
        color: #888;
        margin-bottom: 0.5rem;
    }
    .comment-author {
        font-weight: 700;
        color: #222;
    }
    .comment-text {
        font-size: 0.95rem;
        color: #444;
        line-height: 1.5;
    }
    .comment-form {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .comment-form textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        resize: vertical;
        min-height: 90px;
        font-family: inherit;
    }
    .comment-form textarea:focus {
        outline: none;
        border-color: <?= $type_color ?>;
        box-shadow: 0 0 0 2px rgba(0,0,0,0.05);
    }
    .comment-form button {
        align-self: flex-end;
    }
</style>

<div class="view-wrapper">
    <div class="view-header">
        <a href="<?= $back_page ?>" class="btn btn-outline-secondary btn-sm">← Back</a>
        <h2><?= $type_label === 'LOST' ? 'Lost Item Report' : 'Found Item Report' ?></h2>
        <span class="type-badge"><?= $type_label ?></span>
    </div>

    <div class="view-card">
        <div class="view-image">NO IMAGE</div>

        <div class="view-body">

            <div class="detail-row">
                <span class="detail-label">Item Name</span>
                <span class="detail-value"><?= htmlspecialchars($post['itemName']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Category</span>
                <span class="detail-value"><?= htmlspecialchars($post['categoryName']) ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Description</span>
                <span class="detail-value"><?= htmlspecialchars($post['publicDescription']) ?></span>
            </div>

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

            <div class="detail-row">
                <span class="detail-label"><?= $is_lost ? 'Lost At' : 'Found At' ?></span>
                <span class="detail-value"><?= htmlspecialchars($post['locationName']) ?> — <?= htmlspecialchars($post['zone']) ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Drop-off Point</span>
                <span class="detail-value"><?= htmlspecialchars($post['dropOff_id']) ?></span>
            </div>

            <div class="divider"></div>

            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="status-badge"><?= htmlspecialchars($post['currentStatus']) ?></span>
            </div>

            <div class="detail-row" style="margin-top: 1rem;">
                <span class="detail-label">Reported By</span>
                <span class="detail-value"><?= htmlspecialchars($post['full_name'] ?? 'Unknown') ?></span>
            </div>
        </div>

        <div class="view-footer">
            Reported on <?= date('F j, Y \a\t g:i A', strtotime($post['dateReported'])) ?>
        </div>
    </div>
    
    <div class="comments-section">
        <h3 class="comments-header">Comments (<?= count($comments) ?>)</h3>

        <div class="comment-list">
            <?php if (empty($comments)): ?>
                <p style="color: #777; font-size: 0.95rem; text-align: center; padding: 1rem 0;">
                    No comments yet. Be the first to start the conversation!
                </p>
            <?php else: ?>
                <?php foreach ($comments as $c): ?>
                    <div class="comment-item">
                        <div class="comment-meta">
                            <span class="comment-author"><?= htmlspecialchars($c['full_name']) ?></span> • 
                            <?= date('M j, Y \a\t g:i A', strtotime($c['created_at'])) ?>
                        </div>
                        <div class="comment-text">
                            <?= nl2br(htmlspecialchars($c['comment'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <form action="view_post.php?post_id=<?= $post_id ?>" method="POST" class="comment-form">
            <textarea name="comment_text" placeholder="Write a comment or provide an update..." required></textarea>
            <button type="submit" class="btn btn-primary btn-sm">Post Comment</button>
        </form>
    </div>

</div>

<?php require_once 'header_footer/footer.php'; ?>