<?php
if (!defined('CONFIG_INCLUDED')) {
    die();
}

$freeCodeBlockId = 'alpi-cms-content-free-code-' . uniqid();

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
                $styles[] = "--alpi-free-code-{$property}-{$direction}-{$device}: {$value};";
            }
        }
    }
}

$style = !empty($styles) ? ' style="' . implode(' ', $styles) . '"' : '';
$freeCodeContent = $block['free_code_content'] ?? '';

ob_start();
?>

<div id="<?php echo $freeCodeBlockId; ?>" class="alpi-cms-content-free-code"<?php echo $style; ?>>
    <div class="alpi-cms-content-container">
        <div class="alpi-cms-content-free-code-inner">
            <?php echo $freeCodeContent; ?>
        </div>
    </div>
</div>

<?php
$freeCodeBlockHtml = ob_get_clean();
echo $freeCodeBlockHtml;
