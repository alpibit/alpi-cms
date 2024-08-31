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
            return $image ? $image : 'none';
        case 'video':
            return $video ? $video : 'none';
        case 'color':
            return $color ? $color : 'transparent';
        default:
            return 'none';
    }
}

// Helper function to extract YouTube video ID
function getYoutubeVideoId($url)
{
    $videoId = '';
    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
        $videoId = $match[1];
    }
    return $videoId;
}

// Prepare background styles
$backgroundStyles = [];
$devices = ['desktop', 'tablet', 'mobile'];
foreach ($devices as $device) {
    $type = ${"blockBackgroundType" . ucfirst($device)};
    $image = ${"blockBackgroundImage" . ucfirst($device)};
    $video = $device === 'desktop' ? $blockBackgroundVideoUrl : '';
    $color = $blockBackgroundColor;
    $opacity = ${"blockBackgroundOpacity" . ucfirst($device)};

    $style = getBackgroundStyle($type, $image, $video, $color);
    $backgroundStyles[] = "--alpi-cta-background-{$device}: {$style};";
    $backgroundStyles[] = "--alpi-cta-background-type-{$device}: {$type};";
    $backgroundStyles[] = "--alpi-cta-background-opacity-{$device}: {$opacity};";
}

// YouTube video ID
$youtubeVideoId = getYoutubeVideoId($blockBackgroundVideoUrl);

// Prepare spacing styles
$spacingStyles = [];
$properties = ['padding', 'margin'];
$directions = ['top', 'bottom'];

foreach ($devices as $device) {
    foreach ($properties as $property) {
        foreach ($directions as $direction) {
            $key = "block" . ucfirst($property) . ucfirst($direction) . ucfirst($device);
            if (isset($$key) && $$key !== '') {
                $value = is_numeric($$key) ? $$key . 'px' : $$key;
                $spacingStyles[] = "--alpi-cta-{$property}-{$direction}-{$device}: {$value};";
            }
        }
    }
}

// Combine all styles
$styles = array_merge($backgroundStyles, $spacingStyles);

// Additional custom styles
if (!empty($blockTextColor)) {
    $styles[] = "--alpi-cta-text-color: " . htmlspecialchars($blockTextColor, ENT_QUOTES, 'UTF-8') . ";";
}
if (!empty($blockOverlayColor)) {
    $styles[] = "--alpi-cta-overlay-color: " . htmlspecialchars($blockOverlayColor, ENT_QUOTES, 'UTF-8') . ";";
}
foreach ($devices as $device) {
    $key = "blockTextSize" . ucfirst($device);
    if (!empty($$key)) {
        $styles[] = "--alpi-cta-text-size-{$device}: " . htmlspecialchars($$key, ENT_QUOTES, 'UTF-8') . ";";
    }
}

$style = !empty($styles) ? ' style="' . implode(' ', $styles) . '"' : '';

// Start output buffering
ob_start();
?>

<div id="<?php echo $ctaId; ?>" class="alpi-cms-content-cta <?php echo htmlspecialchars($blockClass, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $style; ?> aria-label="<?php echo htmlspecialchars($blockAriaLabel, ENT_QUOTES, 'UTF-8'); ?>" data-youtube-id="<?php echo $youtubeVideoId; ?>">
    <div class="alpi-cms-content-cta-background"></div>
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