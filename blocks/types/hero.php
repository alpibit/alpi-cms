<?php
if (!defined('CONFIG_INCLUDED')) {
    die();
}

// Unique identifier for this Hero instance
$heroId = 'alpi-cms-content-hero-' . uniqid();

// Helper function to get background value (with a unique name for the Hero block)
function getHeroBackgroundValue($type, $image, $color)
{
    switch ($type) {
        case 'image':
            return $image ? "url('" . htmlspecialchars($image, ENT_QUOTES, 'UTF-8') . "')" : 'none';
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

    $background = getHeroBackgroundValue($type, $image, $color);
    $styles[] = "--alpi-hero-background-{$device}: " . ($background) . ";";

    foreach ($properties as $property) {
        foreach ($directions as $direction) {
            $key = "{$property}_{$direction}_{$device}";
            if (isset($block[$key]) && $block[$key] !== '') {
                $value = is_numeric($block[$key]) ? $block[$key] . 'px' : $block[$key];
                $styles[] = "--alpi-hero-{$property}-{$direction}-{$device}: {$value};";
            }
        }
    }

    if (!empty($block["title_size_{$device}"])) {
        $styles[] = "--alpi-hero-title-size-{$device}: " . htmlspecialchars($block["title_size_{$device}"], ENT_QUOTES, 'UTF-8') . ";";
    }
    if (!empty($block["content_size_{$device}"])) {
        $styles[] = "--alpi-hero-content-size-{$device}: " . htmlspecialchars($block["content_size_{$device}"], ENT_QUOTES, 'UTF-8') . ";";
    }
}

if (!empty($block['text_color'])) {
    $styles[] = "--alpi-hero-text-color: " . htmlspecialchars($block['text_color'], ENT_QUOTES, 'UTF-8') . ";";
}

if (!empty($block['overlay_color'])) {
    $overlay_color = $block['overlay_color'];
    // Convert hex to rgba with 0.5 opacity
    if (preg_match('/#([a-f0-9]{6})/i', $overlay_color, $matches)) {
        $hex = $matches[1];
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $overlay_color = "rgba($r, $g, $b, 0.5)";
    }
    $styles[] = "--alpi-hero-overlay-color: " . $overlay_color . ";";
} else {
    // Default semi-transparent black if no color is set
    $styles[] = "--alpi-hero-overlay-color: rgba(0, 0, 0, 0.5);";
}


$style = !empty($styles) ? ' style="' . implode(' ', $styles) . '"' : '';

// Hero layout
$heroLayout = $block['hero_layout'] ?? 'center';

// Start output buffering
ob_start();
?>

<div id="<?php echo $heroId; ?>" class="alpi-cms-content-hero alpi-cms-content-hero-<?php echo $heroLayout; ?> <?php echo htmlspecialchars($block['class'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo $style; ?> aria-label="<?php echo htmlspecialchars($block['aria_label'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <div class="alpi-cms-content-hero-overlay"></div>
    <div class="alpi-cms-content-container">
        <div class="alpi-cms-content-hero-inner">
            <?php if (!empty($block['title'])): ?>
                <h1 class="alpi-cms-content-hero-title"><?php echo htmlspecialchars($block['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <?php endif; ?>

            <?php if (!empty($block['content'])): ?>
                <div class="alpi-cms-content-hero-content"><?php echo htmlspecialchars($block['content'], ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Get the buffered content
$heroHtml = ob_get_clean();

// Output the Hero HTML
echo $heroHtml;
?>