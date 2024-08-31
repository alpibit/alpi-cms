document.addEventListener('DOMContentLoaded', function () {
    const ctaBlocks = document.querySelectorAll('.alpi-cms-content-cta');

    ctaBlocks.forEach(cta => {
        const backgroundDiv = cta.querySelector('.alpi-cms-content-cta-background');
        const youtubeId = cta.dataset.youtubeId;

        const updateBackground = () => {
            const style = getComputedStyle(cta);
            const backgroundType = style.getPropertyValue('--alpi-cta-background-type-desktop').trim();
            const background = style.getPropertyValue('--alpi-cta-background-desktop').trim();

            // Remove any existing iframe
            const existingIframe = backgroundDiv.querySelector('iframe');
            if (existingIframe) {
                existingIframe.remove();
            }

            if (backgroundType === 'video' && youtubeId) {
                const iframe = document.createElement('iframe');
                iframe.src = `https://www.youtube.com/embed/${youtubeId}?autoplay=1&mute=1&controls=0&loop=1&playlist=${youtubeId}`;
                iframe.allow = "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture";
                iframe.allowFullscreen = true;
                iframe.style.position = 'absolute';
                iframe.style.top = '0';
                iframe.style.left = '0';
                iframe.style.width = '100%';
                iframe.style.height = '100%';
                iframe.style.border = 'none';

                backgroundDiv.appendChild(iframe);
            } else if (backgroundType === 'image') {
                backgroundDiv.style.backgroundImage = `url(${background})`;
                backgroundDiv.style.backgroundSize = 'cover';
                backgroundDiv.style.backgroundPosition = 'center';
            } else if (backgroundType === 'color') {
                backgroundDiv.style.backgroundColor = background;
            }
        };

        updateBackground();
        window.addEventListener('resize', updateBackground);
    });
});