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

$title = "Settings";
$user_id = $_SESSION['userID'];
$success_msg = '';
$error_msg = '';

// Fetch current user info
$stmt = $connection->prepare("SELECT full_name, institutionalEmail FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Change display name
    if (isset($_POST['update_name'])) {
        $new_name = trim($_POST['full_name']);
        if (empty($new_name)) {
            $error_msg = "Name cannot be empty.";
        } else {
            $stmt = $connection->prepare("UPDATE users SET full_name = ? WHERE user_id = ?");
            $stmt->bind_param("si", $new_name, $user_id);
            $stmt->execute() ? $success_msg = "Name updated successfully." : $error_msg = "Failed to update name.";
            $stmt->close();
            $user['full_name'] = $new_name;
        }
    }

    // Change password
    if (isset($_POST['update_password'])) {
        $current_pw  = $_POST['current_password'];
        $new_pw      = $_POST['new_password'];
        $confirm_pw  = $_POST['confirm_password'];

        $stmt = $connection->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!password_verify($current_pw, $row['password'])) {
            $error_msg = "Current password is incorrect.";
        } elseif ($new_pw !== $confirm_pw) {
            $error_msg = "New passwords do not match.";
        } elseif (strlen($new_pw) < 6) {
            $error_msg = "Password must be at least 6 characters.";
        } else {
            $hashed = password_hash($new_pw, PASSWORD_DEFAULT);
            $stmt = $connection->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt->bind_param("si", $hashed, $user_id);
            $stmt->execute() ? $success_msg = "Password changed successfully." : $error_msg = "Failed to change password.";
            $stmt->close();
        }
    }
}
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

    /* Hide sidebar gap when closed */
    #sidebar.closed {
        display: none;
    }

    .settings-wrapper {
        max-width: 680px;
        padding: 0;
    }
    .settings-top {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 28px;
    }
    .settings-top h2 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
    }

    /* Section cards */
    .settings-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        margin-bottom: 20px;
        overflow: hidden;
    }
    .settings-card-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 16px 20px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        user-select: none;
    }
    .settings-card-header .section-icon {
        width: 34px; height: 34px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    .settings-card-header h5 {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        flex: 1;
    }
    .settings-card-header .chevron {
        font-size: 13px;
        color: #aaa;
        transition: transform 0.2s;
    }
    .settings-card-header.open .chevron {
        transform: rotate(180deg);
    }
    .settings-card-body {
        padding: 20px;
        display: none;
    }
    .settings-card-body.open {
        display: block;
    }

    /* Form elements */
    .form-group { margin-bottom: 16px; }
    .form-group label {
        font-size: 0.82rem;
        font-weight: 700;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: block;
        margin-bottom: 6px;
    }
    .form-control {
        width: 100%;
        padding: 9px 12px;
        border: 1.5px solid #e0e0e0;
        border-radius: 8px;
        font-size: 0.9rem;
        transition: border-color 0.15s;
        box-sizing: border-box;
    }
    .form-control:focus {
        border-color: #5A0A15;
        outline: none;
    }

    /* Toggle switch */
    .toggle-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #f5f5f5;
    }
    .toggle-row:last-child { border-bottom: none; }
    .toggle-row-info h6 {
        margin: 0 0 2px;
        font-size: 0.9rem;
        font-weight: 600;
    }
    .toggle-row-info p {
        margin: 0;
        font-size: 0.78rem;
        color: #999;
    }
    .toggle-switch {
        position: relative;
        width: 44px; height: 24px;
        flex-shrink: 0;
    }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute;
        inset: 0;
        background: #ddd;
        border-radius: 24px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .toggle-slider::before {
        content: '';
        position: absolute;
        width: 18px; height: 18px;
        left: 3px; top: 3px;
        background: #fff;
        border-radius: 50%;
        transition: transform 0.2s;
    }
    .toggle-switch input:checked + .toggle-slider { background: #5A0A15; }
    .toggle-switch input:checked + .toggle-slider::before { transform: translateX(20px); }

    /* About us */
    .about-logo {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 18px;
    }
    .about-logo-circle {
        width: 52px; height: 52px;
        background: #5A0A15;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
    }
    .about-logo-circle img { width: 32px; height: 32px; object-fit: contain; }
    .about-logo-text h4 { margin: 0; font-size: 1rem; font-weight: 700; }
    .about-logo-text span { font-size: 0.78rem; color: #999; }
    .about-desc { font-size: 0.88rem; color: #666; line-height: 1.6; margin-bottom: 16px; }
    .about-divider { border: none; border-top: 1px solid #f0f0f0; margin: 14px 0; }
    .about-detail-row {
        display: flex;
        justify-content: space-between;
        font-size: 0.85rem;
        padding: 6px 0;
        color: #555;
    }
    .about-detail-row span:first-child { color: #aaa; }

    /* Danger zone */
    .danger-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: #fff;
        border: 1.5px solid #dc3545;
        color: #dc3545;
        padding: 9px 18px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s, color 0.15s;
        text-decoration: none;
    }
    .danger-btn:hover { background: #dc3545; color: #fff; text-decoration: none; }

    /* Dark mode styles */
    body.dark-mode {
        background: #121212 !important;
        color: #e0e0e0 !important;
    }
    body.dark-mode .settings-card {
        background: #1e1e1e !important;
        box-shadow: 0 2px 12px rgba(0,0,0,0.3) !important;
    }
    body.dark-mode .settings-card-header { border-bottom-color: #2e2e2e !important; }
    body.dark-mode .toggle-row { border-bottom-color: #2a2a2a !important; }
    body.dark-mode .toggle-row-info h6 { color: #e0e0e0; }
    body.dark-mode .form-control {
        background: #2a2a2a !important;
        border-color: #444 !important;
        color: #e0e0e0 !important;
    }
    body.dark-mode .about-desc { color: #aaa; }
    body.dark-mode .about-detail-row { color: #ccc; }
    body.dark-mode .settings-top h2,
    body.dark-mode .settings-card-header h5,
    body.dark-mode .toggle-row-info h6 { color: #f0f0f0 !important; }
    body.dark-mode .about-logo-text h4 { color: #f0f0f0; }
</style>

    <?php include 'side-menu.php'; ?>
<div class="page-wrapper">

    <!-- Sidebar -->

    <!-- Main Content -->
    <div class="main-content">
        <div class="settings-wrapper">

            <div class="settings-top">
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">← Back</a>
                <h2>Settings</h2>
            </div>

            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>

            <!-- Account -->
            <div class="settings-card">
                <div class="settings-card-header open" onclick="toggleSection(this)">
                    <div class="section-icon" style="background:#fff3f5;">
                        <i class="fas fa-user" style="color:#5A0A15;"></i>
                    </div>
                    <h5>Account</h5>
                    <i class="fas fa-chevron-down chevron"></i>
                </div>
                <div class="settings-card-body open">
                    <form method="POST">
                        <div class="form-group">
                            <label>Display Name</label>
                            <input type="text" name="full_name" class="form-control"
                                   value="<?= htmlspecialchars($user['full_name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control"
                                   value="<?= htmlspecialchars($user['institutionalEmail']) ?>" disabled
                                   style="background:#f9f9f9; color:#aaa; cursor:not-allowed;">
                            <small style="color:#aaa; font-size:0.75rem;">Email cannot be changed.</small>
                        </div>
                        <button name="update_name" type="submit"
                                class="btn btn-sm"
                                style="background:#5A0A15; color:#fff; border:none; padding:8px 20px; border-radius:8px; font-weight:600;">
                            Save Changes
                        </button>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="settings-card">
                <div class="settings-card-header" onclick="toggleSection(this)">
                    <div class="section-icon" style="background:#fff3f5;">
                        <i class="fas fa-lock" style="color:#5A0A15;"></i>
                    </div>
                    <h5>Change Password</h5>
                    <i class="fas fa-chevron-down chevron"></i>
                </div>
                <div class="settings-card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button name="update_password" type="submit"
                                class="btn btn-sm"
                                style="background:#5A0A15; color:#fff; border:none; padding:8px 20px; border-radius:8px; font-weight:600;">
                            Update Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Preferences -->
            <div class="settings-card">
                <div class="settings-card-header" onclick="toggleSection(this)">
                    <div class="section-icon" style="background:#fff3f5;">
                        <i class="fas fa-sliders-h" style="color:#5A0A15;"></i>
                    </div>
                    <h5>Preferences</h5>
                    <i class="fas fa-chevron-down chevron"></i>
                </div>
                <div class="settings-card-body">
                    <div class="toggle-row">
                        <div class="toggle-row-info">
                            <h6>Dark Mode</h6>
                            <p>Switch to a darker interface</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="darkModeToggle">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <div class="toggle-row-info">
                            <h6>Email Notifications</h6>
                            <p>Get notified when someone responds to your report</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="emailNotifToggle" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <div class="toggle-row-info">
                            <h6>Show My Reports Publicly</h6>
                            <p>Let others see your name on reports</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="publicReportsToggle" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- About -->
            <div class="settings-card">
                <div class="settings-card-header" onclick="toggleSection(this)">
                    <div class="section-icon" style="background:#fff3f5;">
                        <i class="fas fa-info-circle" style="color:#5A0A15;"></i>
                    </div>
                    <h5>About</h5>
                    <i class="fas fa-chevron-down chevron"></i>
                </div>
                <div class="settings-card-body">
                    <div class="about-logo">
                        <div class="about-logo-circle">
                            <img src="images/solo_white.png" alt="Logo">
                        </div>
                        <div class="about-logo-text">
                            <h4>Wild Find</h4>
                            <span>Lost &amp; Found System</span>
                        </div>
                    </div>
                    <p class="about-desc">
                        Wild Find is a campus lost and found platform designed to help students and staff
                        report, track, and recover lost items quickly and efficiently.
                    </p>
                    <hr class="about-divider">
                    <div class="about-detail-row"><span>Version</span><span>1.0.0</span></div>
                    <div class="about-detail-row"><span>Developed by</span><span>Wild Find Team</span></div>
                    <div class="about-detail-row"><span>Contact</span><span>support@wildfind.com</span></div>
                    <div class="about-detail-row"><span>Built with</span><span>PHP · MySQL · Bootstrap</span></div>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="settings-card">
                <div class="settings-card-header" onclick="toggleSection(this)">
                    <div class="section-icon" style="background:#fff5f5;">
                        <i class="fas fa-exclamation-triangle" style="color:#dc3545;"></i>
                    </div>
                    <h5 style="color:#dc3545;">Danger Zone</h5>
                    <i class="fas fa-chevron-down chevron"></i>
                </div>
                <div class="settings-card-body">
                    <p style="font-size:0.88rem; color:#888; margin-bottom:16px;">
                        These actions are irreversible. Please proceed with caution.
                    </p>
                    <a href="logout.php" class="danger-btn" style="margin-right:10px; margin-bottom:10px;">
                        <i class="fas fa-sign-out-alt"></i> Log Out
                    </a>
                    <button class="danger-btn"
                            onclick="return confirm('Are you sure you want to delete your account? This cannot be undone.')">
                        <i class="fas fa-trash"></i> Delete Account
                    </button>
                </div>
            </div>

        </div>
    </div><!-- /.main-content -->

</div><!-- /.page-wrapper -->

<script>
function toggleSection(header) {
    header.classList.toggle('open');
    const body = header.nextElementSibling;
    body.classList.toggle('open');
}

// Dark mode — persisted in localStorage
const darkToggle = document.getElementById('darkModeToggle');
if (localStorage.getItem('darkMode') === 'true') {
    document.body.classList.add('dark-mode');
    darkToggle.checked = true;
}
darkToggle.addEventListener('change', function () {
    document.body.classList.toggle('dark-mode', this.checked);
    localStorage.setItem('darkMode', this.checked);
});

// Persist other toggles in localStorage
['emailNotifToggle', 'publicReportsToggle'].forEach(id => {
    const el = document.getElementById(id);
    if (localStorage.getItem(id) !== null) {
        el.checked = localStorage.getItem(id) === 'true';
    }
    el.addEventListener('change', () => localStorage.setItem(id, el.checked));
});
</script>

<?php require_once 'header_footer/footer.php'; ?>