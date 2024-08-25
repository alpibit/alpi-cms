<?php
if (!defined('CONFIG_INCLUDED')) {
    die();
}

// Unique identifier for this accordion instance
$accordionId = 'alpi-cms-content-accordion-' . uniqid();

// Parse accordion data
$accordionData = json_decode($blockAccordionData, true);

// Check if we have valid accordion data
if (!is_array($accordionData) || empty($accordionData)) {
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
            $key_hyphen = "{$property}-{$direction}-{$device}";
            $key_underscore = "{$property}_{$direction}_{$device}";

            if (isset($block[$key_hyphen]) && $block[$key_hyphen] !== '') {
                $value = is_numeric($block[$key_hyphen]) ? $block[$key_hyphen] . 'px' : $block[$key_hyphen];
                $spacingStyles[] = "--alpi-accordion-{$key_hyphen}: {$value};";
            } elseif (isset($block[$key_underscore]) && $block[$key_underscore] !== '') {
                $value = is_numeric($block[$key_underscore]) ? $block[$key_underscore] . 'px' : $block[$key_underscore];
                $spacingStyles[] = "--alpi-accordion-{$key_hyphen}: {$value};";
            }
        }
    }
}

$spacingStyle = !empty($spacingStyles) ? ' style="' . implode(' ', $spacingStyles) . '"' : '';


// Start output buffering
ob_start();
?>

<div id="<?php echo $accordionId; ?>" class="alpi-cms-content-accordion" <?php echo $spacingStyle; ?>>
    <div class="alpi-cms-content-container">
        <?php foreach ($accordionData as $index => $section): ?>
            <?php
            $sectionId = $accordionId . '-section-' . $index;
            $headerId = $sectionId . '-header';
            $contentId = $sectionId . '-content';

            // Skip this section if title or content is missing
            if (empty($section['title']) || empty($section['content'])) {
                continue;
            }

            // Sanitize inputs
            $title = htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8');
            $content = htmlspecialchars($section['content'], ENT_QUOTES, 'UTF-8');
            ?>
            <div class="alpi-cms-content-accordion-section" id="<?php echo $sectionId; ?>">
                <h3 id="<?php echo $headerId; ?>" class="alpi-cms-content-accordion-header">
                    <button class="alpi-cms-content-accordion-trigger" aria-expanded="false" aria-controls="<?php echo $contentId; ?>">
                        <?php echo $title; ?>
                    </button>
                </h3>
                <div id="<?php echo $contentId; ?>" class="alpi-cms-content-accordion-content" aria-labelledby="<?php echo $headerId; ?>" role="region" hidden>
                    <div class="alpi-cms-content-accordion-content-inner">
                        <?php echo $content; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
// Get the buffered content
$accordionHtml = ob_get_clean();

// Output the accordion HTML
echo $accordionHtml;

?>