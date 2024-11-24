<?php
if (!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/../config/autoload.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../utils/helpers.php';
    require_once __DIR__ . '/../config/config.php';
    define('CONFIG_INCLUDED', true);
}

if (!isset($_GET['query']) || empty($_GET['query'])) {
    header("Location: " . BASE_URL);
    exit();
}

$query = trim($_GET['query']);
$searchResults = [];
$errorMessage = '';

try {
    $db = new Database();
    $conn = $db->connect();
    $stmt = $conn->prepare("SELECT title, slug FROM contents WHERE title LIKE :query OR subtitle LIKE :query");
    $stmt->bindValue(':query', '%' . $query . '%', PDO::PARAM_STR);
    $stmt->execute();
    $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = "An error occurred while searching. Please try again later.";
    error_log('Search Query Error: ' . $e->getMessage());
}

include __DIR__ . '/../templates/header.php';
?>

<div class="container">
    <h1>Search Results for "<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>"</h1>

    <?php if (!empty($errorMessage)) : ?>
        <p class="error"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
    <?php elseif (empty($searchResults)) : ?>
        <p>No results found.</p>
    <?php else : ?>
        <ul class="search-results">
            <?php foreach ($searchResults as $result) : ?>
                <li>
                    <a href="<?= BASE_URL . '/' . htmlspecialchars($result['slug'], ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($result['title'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<style>
    .container {
        padding: 50px;
    }

    .search-results {
        list-style: none;
        padding: 0;
    }

    .search-results li {
        margin: 10px 0;
    }

    .search-results a {
        color: #007BFF;
        text-decoration: none;
    }

    .search-results a:hover {
        text-decoration: underline;
    }

    .error {
        color: red;
    }
</style>

<?php
include __DIR__ . '/../templates/footer.php';
?>