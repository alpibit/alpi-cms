<?php
ob_start();

require '../../../config/autoload.php';
require '../../../config/database.php';
require '../../../config/config.php';
require '../auth_check.php';

$db = new Database();
$conn = $db->connect();
$user = new User($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!alpiVerifyCsrfToken($_POST['csrf_token'] ?? '')) {
        alpiRegenerateCsrfToken();
        alpiSetFlashValue('change_password_message', [
            'type' => 'danger',
            'message' => 'Invalid CSRF token. Please refresh and try again.',
        ]);
        header('Location: ' . BASE_URL . '/public/admin/settings/change_password.php');
        exit;
    } else {
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
            alpiRegenerateCsrfToken();
            alpiSetFlashValue('change_password_message', [
                'type' => 'success',
                'message' => 'Password successfully updated',
            ]);
            header('Location: ' . BASE_URL . '/public/admin/settings/change_password.php');
            exit;
        } catch (Exception $e) {
            alpiSetFlashValue('change_password_message', [
                'type' => 'danger',
                'message' => $e->getMessage(),
            ]);
            header('Location: ' . BASE_URL . '/public/admin/settings/change_password.php');
            exit;
        }
    }
}

$flashMessage = alpiConsumeFlashValue('change_password_message');

include '../../../templates/header-admin.php';
?>

<div class="alpi-admin-content">
    <h1 class="alpi-text-primary alpi-mb-lg">Change Password</h1>

    <?php if ($flashMessage): ?>
        <div class="alpi-alert <?php echo $flashMessage['type'] === 'success' ? 'alpi-alert-success' : 'alpi-alert-danger'; ?> alpi-mb-md">
            <?php echo htmlspecialchars($flashMessage['message'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <div class="alpi-card alpi-p-lg">
        <form action="" method="POST" class="alpi-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(alpiGetCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
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