<?php
if (!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/../config/autoload.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../utils/helpers.php';
    require_once __DIR__ . '/../config/config.php';
    define('CONFIG_INCLUDED', true);
}

try {
    $db = new Database();
    $conn = $db->connect();

    if (!($conn instanceof PDO)) {
        throw new Exception('Error establishing a database connection.');
    }

    $base = rtrim(BASE_URL, '/');

    $pages = $conn->query("SELECT slug FROM contents WHERE is_active = 1 AND content_type_id = (SELECT id FROM content_types WHERE name = 'page')")->fetchAll(PDO::FETCH_ASSOC);
    $categories = $conn->query("SELECT slug FROM categories")->fetchAll(PDO::FETCH_ASSOC);
    $posts = $conn->query("SELECT c.slug AS post_slug, cat.slug AS category_slug FROM contents c INNER JOIN categories cat ON cat.id = c.category_id WHERE c.is_active = 1 AND c.content_type_id = (SELECT id FROM content_types WHERE name = 'post')")->fetchAll(PDO::FETCH_ASSOC);

    $urls = [$base . '/'];

    foreach ($pages as $page) {
        $urls[] = $base . '/' . $page['slug'];
    }

    foreach ($categories as $category) {
        $urls[] = $base . '/' . $category['slug'] . '/';
    }

    foreach ($posts as $post) {
        $urls[] = $base . '/' . $post['category_slug'] . '/' . $post['post_slug'];
    }

    header('Content-Type: application/xml; charset=UTF-8');

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($urls as $url) {
        echo '    <url><loc>' . htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc></url>' . "\n";
    }

    echo '</urlset>';
} catch (Throwable $e) {
    error_log('Sitemap error: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Unable to generate sitemap.';
}
