<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/helpers.php';
require_once __DIR__ . '/../config/config.php';

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    echo json_encode([]);
    exit;
}

$term = isset($_GET['term']) ? $_GET['term'] : '';

if (empty($term)) {
    echo json_encode([]);
    exit;
}

$results = [];
$searchTerm = '%' . $term . '%';

try {
    $stmt = $conn->prepare("SELECT title, slug FROM contents WHERE content_type_id = (SELECT id FROM content_types WHERE name = 'page') AND (title LIKE :term OR subtitle LIKE :term)");
    $stmt->execute(['term' => $searchTerm]);
    $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($pages as $page) {
        $results[] = [
            'title' => htmlspecialchars($page['title'], ENT_QUOTES, 'UTF-8'),
            'url' => BASE_URL . '/' . htmlspecialchars($page['slug'], ENT_QUOTES, 'UTF-8'),
            'type' => 'Page'
        ];
    }

    $stmt = $conn->prepare("SELECT c.title, c.slug, cat.slug as category_slug FROM contents c JOIN categories cat ON c.category_id = cat.id WHERE c.content_type_id = (SELECT id FROM content_types WHERE name = 'post') AND (c.title LIKE :term OR c.subtitle LIKE :term)");
    $stmt->execute(['term' => $searchTerm]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($posts as $post) {
        $results[] = [
            'title' => htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'),
            'url' => BASE_URL . '/' . htmlspecialchars($post['category_slug'], ENT_QUOTES, 'UTF-8') . '/' . htmlspecialchars($post['slug'], ENT_QUOTES, 'UTF-8'),
            'type' => 'Post'
        ];
    }

    $stmt = $conn->prepare("SELECT name, slug FROM categories WHERE name LIKE :term");
    $stmt->execute(['term' => $searchTerm]);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($categories as $category) {
        $results[] = [
            'title' => htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'),
            'url' => BASE_URL . '/category/' . htmlspecialchars($category['slug'], ENT_QUOTES, 'UTF-8'),
            'type' => 'Category'
        ];
    }

    $stmt = $conn->prepare("
        SELECT DISTINCT b.content_id 
        FROM blocks b 
        WHERE b.title LIKE :term 
           OR b.content LIKE :term 
           OR b.alt_text LIKE :term 
           OR b.caption LIKE :term
    ");
    $stmt->execute(['term' => $searchTerm]);
    $contentIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($contentIds)) {
        $placeholders = implode(',', array_fill(0, count($contentIds), '?'));

        $stmt = $conn->prepare("SELECT title, slug FROM contents WHERE id IN ($placeholders) AND content_type_id = (SELECT id FROM content_types WHERE name = 'page')");
        $stmt->execute($contentIds);
        $pagesFromBlocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($pagesFromBlocks as $page) {
            $url = BASE_URL . '/' . htmlspecialchars($page['slug'], ENT_QUOTES, 'UTF-8');
            if (!in_array($url, array_column($results, 'url'))) {
                $results[] = [
                    'title' => htmlspecialchars($page['title'], ENT_QUOTES, 'UTF-8'),
                    'url' => $url,
                    'type' => 'Page (from block)'
                ];
            }
        }

        $stmt = $conn->prepare("SELECT c.title, c.slug, cat.slug as category_slug FROM contents c JOIN categories cat ON c.category_id = cat.id WHERE c.id IN ($placeholders) AND c.content_type_id = (SELECT id FROM content_types WHERE name = 'post')");
        $stmt->execute($contentIds);
        $postsFromBlocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($postsFromBlocks as $post) {
            $url = BASE_URL . '/' . htmlspecialchars($post['category_slug'], ENT_QUOTES, 'UTF-8') . '/' . htmlspecialchars($post['slug'], ENT_QUOTES, 'UTF-8');
            if (!in_array($url, array_column($results, 'url'))) {
                $results[] = [
                    'title' => htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'),
                    'url' => $url,
                    'type' => 'Post (from block)'
                ];
            }
        }
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode([]);
    exit;
}

echo json_encode($results);
