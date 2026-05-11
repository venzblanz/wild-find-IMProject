<?php
require_once 'header_footer/main-header.php';
?>

<div class="login-page d-flex justify-content-center align-items-center">
    <div class="card login-card">
        <div class="card-body login-card-body">
            <h3 class="text-center mb-4">Login</h3>

            <form method="POST" class="login-form">
                <div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="txtemail" class="form-control" placeholder="Enter email" required>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="txtpassword" class="form-control" placeholder="Enter password" required>
                    </div>

                    <div class="mb-3">
                        <a class="register-link" href="register.php">Don't have account? Register</a>
                    </div>
                </div>

                <button name="btnLogin" type="submit" class="btn btn-primary btn-block">
                    Login
                </button>
            </form>
        </div>
    </div>
</div>

<?php
require_once 'header_footer/footer.php';
?>