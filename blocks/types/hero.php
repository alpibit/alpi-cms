<?php
$layoutClass = "hero-layout-$blockHeroLayout";
$textStyle = "color: $blockTextColor; font-size: $blockTextSizeDesktop;";

function getBackground($type, $color, $image, $video)
{
    if ($type === 'video') {
        if (strpos($video, 'youtube.com') !== false) {
            preg_match("/youtube\.com.*(\?v=|\/embed\/)(.{11})/", $video, $matches);
            $videoId = $matches[2];
            return ['type' => 'video', 'code' => "<iframe src='https://www.youtube.com/embed/$videoId?autoplay=1&loop=1&playlist=$videoId&controls=0&showinfo=0&autohide=1&mute=1' style='position:absolute; top:0; left:0; width:100%; height:100%; border:0; z-index:-100;' frameborder='0' allow='autoplay; encrypted-media' allowfullscreen></iframe>"];
        } elseif (strpos($video, 'vimeo.com') !== false) {
            preg_match("/vimeo\.com\/(\d+)/", $video, $matches);
            $videoId = $matches[1];
            return ['type' => 'video', 'code' => "<iframe src='https://player.vimeo.com/video/$videoId?background=1&autoplay=1&muted=1&loop=1&byline=0&title=0' style='position:absolute; top:0; left:0; width:100%; height:100%; border:0; z-index:-100;' frameborder='0' allow='autoplay; fullscreen' allowfullscreen></iframe>"];
        }
    } elseif ($type === 'image') {
        return ['type' => 'image', 'code' => "background-image: url('$image');"];
    } else {
        return ['type' => 'color', 'code' => "background-color: $color;"];
    }
}

$backgroundStyles = [
    'desktop' => getBackground($block['background_type_desktop'], $block['background_color'], $block['background_image_desktop'], $block['background_video_url']),
    'tablet' => getBackground($block['background_type_tablet'], $block['background_color'], $block['background_image_tablet'], $block['background_video_url']),
    'mobile' => getBackground($block['background_type_mobile'], $block['background_color'], $block['background_image_mobile'], $block['background_video_url'])
];
?>

<div class="hero-block <?php echo $layoutClass; ?>">
    <div class="hero-background">
        <?php
        echo $backgroundStyles['desktop']['code'];
        ?>
    </div>
    <div class="hero-content" style="<?php echo $textStyle; ?>">
        <?php if (!empty($blockTitle)) : ?>
            <h1><?php echo $blockTitle; ?></h1>
        <?php endif; ?>
        <?php if (!empty($blockContent)) : ?>
            <p><?php echo $blockContent; ?></p>
        <?php endif; ?>
    </div>
</div>

<style>
    .hero-block {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 400px;
        width: 100%;
        position: relative;
        overflow: hidden;
    }

    .hero-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
    }

    .hero-content {
        position: relative;
        z-index: 1;
        width: 100%;
        text-align: center;
    }

    .hero-layout-center {
        text-align: center;
    }

    .hero-layout-left {
        text-align: left;
        justify-content: flex-start;
    }

    .hero-layout-right {
        text-align: right;
        justify-content: flex-end;
    }
</style>