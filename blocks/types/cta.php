<?php
if (!defined('CONFIG_INCLUDED')) {
    die();
}

// Unique identifier for this CTA instance
$ctaId = 'alpi-cms-content-cta-' . uniqid();

// Helper function to get background value
function getBackgroundValue($type, $image, $color)
{
    switch ($type) {
        case 'image':
            return $image ? htmlspecialchars($image, ENT_QUOTES, 'UTF-8') : 'none';
        case 'color':
            return $color ? htmlspecialchars($color, ENT_QUOTES, 'UTF-8') : 'transparent';
        default:
            return 'none';
    }
}

// Prepare styles
$styles = [];
$devices = ['desktop', 'tablet', 'mobile'];
$properties = ['padding', 'margin'];
$directions = ['top', 'bottom'];

foreach ($devices as $device) {
    $type = $block["background_type_{$device}"] ?? '';
    $image = $block["background_image_{$device}"] ?? '';
    $color = $block['background_color'] ?? '';

    $background = getBackgroundValue($type, $image, $color);
    $styles[] = "--alpi-cta-background-{$device}: " . ($type === 'image' ? "url('{$background}')" : $background) . ";";

    foreach ($properties as $property) {
        foreach ($directions as $direction) {
            $key = "{$property}_{$direction}_{$device}";
            if (isset($block[$key]) && $block[$key] !== '') {
                $value = is_numeric($block[$key]) ? $block[$key] . 'px' : $block[$key];
                $styles[] = "--alpi-cta-{$property}-{$direction}-{$device}: {$value};";
            }
        }
    }

    if (!empty($block["text_size_{$device}"])) {
        $styles[] = "--alpi-cta-text-size-{$device}: " . htmlspecialchars($block["text_size_{$device}"], ENT_QUOTES, 'UTF-8') . ";";
    }
}

if (!empty($block['text_color'])) {
    $styles[] = "--alpi-cta-text-color: " . htmlspecialchars($block['text_color'], ENT_QUOTES, 'UTF-8') . ";";
}

if (!empty($block['overlay_color'])) {
    $styles[] = "--alpi-cta-overlay-color: " . htmlspecialchars($block['overlay_color'], ENT_QUOTES, 'UTF-8') . ";";
}

$style = !empty($styles) ? ' style="' . implode(' ', $styles) . '"' : '';

// Start output buffering
ob_start();
?>

<div id="<?php echo $ctaId; ?>" class="alpi-cms-content-cta <?php echo htmlspecialchars($block['class'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo $style; ?> aria-label="<?php echo htmlspecialchars($block['aria_label'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <div class="alpi-cms-content-container">
        <div class="alpi-cms-content-cta-inner">
            <?php if (!empty($block['title'])): ?>
                <h2 class="alpi-cms-content-cta-title"><?php echo htmlspecialchars($block['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <?php endif; ?>

            <?php if (!empty($block['content'])): ?>
                <div class="alpi-cms-content-cta-content"><?php echo htmlspecialchars($block['content'], ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <div class="alpi-cms-content-cta-buttons">
                <?php if (!empty($block['url1']) && !empty($block['cta_text1'])): ?>
                    <a href="<?php echo htmlspecialchars($block['url1'], ENT_QUOTES, 'UTF-8'); ?>" class="alpi-cms-content-cta-button alpi-cms-content-cta-button-1">
                        <?php echo htmlspecialchars($block['cta_text1'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php endif; ?>

                <?php if (!empty($block['url2']) && !empty($block['cta_text2'])): ?>
                    <a href="<?php echo htmlspecialchars($block['url2'], ENT_QUOTES, 'UTF-8'); ?>" class="alpi-cms-content-cta-button alpi-cms-content-cta-button-2">
                        <?php echo htmlspecialchars($block['cta_text2'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Get the buffered content
$ctaHtml = ob_get_clean();

// Output the CTA HTML
echo $ctaHtml;
?>