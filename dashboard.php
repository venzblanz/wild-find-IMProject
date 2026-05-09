<?php
    require_once 'header_footer/header.php'
?>

<div class="login-page d-flex justify-content-center align-items-center">
    <div class="card login-card">
        <div class="card-body">
            <h3 class="text-center mb-4">Login</h3>

            <form action="loginprocess.php" method="POST">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter email" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Login
                </button>
            </form>
        </div>
    </div>
</div>

<?php
    require_once 'header_footer/footer.php'
?>