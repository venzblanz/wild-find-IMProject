<?php
// Fetch logged-in admin's name
$stmt = $connection->prepare("SELECT full_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['userID']);
$stmt->execute();
$user1 = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Detect current page for active highlight
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
/* ── Sidebar base ── */
.sidebar {
    width: 230px !important;
    background-color: #5A0A15 !important;
    padding: 0 !important;
    align-items: stretch !important;
    justify-content: flex-start;
    transition: width 0.3s ease !important;
    overflow: hidden;
    position: relative;
    z-index: 100;
    display: flex;
    flex-direction: column;
}
.sidebar.closed {
    width: 0 !important;
    padding: 0 !important;
}

/* Profile section */
.sidebar .profile-section {
    padding: 28px 20px 20px;
    border-bottom: 1px solid rgba(255,255,255,.12);
    margin-bottom: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0;
}
.sidebar .profile-icon {
    width: 72px; height: 72px;
    background: rgba(255,255,255,.15);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 10px;
    overflow: hidden;
}
.sidebar .profile-icon img {
    margin-top: 0 !important;
    width: 44px !important; height: 44px !important;
    border-radius: 0 !important;
    object-fit: contain;
    opacity: .85;
}
.sidebar .profile-name {
    font-size: 12px !important;
    font-weight: 700 !important;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #fff !important;
    margin: 0 !important;
    white-space: normal !important;
    text-align: center !important;
}

/* Admin chip under name */
.sidebar .admin-chip {
    display: inline-flex; align-items: center; gap: 4px;
    background: #C8193A;
    color: #fff;
    font-size: 9px; font-weight: 700; letter-spacing: .08em;
    text-transform: uppercase;
    padding: 2px 9px; border-radius: 20px;
    margin-top: 6px;
}
.sidebar .admin-chip i { font-size: 8px; }

/* Nav links */
.sidebar-nav-links {
    flex: 1;
    padding: 16px 10px;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.sidebar a,
.sidebar-nav-links a {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    padding: 11px 16px !important;
    border-radius: 10px !important;
    color: rgba(255,255,255,.7) !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    text-decoration: none !important;
    white-space: nowrap !important;
    text-align: left !important;
    transition: background .18s, color .18s !important;
}
.sidebar-nav-links a i {
    width: 18px;
    text-align: center;
    font-size: 15px;
    flex-shrink: 0;
}
.sidebar-nav-links a:hover {
    background: rgba(255,255,255,.10) !important;
    color: #fff !important;
}
.sidebar-nav-links a.active {
    background: #C8193A !important;
    color: #fff !important;
}

/* Divider */
.sidebar-nav-links .nav-divider {
    height: 1px;
    background: rgba(255,255,255,.10);
    margin: 8px 6px;
}

/* Help card */
.sidebar-help-card {
    margin: 10px 10px 16px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.14);
    border-radius: 12px;
    padding: 16px 14px;
    text-align: center;
    flex-shrink: 0;
}
.sidebar-help-card .help-ico {
    width: 34px; height: 34px;
    background: rgba(255,255,255,.15);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 8px;
}
.sidebar-help-card .help-ico i { color: rgba(255,255,255,.85); font-size: 14px; }
.sidebar-help-card h4 { font-size: 13px; font-weight: 700; color: #fff; margin: 0 0 3px; }
.sidebar-help-card p  { font-size: 11px; color: rgba(255,255,255,.6); margin: 0 0 10px; }
.sidebar-help-card a {
    display: block !important;
    background: transparent !important;
    border: 1.5px solid rgba(255,255,255,.35) !important;
    border-radius: 7px !important;
    color: #fff !important;
    font-size: 12px !important; font-weight: 600 !important;
    padding: 7px 0 !important;
    text-decoration: none !important;
    transition: background .18s !important;
    text-align: center !important;
    gap: 0 !important;
}
.sidebar-help-card a:hover {
    background: rgba(255,255,255,.12) !important;
}

/* Hide content when closed */
.sidebar.closed .profile-section,
.sidebar.closed .sidebar-nav-links,
.sidebar.closed .sidebar-help-card {
    opacity: 0;
    pointer-events: none;
}
</style>

<aside id="sidebar" class="sidebar closed">

    <!-- Profile -->
    <div class="profile-section">
        <div class="profile-icon">
            <img src="images/solo_white.png" alt="Admin Icon">
        </div>
        <p class="profile-name">
            <?= htmlspecialchars($user1['full_name'] ?? 'Admin') ?>
        </p>
        <span class="admin-chip"><i class="fas fa-shield-alt"></i> Admin</span>
    </div>

    <!-- Nav -->
    <nav class="sidebar-nav-links">
        <a href="admin-dashboard.php" class="<?= $currentPage === 'admin-dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-home"></i> Home
        </a>
        <a href="manage-users.php" class="<?= $currentPage === 'manage-users.php' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Manage Users
        </a>
        <a href="all-reports.php" class="<?= $currentPage === 'all-reports.php' ? 'active' : '' ?>">
            <i class="fas fa-clipboard-list"></i> All Reports
        </a>
        <a href="map.php" class="<?= $currentPage === 'map.php' ? 'active' : '' ?>">
            <i class="fas fa-map"></i> Map
        </a>

        <div class="nav-divider"></div>

        <a href="profile.php" class="<?= $currentPage === 'profile.php' ? 'active' : '' ?>">
            <i class="fas fa-user"></i> Profile
        </a>
        <a href="settings.php" class="<?= $currentPage === 'settings.php' ? 'active' : '' ?>">
            <i class="fas fa-cog"></i> Settings
        </a>
        <a href="login.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>

    <!-- Help Card -->
    <div class="sidebar-help-card">
        <div class="help-ico"><i class="fas fa-question"></i></div>
        <h4>Need Help?</h4>
        <p>Check our help center</p>
        <a href="#">Go to Help Center</a>
    </div>

</aside>