<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/autoload.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, '/');
$segments = explode('/', $path);
$pageSlug = $segments[0] ?? null;

if (!$pageSlug) {
    die('Page slug is missing.');
}

$dbInstance = new Database();
$dbConnection = $dbInstance->connect();

if (!($dbConnection instanceof PDO)) {
    die("Error establishing a database connection.");
}

// Instantiate the Page class
$pageObj = new Page($dbConnection);

// Retrieve the page ID by its slug
$pageData = $pageObj->getPageBySlug($pageSlug);

var_dump($pageData);

// Check if the page exists
if (!$pageData) {
    header("HTTP/1.0 404 Not Found");
    echo "Page not found.";
    exit;
}

// Now retrieve the full page data by ID
$singlePage = $pageObj->getPageById($pageData['id']);

// Retrieve blocks
$blocks = $singlePage['blocks'] ?? [];

include __DIR__ . '/../templates/header.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($singlePage['title'], ENT_QUOTES, 'UTF-8') ?: 'Page'; ?></title>
</head>

<body>
    <header>
        <h1><?= htmlspecialchars($singlePage['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
    </header>

    <main class="content">
        <!-- Display blocks -->
        <?php foreach ($blocks as $block) {
            renderBlock($block, $singlePage);
        } ?>
    </main>

    <footer>
        <!-- Footer content -->
        <a href="/">Back to Home</a>
    </footer>
</body>

</html>

<?php
// Include the footer template
include __DIR__ . '/../templates/footer.php';
?>