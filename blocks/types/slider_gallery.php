<?php
// !!! Must add slider speed, show controls, show dots, auto run options
$sliderGalleryData = json_decode($block['gallery_data'] ?? '[]', true);
$sliderId = uniqid("slider_");
$sliderSpeed = 2000;
$showControls = true;
$showDots = true;
$autoRun = true;
?>


<div id="<?= $sliderId ?>" class="custom-slider">
    <div class="custom-slider-inner">
        <?php foreach ($sliderGalleryData as $index => $slide) : ?>
            <div class="custom-slide<?= $index === 0 ? ' active' : '' ?>">
                <img src="<?= $slide['url'] ?>" alt="<?= $slide['alt_text'] ?>">
                <?php if (!empty($slide['caption'])) : ?>
                    <div class="custom-slide-caption"><?= $slide['caption'] ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if ($showControls) : ?>
        <a class="custom-slider-prev">&#10094;</a>
        <a class="custom-slider-next">&#10095;</a>
    <?php endif; ?>
    <?php if ($showDots) : ?>
        <div class="custom-slider-dots">
            <?php foreach ($sliderGalleryData as $index => $slide) : ?>
                <span class="custom-dot<?= $index === 0 ? ' active' : '' ?>"></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>


<style>
    .custom-slider {
        position: relative;
        max-width: 100%;
        overflow: hidden;
    }

    .custom-slider-inner {
        display: flex;
        transition: transform 0.5s ease;
    }

    .custom-slide {
        display: none;
        width: 100%;
        flex-shrink: 0;
    }

    .custom-slide.active {
        display: block;
    }

    .custom-slider img {
        width: 100%;
        display: block;
    }

    .custom-slider-prev,
    .custom-slider-next {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        background-color: #fff;
        border: none;
        padding: 10px;
        margin-top: -22px;
    }

    .custom-slider-prev {
        left: 10px;
    }

    .custom-slider-next {
        right: 10px;
    }

    .custom-slider-dots {
        position: absolute;
        bottom: 10px;
        width: 100%;
        text-align: center;
    }

    .custom-dot {
        cursor: pointer;
        display: inline-block;
        margin: 0 5px;
        background-color: #bbb;
        border-radius: 50%;
        width: 15px;
        height: 15px;
    }

    .custom-dot.active {
        background-color: #717171;
    }
</style>




<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.custom-slider').forEach(function(slider) {
            const slides = slider.querySelectorAll('.custom-slide');
            const prevButton = slider.querySelector('.custom-slider-prev');
            const nextButton = slider.querySelector('.custom-slider-next');
            const dots = slider.querySelectorAll('.custom-dot');
            let currentIndex = 0;
            let autoRunInterval;

            function goToSlide(index) {
                slides.forEach((slide, i) => {
                    slide.classList.remove('active');
                    if (dots.length) dots[i].classList.remove('active');
                });

                slides[index].classList.add('active');
                if (dots.length) dots[index].classList.add('active');

                currentIndex = index;
            }

            function startAutoSlide() {
                clearInterval(autoRunInterval);
                if (<?= $autoRun ? 'true' : 'false' ?>) {
                    autoRunInterval = setInterval(() => {
                        const nextIndex = (currentIndex + 1) % slides.length;
                        goToSlide(nextIndex);
                    }, <?= $sliderSpeed ?>);
                }
            }

            nextButton.addEventListener('click', function() {
                const nextIndex = (currentIndex + 1) % slides.length;
                goToSlide(nextIndex);
                startAutoSlide();
            });

            prevButton.addEventListener('click', function() {
                const prevIndex = (currentIndex - 1 + slides.length) % slides.length;
                goToSlide(prevIndex);
                startAutoSlide();
            });

            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    goToSlide(index);
    
                    startAutoSlide();
                });
            });

            slider.addEventListener('mouseenter', () => clearInterval(autoRunInterval));
            slider.addEventListener('mouseleave', startAutoSlide);


            goToSlide(currentIndex);
            startAutoSlide();
        });
    });
</script>