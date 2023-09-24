<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Blog</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/header.css">
</head>

<body>

    <header class="header-wrap">
        <h1>Your Blog</h1>
        <div class="header-menu-container">
            <button>Posts</button>
            <div class="header-dropdown-content">
                <?php foreach ($latestPosts as $post) : ?>
                    <a href="/public/single-post.php?slug=<?php echo htmlspecialchars($post['slug'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </header>