<?php 
session_start();
require_once 'connect.php';

if(!isset($_SESSION['userID'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['userID'];

$stmt = $connection->prepare("SELECT full_name, institutionalEmail FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

?>
<div class="layout">
    <?php
    require_once 'admin-side-menu.php';
    ?>
    <main id="mainContent" class="main-content">   
        <?php
        require_once 'header_footer/main-header.php';
        ?>

        <div class="tabs-container">
            <nav class="tabs-nav">
                <button name="lostTab" type="button" class="btn-tabs">
                    Lost Items
                </button>
                <button name="foundTab" type="button" class="btn-tabs">
                    Found Items
                </button>
            </nav>
        </div>
        <div class="dashboard-page">
            <div class="welcome-card">
                <div class="welcome-body">
                    <h2>WELCOME, ADMIN!</h2>

                    <div class="button-group">
                        <button 
                            name="btnManageUser" 
                            type="button" 
                            class="btn-rep"
                            onclick="window.location.href='manage-users.php'"
                        >
                            Manage Users
                        </button>

                        <button name="btnViewReports" type="button" class="btn-rep">
                            View All Reports
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<?php
require_once 'header_footer/footer.php';
?>