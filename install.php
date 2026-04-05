<?php
if (!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/config/autoload.php';
    require_once __DIR__ . '/utils/helpers.php';
    require_once __DIR__ . '/config/config.php';
    define('CONFIG_INCLUDED', true);
}

$forwardedProto = strtolower(trim((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')));
$isHttpsRequest = (
    (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
    || ($forwardedProto !== '' && trim(explode(',', $forwardedProto)[0]) === 'https')
    || ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443)
);

ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.cookie_secure', $isHttpsRequest ? '1' : '0');

$errors = [];

$host = isset($_POST['db_host']) ? htmlspecialchars($_POST['db_host'], ENT_QUOTES, 'UTF-8') : '';
$name = isset($_POST['db_name']) ? htmlspecialchars($_POST['db_name'], ENT_QUOTES, 'UTF-8') : '';
$user = isset($_POST['db_user']) ? htmlspecialchars($_POST['db_user'], ENT_QUOTES, 'UTF-8') : '';
$adminUser = isset($_POST['admin_user']) ? htmlspecialchars($_POST['admin_user'], ENT_QUOTES, 'UTF-8') : '';
$adminEmail = isset($_POST['admin_email']) ? htmlspecialchars($_POST['admin_email'], ENT_QUOTES, 'UTF-8') : '';
$websiteUrl = isset($_POST['website_url']) ? htmlspecialchars($_POST['website_url'], ENT_QUOTES, 'UTF-8') : '';
$email_smtp_host = isset($_POST['email_smtp_host']) ? htmlspecialchars($_POST['email_smtp_host'], ENT_QUOTES, 'UTF-8') : '';
$email_smtp_port = isset($_POST['email_smtp_port']) ? htmlspecialchars($_POST['email_smtp_port'], ENT_QUOTES, 'UTF-8') : '';
$email_smtp_username = isset($_POST['email_smtp_username']) ? htmlspecialchars($_POST['email_smtp_username'], ENT_QUOTES, 'UTF-8') : '';
$email_smtp_encryption = isset($_POST['email_smtp_encryption']) ? htmlspecialchars($_POST['email_smtp_encryption'], ENT_QUOTES, 'UTF-8') : '';

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
    $adminPassConfirm = trim($_POST['admin_pass_confirm'] ?? '');
    $adminEmail = trim($_POST['admin_email']);
    $websiteUrl = trim($_POST['website_url']);

    $errors = [];

    if (!alpiVerifyCsrfToken($_POST['csrf_token'] ?? '')) {
        alpiRegenerateCsrfToken();
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        if (empty($host)) {
            $errors[] = "Please enter the database host.";
        }
        if (empty($name)) {
            $errors[] = "Please enter the database name.";
        }
        if (empty($user)) {
            $errors[] = "Please enter the database user.";
        }
        if (empty($pass)) {
            $errors[] = "Please enter the database password.";
        }
        if (empty($adminUser)) {
            $errors[] = "Please enter the admin username.";
        }
        if (empty($adminPass)) {
            $errors[] = "Please enter the admin password.";
        }
        if (empty($adminPassConfirm)) {
            $errors[] = "Please confirm the admin password.";
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

        if (!empty($adminPass) && !empty($adminPassConfirm) && $adminPass !== $adminPassConfirm) {
            $errors[] = "Admin passwords do not match.";
        }

        // Use the new password validation
        if (!empty($adminPass)) {
            $tempUser = new User(null);
            try {
                if (!$tempUser->validatePassword($adminPass)) {
                    $errors = array_merge($errors, $tempUser->getPasswordErrors());
                }
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
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
}

$csrfToken = alpiGetCsrfToken();

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
    <link rel="stylesheet" href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/assets/css/admin/admin-global.css">
</head>

<body class="alpi-auth-shell alpi-auth-shell-install">
    <div class="alpi-auth-backdrop" aria-hidden="true">
        <div class="alpi-auth-backdrop-orb alpi-auth-backdrop-orb-one"></div>
        <div class="alpi-auth-backdrop-orb alpi-auth-backdrop-orb-two"></div>
    </div>

    <main class="alpi-container alpi-auth-layout">
        <div class="alpi-auth-grid">
            <section class="alpi-auth-hero">
                <p class="alpi-auth-kicker">Setup</p>
                <h1 class="alpi-auth-title">Set up AlpiCMS with a calmer, cleaner install flow.</h1>
                <p class="alpi-auth-copy">Connect the database, create the first admin account, and set the site basics. Email settings can be added now or handled later from the dashboard.</p>

                <ol class="alpi-list-clean alpi-auth-step-list">
                    <li class="alpi-auth-step">
                        <strong>Database connection</strong>
                        <span>Point the CMS to the correct host, database, and credentials.</span>
                    </li>
                    <li class="alpi-auth-step">
                        <strong>Admin account</strong>
                        <span>Create the first secure login for content and settings management.</span>
                    </li>
                    <li class="alpi-auth-step">
                        <strong>Site basics</strong>
                        <span>Set the main URL so redirects and asset paths behave correctly.</span>
                    </li>
                    <li class="alpi-auth-step">
                        <strong>Email setup</strong>
                        <span>Optional SMTP settings can be added now or revisited later.</span>
                    </li>
                </ol>

                <div class="alpi-auth-meta">
                    <div class="alpi-auth-stat">
                        <span class="alpi-auth-stat-value">4</span>
                        <span class="alpi-auth-stat-label">Setup sections</span>
                    </div>
                    <div class="alpi-auth-stat">
                        <span class="alpi-auth-stat-value">1</span>
                        <span class="alpi-auth-stat-label">Admin account</span>
                    </div>
                </div>
            </section>

            <section class="alpi-auth-panel">
                <div class="alpi-card alpi-auth-card">
                    <div class="alpi-auth-card-header">
                        <p class="alpi-auth-eyebrow">AlpiCMS</p>
                        <h2 class="alpi-auth-card-title">Installation</h2>
                        <p class="alpi-auth-card-copy">Fill out the essentials, then finish the rest from the admin once the CMS is live.</p>
                    </div>

                    <?php if (!empty($errors)) : ?>
                        <div class="alpi-alert alpi-alert-danger">
                            <ul class="alpi-list-clean alpi-alert-list">
                                <?php foreach ($errors as $error) : ?>
                                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="alpi-form alpi-auth-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

                        <section class="alpi-form-section">
                            <div class="alpi-form-section-header">
                                <h3 class="alpi-form-section-title">Database Configuration</h3>
                                <span class="alpi-badge alpi-badge-secondary alpi-auth-inline-badge">Required</span>
                            </div>

                            <div class="alpi-form-group">
                                <label for="db_host" class="alpi-form-label">Database Host</label>
                                <input type="text" name="db_host" id="db_host" class="alpi-form-input" required value="<?php echo $host; ?>" placeholder="localhost" autocomplete="off">
                            </div>
                            <div class="alpi-form-group">
                                <label for="db_name" class="alpi-form-label">Database Name</label>
                                <input type="text" name="db_name" id="db_name" class="alpi-form-input" required value="<?php echo $name; ?>" placeholder="alp_cms" autocomplete="off">
                            </div>
                            <div class="alpi-form-group">
                                <label for="db_user" class="alpi-form-label">Database User</label>
                                <input type="text" name="db_user" id="db_user" class="alpi-form-input" required value="<?php echo $user; ?>" placeholder="root" autocomplete="username">
                            </div>
                            <div class="alpi-form-group">
                                <label for="db_pass" class="alpi-form-label">Database Password</label>
                                <input type="password" name="db_pass" id="db_pass" class="alpi-form-input" required autocomplete="current-password">
                            </div>
                        </section>

                        <section class="alpi-form-section">
                            <div class="alpi-form-section-header">
                                <h3 class="alpi-form-section-title">Admin Configuration</h3>
                                <span class="alpi-badge alpi-badge-secondary alpi-auth-inline-badge">Required</span>
                            </div>

                            <div class="alpi-form-group">
                                <label for="admin_email" class="alpi-form-label">Admin Email</label>
                                <input type="email" name="admin_email" id="admin_email" class="alpi-form-input" required value="<?php echo $adminEmail; ?>" placeholder="admin@example.com" autocomplete="email">
                            </div>
                            <div class="alpi-form-group">
                                <label for="admin_user" class="alpi-form-label">Admin Username</label>
                                <input type="text" name="admin_user" id="admin_user" class="alpi-form-input" required value="<?php echo $adminUser; ?>" placeholder="adminuser" autocomplete="username">
                            </div>
                            <div class="alpi-form-group">
                                <label for="admin_pass" class="alpi-form-label">Admin Password</label>
                                <input type="password"
                                    id="admin_pass"
                                    name="admin_pass"
                                    class="alpi-form-input"
                                    required
                                    autocomplete="new-password">
                                <div class="alpi-form-help alpi-password-hints">
                                    <p class="alpi-form-help-title">Password must include:</p>
                                    <ul class="alpi-list-clean alpi-check-list">
                                        <li>At least 12 characters</li>
                                        <li>At least one uppercase letter</li>
                                        <li>At least one lowercase letter</li>
                                        <li>At least one number</li>
                                        <li>At least one special character</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="alpi-form-group">
                                <label for="admin_pass_confirm" class="alpi-form-label">Confirm Admin Password</label>
                                <input type="password"
                                    id="admin_pass_confirm"
                                    name="admin_pass_confirm"
                                    class="alpi-form-input"
                                    required
                                    autocomplete="new-password">
                            </div>
                        </section>

                        <section class="alpi-form-section">
                            <div class="alpi-form-section-header">
                                <h3 class="alpi-form-section-title">Site Configuration</h3>
                                <span class="alpi-badge alpi-badge-secondary alpi-auth-inline-badge">Required</span>
                            </div>

                            <div class="alpi-form-group">
                                <label for="website_url" class="alpi-form-label">Website URL</label>
                                <input type="url" name="website_url" id="website_url" class="alpi-form-input" required value="<?php echo $websiteUrl; ?>" placeholder="https://example.com" autocomplete="url">
                            </div>
                        </section>

                        <section class="alpi-form-section">
                            <div class="alpi-form-section-header">
                                <h3 class="alpi-form-section-title">Email Configuration</h3>
                                <span class="alpi-badge alpi-badge-info alpi-auth-inline-badge">Optional</span>
                            </div>

                            <div class="alpi-form-group">
                                <label for="email_smtp_host" class="alpi-form-label">SMTP Host</label>
                                <input type="text" name="email_smtp_host" id="email_smtp_host" class="alpi-form-input" value="<?php echo $email_smtp_host; ?>" placeholder="smtp.example.com" autocomplete="off">
                            </div>
                            <div class="alpi-form-group">
                                <label for="email_smtp_port" class="alpi-form-label">SMTP Port</label>
                                <input type="number" name="email_smtp_port" id="email_smtp_port" class="alpi-form-input" value="<?php echo $email_smtp_port; ?>" placeholder="587" autocomplete="off">
                            </div>
                            <div class="alpi-form-group">
                                <label for="email_smtp_username" class="alpi-form-label">SMTP Username</label>
                                <input type="text" name="email_smtp_username" id="email_smtp_username" class="alpi-form-input" value="<?php echo $email_smtp_username; ?>" placeholder="mailer@example.com" autocomplete="username">
                            </div>
                            <div class="alpi-form-group">
                                <label for="email_smtp_password" class="alpi-form-label">SMTP Password</label>
                                <input type="password" name="email_smtp_password" id="email_smtp_password" class="alpi-form-input" autocomplete="new-password">
                            </div>
                            <div class="alpi-form-group">
                                <label for="email_smtp_encryption" class="alpi-form-label">SMTP Encryption</label>
                                <select name="email_smtp_encryption" id="email_smtp_encryption" class="alpi-form-input">
                                    <option value="" <?php if ($email_smtp_encryption == '') echo 'selected'; ?>>None</option>
                                    <option value="tls" <?php if ($email_smtp_encryption == 'tls') echo 'selected'; ?>>TLS</option>
                                    <option value="ssl" <?php if ($email_smtp_encryption == 'ssl') echo 'selected'; ?>>SSL</option>
                                </select>
                            </div>
                        </section>

                        <div class="alpi-auth-actions">
                            <button type="submit" class="alpi-btn alpi-btn-primary alpi-btn-block">Install AlpiCMS</button>
                            <p class="alpi-auth-note">You can revisit most settings later from the admin panel.</p>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </main>
</body>

</html>