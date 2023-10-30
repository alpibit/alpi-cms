<?php

function setupTables($conn)
{
    // SQL statement for creating a `content_types` table
    $contentTypesSQL = "
        CREATE TABLE content_types (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE
        );
    ";

    // SQL statement for creating a `contents` table
    $contentsTableSQL = "
        CREATE TABLE contents (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            content_type_id INT(11) NOT NULL,
            user_id INT(11),
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (content_type_id) REFERENCES content_types(id)
        );
    ";

    // SQL statement for creating a `settings` table
    $settingsTableSQL = "
        CREATE TABLE settings (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(255) NOT NULL UNIQUE,
            setting_value TEXT NOT NULL
        );
    ";

    // SQL statement for creating a `users` table
    $usersTableSQL = "
        CREATE TABLE users (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            role ENUM('admin', 'editor') DEFAULT 'editor',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ";

    $blocksTableSQL = "
        CREATE TABLE blocks (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            content_id INT(11) NOT NULL,
            type ENUM('text', 'image_text', 'image', 'cta') NOT NULL,
            content TEXT NOT NULL,
            image_path VARCHAR(255),
            order_num INT(11) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (content_id) REFERENCES contents(id)
        );
    ";

    $contentTypesInsertSQL = "
    INSERT INTO content_types (name) VALUES ('post'), ('page');
";

    // Execute the SQL statements
    $conn->exec($contentTypesSQL);
    $conn->exec($contentsTableSQL);
    $conn->exec($settingsTableSQL);
    $conn->exec($usersTableSQL);
    $conn->exec($blocksTableSQL);
    $conn->exec($contentTypesInsertSQL);
}

function generateSlug($string)
{
    $string = trim($string);
    $slug = strtolower($string);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

function insertSampleBlock($conn, $contentId, $block)
{
    $sql = "INSERT INTO blocks (content_id, type, content, image_path, order_num) VALUES (:contentId, :type, :content, :image_path, :order_num)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':contentId', $contentId);
    $stmt->bindParam(':type', $block['type']);
    $stmt->bindParam(':content', $block['content']);
    $stmt->bindParam(':image_path', $block['image_path']);
    $stmt->bindParam(':order_num', $block['order_num']);
    $stmt->execute();
}

function insertSampleContent($conn, $contentType, $content)
{
    // Get the ID of the content type
    $sqlContentType = "SELECT id FROM content_types WHERE name = :contentType";
    $stmt = $conn->prepare($sqlContentType);
    $stmt->bindParam(':contentType', $contentType);
    $stmt->execute();
    $contentTypeId = $stmt->fetchColumn();

    $slug = generateSlug($content['title']);
    $sql = "INSERT INTO contents (content_type_id, title, slug) VALUES (:contentTypeId, :title, :slug)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':contentTypeId', $contentTypeId);
    $stmt->bindParam(':title', $content['title']);
    $stmt->bindParam(':slug', $slug);
    $stmt->execute();
    return $conn->lastInsertId();
}

function setDefaultSettings($conn)
{
    $sql = "INSERT INTO settings (setting_key, setting_value) VALUES 
    ('site_name', 'My New Site'),
    ('footer_text', 'My Site powered by AlpiCMS'),
    ('header_logo', 'path_to_logo_image.jpg')"; // !!! Need to change this
    $conn->exec($sql);
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

        $samplePage = [
            'title' => 'About Us',
            'content' => 'This is an about page for our new CMS. Edit or delete it to start creating your own content!'
        ];

        setupTables($conn);

        $samplePostId = insertSampleContent($conn, 'post', $samplePost);

        $sampleBlocks = [
            [
                'type' => 'text',
                'content' => 'This is a text block content.',
                'image_path' => '',
                'order_num' => 1
            ],
            [
                'type' => 'image_text',
                'content' => 'This is the text content for the image-text block.',
                'image_path' => 'path_to_sample_image.jpg',
                'order_num' => 2
            ]
        ];

        foreach ($sampleBlocks as $block) {
            insertSampleBlock($conn, $samplePostId, $block);
        }

        insertSampleContent($conn, 'page', $samplePage);
        setDefaultSettings($conn);
        flagAsInstalled($conn);
        createAdminUser($conn, $admin_user, $admin_pass);

        echo "Installation successful! For security reasons, please delete or rename the install.php file.";
        exit;
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