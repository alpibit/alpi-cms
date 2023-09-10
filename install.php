<?php

require 'config/database.php';
require 'classes/Database.php';

$db = new Database();
$conn = $db->connect();

// Check if the CMS is already installed
function isInstalled($conn)
{
    try {
        $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'installed'");
        $stmt->execute();
        $installed = $stmt->fetchColumn();

        return $installed === 'true';
    } catch (PDOException $e) {
        // Table probably doesn't exist yet
        return false;
    }
}

if (isInstalled($conn)) {
    die("The CMS is already installed. For security reasons, please delete or rename the install.php file.");
}

$samplePost = [
    'title' => 'Welcome to Your New CMS!',
    'content' => 'Congratulations on successfully installing your new CMS. This is a sample post. You can edit or delete it to start creating your own content!'
];

function setupTables($conn)
{
    // SQL statement for creating a `posts` table
    $postsTableSQL = "
        CREATE TABLE posts (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
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

function insertSamplePost($conn, $post)
{
    $sql = "INSERT INTO posts (title, content) VALUES (:title, :content)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':title', $post['title']);
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

setupTables($conn);
insertSamplePost($conn, $samplePost);
flagAsInstalled($conn);
createAdminUser($conn, "admin", "secureAdminPassword");

echo "Installation successful! For security reasons, please delete or rename the install.php file.";
