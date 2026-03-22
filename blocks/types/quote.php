<?php
if (!defined('CONFIG_INCLUDED')) {
    die();
}

// Unique identifier for this Quote Slider instance
$quoteId = 'alpi-cms-content-quote-' . uniqid();

if (!function_exists('alpiCmsNormalizeResponsiveBlockSizeValue')) {
    function alpiCmsNormalizeResponsiveBlockSizeValue($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        return is_numeric($value) ? $value . 'px' : $value;
    }
}

// Parse quotes data
$quotesData = json_decode($block['quotes_data'] ?? '[]', true);

// Check if we have valid quotes data
if (!is_array($quotesData) || empty($quotesData)) {
    return; // Exit silently if no valid data
}

$quotesData = array_values(array_filter($quotesData, static function ($quote) {
    return trim((string) ($quote['content'] ?? '')) !== '';
}));

if (empty($quotesData)) {
    return;
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
                <?php
                $quoteStyles = [];

                if (!empty($quote['text_color'])) {
                    $quoteStyles[] = '--alpi-quote-slide-text-color: ' . htmlspecialchars((string) $quote['text_color'], ENT_QUOTES, 'UTF-8') . ';';
                }

                if (!empty($quote['background_color'])) {
                    $quoteStyles[] = '--alpi-quote-slide-background-color: ' . htmlspecialchars((string) $quote['background_color'], ENT_QUOTES, 'UTF-8') . ';';
                }

                foreach ($devices as $device) {
                    $sizeKey = "text_size_{$device}";
                    if (!empty($quote[$sizeKey])) {
                        $quoteStyles[] = '--alpi-quote-content-size-' . $device . ': ' . htmlspecialchars(alpiCmsNormalizeResponsiveBlockSizeValue($quote[$sizeKey]), ENT_QUOTES, 'UTF-8') . ';';
                    }
                }

                $quoteStyle = !empty($quoteStyles) ? ' style="' . implode(' ', $quoteStyles) . '"' : '';
                $quoteContent = htmlspecialchars((string) ($quote['content'] ?? ''), ENT_QUOTES, 'UTF-8');
                $quoteAuthor = trim((string) ($quote['author'] ?? ''));
                ?>
                <div class="quote-slide"<?php echo $quoteStyle; ?>>
                    <blockquote class="quote-content">
                        <?php echo $quoteContent; ?>
                    </blockquote>
                    <?php if ($quoteAuthor !== ''): ?>
                        <cite class="quote-author"><?php echo htmlspecialchars($quoteAuthor, ENT_QUOTES, 'UTF-8'); ?></cite>
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
