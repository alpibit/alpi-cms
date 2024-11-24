<?php
if (!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/../config/autoload.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../utils/helpers.php';
    require_once __DIR__ . '/../config/config.php';
    define('CONFIG_INCLUDED', true);
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!($conn instanceof PDO)) {
        throw new Exception("Error establishing a database connection.");
    }

    // Get the category slug from the URL
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $categorySlug = trim($requestUri, '/');

    $categoryObj = new Category($conn);
    $postObj = new Post($conn);

    // Fetch the category details
    $category = $categoryObj->getCategoryBySlug($categorySlug);
    if (!$category) {
        header("HTTP/1.0 404 Not Found");
        require __DIR__ . '/404.php';
        exit;
    }

    // Fetch posts in this category
    $posts = $postObj->getPostsByCategoryId($category['id']);

    $router = new Router($conn);

?>
    <?php include __DIR__ . '/../templates/header.php'; ?>


    <h1>Posts in Category: <?= htmlspecialchars($category['name'] ?? "") ?></h1>
    <?php foreach ($posts as $post) :
        // Generate URL for the post
        $postUrl = $router->generateUrl('post', $post['slug'], $categorySlug);
    ?>
        <div>
            <h2><a href="<?= $postUrl ?>"><?= htmlspecialchars($post['title'] ?? "") ?></a></h2>
            <p><?= htmlspecialchars($post['content'] ?? "") ?></p>
        </div>
    <?php endforeach; ?>

    <?php include __DIR__ . '/../templates/footer.php'; ?>

<?php
} catch (Exception $e) {
    die($e->getMessage());
}
?>