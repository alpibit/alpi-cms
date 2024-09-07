<?php
if (!defined('CONFIG_INCLUDED')) {
    die();
}

// Unique identifier for this image-text block instance
$imageTextBlockId = 'alpi-cms-content-image-text-' . uniqid();

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
                $spacingStyles[] = "--alpi-image-text-{$property}-{$direction}-{$device}: {$value};";
            }
        }
    }

    // Text size for each device
    if (!empty($block["title_size_{$device}"])) {
        $spacingStyles[] = "--alpi-image-text-title-size-{$device}: " . htmlspecialchars($block["title_size_{$device}"], ENT_QUOTES, 'UTF-8') . ";";
    }
    if (!empty($block["content_size_{$device}"])) {
        $spacingStyles[] = "--alpi-image-text-content-size-{$device}: " . htmlspecialchars($block["content_size_{$device}"], ENT_QUOTES, 'UTF-8') . ";";
    }
}

// Background color
if (!empty($block['background_color'])) {
    $spacingStyles[] = "--alpi-image-text-background-color: " . htmlspecialchars($block['background_color'], ENT_QUOTES, 'UTF-8') . ";";
}

$spacingStyle = !empty($spacingStyles) ? ' style="' . implode(' ', $spacingStyles) . '"' : '';

// Start output buffering
ob_start();
?>

<div id="<?php echo $imageTextBlockId; ?>" class="alpi-cms-content-image-text" <?php echo $spacingStyle; ?>>
    <div class="alpi-cms-content-container">
        <div class="alpi-cms-content-image-text-inner">
            <div class="alpi-cms-content-image-text-image">
                <?php if (!empty($block['image_path'])): ?>
                    <img src="<?php echo htmlspecialchars($block['image_path'], ENT_QUOTES, 'UTF-8'); ?>"
                        alt="<?php echo htmlspecialchars($block['alt_text'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        class="alpi-cms-content-image-text-img">
                <?php endif; ?>
                <?php if (!empty($block['caption'])): ?>
                    <figcaption class="alpi-cms-content-image-text-caption">
                        <?php echo htmlspecialchars($block['caption'], ENT_QUOTES, 'UTF-8'); ?>
                    </figcaption>
                <?php endif; ?>
            </div>
            <div class="alpi-cms-content-image-text-content">
                <?php if (!empty($block['title'])): ?>
                    <h2 class="alpi-cms-content-image-text-title"><?php echo htmlspecialchars($block['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                <?php endif; ?>

                <?php if (!empty($block['content'])): ?>
                    <div class="alpi-cms-content-image-text-body">
                        <?php echo htmlspecialchars($block['content'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Get the buffered content
$imageTextBlockHtml = ob_get_clean();

// Output the image-text block HTML
echo $imageTextBlockHtml;
?>