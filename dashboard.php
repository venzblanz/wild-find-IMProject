<div class="layout">
    <?php
    require_once 'side-menu.php';
    ?>
    <?php
    require_once 'header_footer/main-header.php';
    ?>

    <main id="mainContent" class="main-content">
        <nav class="navbar navbar-expand-lg navbar-light bg-light main-navbar">
            <button name="lostTab" type="button" class="btn btn-rep">
                Lost Items
            </button>
            <button name="foundTab" type="button" class="btn btn-rep">
                Found Items
            </button>
        </nav>
        <div class="dashboard-page">
            <div class="card welcome-card">
                <div class="card welcome-body">
                    <h2>WELCOME, USER!</h2>
                    <button name="btnReportLost" type="button" class="btn btn-primary btn-block btn-rep">
                        Report a Lost Item
                    </button>
                    <button name="btnReportFound" type="button" class="btn btn-primary btn-block btn-rep">
                        Report a Found Item
                    </button>
                </div>
            </div>
        </div>
    </main>
</div>

<?php
require_once 'header_footer/footer.php';
?>