<?php
if (!defined('CONFIG_INCLUDED')) {
    die();
}

// Unique identifier for this CTA instance
$ctaId = 'alpi-cms-content-cta-' . uniqid();

// Helper function to get background style
function getBackgroundStyle($type, $image, $video, $color)
{
    switch ($type) {
        case 'image':
            return $image ? "background-image: url('" . htmlspecialchars($image, ENT_QUOTES, 'UTF-8') . "');" : '';
        case 'video':
            return $video ? "background-video: url('" . htmlspecialchars($video, ENT_QUOTES, 'UTF-8') . "');" : '';
        case 'color':
            return $color ? "background-color: " . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . ";" : '';
        default:
            return '';
    }
}

// Prepare background styles
$backgroundStyles = [];
$devices = ['desktop', 'tablet', 'mobile'];
foreach ($devices as $device) {
    $type = $block["background_type_{$device}"] ?? '';
    $image = $block["background_image_{$device}"] ?? '';
    $video = $device === 'desktop' ? ($block['background_video_url'] ?? '') : '';
    $color = $block['background_color'] ?? '';
    $opacity = $block["background_opacity_{$device}"] ?? 1;

    $style = getBackgroundStyle($type, $image, $video, $color);
    if ($style) {
        $backgroundStyles[] = "--alpi-cta-background-{$device}: {$style}";
        $backgroundStyles[] = "--alpi-cta-background-opacity-{$device}: {$opacity};";
    }
}

// Prepare spacing styles
$spacingStyles = [];
$properties = ['padding', 'margin'];
$directions = ['top', 'bottom'];

foreach ($devices as $device) {
    foreach ($properties as $property) {
        foreach ($directions as $direction) {
            $key = "{$property}_{$direction}_{$device}";
            if (isset($block[$key]) && $block[$key] !== '') {
                $value = is_numeric($block[$key]) ? $block[$key] . 'px' : $block[$key];
                $spacingStyles[] = "--alpi-cta-{$property}-{$direction}-{$device}: {$value};";
            }
        }
    }
}

// Combine all styles
$styles = array_merge($backgroundStyles, $spacingStyles);
$style = !empty($styles) ? ' style="' . implode(' ', $styles) . '"' : '';

// Start output buffering
ob_start();
?>

<div id="<?php echo $ctaId; ?>" class="alpi-cms-content-cta" <?php echo $style; ?>>
    <div class="alpi-cms-content-container">
        <div class="alpi-cms-content-cta-inner">
            <?php if (!empty($blockTitle)): ?>
                <h2 class="alpi-cms-content-cta-title"><?php echo htmlspecialchars($blockTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
            <?php endif; ?>

            <?php if (!empty($blockContent)): ?>
                <div class="alpi-cms-content-cta-content"><?php echo htmlspecialchars($blockContent, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <div class="alpi-cms-content-cta-buttons">
                <?php if (!empty($blockUrl1) && !empty($blockCtaText1)): ?>
                    <a href="<?php echo htmlspecialchars($blockUrl1, ENT_QUOTES, 'UTF-8'); ?>" class="alpi-cms-content-cta-button alpi-cms-content-cta-button-1">
                        <?php echo htmlspecialchars($blockCtaText1, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php endif; ?>

                <?php if (!empty($blockUrl2) && !empty($blockCtaText2)): ?>
                    <a href="<?php echo htmlspecialchars($blockUrl2, ENT_QUOTES, 'UTF-8'); ?>" class="alpi-cms-content-cta-button alpi-cms-content-cta-button-2">
                        <?php echo htmlspecialchars($blockCtaText2, ENT_QUOTES, 'UTF-8'); ?>
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