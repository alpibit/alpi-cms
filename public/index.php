<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/autoload.php';

// Start session
session_start();

// Load utility functions
require_once __DIR__ . '/../utils/helpers.php';

try {
    $dbInstance = new Database();
    $dbConnection = $dbInstance->connect();

    // Ensure the connection is a PDO instance
    if (!($dbConnection instanceof PDO)) {
        throw new Exception("Error establishing a database connection.");
    }

    $postObj = new Post($dbConnection);
    $latestPosts = $postObj->getLatestPosts();
} catch (Exception $e) {
    die($e->getMessage());
}

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
            <?php foreach ($post['blocks'] as $block) : ?>
                <div class="post-content">
                    <?php echo nl2br(htmlspecialchars($block['content'], ENT_QUOTES, 'UTF-8')); ?>
                </div>
            <?php endforeach; ?>
        </article>
        <hr class="post-divider">
    <?php endforeach; ?>
</main>

<!-- End the HTML section -->

<?php
include __DIR__ . '/../templates/footer.php';
?>