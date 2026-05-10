<?php
session_start();
require_once 'connect.php';

if (isset($_POST['btnLogin'])) {
    $email = $_POST['txtemail'];
    $pwd = $_POST['txtpassword'];

    $sql = "SELECT * FROM users WHERE i_email = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $count = $result->num_rows;

    if ($count == 0) {
        echo "<script>
                alert('Email does not exist.');
              </script>";
    } else {
        $row = $result->fetch_assoc();

        $stored_password = $row['password'];

        if (!password_verify($pwd, $stored_password)) {
            echo "<script>
                    alert('Incorrect password.');
                  </script>";
        } else {
            $_SESSION['userID'] = $row['userID'];
            $_SESSION['email'] = $row['i_email'];

            header("Location: dashboard.php");
            exit();
        }
    }
}
?>

<?php
require_once 'header_footer/header.php';
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