<?php
if (!empty($blockSelectedPostIds)) {
    $post = new Post($dbConnection);
    $selectedPostIds = explode(',', $blockSelectedPostIds);


    if (is_array($selectedPostIds) && count($selectedPostIds) > 0) : ?>
        <div class="post-picker-block" style="<?= !empty($blockBackgroundColor) ? "background-color: " . htmlspecialchars($blockBackgroundColor) . ";" : "" ?>
            <?= !empty($blockTopPadding) ? "padding-top: " . htmlspecialchars($blockTopPadding) . ";" : "" ?>
            <?= !empty($blockBottomPadding) ? "padding-bottom: " . htmlspecialchars($blockBottomPadding) . ";" : "" ?>
        ">
            <?php if (!empty($blockTitle)) : ?>
                <h2><?= htmlspecialchars($blockTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
            <?php endif; ?>

            <?php if (!empty($blockContent)) : ?>
                <p><?= nl2br(htmlspecialchars($blockContent, ENT_QUOTES, 'UTF-8')); ?></p>
            <?php endif; ?>

            <ul>
                <?php
                foreach ($selectedPostIds as $postId) {
                    $postDetails = $post->getPostDetailsById(trim($postId));
                    if ($postDetails && !empty($postDetails['title'])) {
                        $categorySlug = $post->getCategorySlugByPostId($postId);
                        $router = new Router($dbConnection);
                        $postUrl = $router->generateUrl('post', $postDetails['slug'], $categorySlug);
                        echo '<li><a href="' . htmlspecialchars($postUrl, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($postDetails['title'], ENT_QUOTES, 'UTF-8') . '</a></li>';
                    }
                }
                ?>
            </ul>
        </div>

        <style>
            .post-picker-block {
                margin-bottom: 20px;
                padding: 10px;
            }

            .post-picker-block h2 {
                margin-bottom: 10px;
            }

            .post-picker-block ul {
                list-style-type: none;
                padding: 0;
            }

            .post-picker-block ul li {
                margin-bottom: 5px;
            }

            .post-picker-block ul li a {
                text-decoration: none;
                color: #007bff;
            }
        </style>
<?php endif;
}
?>