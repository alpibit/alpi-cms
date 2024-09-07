<?php
if (!defined('CONFIG_INCLUDED')) {
    die();
}

// Unique identifier for this image block instance
$imageBlockId = 'alpi-cms-content-image-' . uniqid();

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
                $spacingStyles[] = "--alpi-image-{$property}-{$direction}-{$device}: {$value};";
            }
        }
    }
}

$spacingStyle = !empty($spacingStyles) ? ' style="' . implode(' ', $spacingStyles) . '"' : '';

// Start output buffering
ob_start();
?>

<div id="<?php echo $imageBlockId; ?>" class="alpi-cms-content-image" <?php echo $spacingStyle; ?>>
    <div class="alpi-cms-content-container">
        <figure class="alpi-cms-content-image-figure">
            <?php if (!empty($block['image_path'])): ?>
                <img src="<?php echo htmlspecialchars($block['image_path'], ENT_QUOTES, 'UTF-8'); ?>"
                    alt="<?php echo htmlspecialchars($block['alt_text'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    class="alpi-cms-content-image-img">
            <?php endif; ?>
            <?php if (!empty($block['caption'])): ?>
                <figcaption class="alpi-cms-content-image-caption">
                    <?php echo htmlspecialchars($block['caption'], ENT_QUOTES, 'UTF-8'); ?>
                </figcaption>
            <?php endif; ?>
        </figure>
    </div>
</div>

<?php
// Get the buffered content
$imageBlockHtml = ob_get_clean();

// Output the image block HTML
echo $imageBlockHtml;
?>