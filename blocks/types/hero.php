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
        return ['type' => 'image', 'code' => "background-image: url('$image'); position:absolute; top:0; left:0; width:100%; height:100%;"];
    } else {
        return ['type' => 'color', 'code' => "background-color: $color; position:absolute; top:0; left:0; width:100%; height:100%;"];
    }
}

$backgroundStyles = [
    'desktop' => getBackground($block['background_type_desktop'], $block['background_color'], $block['background_image_desktop'], $block['background_video_url']),
    'tablet' => getBackground($block['background_type_tablet'], $block['background_color'], $block['background_image_tablet'], $block['background_video_url']),
    'mobile' => getBackground($block['background_type_mobile'], $block['background_color'], $block['background_image_mobile'], $block['background_video_url'])
];
?>

<div class="hero-block <?php echo $layoutClass; ?>" style="color: <?php echo $blockTextColor; ?>;" data-text-size-desktop="<?php echo $blockTextSizeDesktop . 'px'; ?>" data-text-size-tablet="<?php echo $blockTextSizeTablet . 'px'; ?>" data-text-size-mobile="<?php echo $blockTextSizeMobile . 'px'; ?>" data-padding-top-desktop="<?php echo $blockPaddingTopDesktop . 'px'; ?>" data-padding-right-desktop="<?php echo $blockPaddingRightDesktop . 'px'; ?>" data-padding-bottom-desktop="<?php echo $blockPaddingBottomDesktop . 'px'; ?>" data-padding-left-desktop="<?php echo $blockPaddingLeftDesktop . 'px'; ?>" data-padding-top-tablet="<?php echo $blockPaddingTopTablet . 'px'; ?>" data-padding-right-tablet="<?php echo $blockPaddingRightTablet . 'px'; ?>" data-padding-bottom-tablet="<?php echo $blockPaddingBottomTablet . 'px'; ?>" data-padding-left-tablet="<?php echo $blockPaddingLeftTablet . 'px'; ?>" data-padding-top-mobile="<?php echo $blockPaddingTopMobile . 'px'; ?>" data-padding-right-mobile="<?php echo $blockPaddingRightMobile . 'px'; ?>" data-padding-bottom-mobile="<?php echo $blockPaddingBottomMobile . 'px'; ?>" data-padding-left-mobile="<?php echo $blockPaddingLeftMobile . 'px'; ?>" data-margin-top-desktop="<?php echo $blockMarginTopDesktop . 'px'; ?>" data-margin-right-desktop="<?php echo $blockMarginRightDesktop . 'px'; ?>" data-margin-bottom-desktop="<?php echo $blockMarginBottomDesktop . 'px'; ?>" data-margin-left-desktop="<?php echo $blockMarginLeftDesktop . 'px'; ?>" data-margin-top-tablet="<?php echo $blockMarginTopTablet . 'px'; ?>" data-margin-right-tablet="<?php echo $blockMarginRightTablet . 'px'; ?>" data-margin-bottom-tablet="<?php echo $blockMarginBottomTablet . 'px'; ?>" data-margin-left-tablet="<?php echo $blockMarginLeftTablet . 'px'; ?>" data-margin-top-mobile="<?php echo $blockMarginTopMobile . 'px'; ?>" data-margin-right-mobile="<?php echo $blockMarginRightMobile . 'px'; ?>" data-margin-bottom-mobile="<?php echo $blockMarginBottomMobile . 'px'; ?>" data-margin-left-mobile="<?php echo $blockMarginLeftMobile . 'px'; ?>">

    <div class="hero-background" style="<?php echo $backgroundStyles['desktop']['code']; ?>">
        <?php
        // check if it's video
        if ($backgroundStyles['desktop']['type'] === 'video') {
            echo $backgroundStyles['desktop']['code'];
        }
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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var heroBlock = document.querySelector('.hero-block');

        function applyResponsiveStyles() {
            var width = window.innerWidth;
            if (width < 768) { // Mobile styles
                heroBlock.style.fontSize = heroBlock.getAttribute('data-text-size-mobile');
                heroBlock.style.paddingTop = heroBlock.getAttribute('data-padding-top-mobile');
                heroBlock.style.paddingRight = heroBlock.getAttribute('data-padding-right-mobile');
                heroBlock.style.paddingBottom = heroBlock.getAttribute('data-padding-bottom-mobile');
                heroBlock.style.paddingLeft = heroBlock.getAttribute('data-padding-left-mobile');
                heroBlock.style.marginTop = heroBlock.getAttribute('data-margin-top-mobile');
                heroBlock.style.marginRight = heroBlock.getAttribute('data-margin-right-mobile');
                heroBlock.style.marginBottom = heroBlock.getAttribute('data-margin-bottom-mobile');
                heroBlock.style.marginLeft = heroBlock.getAttribute('data-margin-left-mobile');
            } else if (width < 992) { // Tablet styles
                heroBlock.style.fontSize = heroBlock.getAttribute('data-text-size-tablet');
                heroBlock.style.paddingTop = heroBlock.getAttribute('data-padding-top-tablet');
                heroBlock.style.paddingRight = heroBlock.getAttribute('data-padding-right-tablet');
                heroBlock.style.paddingBottom = heroBlock.getAttribute('data-padding-bottom-tablet');
                heroBlock.style.paddingLeft = heroBlock.getAttribute('data-padding-left-tablet');
                heroBlock.style.marginTop = heroBlock.getAttribute('data-margin-top-tablet');
                heroBlock.style.marginRight = heroBlock.getAttribute('data-margin-right-tablet');
                heroBlock.style.marginBottom = heroBlock.getAttribute('data-margin-bottom-tablet');
                heroBlock.style.marginLeft = heroBlock.getAttribute('data-margin-left-tablet');
            } else { // Desktop styles
                heroBlock.style.fontSize = heroBlock.getAttribute('data-text-size-desktop');
                heroBlock.style.paddingTop = heroBlock.getAttribute('data-padding-top-desktop');
                heroBlock.style.paddingRight = heroBlock.getAttribute('data-padding-right-desktop');
                heroBlock.style.paddingBottom = heroBlock.getAttribute('data-padding-bottom-desktop');
                heroBlock.style.paddingLeft = heroBlock.getAttribute('data-padding-left-desktop');
                heroBlock.style.marginTop = heroBlock.getAttribute('data-margin-top-desktop');
                heroBlock.style.marginRight = heroBlock.getAttribute('data-margin-right-desktop');
                heroBlock.style.marginBottom = heroBlock.getAttribute('data-margin-bottom-desktop');
                heroBlock.style.marginLeft = heroBlock.getAttribute('data-margin-left-desktop');
            }
            console.log('Responsive styles applied at width: ' + width);
        }

        setTimeout(applyResponsiveStyles, 3000); // Apply styles after a delay of 3 seconds
        window.addEventListener('resize', applyResponsiveStyles); // Reapply styles on window resize
    });
</script>
