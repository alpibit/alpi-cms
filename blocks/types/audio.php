<?php
if (!defined('CONFIG_INCLUDED')) {
    die();
}

$audioBlockId = 'alpi-cms-content-audio-' . uniqid();

if (!function_exists('alpiCmsGetAudioFileExtension')) {
    function alpiCmsGetAudioFileExtension($filename)
    {
        $path = parse_url((string) $filename, PHP_URL_PATH);
        return strtolower(pathinfo($path ?: (string) $filename, PATHINFO_EXTENSION));
    }
}

if (!function_exists('alpiCmsGetAudioMimeType')) {
    function alpiCmsGetAudioMimeType($extension)
    {
        $mimeTypes = [
            'mp3' => 'audio/mpeg',
            'mpeg' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            'oga' => 'audio/ogg',
            'webm' => 'audio/webm',
            'm4a' => 'audio/mp4',
            'aac' => 'audio/aac',
            'flac' => 'audio/flac',
        ];

        return $mimeTypes[$extension] ?? 'audio/mpeg';
    }
}

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
                $styles[] = "--alpi-audio-{$property}-{$direction}-{$device}: {$value};";
            }
        }
    }
}

$style = !empty($styles) ? ' style="' . implode(' ', $styles) . '"' : '';

$audioSource = $block['audio_source'] ?? 'url';
$audioUrl = trim((string) ($block['audio_url'] ?? ''));
$audioFile = trim((string) ($block['audio_file'] ?? ''));

if ($audioFile === '') {
    $audioFile = trim((string) ($block['video_url'] ?? ''));
}

if ($audioSource === 'upload' && $audioFile === '' && $audioUrl !== '') {
    $audioFile = $audioUrl;
}

if ($audioSource === 'url' && $audioUrl === '' && $audioFile !== '') {
    $audioUrl = $audioFile;
}

$audioPath = $audioSource === 'upload' ? $audioFile : $audioUrl;
$audioExtension = alpiCmsGetAudioFileExtension($audioPath);
$audioMimeType = alpiCmsGetAudioMimeType($audioExtension);

ob_start();
?>

<div id="<?php echo $audioBlockId; ?>" class="alpi-cms-content-audio"<?php echo $style; ?>>
    <div class="alpi-cms-content-container">
        <div class="alpi-cms-content-audio-inner">
            <?php if ($audioPath !== ''): ?>
                <audio controls preload="metadata" class="alpi-cms-content-audio-player">
                    <source src="<?php echo htmlspecialchars($audioPath, ENT_QUOTES, 'UTF-8'); ?>" type="<?php echo htmlspecialchars($audioMimeType, ENT_QUOTES, 'UTF-8'); ?>">
                    <p>Your browser doesn't support HTML5 audio. Here is a <a href="<?php echo htmlspecialchars($audioPath, ENT_QUOTES, 'UTF-8'); ?>">link to the audio file</a>.</p>
                </audio>
            <?php else: ?>
                <p class="audio-error">No valid audio source provided.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$audioBlockHtml = ob_get_clean();
echo $audioBlockHtml;
