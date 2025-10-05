<?php

if (!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/../config/autoload.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../utils/helpers.php';
    require_once __DIR__ . '/../config/config.php';
    define('CONFIG_INCLUDED', true);
}

$pageTitle = 'Page Not Found';

include __DIR__ . '/../templates/header.php';
?>
<div class="container">
    <h1>Oops! Page Not Found</h1>
    <p>We can't seem to find the page you're looking for.</p>

    <div class="search-bar">
        <div class="search-container">
            <input type="search" id="live-search-input-404" placeholder="Search our site..." aria-label="Search">
            <div id="live-search-results-404"></div>
        </div>
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

    .search-container {
        position: relative;
        display: inline-block;
        width: 70%;
        max-width: 400px;
    }

    .search-bar input {
        padding: 10px;
        width: 100%;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 16px;
    }

    #live-search-results-404 {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        border-radius: 0 0 5px 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        z-index: 1000;
        max-height: 300px;
        overflow-y: auto;
    }

    #live-search-results-404 ul {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    #live-search-results-404 li {
        margin: 0;
        border-bottom: 1px solid #eee;
    }

    #live-search-results-404 li:last-child {
        border-bottom: none;
    }

    #live-search-results-404 a {
        display: block;
        padding: 10px 15px;
        text-decoration: none;
        color: #333;
        transition: background-color 0.2s;
    }

    #live-search-results-404 a:hover,
    #live-search-results-404 li.selected a {
        background-color: #f5f5f5;
        color: #007BFF;
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