<?php
if (!defined('CONFIG_INCLUDED')) {
    die();
}

// Unique identifier for this post picker block instance
$postPickerBlockId = 'alpi-cms-content-post-picker-' . uniqid();

// Prepare spacing styles
$spacingStyles = [];
$devices = ['desktop', 'tablet', 'mobile'];
$properties = ['padding', 'margin'];
$directions = ['top', 'bottom'];

foreach ($devices as $device) {
    foreach ($properties as $property) {
        foreach ($directions as $direction) {
            $key = "{$property}_{$direction}_{$device}";
            if (isset($block[$key]) && $block[$key] !== '') {
                $value = is_numeric($block[$key]) ? $block[$key] . 'px' : $block[$key];
                $spacingStyles[] = "--alpi-post-picker-{$property}-{$direction}-{$device}: {$value};";
            }
        }
    }
}

$spacingStyle = !empty($spacingStyles) ? ' style="' . implode(' ', $spacingStyles) . '"' : '';

// Get selected post IDs
$selectedPostIds = !empty($block['selected_post_ids']) ? explode(',', $block['selected_post_ids']) : [];

// Fetch posts
$postObj = new Post($conn);
$posts = [];
foreach ($selectedPostIds as $postId) {
    $post = $postObj->getPostById($postId);
    if (!empty($post)) {
        $posts[] = $post[0]; // getPostById returns an array of posts, we need the first (and only) element
    }
}

// Start output buffering
ob_start();
?>

<div id="<?php echo $postPickerBlockId; ?>" class="alpi-cms-content-post-picker" <?php echo $spacingStyle; ?>>
    <div class="alpi-cms-content-container">
        <?php if (!empty($posts)): ?>
            <div class="alpi-cms-content-post-picker-grid">
                <?php foreach ($posts as $post): ?>
                    <a href="/<?php echo htmlspecialchars($post['category_slug'], ENT_QUOTES, 'UTF-8'); ?>/<?php echo htmlspecialchars($post['slug'], ENT_QUOTES, 'UTF-8'); ?>" class="alpi-cms-content-post-picker-item-link">
                        <div class="alpi-cms-content-post-picker-item">
                            <?php if (!empty($post['main_image_path']) && $post['show_main_image']): ?>
                                <img src="<?php echo htmlspecialchars($post['main_image_path'], ENT_QUOTES, 'UTF-8'); ?>"
                                    alt="<?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                    class="alpi-cms-content-post-picker-image">
                            <?php else: ?>
                                <div class="alpi-cms-content-post-picker-image-placeholder"></div>
                            <?php endif; ?>
                            <h3 class="alpi-cms-content-post-picker-title">
                                <?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?>
                            </h3>
                            <?php if (!empty($post['subtitle'])): ?>
                                <p class="alpi-cms-content-post-picker-subtitle">
                                    <?php echo htmlspecialchars($post['subtitle'], ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No posts selected.</p>
        <?php endif; ?>
    </div>
</div>

<?php
// Get the buffered content
$postPickerBlockHtml = ob_get_clean();

// Output the post picker block HTML
echo $postPickerBlockHtml;
?>