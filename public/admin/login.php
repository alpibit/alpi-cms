<?php
session_start();

require '../../config/database.php';
require '../../config/autoload.php';

$db = new Database();
$conn = $db->connect();
$user = new User($conn);

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($user->authenticate($username, $password)) {
        $_SESSION['loggedIn'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $user->getRole($username);
        header("Location: /public/admin/index.php");
    } else {
        $error = 'Invalid credentials';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>

<h1>Login to Admin Panel</h1>
<?php if ($error): ?>
    <p style="color: red;"><?= $error ?></p>
<?php endif; ?>
<form action="" method="POST">
    Username: <input type="text" name="username"><br>
    Password: <input type="password" name="password"><br>
    <input type="submit" value="Login">
</form>

</body>
</html>
