document.addEventListener('DOMContentLoaded', function () {
    const ctaBlocks = document.querySelectorAll('.alpi-cms-content-cta');

    ctaBlocks.forEach(cta => {
        const style = getComputedStyle(cta);
        const desktopBackground = style.getPropertyValue('--alpi-cta-background-desktop');

        if (desktopBackground.includes('background-video')) {
            const videoUrl = desktopBackground.match(/url\(['"]?(.+?)['"]?\)/)[1];

            const video = document.createElement('video');
            video.src = videoUrl;
            video.autoplay = true;
            video.loop = true;
            video.muted = true;
            video.style.position = 'absolute';
            video.style.top = '0';
            video.style.left = '0';
            video.style.width = '100%';
            video.style.height = '100%';
            video.style.objectFit = 'cover';
            video.style.zIndex = '-1';

            cta.insertBefore(video, cta.firstChild);
        }
    });
});