<aside id="sidebar" class="sidebar closed">
    <div class="profile-section">
        <div class="profile-icon">
            <img src="images/solo_white.png" alt="User Icon">
        </div>

        <p class="profile-name">
            <?= htmlspecialchars($user['full_name']) ?>
        </p>
    </div>

    <a href="dashboard.php">Home</a>
    <a href="profile.php">Profile</a>
    <a href="myreports.php">My Reports</a>
    <a href="settings.php">Settings</a>
    <a href="map.php">Map</a>
    <a href="login.php">Logout</a>
</aside>