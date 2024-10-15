<?php
if (!defined('CONFIG_INCLUDED')) {
    die();
}

// Unique identifier for this Text Block instance
$textBlockId = 'alpi-cms-content-text-' . uniqid();

// Prepare styles
$styles = [];
$devices = ['desktop', 'tablet', 'mobile'];
$properties = ['padding', 'margin'];
$directions = ['top', 'bottom'];

foreach ($devices as $device) {
    foreach ($properties as $property) {
        foreach ($directions as $direction) {
            $key = "{$property}_{$direction}_{$device}";
            if (isset($block[$key]) && $block[$key] !== '') {
                $value = is_numeric($block[$key]) ? $block[$key] . 'px' : $block[$key];
                $styles[] = "--alpi-text-{$property}-{$direction}-{$device}: {$value};";
            }
        }
    }

    if (!empty($block["text_size_{$device}"])) {
        $size = is_numeric($block["text_size_{$device}"]) ? $block["text_size_{$device}"] . 'px' : $block["text_size_{$device}"];
        $styles[] = "--alpi-text-size-{$device}: " . htmlspecialchars($size, ENT_QUOTES, 'UTF-8') . ";";
    }
}

if (!empty($block['text_color'])) {
    $styles[] = "--alpi-text-color: " . htmlspecialchars($block['text_color'], ENT_QUOTES, 'UTF-8') . ";";
}

if (!empty($block['background_color'])) {
    $styles[] = "--alpi-text-background-color: " . htmlspecialchars($block['background_color'], ENT_QUOTES, 'UTF-8') . ";";
}

$style = !empty($styles) ? ' style="' . implode(' ', $styles) . '"' : '';

// Start output buffering
ob_start();
?>

<div id="<?php echo $textBlockId; ?>" class="alpi-cms-content-text" <?php echo $style; ?>>
    <div class="alpi-cms-content-text-background"></div>
    <div class="alpi-cms-content-container">
        <div class="alpi-cms-content-text-inner">
            <?php if (!empty($block['title'])): ?>
                <h2 class="alpi-cms-content-text-title"><?php echo htmlspecialchars($block['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <?php endif; ?>

            <?php if (!empty($block['content'])): ?>
                <div class="alpi-cms-content-text-content"><?php echo $block['content']; ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Get the buffered content
$textBlockHtml = ob_get_clean();

// Output the Text Block HTML
echo $textBlockHtml;
?>