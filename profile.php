<?php
require_once 'connect.php';

// Redirect if not logged in
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$title = "Profile";
$user_id = $_SESSION['userID'];
$success_msg = '';
$error_msg = '';

// 1. UPDATED: Fetch current user full name and email
$stmt = $connection->prepare("SELECT full_name, institutionalEmail FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnSave'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Fetch stored hash
    $stmt = $connection->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!password_verify($current_password, $row['password'])) {
        $error_msg = "Current password is incorrect.";
    } elseif (strlen($new_password) < 8) {
        $error_msg = "New password must be at least 8 characters.";
    } elseif ($new_password !== $confirm_password) {
        $error_msg = "New passwords do not match.";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $connection->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashed, $user_id);
        if ($stmt->execute()) {
            $success_msg = "Password updated successfully.";
        } else {
            $error_msg = "Something went wrong. Please try again.";
        }
        $stmt->close();
    }
}
?>

<style>
    .profile-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 16px rgba(0,0,0,0.08);
        padding: 2rem;
        max-width: 480px;
        width: 100%;
        margin: 50px auto;
    }
    .profile-card h3 {
        text-align: center;
        margin-bottom: 1.5rem;
        font-weight: 600;
    }
    .section-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.75rem;
    }
    .divider {
        border-top: 1px solid #eee;
        margin: 1.25rem 0;
    }
    .input-wrapper {
        position: relative;
    }
    .toggle-eye {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: #aaa;
        padding: 0;
    }
    .toggle-eye:hover { color: #555; }
    .password-hint {
        font-size: 0.78rem;
        color: #999;
        margin-top: 3px;
    }
</style>

<div class="layout">
    <?php
    require_once 'side-menu.php';
    ?>
    <main id="mainContent" class="main-content">
        <?php
        require_once 'header_footer/main-header.php';
        ?>

        <div class="profile-card">
            <h3>My Profile</h3>

            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>

            <div class="form-group">
                <label>Full Name</label>
                <input type="text"
                    class="form-control"
                    value="<?= htmlspecialchars($user['full_name'] ?? '') ?>"
                    disabled>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email"
                    class="form-control"
                    value="<?= htmlspecialchars($user['institutionalEmail'] ?? '') ?>"
                    disabled>
            </div>

            <div class="divider"></div>
            <div class="section-label">Change Password</div>

            <form method="POST">
                <div class="form-group">
                    <label>Current Password</label>
                    <div class="input-wrapper">
                        <input type="password" class="form-control pr-5" id="current_password"
                            name="current_password" placeholder="Enter current password" required>
                        <button type="button" class="toggle-eye" onclick="togglePw('current_password', this)">👁</button>
                    </div>
                </div>

                <div class="form-group">
                    <label>New Password</label>
                    <div class="input-wrapper">
                        <input type="password" class="form-control pr-5" id="new_password"
                            name="new_password" placeholder="Enter new password" required>
                        <button type="button" class="toggle-eye" onclick="togglePw('new_password', this)">👁</button>
                    </div>
                    <small class="password-hint">Minimum 8 characters.</small>
                </div>

                <div class="form-group">
                    <label>Confirm New Password</label>
                    <div class="input-wrapper">
                        <input type="password" class="form-control pr-5" id="confirm_password"
                            name="confirm_password" placeholder="Re-enter new password" required>
                        <button type="button" class="toggle-eye" onclick="togglePw('confirm_password', this)">👁</button>
                    </div>
                </div>

                <button name="btnSave" type="submit" class="btn btn-primary btn-block">
                    Save Changes
                </button>
                
                <?php
                $backLink = (isset($_SESSION['is_admin']) && (int)$_SESSION['is_admin'] === 1)
                    ? 'admin-dashboard.php'
                    : 'dashboard.php';
                ?>

                <a href="<?= $backLink ?>" class="btn btn-secondary btn-block mt-2">
                    Go Back
                </a>
            </form>
        </div>
    </main>
</div>

<script>
    function togglePw(fieldId, btn) {
        const input = document.getElementById(fieldId);
        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = '🙈';
        } else {
            input.type = 'password';
            btn.textContent = '👁';
        }
    }
</script>

<?php require_once 'header_footer/footer.php'; ?>