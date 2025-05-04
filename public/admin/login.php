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

$db = new Database();
$conn = $db->connect();
$user = new User($conn);

$error = '';
$username = '';

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
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = 'Invalid CSRF token. Please try again.';
        // Regenerate token on failure
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $csrf_token = $_SESSION['csrf_token']; // Update token for the form redisplay
        $_SESSION['captcha_question'] = generateMathCaptcha(); // Also refresh captcha
    } else {
        // CSRF token is valid, proceed with login logic
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $password = $_POST['password'] ?? '';
        $captcha_answer = isset($_POST['captcha_answer']) ? intval($_POST['captcha_answer']) : 0;
        $expected_answer = $_SESSION['captcha_answer']; // Store current answer before generating new one

        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password';
            $_SESSION['captcha_question'] = generateMathCaptcha();
        } elseif ($captcha_answer !== $expected_answer) {
            $error = 'Incorrect captcha answer';
            $_SESSION['captcha_question'] = generateMathCaptcha();
        } else {
            try {
                if ($user->authenticate($username, $password)) {
                    // Regenerate session ID *after* successful authentication
                    session_regenerate_id(true);
                    // Regenerate CSRF token after successful login
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
        // Regenerate CSRF token after processing POST, even on failure, for the next attempt
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
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: var(--alpi-background);
        }

        .login-container {
            width: 100%;
            max-width: 400px;
        }

        .password-requirements {
            font-size: 0.85em;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="alpi-card">
            <h1 class="alpi-text-center alpi-text-primary alpi-mb-lg">Login to Admin Panel</h1>

            <?php if ($error): ?>
                <div class="alpi-alert alpi-alert-danger alpi-mb-md">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form class="alpi-form" action="" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">

                <div class="alpi-form-group">
                    <label for="username" class="alpi-form-label">Username:</label>
                    <input type="text" id="username" name="username"
                        class="alpi-form-input"
                        value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>"
                        required
                        autocomplete="username">
                </div>
                <div class="alpi-form-group">
                    <label for="password" class="alpi-form-label">Password:</label>
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
                        autocomplete="off">
                </div>
                <div class="alpi-text-center">
                    <button type="submit" class="alpi-btn alpi-btn-primary">Login</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>