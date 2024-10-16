<?php
if (!defined('CONFIG_INCLUDED')) {
    die();
}

// Unique identifier for this Video Block instance
$videoBlockId = 'alpi-cms-content-video-' . uniqid();

// Helper function to get YouTube video ID
function getYoutubeId($url) {
    $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i';
    if (preg_match($pattern, $url, $match)) {
        return $match[1];
    }
    return false;
}

// Helper function to get Vimeo video ID
function getVimeoId($url) {
    $pattern = '/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/';
    if (preg_match($pattern, $url, $match)) {
        return $match[5];
    }
    return false;
}

// Helper function to get file extension
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

// Helper function to get MIME type
function getMimeType($extension) {
    $mime_types = [
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'ogg' => 'video/ogg',
        'avi' => 'video/x-msvideo',
        'mov' => 'video/quicktime',
        'wmv' => 'video/x-ms-wmv',
        'flv' => 'video/x-flv',
        '3gp' => 'video/3gpp',
    ];
    return $mime_types[$extension] ?? 'video/mp4';
}

// Helper function to check if file exists
function fileExists($filePath) {
    return file_exists($_SERVER['DOCUMENT_ROOT'] . parse_url($filePath, PHP_URL_PATH));
}

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
                $styles[] = "--alpi-video-{$property}-{$direction}-{$device}: {$value};";
            }
        }
    }
}

$style = !empty($styles) ? ' style="' . implode(' ', $styles) . '"' : '';

// Start output buffering
ob_start();
?>

<div id="<?php echo $videoBlockId; ?>" class="alpi-cms-content-video" <?php echo $style; ?>>
    <div class="alpi-cms-content-container">
        <div class="alpi-cms-content-video-inner">
            <?php
            $videoSource = $block['video_source'] ?? '';
            $videoUrl = $block['video_url'] ?? '';
            $videoFile = $block['video_file'] ?? '';

            if ($videoSource === 'url') {
                $youtubeId = getYoutubeId($videoUrl);
                $vimeoId = getVimeoId($videoUrl);

                if ($youtubeId) {
                    ?>
                    <div class="video-container" data-video-type="youtube" data-video-id="<?php echo htmlspecialchars($youtubeId, ENT_QUOTES, 'UTF-8'); ?>">
                        <div id="youtube-player-<?php echo $videoBlockId; ?>"></div>
                    </div>
                    <?php
                } elseif ($vimeoId) {
                    ?>
                    <div class="video-container" data-video-type="vimeo" data-video-id="<?php echo htmlspecialchars($vimeoId, ENT_QUOTES, 'UTF-8'); ?>">
                        <iframe src="https://player.vimeo.com/video/<?php echo htmlspecialchars($vimeoId, ENT_QUOTES, 'UTF-8'); ?>" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
                    </div>
                    <?php
                } else {
                    echo '<p class="video-error">Invalid video URL provided.</p>';
                }
            } elseif ($videoSource === 'upload' && $videoFile) {
                $fileExtension = getFileExtension($videoFile);
                $mimeType = getMimeType($fileExtension);
                ?>
                <div class="video-container" data-video-type="upload">
                    <video controls preload="metadata" playsinline>
                        <source src="<?php echo htmlspecialchars($videoFile, ENT_QUOTES, 'UTF-8'); ?>" type="<?php echo $mimeType; ?>">
                        <?php
                        $mp4File = str_replace('.'.$fileExtension, '.mp4', $videoFile);
                        if ($fileExtension !== 'mp4' && fileExists($mp4File)) : 
                        ?>
                            <source src="<?php echo htmlspecialchars($mp4File, ENT_QUOTES, 'UTF-8'); ?>" type="video/mp4">
                        <?php endif; ?>
                        <p>Your browser doesn't support HTML5 video. Here is a <a href="<?php echo htmlspecialchars($videoFile, ENT_QUOTES, 'UTF-8'); ?>" download>link to download the video</a> instead.</p>
                    </video>
                </div>
                <?php
            } else {
                echo '<p class="video-error">No valid video source provided.</p>';
            }
            ?>
        </div>
    </div>
</div>

<?php
// Get the buffered content
$videoBlockHtml = ob_get_clean();

// Output the Video Block HTML
echo $videoBlockHtml;
?>