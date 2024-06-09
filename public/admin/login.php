<?php

if (!defined('ROUTER_ACCESS')) {
    header("Location: /admin");
    exit;
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true) {
    header("Location: /public/admin/index.php");
    exit;
}

$db = new Database();
$conn = $db->connect();
$user = new User($conn);

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Validate user input
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        if ($user->authenticate($username, $password)) {
            session_regenerate_id(true);
            $userData = $user->getUserData($username);
            $_SESSION['loggedIn'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $user->getRole($username);
            $_SESSION['user_id'] = $userData['id'];
            header("Location: /public/admin/index.php");
            exit();
        } else {
            $error = 'Invalid credentials';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="/assets/css/admin/login.css">
</head>

<body class="login-page">
    
    <div class="container">
        <h1 class="login-title">Login to Admin Panel</h1>
        <?php if ($error) : ?>
            <p class="error-message"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form class="login-form" action="" method="POST">
            <div class="login-input-wrap">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <input type="submit" value="Login" class="login-button">
            </div>
        </form>
    </div>

</body>

</html>