<?php

if (!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../config/autoload.php';
    require_once __DIR__ . '/../utils/helpers.php';
    define('CONFIG_INCLUDED', true);
}


include __DIR__ . '/../templates/header.php';
?>
<div class="container">
    <h1>Oops! Page Not Found</h1>
    <p>We can't seem to find the page you're looking for.</p>

    <div class="search-bar">
        <form action="<?= BASE_URL ?>/public/search.php" method="GET">
            <input type="text" name="query" placeholder="Search our site..." required>
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="navigation-links">
        <p>Here are some helpful links:</p>
        <ul>
            <li><a href="<?= BASE_URL ?>">Home</a></li>
            <li><a href="<?= BASE_URL ?>/about">About Us</a></li>
            <li><a href="<?= BASE_URL ?>/contact">Contact Us</a></li>
        </ul>
    </div>

    <div class="site-map">
        <h2>Site Map</h2>
        <ul>
            <?php
            // Fetch pages and display them in a site map
            try {
                $db = new Database();
                $conn = $db->connect();
                $stmt = $conn->query("SELECT title, slug FROM contents WHERE content_type_id = (SELECT id FROM content_types WHERE name = 'page')");
                $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($pages as $page) {
                    echo '<li><a href="/' . htmlspecialchars($page['slug'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($page['title'], ENT_QUOTES, 'UTF-8') . '</a></li>';
                }
            } catch (PDOException $e) {
                // Handle query errors gracefully
                echo '<li>Error loading site map.</li>';
            }
            ?>
        </ul>
    </div>
</div>

<style>
    .container {
        text-align: center;
        padding: 50px;
    }

    .search-bar {
        margin: 20px 0;
    }

    .search-bar input {
        padding: 10px;
        width: 70%;
        max-width: 400px;
        margin-right: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .search-bar button {
        padding: 10px 20px;
        border: none;
        background-color: #007BFF;
        color: white;
        border-radius: 5px;
        cursor: pointer;
    }

    .search-bar button:hover {
        background-color: #0056b3;
    }

    .navigation-links ul,
    .site-map ul {
        list-style: none;
        padding: 0;
    }

    .navigation-links li,
    .site-map li {
        margin: 10px 0;
    }

    .navigation-links a,
    .site-map a {
        color: #007BFF;
        text-decoration: none;
    }

    .navigation-links a:hover,
    .site-map a:hover {
        text-decoration: underline;
    }
</style>

<?php
include __DIR__ . '/../templates/footer.php';
?>