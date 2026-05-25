<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if the user is not authenticated
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$title = "About Us";
?>

<?php require_once 'header_footer/pre-header.php'; ?>

<style>
    /* ── Outer shell: sidebar + content side by side ── */
    .page-wrapper {
        display: flex;
        align-items: flex-start;
        padding: 30px 20px;
        width: 100%;
        max-width: 1400px;
        margin: 0 auto;
        gap: 24px;
        box-sizing: border-box;
        overflow-x: hidden;
    }

    /* ── Main content area ── */
    .main-content {
        flex: 1 1 auto;
        width: 100%;
        min-width: 0;
        transition: margin 0.3s ease, width 0.3s ease;
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

    /* About Us Content Styling */
    .about-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        padding: 30px;
        margin-bottom: 24px;
    }

    .brand-section {
        display: flex;
        align-items: center;
        gap: 20px;
        border-bottom: 2px solid #eee;
        padding-bottom: 20px;
        margin-bottom: 24px;
    }

    .brand-logos {
        display: flex;
        gap: 10px;
    }

    .brand-logos img {
        height: 60px;
        width: auto;
        object-fit: contain;
    }

    .brand-text h3 {
        margin: 0;
        font-size: 1.8rem;
        color: #222;
        font-weight: 700;
    }

    .brand-text p {
        margin: 5px 0 0 0;
        color: #666;
        font-size: 1rem;
    }

    .about-section-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #222;
        margin-top: 20px;
        margin-bottom: 10px;
        letter-spacing: 0.02em;
    }

    .about-text {
        color: #555;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 15px;
    }

    .feature-box {
        background: #f9f9f9;
        border-left: 4px solid #dc3545; /* Matches lost tab color scheme */
        padding: 15px;
        border-radius: 0 8px 8px 0;
    }

    .feature-box h4 {
        margin: 0 0 8px 0;
        font-size: 1rem;
        font-weight: 600;
        color: #222;
    }

    .feature-box p {
        margin: 0;
        font-size: 0.88rem;
        color: #666;
        line-height: 1.4;
    }

    #sidebar.closed {
        display: none;
    }
</style>

<div class="page-wrapper">

    <?php include 'side-menu.php'; ?>

    <div class="main-content">

        <div class="page-top">
            <a href="login.php" class="btn btn-outline-secondary btn-sm">← Back</a>
            <h2>About Us</h2>
        </div>

        <div class="about-card">
            
            <div class="brand-section">
                <div class="brand-logos">
                    <img src="images/cit-logo.png" alt="CIT Logo">
                </div>
                <div class="brand-text">
                    <h3>Wild Find</h3>
                    <p>Lost Today, Found the Maroon Way.</p>
                </div>
            </div>

            <div class="about-section-title">Our Mission</div>
            <p class="about-text">
                Wild Find is a dedicated digital platform created specifically for the Cebu Institute of Technology - University community. Our goal is to streamline, modernize, and simplify the process of reporting lost properties and returning found assets to their rightful owners within the campus. We aim to bridge the gap between finders and losers through an efficient, transparent, and secure online environment.
            </p>

            <div class="about-section-title">How It Helps Technologians</div>
            <p class="about-text">
                Misplacing personal belongings on a busy university campus can be stressful. Wild Find takes away the guesswork by offering an organized web-based notice board where updates happen in real-time.
            </p>

            <div class="features-grid">
                <div class="feature-box">
                    <h4>Report Instantly</h4>
                    <p>Quickly fill out a lost or found report with item descriptions, categories, and locations to alert the community immediately.</p>
                </div>
                <div class="feature-box" style="border-left-color: #28a745;"> <h4>Filtered Browsing</h4>
                    <p>Easily filter item feeds by specific categories to trace your missing books, gadgets, keys, or uniform pieces efficiently.</p>
                </div>
                <div class="feature-box" style="border-left-color: #17a2b8;">
                    <h4>Accountability</h4>
                    <p>Secure login and real-time status updates (such as "Found" or "Claimed") guarantee that listings stay completely up to date.</p>
                </div>
            </div>

            <div class="about-section-title" style="margin-top: 30px;">Platform Integrity</div>
            <p class="about-text" style="margin-bottom: 0;">
                To maintain a safe, dependable space, all user profiles are strictly registered and tied to verified accounts. System administrators closely monitor and manage user interactions to prevent misinformation, ensuring that Wild Find remains a helpful and trusted network for all Technologians.
            </p>

        </div></div></div><?php require_once 'header_footer/footer.php'; ?>