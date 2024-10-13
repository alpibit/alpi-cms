document.addEventListener('DOMContentLoaded', function () {
    const quoteSliders = document.querySelectorAll('[data-quote-slider]');

    quoteSliders.forEach(slider => {
        const quoteSlider = slider.querySelector('.quote-slider');
        let slides = quoteSlider.querySelectorAll('.quote-slide');
        const slideCount = slides.length;
        let currentIndex = 1;
        let intervalId;

        const firstSlideClone = slides[0].cloneNode(true);
        const lastSlideClone = slides[slideCount - 1].cloneNode(true);

        quoteSlider.appendChild(firstSlideClone);
        quoteSlider.insertBefore(lastSlideClone, slides[0]);

        slides = quoteSlider.querySelectorAll('.quote-slide');

        quoteSlider.style.transform = 'translateX(-100%)';

        function showSlide(index, smooth = true) {
            if (smooth) {
                quoteSlider.style.transition = 'transform 0.5s ease';
            } else {
                quoteSlider.style.transition = 'none';
            }
            quoteSlider.style.transform = `translateX(-${index * 100}%)`;
        }

        function nextSlide() {
            currentIndex++;
            showSlide(currentIndex);
            if (currentIndex === slides.length - 1) {
                setTimeout(() => {
                    quoteSlider.style.transition = 'none';
                    currentIndex = 1;
                    showSlide(currentIndex, false);
                }, 500);
            }
        }

        function prevSlide() {
            currentIndex--;
            showSlide(currentIndex);
            if (currentIndex === 0) {
                setTimeout(() => {
                    quoteSlider.style.transition = 'none';
                    currentIndex = slides.length - 2;
                    showSlide(currentIndex, false);
                }, 500);
            }
        }

        function startAutoScroll() {
            stopAutoScroll();
            intervalId = setInterval(nextSlide, 5000);
        }

        function stopAutoScroll() {
            if (intervalId) {
                clearInterval(intervalId);
                intervalId = null;
            }
        }

        let startX = 0;
        let isSwiping = false;

        quoteSlider.addEventListener('touchstart', handleTouchStart, { passive: true });
        quoteSlider.addEventListener('touchmove', handleTouchMove, { passive: true });
        quoteSlider.addEventListener('touchend', handleTouchEnd);
        quoteSlider.addEventListener('mousedown', handleMouseDown);
        quoteSlider.addEventListener('mousemove', handleMouseMove);
        quoteSlider.addEventListener('mouseup', handleMouseUp);
        quoteSlider.addEventListener('mouseleave', handleMouseLeave);

        function handleTouchStart(e) {
            stopAutoScroll();
            startX = e.touches[0].clientX;
            isSwiping = true;
        }

        function handleTouchMove(e) {
            if (!isSwiping) return;
            let diffX = e.touches[0].clientX - startX;
            quoteSlider.style.transition = 'none';
            quoteSlider.style.transform = `translateX(${-currentIndex * 100 + (diffX / slider.offsetWidth) * 100}%)`;
        }

        function handleTouchEnd(e) {
            isSwiping = false;
            let diffX = e.changedTouches[0].clientX - startX;
            if (Math.abs(diffX) > 50) {
                if (diffX > 0) {
                    prevSlide();
                } else {
                    nextSlide();
                }
            } else {
                showSlide(currentIndex);
            }
            startAutoScroll();
        }

        function handleMouseDown(e) {
            e.preventDefault();
            stopAutoScroll();
            startX = e.clientX;
            isSwiping = true;
            quoteSlider.style.cursor = 'grabbing';
        }

        function handleMouseMove(e) {
            if (!isSwiping) return;
            let diffX = e.clientX - startX;
            quoteSlider.style.transition = 'none';
            quoteSlider.style.transform = `translateX(${-currentIndex * 100 + (diffX / slider.offsetWidth) * 100}%)`;
        }

        function handleMouseUp(e) {
            if (!isSwiping) return;
            isSwiping = false;
            quoteSlider.style.cursor = 'grab';
            let diffX = e.clientX - startX;
            if (Math.abs(diffX) > 50) {
                if (diffX > 0) {
                    prevSlide();
                } else {
                    nextSlide();
                }
            } else {
                showSlide(currentIndex);
            }
            startAutoScroll();
        }

        function handleMouseLeave(e) {
            if (!isSwiping) return;
            isSwiping = false;
            quoteSlider.style.cursor = 'grab';
            showSlide(currentIndex);
            startAutoScroll();
        }

        slider.addEventListener('mouseenter', stopAutoScroll);
        slider.addEventListener('mouseleave', startAutoScroll);

        showSlide(currentIndex, false);
        startAutoScroll();
    });
});
