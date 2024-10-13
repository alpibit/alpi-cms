<?php
if (!defined('CONFIG_INCLUDED')) {
    die();
}

// Unique identifier for this Quote Slider instance
$quoteId = 'alpi-cms-content-quote-' . uniqid();

// Parse quotes data
$quotesData = json_decode($block['quotes_data'] ?? '[]', true);

// Check if we have valid quotes data
if (!is_array($quotesData) || empty($quotesData)) {
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
                $spacingStyles[] = "--alpi-quote-{$property}-{$direction}-{$device}: {$value};";
            }
        }
    }
}

$spacingStyle = !empty($spacingStyles) ? ' style="' . implode(' ', $spacingStyles) . '"' : '';

// Start output buffering
ob_start();
?>

<div id="<?php echo $quoteId; ?>" class="alpi-cms-content-quote-slider" <?php echo $spacingStyle; ?> data-quote-slider>
    <div class="alpi-cms-content-container">
        <div class="quote-slider">
            <?php foreach ($quotesData as $index => $quote): ?>
                <div class="quote-slide" style="color: <?php echo htmlspecialchars($quote['text_color'] ?? '#000000', ENT_QUOTES, 'UTF-8'); ?>; background-color: <?php echo htmlspecialchars($quote['background_color'] ?? '#ffffff', ENT_QUOTES, 'UTF-8'); ?>;">
                    <blockquote class="quote-content">
                        <?php echo htmlspecialchars($quote['content'], ENT_QUOTES, 'UTF-8'); ?>
                    </blockquote>
                    <?php if (!empty($quote['author'])): ?>
                        <cite class="quote-author"><?php echo htmlspecialchars($quote['author'], ENT_QUOTES, 'UTF-8'); ?>
                        <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
// Get the buffered content
$quoteSliderHtml = ob_get_clean();

// Output the Quote Slider HTML
echo $quoteSliderHtml;
?>
