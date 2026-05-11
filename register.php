<?php
require_once 'header_footer/pre-header.php';
?>

<?php
session_start();
require_once 'connect.php';

$message = "";

if (isset($_POST['btnConfirm'])) {
    // 1. Capture the new full name input
    $fullname = trim($_POST['txtfullname']);
    $email = trim($_POST['txtemail']);
    $pwd = $_POST['txtpassword'];
    $pwd1 = $_POST['txtpassword1'];

    if ($pwd != $pwd1) {
        $message = "Passwords don't match.";
    } else {
        $checkSql = "SELECT * FROM users WHERE institutionalEmail = ?";
        $checkStmt = $connection->prepare($checkSql);
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();

        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            $message = "Email is already registered.";
        } else {
            $hashedPassword = password_hash($pwd, PASSWORD_DEFAULT);

            // 2. Update the SQL to include full_name
            $sql = "INSERT INTO users(full_name, institutionalEmail, password) VALUES (?, ?, ?)";
            $stmt = $connection->prepare($sql);
            
            // 3. Update bind_param to include 3 strings ("sss") and the new variable
            $stmt->bind_param("sss", $fullname, $email, $hashedPassword);

            if ($stmt->execute()) {
                echo "<script>
                        alert('Registration successful.');
                        window.location.href = 'login.php';
                      </script>";
                exit();
            } else {
                $message = "Registration failed.";
            }
        }
    }
}
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
                                <?php echo htmlspecialchars($message); ?>
                            </label>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="txtfullname" class="form-control" placeholder="Enter your full name" required>
                    </div>

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