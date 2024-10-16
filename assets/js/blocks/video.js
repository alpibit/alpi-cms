document.addEventListener('DOMContentLoaded', function () {
    // Load YouTube API
    var tag = document.createElement('script');
    tag.src = "https://www.youtube.com/iframe_api";
    var firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

    // Initialize YouTube players when API is ready
    window.onYouTubeIframeAPIReady = function () {
        document.querySelectorAll('.video-container[data-video-type="youtube"]').forEach(function (container) {
            var videoId = container.getAttribute('data-video-id');
            var playerId = container.firstElementChild.id;

            new YT.Player(playerId, {
                height: '360',
                width: '640',
                videoId: videoId,
                playerVars: {
                    'playsinline': 1,
                    'rel': 0
                },
                events: {
                    'onReady': onPlayerReady
                }
            });
        });
    };

    function onPlayerReady(event) {
        // You can add custom behavior here when the player is ready
        console.log('YouTube player is ready');
    }
});