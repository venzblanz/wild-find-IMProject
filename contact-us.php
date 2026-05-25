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

$title = "Contact Us";
$success_msg = "";

// Handle the contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    // In a full implementation, you could save this to a 'messages' table 
    // or send an email. For now, we simulate a successful submission.
    $success_msg = "Thank you! Your message has been sent to the Wild Find administration.";
}
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

    /* Contact Layout Split */
    .contact-container {
        display: flex;
        flex-wrap: wrap;
        gap: 24px;
    }

    .contact-info-card, .contact-form-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        padding: 30px;
        box-sizing: border-box;
    }

    .contact-info-card {
        flex: 1;
        min-width: 280px;
    }

    .contact-form-card {
        flex: 2;
        min-width: 320px;
    }

    .info-group {
        margin-bottom: 20px;
    }

    .info-group h4 {
        margin: 0 0 5px 0;
        font-size: 1rem;
        font-weight: 600;
        color: #222;
    }

    .info-group p {
        margin: 0;
        font-size: 0.92rem;
        color: #666;
        line-height: 1.5;
    }

    /* Form Fields Styling */
    .form-group {
        margin-bottom: 18px;
    }

    .form-group label {
        display: block;
        font-size: 0.88rem;
        font-weight: 600;
        color: #444;
        margin-bottom: 6px;
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        font-size: 0.92rem;
        border: 1.5px solid #ccc;
        border-radius: 6px;
        box-sizing: border-box;
        transition: border-color 0.15s;
    }

    .form-control:focus {
        border-color: #dc3545; /* Maroon accent focus */
        outline: none;
    }

    .btn-submit {
        background: #dc3545; /* Maroon brand color */
        color: #fff;
        border: none;
        padding: 10px 24px;
        font-size: 0.9rem;
        font-weight: 600;
        border-radius: 6px;
        cursor: pointer;
        letter-spacing: 0.03em;
        transition: background 0.15s;
    }

    .btn-submit:hover {
        background: #b02a37;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        padding: 12px;
        border-radius: 6px;
        font-size: 0.9rem;
        margin-bottom: 20px;
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
            <h2>Contact Us</h2>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div class="alert-success">
                <?php echo htmlspecialchars($success_msg); ?>
            </div>
        <?php endif; ?>

        <div class="contact-container">
            
            <div class="contact-info-card">
                <h3 style="margin-top:0; font-size:1.3rem; font-weight:700;">Campus Information</h3>
                <p style="color:#666; font-size:0.9rem; line-height:1.5; margin-bottom:24px;">
                    Have questions regarding a claimed item, or need to reach out directly to the Property and Safety office? Use the information below.
                </p>

                <div class="info-group">
                    <h4>Office Location</h4>
                    <p>CIT-U Safety & Security Command Center</p>
                    <p>Ground Floor, Main Building</p>
                    <p>N. Bacalso Avenue, Cebu City, 6000</p>
                </div>

                <div class="info-group">
                    <h4>Email Address</h4>
                    <p>wildfind@cit.edu</p>
                    <p>support.property@cit.edu</p>
                </div>

                <div class="info-group">
                    <h4>Office Hours</h4>
                    <p>Monday – Saturday</p>
                    <p>8:00 AM – 5:00 PM</p>
                </div>
            </div>

            <div class="contact-form-card">
                <h3 style="margin-top:0; font-size:1.3rem; font-weight:700;">Get in Touch</h3>
                <p style="color:#666; font-size:0.9rem; margin-bottom:24px;">
                    Submit an inquiry or report a platform issue directly to our administrators.
                </p>

                <form method="POST" action="contact_us.php">
                    <div class="form-group">
                        <label for="subject">Subject / Inquiry Type</label>
                        <select id="subject" name="subject" class="form-control" required>
                            <option value="General Inquiry">General Inquiry</option>
                            <option value="Disputed Claim">Disputed Item Claim</option>
                            <option value="Technical Bug">Report a Website Bug</option>
                            <option value="Account Issue">Account / Login Issues</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message">Your Message</label>
                        <textarea id="message" name="message" class="form-control" rows="5" placeholder="Describe your concern in detail..." required></textarea>
                    </div>

                    <button type="submit" name="send_message" class="btn-submit">
                        Send Message
                    </button>
                </form>
            </div>

        </div></div></div><?php require_once 'header_footer/footer.php'; ?>