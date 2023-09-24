<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/autoload.php';

session_start();

// Load utility functions
require_once __DIR__ . '/../utils/helpers.php';

$dbInstance = new Database();
$dbConnection = $dbInstance->connect();

if (!($dbConnection instanceof PDO)) {
    die("Error establishing a database connection.");
}

$postObj = new Post($dbConnection);
$latestPosts = $postObj->getLatestPosts();

include __DIR__ . '/../templates/header.php';
?>

<!-- Start the HTML section -->

<div class="navbar">
    <?php if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn']) : ?>
        <a class="navbar-link" href="/public/admin/">Admin Dashboard</a>
    <?php else : ?>
        <a class="navbar-link" href="/public/admin/login.php">Log in</a>
    <?php endif; ?>
</div>

<main class="content">
    <?php foreach ($latestPosts as $post) : ?>
        <article class="post">
            <h2 class="post-title">
                <a href="/public/single-post.php?slug=<?php echo htmlspecialchars($post['slug'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </h2>
            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8')); ?>
            </div>
        </article>
        <hr class="post-divider">
    <?php endforeach; ?>
</main>

<!-- End the HTML section -->

<?php
include __DIR__ . '/../templates/footer.php';
?>