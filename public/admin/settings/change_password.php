<?php
ob_start();

require '../../../config/autoload.php';
require '../../../config/database.php';
require '../../../config/config.php';
require '../auth_check.php';

$db = new Database();
$conn = $db->connect();
$user = new User($conn);

$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    try {
        if (!$user->authenticate($_SESSION['username'], $currentPassword)) {
            throw new Exception("Current password is incorrect");
        }

        if ($newPassword !== $confirmPassword) {
            throw new Exception("New passwords do not match");
        }

        $user->updateUser($_SESSION['username'], $newPassword);
        $success = "Password successfully updated";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

include '../../../templates/header-admin.php';
?>

<div class="alpi-admin-content">
    <h1 class="alpi-text-primary alpi-mb-lg">Change Password</h1>

    <?php if ($success): ?>
        <div class="alpi-alert alpi-alert-success alpi-mb-md">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alpi-alert alpi-alert-danger alpi-mb-md">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="alpi-card alpi-p-lg">
        <form action="" method="POST" class="alpi-form">
            <div class="alpi-form-group">
                <label for="current_password" class="alpi-form-label">Current Password:</label>
                <input type="password"
                    id="current_password"
                    name="current_password"
                    class="alpi-form-input"
                    required
                    autocomplete="current-password">
            </div>

            <div class="alpi-form-group">
                <label for="new_password" class="alpi-form-label">New Password:</label>
                <input type="password"
                    id="new_password"
                    name="new_password"
                    class="alpi-form-input"
                    required
                    autocomplete="new-password">
                <div class="password-requirements alpi-mt-sm alpi-text-secondary">
                    Password must be at least 12 characters long and contain:
                    <ul>
                        <li>At least one uppercase letter</li>
                        <li>At least one lowercase letter</li>
                        <li>At least one number</li>
                        <li>At least one special character</li>
                    </ul>
                </div>
            </div>

            <div class="alpi-form-group">
                <label for="confirm_password" class="alpi-form-label">Confirm New Password:</label>
                <input type="password"
                    id="confirm_password"
                    name="confirm_password"
                    class="alpi-form-input"
                    required
                    autocomplete="new-password">
            </div>

            <div class="alpi-text-right">
                <button type="submit" class="alpi-btn alpi-btn-primary">Change Password</button>
            </div>
        </form>
    </div>
</div>

<?php
include '../../../templates/footer-admin.php';
ob_end_flush();
?>