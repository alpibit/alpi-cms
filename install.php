<?php
if (!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/config/autoload.php';
    require_once __DIR__ . '/utils/helpers.php';
    require_once __DIR__ . '/config/config.php';
    define('CONFIG_INCLUDED', true);
}

$errors = [];

if (file_exists('config/database.php')) {
    require 'config/database.php';

    try {
        $db = new Database();
        $conn = $db->connect();

        // Perform a basic health check
        $stmt = $conn->query("SELECT 1");
        if ($stmt !== false) {
            if (isInstalled($conn)) {
                header("Location: " . BASE_URL . "/admin");
                exit;
            }
        }
    } catch (PDOException $e) {
        $errors[] = "Database connection failed. Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $host = trim($_POST['db_host']);
    $name = trim($_POST['db_name']);
    $user = trim($_POST['db_user']);
    $pass = trim($_POST['db_pass']);
    $adminUser = trim($_POST['admin_user']);
    $adminPass = trim($_POST['admin_pass']);
    $adminEmail = trim($_POST['admin_email']);
    $websiteUrl = trim($_POST['website_url']);

    $errors = [];

    if (empty($host)) {
        $errors[] = "Please enter the database host.";
    }
    if (empty($name)) {
        $errors[] = "Please enter the database name.";
    }
    if (empty($user)) {
        $errors[] = "Please enter the database user.";
    }
    if (empty($adminUser)) {
        $errors[] = "Please enter the admin username.";
    }
    if (empty($adminPass)) {
        $errors[] = "Please enter the admin password.";
    }
    if (empty($adminEmail)) {
        $errors[] = "Please enter the admin email.";
    }
    if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid admin email.";
    }
    if (strlen($adminUser) < 5) {
        $errors[] = "Admin username should be at least 5 characters long.";
    }

    // Use the new password validation
    $tempUser = new User(null);
    try {
        if (!$tempUser->validatePassword($adminPass)) {
            $errors = array_merge($errors, $tempUser->getPasswordErrors());
        }
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }

    if (empty($websiteUrl)) {
        $errors[] = "Please enter the website URL.";
    }

    if (empty($errors)) {
        try {
            $installer = new Installer();
            $installer->install($adminUser, $adminPass, $adminEmail, $host, $name, $user, $pass, $websiteUrl);

            header("Location: " . BASE_URL . "/admin");
            exit;
        } catch (PDOException $e) {
            $errors[] = "Database connection failed. Error: " . $e->getMessage();
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}

function isInstalled($conn)
{
    $sql = "SELECT setting_value FROM settings WHERE setting_key = 'installed'";
    $stmt = $conn->query($sql);
    $result = $stmt->fetchColumn();
    return $result === 'true';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlpiCMS Installation</title>
    <link rel="stylesheet" href="/assets/css/admin/admin-global.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: var(--alpi-background);
        }

        .alpi-install-wrap {
            width: 100%;
            max-width: 600px;
        }
    </style>
</head>

<body>
    <div class="alpi-container alpi-install-wrap">
        <div class="alpi-card">
            <h1 class="alpi-text-center alpi-mb-md">AlpiCMS Installation</h1>

            <?php if (!empty($errors)) : ?>
                <div class="alpi-alert alpi-alert-danger">
                    <ul>
                        <?php foreach ($errors as $error) : ?>
                            <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" class="alpi-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>">
                <h2 class="alpi-text-primary alpi-mb-md">Database Configuration</h2>
                <div class="alpi-form-group">
                    <label for="db_host" class="alpi-form-label">Database Host</label>
                    <input type="text" name="db_host" id="db_host" class="alpi-form-input" required>
                </div>
                <div class="alpi-form-group">
                    <label for="db_name" class="alpi-form-label">Database Name</label>
                    <input type="text" name="db_name" id="db_name" class="alpi-form-input" required>
                </div>
                <div class="alpi-form-group">
                    <label for="db_user" class="alpi-form-label">Database User</label>
                    <input type="text" name="db_user" id="db_user" class="alpi-form-input" required>
                </div>
                <div class="alpi-form-group">
                    <label for="db_pass" class="alpi-form-label">Database Password</label>
                    <input type="password" name="db_pass" id="db_pass" class="alpi-form-input">
                </div>

                <h2 class="alpi-text-primary alpi-mb-md alpi-mt-md">Admin Configuration</h2>
                <div class="alpi-form-group">
                    <label for="admin_email" class="alpi-form-label">Admin Email</label>
                    <input type="email" name="admin_email" id="admin_email" class="alpi-form-input" required>
                </div>
                <div class="alpi-form-group">
                    <label for="admin_user" class="alpi-form-label">Admin Username</label>
                    <input type="text" name="admin_user" id="admin_user" class="alpi-form-input" required>
                </div>
                <div class="alpi-form-group">
                    <label for="admin_pass" class="alpi-form-label">Admin Password:</label>
                    <input type="password"
                        id="admin_pass"
                        name="admin_pass"
                        class="alpi-form-input"
                        required
                        autocomplete="new-password">
                    <div class="password-requirements">
                        Password must be at least 12 characters long and contain:
                        <ul>
                            <li>At least one uppercase letter</li>
                            <li>At least one lowercase letter</li>
                            <li>At least one number</li>
                            <li>At least one special character</li>
                        </ul>
                    </div>
                </div>

                <h2 class="alpi-text-primary alpi-mb-md alpi-mt-md">Site Configuration</h2>
                <div class="alpi-form-group">
                    <label for="website_url" class="alpi-form-label">Website URL</label>
                    <input type="url" name="website_url" id="website_url" class="alpi-form-input" required>
                </div>

                <h2 class="alpi-text-primary alpi-mb-md alpi-mt-md">Email Configuration (Optional)</h2>
                <div class="alpi-form-group">
                    <label for="email_smtp_host" class="alpi-form-label">SMTP Host</label>
                    <input type="text" name="email_smtp_host" id="email_smtp_host" class="alpi-form-input">
                </div>
                <div class="alpi-form-group">
                    <label for="email_smtp_port" class="alpi-form-label">SMTP Port</label>
                    <input type="number" name="email_smtp_port" id="email_smtp_port" class="alpi-form-input">
                </div>
                <div class="alpi-form-group">
                    <label for="email_smtp_username" class="alpi-form-label">SMTP Username</label>
                    <input type="text" name="email_smtp_username" id="email_smtp_username" class="alpi-form-input">
                </div>
                <div class="alpi-form-group">
                    <label for="email_smtp_password" class="alpi-form-label">SMTP Password</label>
                    <input type="password" name="email_smtp_password" id="email_smtp_password" class="alpi-form-input">
                </div>
                <div class="alpi-form-group">
                    <label for="email_smtp_encryption" class="alpi-form-label">SMTP Encryption</label>
                    <select name="email_smtp_encryption" id="email_smtp_encryption" class="alpi-form-input">
                        <option value="">None</option>
                        <option value="tls">TLS</option>
                        <option value="ssl">SSL</option>
                    </select>
                </div>

                <div class="alpi-text-center">
                    <button type="submit" class="alpi-btn alpi-btn-primary">Install</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>