<?php
if (!defined('CONFIG_INCLUDED')) {
    die();
}

// Unique identifier for this Slider Gallery instance
$sliderId = 'alpi-cms-content-slider-gallery-' . uniqid();

// Parse gallery data
$galleryData = json_decode($block['gallery_data'] ?? '[]', true);

// Check if we have valid gallery data
if (!is_array($galleryData) || empty($galleryData)) {
    return; // Exit silently if no valid data
}

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
                $spacingStyles[] = "--alpi-slider-gallery-{$property}-{$direction}-{$device}: {$value};";
            }
        }
    }
}

$spacingStyle = !empty($spacingStyles) ? ' style="' . implode(' ', $spacingStyles) . '"' : '';

// Start output buffering
ob_start();
?>

<div id="<?php echo $sliderId; ?>" class="alpi-cms-content-slider-gallery" <?php echo $spacingStyle; ?> data-slider-gallery>
    <div class="alpi-cms-content-container">
        <div class="slider-gallery">
            <?php foreach ($galleryData as $index => $slide): ?>
                <div class="slider-slide">
                    <img src="<?php echo htmlspecialchars($slide['url'], ENT_QUOTES, 'UTF-8'); ?>"
                        alt="<?php echo htmlspecialchars($slide['alt_text'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        class="slider-image">
                    <?php if (!empty($slide['caption'])): ?>
                        <div class="slider-caption"><?php echo htmlspecialchars($slide['caption'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
// Get the buffered content
$sliderGalleryHtml = ob_get_clean();

// Output the Slider Gallery HTML
echo $sliderGalleryHtml;
?>