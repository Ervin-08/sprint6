function initGallery(gallery) {
    const track = gallery.querySelector('.media-gallery-track');
    const slides = Array.from(gallery.querySelectorAll('.media-gallery-slide'));
    const prev = gallery.querySelector('[data-gallery-prev]');
    const next = gallery.querySelector('[data-gallery-next]');
    const dots = Array.from(gallery.querySelectorAll('[data-gallery-dot]'));
    const autoplay = gallery.dataset.autoplay === 'true';
    let index = 0;
    let timer = null;

    if (!track || slides.length <= 1) {
        if (prev) prev.hidden = true;
        if (next) next.hidden = true;
        return;
    }

    function render() {
        track.style.transform = 'translateX(-' + (index * 100) + '%)';

        slides.forEach(function (slide, slideIndex) {
            slide.classList.toggle('is-active', slideIndex === index);
        });

        dots.forEach(function (dot, dotIndex) {
            dot.classList.toggle('is-active', dotIndex === index);
            dot.setAttribute('aria-current', dotIndex === index ? 'true' : 'false');
        });
    }

    function goTo(nextIndex) {
        index = (nextIndex + slides.length) % slides.length;
        render();
    }

    function startAutoplay() {
        if (!autoplay) {
            return;
        }

        stopAutoplay();
        timer = window.setInterval(function () {
            goTo(index + 1);
        }, 4800);
    }

    function stopAutoplay() {
        if (timer) {
            window.clearInterval(timer);
            timer = null;
        }
    }

    if (prev) {
        prev.addEventListener('click', function () {
            goTo(index - 1);
            startAutoplay();
        });
    }

    if (next) {
        next.addEventListener('click', function () {
            goTo(index + 1);
            startAutoplay();
        });
    }

    dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
            goTo(parseInt(dot.dataset.galleryDot, 10));
            startAutoplay();
        });
    });

    gallery.addEventListener('mouseenter', stopAutoplay);
    gallery.addEventListener('mouseleave', startAutoplay);

    render();
    startAutoplay();
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-gallery]').forEach(initGallery);
});
