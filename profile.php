<?php
require_once 'header_footer/main-header.php';
?>

<div class="login-page d-flex justify-content-center align-items-center">
    <div class="card login-card">
        <div class="card-body login-card-body">
            <h3 class="text-center mb-4">Register</h3>

            <form method="POST" class="register-form">
                <div>
                    <?php if (!empty($message)): ?>
                        <div class="mb-3 text-center" id="errorBox">
                            <label class="error-message">
                                <?php echo $message; ?>
                            </label>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="txtemail" class="form-control" placeholder="Enter email" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="txtpassword" class="form-control" placeholder="Enter password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="txtpassword1" class="form-control" placeholder="Confirm password" required>
                    </div>
                    <div class="mb-3">
                        <a class="login-link" href="login.php">Already have an account? Log in</a>
                    </div>
                </div>

                <button name="btnConfirm" type="submit" class="btn btn-primary btn-block">
                    Confirm
                </button>
            </form>
        </div>
    </div>
</div>

<?php
require_once 'header_footer/footer.php';
?>