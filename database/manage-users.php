    <?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['is_admin']) || (int)$_SESSION['is_admin'] !== 1) {
    header("Location: dashboard.php");
    exit();
}

$title = "Manage Users";
$current_user_id = (int)$_SESSION['userID'];

$success_msg = '';
$error_msg = '';

$stmt = $connection->prepare("SELECT full_name, institutionalEmail FROM users WHERE user_id = ?");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnUpdateUser'])) {
    $user_id = (int)$_POST['user_id'];
    $full_name = trim($_POST['full_name']);
    $institutionalEmail = trim($_POST['institutionalEmail']);

    if (empty($full_name) || empty($institutionalEmail)) {
        $error_msg = "Full name and email are required.";
    } else {
        if ($user_id === $current_user_id) {
            $stmt = $connection->prepare("
                UPDATE users
                SET full_name = ?, institutionalEmail = ?
                WHERE user_id = ?
            ");
            $stmt->bind_param("ssi", $full_name, $institutionalEmail, $user_id);
        } else {
            $is_admin = isset($_POST['is_admin']) ? 1 : 0;

            $stmt = $connection->prepare("
                UPDATE users
                SET full_name = ?, institutionalEmail = ?, is_admin = ?
                WHERE user_id = ?
            ");
            $stmt->bind_param("ssii", $full_name, $institutionalEmail, $is_admin, $user_id);
        }

        if ($stmt->execute()) {
            $success_msg = "User updated successfully.";

            if ($user_id === $current_user_id) {
                $stmt2 = $connection->prepare("SELECT full_name, institutionalEmail FROM users WHERE user_id = ?");
                $stmt2->bind_param("i", $current_user_id);
                $stmt2->execute();
                $user = $stmt2->get_result()->fetch_assoc();
                $stmt2->close();

                $_SESSION['email'] = $user['institutionalEmail'];
            }
        } else {
            $error_msg = "Failed to update user.";
        }

        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnDeleteUser'])) {
    $user_id = (int)$_POST['user_id'];

    if ($user_id === $current_user_id) {
        $error_msg = "You cannot delete your own account.";
    } else {
        $stmt = $connection->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            $success_msg = "User deleted successfully.";
        } else {
            $error_msg = "Failed to delete user. This user may have related records.";
        }

        $stmt->close();
    }
}

$stmt = $connection->prepare("
    SELECT user_id, full_name, institutionalEmail, is_admin
    FROM users
    ORDER BY user_id ASC
");
$stmt->execute();
$users = $stmt->get_result();
$stmt->close();
?>

<div class="layout">
    <?php require_once 'admin-side-menu.php'; ?>

    <main id="mainContent" class="main-content">
        <?php require_once 'header_footer/main-header.php'; ?>

        <div class="manage-users-page">
            <div class="manage-users-card">
                <div class="manage-users-header">
                    <h3>Manage Users</h3>
                    <a href="admin-dashboard.php" class="btn-back">Go Back</a>
                </div>

                <?php if (!empty($success_msg)): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($success_msg) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error_msg) ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Set Admin</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php while ($rowUser = $users->fetch_assoc()): ?>
                                <?php
                                    $isCurrentUser = (int)$rowUser['user_id'] === $current_user_id;
                                    $isAdmin = (int)$rowUser['is_admin'] === 1;
                                ?>

                                <tr>
                                    <form method="POST">
                                        <td>
                                            <?= htmlspecialchars($rowUser['user_id']) ?>

                                            <?php if ($isCurrentUser): ?>
                                                <br>
                                                <small style="color: gray;">You</small>
                                            <?php endif; ?>

                                            <input 
                                                type="hidden" 
                                                name="user_id" 
                                                value="<?= htmlspecialchars($rowUser['user_id']) ?>"
                                            >
                                        </td>

                                        <td>
                                            <input
                                                type="text"
                                                name="full_name"
                                                value="<?= htmlspecialchars($rowUser['full_name']) ?>"
                                                required
                                            >
                                        </td>

                                        <td>
                                            <input
                                                type="email"
                                                name="institutionalEmail"
                                                value="<?= htmlspecialchars($rowUser['institutionalEmail']) ?>"
                                                required
                                            >
                                        </td>

                                        <td>
                                            <?php if ($isAdmin): ?>
                                                <span class="admin-badge">Admin</span>
                                            <?php else: ?>
                                                <span class="user-badge">User</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <input
                                                type="checkbox"
                                                name="is_admin"
                                                class="checkbox-admin"
                                                <?= $isAdmin ? 'checked' : '' ?>
                                                <?= $isCurrentUser ? 'disabled' : '' ?>
                                            >

                                            <?php if ($isCurrentUser): ?>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <div class="action-buttons">
                                                <button
                                                    type="submit"
                                                    name="btnUpdateUser"
                                                    class="btn-update"
                                                >
                                                    Save
                                                </button>

                                                <button
                                                    type="submit"
                                                    name="btnDeleteUser"
                                                    class="btn-delete"
                                                    onclick="return confirm('Are you sure you want to delete this user?');"
                                                    <?= $isCurrentUser ? 'disabled' : '' ?>
                                                >
                                                    Delete
                                                </button>

                                                <?php if ($isCurrentUser): ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </form>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once 'header_footer/footer.php'; ?>