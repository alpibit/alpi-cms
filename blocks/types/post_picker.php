<div class="post-picker-block">

    <?php if (isset($block['title']) && !empty($block['title'])) : ?>
        <h2><?= htmlspecialchars($block['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
    <?php endif; ?>

    <?php if (isset($block['content']) && !empty($block['content'])) : ?>
        <p><?= nl2br(htmlspecialchars($block['content'], ENT_QUOTES, 'UTF-8')); ?></p>
    <?php endif; ?>
    
    <?php if (isset($block['selected_post_ids']) && !empty($block['selected_post_ids'])) : ?>
        <ul>
            <?php
            $blockSelectedPostIds = explode(',', $block['selected_post_ids']);
            foreach ($blockSelectedPostIds as $postId) {
                $postDetails = $post->getPostDetailsById($postId);
                if ($postDetails) {
                    $postUrl = BASE_URL . '/public/single-post.php?slug=' . $postDetails['slug'];
                    if (isset($postDetails['title'])) {
                        $postTitle = htmlspecialchars($postDetails['title'], ENT_QUOTES, 'UTF-8');
                        echo '<li><a href="' . htmlspecialchars($postUrl, ENT_QUOTES, 'UTF-8') . '">' . $postTitle . '</a></li>';
                    }
                }
            } ?>
        </ul>
    <?php endif; ?>
</div>