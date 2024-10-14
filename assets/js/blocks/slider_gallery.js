document.addEventListener('DOMContentLoaded', function () {
    const sliderGalleries = document.querySelectorAll('[data-slider-gallery]');

    sliderGalleries.forEach(gallery => {
        const slider = gallery.querySelector('.slider-gallery');
        let slides = slider.querySelectorAll('.slider-slide');
        const slideCount = slides.length;
        let currentIndex = 1;
        let intervalId;

        const firstSlideClone = slides[0].cloneNode(true);
        const lastSlideClone = slides[slideCount - 1].cloneNode(true);

        slider.appendChild(firstSlideClone);
        slider.insertBefore(lastSlideClone, slides[0]);

        slides = slider.querySelectorAll('.slider-slide');

        slider.style.transform = 'translateX(-100%)';

        function showSlide(index, smooth = true) {
            if (smooth) {
                slider.style.transition = 'transform 0.5s ease';
            } else {
                slider.style.transition = 'none';
            }
            slider.style.transform = `translateX(-${index * 100}%)`;
        }

        function nextSlide() {
            currentIndex++;
            showSlide(currentIndex);
            if (currentIndex === slides.length - 1) {
                setTimeout(() => {
                    slider.style.transition = 'none';
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
                    slider.style.transition = 'none';
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

        slider.addEventListener('touchstart', handleTouchStart, { passive: true });
        slider.addEventListener('touchmove', handleTouchMove, { passive: true });
        slider.addEventListener('touchend', handleTouchEnd);
        slider.addEventListener('mousedown', handleMouseDown);
        slider.addEventListener('mousemove', handleMouseMove);
        slider.addEventListener('mouseup', handleMouseUp);
        slider.addEventListener('mouseleave', handleMouseLeave);

        function handleTouchStart(e) {
            stopAutoScroll();
            startX = e.touches[0].clientX;
            isSwiping = true;
        }

        function handleTouchMove(e) {
            if (!isSwiping) return;
            let diffX = e.touches[0].clientX - startX;
            slider.style.transition = 'none';
            slider.style.transform = `translateX(${-currentIndex * 100 + (diffX / gallery.offsetWidth) * 100}%)`;
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
            slider.style.cursor = 'grabbing';
        }

        function handleMouseMove(e) {
            if (!isSwiping) return;
            let diffX = e.clientX - startX;
            slider.style.transition = 'none';
            slider.style.transform = `translateX(${-currentIndex * 100 + (diffX / gallery.offsetWidth) * 100}%)`;
        }

        function handleMouseUp(e) {
            if (!isSwiping) return;
            isSwiping = false;
            slider.style.cursor = 'grab';
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
            slider.style.cursor = 'grab';
            showSlide(currentIndex);
            startAutoScroll();
        }

        gallery.addEventListener('mouseenter', stopAutoScroll);
        gallery.addEventListener('mouseleave', startAutoScroll);

        showSlide(currentIndex, false);
        startAutoScroll();
    });
});