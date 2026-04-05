<?php

if (!defined('ROUTER_ACCESS')) {
    header("Location: /admin");
    exit;
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_samesite', 'Strict');
}

// Generate CSRF token if one doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true) {
    header("Location: /public/admin/index.php");
    exit;
}

$error = '';
$username = '';
$user = null;

try {
    $db = new Database();
    $conn = $db->connect();

    if (!($conn instanceof PDO)) {
        throw new Exception('Error establishing a database connection.');
    }

    $user = new User($conn);
} catch (Throwable $exception) {
    error_log('Admin login bootstrap error: ' . $exception->getMessage());
    $error = 'Admin sign-in is temporarily unavailable right now. Please try again shortly.';
}

// Generate a simple math captcha
function generateMathCaptcha()
{
    $num1 = rand(1, 10);
    $num2 = rand(1, 10);
    $_SESSION['captcha_answer'] = $num1 + $num2;
    return "What is $num1 + $num2?";
}

// Initialize captcha if it doesn't exist
if (!isset($_SESSION['captcha_question']) || !isset($_SESSION['captcha_answer'])) {
    $_SESSION['captcha_question'] = generateMathCaptcha();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($user === null) {
        $error = 'Admin sign-in is temporarily unavailable right now. Please try again shortly.';
        $_SESSION['captcha_question'] = generateMathCaptcha();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $csrf_token = $_SESSION['csrf_token'];
    } elseif (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = 'Invalid CSRF token. Please try again.';
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $csrf_token = $_SESSION['csrf_token'];
        $_SESSION['captcha_question'] = generateMathCaptcha();
    } else {
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $password = $_POST['password'] ?? '';
        $captcha_answer = isset($_POST['captcha_answer']) ? (int) $_POST['captcha_answer'] : 0;
        $expected_answer = $_SESSION['captcha_answer'];

        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password';
            $_SESSION['captcha_question'] = generateMathCaptcha();
        } elseif ($captcha_answer !== $expected_answer) {
            $error = 'Incorrect captcha answer';
            $_SESSION['captcha_question'] = generateMathCaptcha();
        } else {
            try {
                if ($user->authenticate($username, $password)) {
                    session_regenerate_id(true);
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                    $userData = $user->getUserData($username);
                    $_SESSION['loggedIn'] = true;
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $user->getRole($username);
                    $_SESSION['user_id'] = $userData['id'];
                    $_SESSION['last_activity'] = time();

                    header("Location: /public/admin/index.php");
                    exit();
                } else {
                    sleep(1);
                    $error = 'Invalid credentials';
                    $_SESSION['captcha_question'] = generateMathCaptcha();
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
                $_SESSION['captcha_question'] = generateMathCaptcha();
            }
        }
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $csrf_token = $_SESSION['csrf_token'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AlpiCMS Admin</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/assets/css/admin/admin-global.css">
</head>

<body class="alpi-auth-shell alpi-auth-shell-login">
    <div class="alpi-auth-backdrop" aria-hidden="true">
        <div class="alpi-auth-backdrop-orb alpi-auth-backdrop-orb-one"></div>
        <div class="alpi-auth-backdrop-orb alpi-auth-backdrop-orb-two"></div>
    </div>

    <main class="alpi-container alpi-auth-layout">
        <div class="alpi-auth-grid">
            <section class="alpi-auth-hero">
                <p class="alpi-auth-kicker">Admin Access</p>
                <h1 class="alpi-auth-title">Sign in to manage content, settings, and uploads.</h1>
                <p class="alpi-auth-copy">Use the credentials created during installation. The captcha is only there to keep automated noise down, not to slow you down.</p>

                <ul class="alpi-list-clean alpi-auth-feature-list">
                    <li class="alpi-auth-feature">
                        <strong>Content editing</strong>
                        <span>Create and update posts, pages, and categories.</span>
                    </li>
                    <li class="alpi-auth-feature">
                        <strong>Site controls</strong>
                        <span>Adjust settings, manage data, and handle maintenance tasks.</span>
                    </li>
                    <li class="alpi-auth-feature">
                        <strong>Media management</strong>
                        <span>Upload, review, and clean up the media library from one place.</span>
                    </li>
                </ul>
            </section>

            <section class="alpi-auth-panel">
                <div class="alpi-card alpi-auth-card">
                    <div class="alpi-auth-card-header">
                        <p class="alpi-auth-eyebrow">AlpiCMS</p>
                        <h2 class="alpi-auth-card-title">Login to Admin Panel</h2>
                        <p class="alpi-auth-card-copy">Pick up where you left off and keep the site moving.</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alpi-alert alpi-alert-danger alpi-mb-md">
                            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>

                    <form class="alpi-form alpi-auth-form" action="" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">

                        <div class="alpi-form-section">
                            <div class="alpi-form-section-header">
                                <h3 class="alpi-form-section-title">Account Login</h3>
                                <span class="alpi-badge alpi-badge-secondary alpi-auth-inline-badge">Secure</span>
                            </div>

                            <div class="alpi-form-group">
                                <label for="username" class="alpi-form-label">Username</label>
                                <input type="text" id="username" name="username"
                                    class="alpi-form-input"
                                    value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>"
                                    required
                                    placeholder="Enter your admin username"
                                    autocomplete="username">
                            </div>
                            <div class="alpi-form-group">
                                <label for="password" class="alpi-form-label">Password</label>
                                <input type="password"
                                    id="password"
                                    name="password"
                                    class="alpi-form-input"
                                    required
                                    autocomplete="current-password">
                            </div>
                            <div class="alpi-form-group">
                                <label for="captcha_answer" class="alpi-form-label"><?= htmlspecialchars($_SESSION['captcha_question'], ENT_QUOTES, 'UTF-8') ?></label>
                                <input type="text"
                                    id="captcha_answer"
                                    name="captcha_answer"
                                    class="alpi-form-input"
                                    required
                                    placeholder="Enter the answer"
                                    autocomplete="off">
                                <p class="alpi-form-help">Simple human check to protect the login form from automated attempts.</p>
                            </div>
                        </div>

                        <div class="alpi-auth-actions">
                            <button type="submit" class="alpi-btn alpi-btn-primary alpi-btn-block">Log In</button>
                            <p class="alpi-auth-note">Use the credentials created during installation.</p>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </main>
</body>

</html>