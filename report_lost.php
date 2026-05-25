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

$title = "Report Lost Item";
$success_msg = '';
$error_msg = '';

$locations  = $connection->query("SELECT location_id, locationName, zone FROM location");
$categories = $connection->query("SELECT category_id, categoryName FROM category");
$dropoffs   = $connection->query("SELECT dropOff_id, dropOffPointName FROM dropoffpoint");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnSubmit'])) {
    $user_id          = $_SESSION['userID'];
    $location_id      = $_POST['location_id'];
    $category_id      = $_POST['category_id'];
    $dropOff_id       = $_POST['dropOff_id'];
    $type             = 'LOST';
    $publicDesc       = trim($_POST['publicDescription']);
    $specificLocation = trim($_POST['specificLocation']);
    $privateDetails   = '';
    $currentStatus    = 'Searching';
    $dateReported     = date('Y-m-d H:i:s');
    $itemName         = trim($_POST['itemName']);

    if (empty($publicDesc)) {
        $error_msg = "Please provide a description of the lost item.";
    } else {
        $stmt = $connection->prepare(
            "INSERT INTO posts (user_id, location_id, specific_location, category_id, dropOff_id, type, itemName, publicDescription, privateDetails, currentStatus, dateReported)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        // i  i  s                i            i          s     s         s          s               s              s
        $stmt->bind_param("iisiissssss",
            $user_id, $location_id, $specificLocation, $category_id, $dropOff_id,
            $type, $itemName, $publicDesc, $privateDetails, $currentStatus, $dateReported
        );

        if ($stmt->execute()) {
            $success_msg = "Lost item report submitted successfully!";
        } else {
            $error_msg = "Something went wrong. Please try again. " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<?php require_once 'header_footer/main-header.php'; ?>

<style>
    .report-wrapper {
        display: flex;
        justify-content: center;
        padding: 40px 16px;
    }
    .report-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 16px rgba(0,0,0,0.09);
        padding: 2rem;
        width: 100%;
        max-width: 560px;
    }
    .report-card h3 {
        text-align: center;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .report-subtitle {
        text-align: center;
        color: #888;
        font-size: 0.88rem;
        margin-bottom: 1.5rem;
    }
    .badge-lost {
        display: inline-block;
        background: #dc3545;
        color: #fff;
        border-radius: 20px;
        padding: 3px 14px;
        font-size: 0.8rem;
        font-weight: 600;
        margin: 0 auto 1.2rem auto;
        letter-spacing: 0.04em;
    }
    .badge-wrapper {
        text-align: center;
    }
    .divider {
        border-top: 1px solid #eee;
        margin: 1rem 0;
    }
    .hint {
        font-size: 0.78rem;
        color: #999;
        margin-top: 3px;
    }
</style>

<div class="report-wrapper">
    <div class="report-card">
        <div class="badge-wrapper">
            <span class="badge-lost">LOST</span>
        </div>
        <h3>Report a Lost Item</h3>
        <p class="report-subtitle">Fill in as much detail as possible to help others identify your item.</p>

        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Item Category</label>
                <select name="category_id" class="form-control" required>
                    <option value="" disabled selected>Select a category</option>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['categoryName']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Item Name <span class="text-danger">*</span></label>
                <input type="text" name="itemName" class="form-control" placeholder="e.g. Black iPhone 13" required>
            </div>

            <div class="form-group">
                <label>Public Description <span class="text-danger">*</span></label>
                <textarea name="publicDescription" class="form-control" rows="3"
                    placeholder="e.g. Black wallet with school ID inside, lost near the library" required></textarea>
                <small class="hint">This will be visible to everyone.</small>
            </div>

            <div class="divider"></div>

            <div class="form-group">
                <label>Where did you lose it? (General Area)</label>
                <select name="location_id" class="form-control" required>
                    <option value="" disabled selected>Select a location</option>
                    <?php while ($loc = $locations->fetch_assoc()): ?>
                        <option value="<?= $loc['location_id'] ?>">
                            <?= htmlspecialchars($loc['locationName']) ?> — <?= htmlspecialchars($loc['zone']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Specific Location Details</label>
                <input type="text" name="specificLocation" class="form-control" placeholder="e.g. Room 402, 3rd row desk, under the chair">
                <small class="hint">Helps others pin-point exactly where it might be within the general area.</small>
            </div>

            <div class="form-group">
                <label>Preferred Drop-off Point</label>
                <select name="dropOff_id" class="form-control" required>
                    <option value="" disabled selected>Select a drop-off point</option>
                    <?php while ($drop = $dropoffs->fetch_assoc()): ?>
                        <option value="<?= $drop['dropOff_id'] ?>"><?= htmlspecialchars($drop['dropOffPointName']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button name="btnSubmit" type="submit" class="btn btn-danger btn-block mt-2">
                Submit Report
            </button>
            <a href="dashboard.php" class="btn btn-outline-secondary btn-block mt-2">Cancel</a>
        </form>
    </div>
</div>

<?php require_once 'header_footer/footer.php'; ?>