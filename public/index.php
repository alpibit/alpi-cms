<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/autoload.php';

session_start();

// Load utility functions (in progress)
require_once __DIR__ . '/../utils/helpers.php';

$dbInstance = new Database();
$dbConnection = $dbInstance->connect();

if ($dbConnection instanceof PDO) {
    $postObj = new Post($dbConnection);
} else {
    die("Error establishing a database connection.");
}

$latestPosts = $postObj->getLatestPosts();

// Include header template (in progress)
include __DIR__ . '/../templates/header.php';
?>

<!-- Start the HTML section -->

<?php if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn']) : ?>
    <a href="/public/admin/">Admin</a>
<?php else : ?>
    <a href="/public/admin/login.php">Log in</a>
<?php endif; ?>

<?php foreach ($latestPosts as $post) : ?>
    <h2><a href="/public/single-post.php?id=<?php echo $post['id']; ?>"><?php echo $post['title']; ?></a></h2>
    <p><?php echo $post['content']; ?></p>
    <hr>
<?php endforeach; ?>

<!-- End the HTML section -->

<?php
include __DIR__ . '/../templates/footer.php';
?>