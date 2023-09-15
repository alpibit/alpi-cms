<?php

function setupTables($conn)
{
    // SQL statement for creating a `posts` table with the slug column
    $postsTableSQL = "
        CREATE TABLE posts (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );
    ";

    // SQL statement for creating a `settings` table
    $settingsTableSQL = "
        CREATE TABLE settings (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(255) NOT NULL,
            setting_value TEXT NOT NULL
        );
    ";

    $usersTableSQL = "
    CREATE TABLE users (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'editor') DEFAULT 'admin',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
";

    // Execute the SQL statements
    $conn->exec($postsTableSQL);
    $conn->exec($settingsTableSQL);
    $conn->exec($usersTableSQL);
}

function generateSlug($string)
{
    $string = trim($string);
    $slug = strtolower($string);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}
function insertSamplePost($conn, $post)
{
    $slug = generateSlug($post['title']);
    $sql = "INSERT INTO posts (title, slug, content) VALUES (:title, :slug, :content)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':title', $post['title']);
    $stmt->bindParam(':slug', $slug);
    $stmt->bindParam(':content', $post['content']);
    $stmt->execute();
}

function flagAsInstalled($conn)
{
    $sql = "INSERT INTO settings (setting_key, setting_value) VALUES ('installed', 'true')";
    $conn->exec($sql);
}

function createAdminUser($conn, $username, $password)
{
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password, role) VALUES (:username, :hashedPassword, 'admin')";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':hashedPassword', $hashedPassword);
    $stmt->execute();
}

// Check if config/database.php exists
if (file_exists('config/database.php')) {
    die("The CMS is already installed. For security reasons, please delete or rename the install.php file.");
}

// If the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $host = $_POST['db_host'];
    $name = $_POST['db_name'];
    $user = $_POST['db_user'];
    $pass = $_POST['db_pass'];
    $admin_user = trim($_POST['admin_user']);
    $admin_pass = trim($_POST['admin_pass']);

    if (empty($host) || empty($name) || empty($user) || empty($admin_user) || empty($admin_pass)) {
        echo "Please fill in all the fields!";
        exit;
    }

    if (strlen($admin_user) < 5 || strlen($admin_pass) < 5) {
        echo "Admin username and password should be at least 5 characters long!";
        exit;
    }

    $configContent = "<?php\n\n";
    $configContent .= "define('DB_HOST', '{$host}');\n";
    $configContent .= "define('DB_NAME', '{$name}');\n";
    $configContent .= "define('DB_USER', '{$user}');\n";
    $configContent .= "define('DB_PASS', '{$pass}');\n\n";
    $configContent .= "?>";

    require 'classes/Database.php';
    $db = new Database($host, $name, $user, $pass);

    // Test the database connection
    $conn = $db->connect();

    if ($conn instanceof PDO) {
        file_put_contents('config/database.php', $configContent);
        require 'config/database.php';

        $samplePost = [
            'title' => 'Welcome to Your New CMS!',
            'content' => 'Congratulations on successfully installing your new CMS. This is a sample post. You can edit or delete it to start creating your own content!'
        ];

        setupTables($conn);
        insertSamplePost($conn, $samplePost);
        flagAsInstalled($conn);
        createAdminUser($conn, $admin_user, $admin_pass);

        echo "Installation successful! For security reasons, please delete or rename the install.php file.";
        exit;
    } else {
        echo "Failed to connect to the database. Please check your credentials and try again.";
    }
}

?>

<form method="post">
    DB Host: <input type="text" name="db_host" required><br>
    DB Name: <input type="text" name="db_name" required><br>
    DB User: <input type="text" name="db_user" required><br>
    DB Password: <input type="text" name="db_pass" required><br><br>
    Admin Username: <input type="text" name="admin_user" required><br>
    Admin Password: <input type="password" name="admin_pass" required><br>
    <input type="submit" value="Install">
</form>