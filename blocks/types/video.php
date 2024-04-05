<?php

if ($blockVideoSource === 'url') {
    if (strpos($blockVideoUrl, 'vimeo') !== false) {
        $videoId = explode('vimeo.com/', $blockVideoUrl)[1];
        ?>
        <div class='video-container'>
            <iframe id='vimeo-video' class='video-player' src='https://player.vimeo.com/video/<?php echo $videoId; ?>' frameborder='0' allow='autoplay; fullscreen' allowfullscreen></iframe>
        </div>
        <?php
    } else {
        $videoId = explode('v=', $blockVideoUrl)[1];
        ?>
        <div class='video-container'>
            <iframe id='youtube-video' class='video-player' src='https://www.youtube.com/embed/<?php echo $videoId; ?>' frameborder='0' allow='autoplay; fullscreen' allowfullscreen></iframe>
        </div>
        <?php
    }
} else {
    ?>
    <div class='video-container'>
        <video id='uploaded-video' class='video-player' controls>
            <source src='/uploads/<?php echo $blockVideoFile; ?>' type='video/mp4'>
        </video>
    </div>
    <?php
}

?>

<style>
    .video-container {
        position: relative;
        width: 100%;
        height: 0;
        padding-bottom: 56.25%;
    }

    .video-player {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
</style>