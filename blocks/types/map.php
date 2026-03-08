<?php
if (!defined('CONFIG_INCLUDED')) {
    die();
}

$mapBlockId = 'alpi-cms-content-map-' . uniqid();

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
                $styles[] = "--alpi-map-{$property}-{$direction}-{$device}: {$value};";
            }
        }
    }
}

$style = !empty($styles) ? ' style="' . implode(' ', $styles) . '"' : '';
$mapEmbedCode = $block['map_embed_code'] ?? '';

ob_start();
?>

<div id="<?php echo $mapBlockId; ?>" class="alpi-cms-content-map"<?php echo $style; ?>>
    <div class="alpi-cms-content-container">
        <div class="alpi-cms-content-map-inner">
            <?php echo $mapEmbedCode; ?>
        </div>
    </div>
</div>

<?php
$mapBlockHtml = ob_get_clean();
echo $mapBlockHtml;