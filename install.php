<?php
require_once 'config/config.php';
require_once 'classes/Database.php';
require_once 'classes/Installer.php';

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
    if (strlen($adminPass) < 8) {
        $errors[] = "Admin password should be at least 8 characters long.";
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
<html>

<head>
    <title>AlpiCMS Installation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .alpi-install-wrap {
            max-width: 500px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .alpi-install-wrap .alpi-install-heading {
            color: #333;
            margin-top: 0;
        }

        .alpi-install-wrap .alpi-install-alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alpi-install-wrap .alpi-install-alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alpi-install-wrap .alpi-install-form .alpi-install-form-group {
            margin-bottom: 20px;
        }

        .alpi-install-wrap .alpi-install-form .alpi-install-form-label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .alpi-install-wrap .alpi-install-form .alpi-install-form-control {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .alpi-install-wrap .alpi-install-form .alpi-install-btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }

        .alpi-install-wrap .alpi-install-form .alpi-install-btn-primary {
            background-color: #007bff;
            color: #fff;
            border: none;
        }

        .alpi-install-wrap .alpi-install-form .alpi-install-btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="alpi-install-wrap">
        <h1 class="alpi-install-heading">AlpiCMS Installation</h1>
        <?php if (!empty($errors)) : ?>
            <div class="alpi-install-alert alpi-install-alert-danger">
                <ul>
                    <?php foreach ($errors as $error) : ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="post" class="alpi-install-form" action="<?php echo BASE_URL; ?>/install.php">
            <div class="alpi-install-form-group">
                <label for="db_host" class="alpi-install-form-label">Database Host</label>
                <input type="text" name="db_host" id="db_host" class="alpi-install-form-control" required>
            </div>
            <div class="alpi-install-form-group">
                <label for="db_name" class="alpi-install-form-label">Database Name</label>
                <input type="text" name="db_name" id="db_name" class="alpi-install-form-control" required>
            </div>
            <div class="alpi-install-form-group">
                <label for="db_user" class="alpi-install-form-label">Database User</label>
                <input type="text" name="db_user" id="db_user" class="alpi-install-form-control" required>
            </div>
            <div class="alpi-install-form-group">
                <label for="db_pass" class="alpi-install-form-label">Database Password</label>
                <input type="password" name="db_pass" id="db_pass" class="alpi-install-form-control">
            </div>
            <div class="alpi-install-form-group">
                <label for="admin_email" class="alpi-install-form-label">Admin Email</label>
                <input type="email" name="admin_email" id="admin_email" class="alpi-install-form-control" required>
            </div>
            <div class="alpi-install-form-group">
                <label for="admin_user" class="alpi-install-form-label">Admin Username</label>
                <input type="text" name="admin_user" id="admin_user" class="alpi-install-form-control" required>
            </div>
            <div class="alpi-install-form-group">
                <label for="admin_pass" class="alpi-install-form-label">Admin Password</label>
                <input type="password" name="admin_pass" id="admin_pass" class="alpi-install-form-control" required>
            </div>
            <div class="alpi-install-form-group">
                <label for="website_url" class="alpi-install-form-label">Website URL</label>
                <input type="url" name="website_url" id="website_url" class="alpi-install-form-control" required>
            </div>
            <button type="submit" class="alpi-install-btn alpi-install-btn-primary">Install</button>
        </form>
    </div>
</body>

</html>