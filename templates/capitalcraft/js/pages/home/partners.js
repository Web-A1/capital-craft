'use strict';

document.addEventListener('DOMContentLoaded', () => {
  const viewport = document.querySelector('.partners .embla__viewport');
  const container = document.querySelector('.partners .embla__container');
  if (!viewport || !container) return;

  const mobileQuery = window.matchMedia('(max-width: 767px)');

  const initMobileCarousel = () => {
    EmblaCarousel(
      viewport,
      {
        loop: true,
        align: 'center',
        skipSnaps: false,
        containScroll: false,
      },
      [
        EmblaCarouselAutoplay({
          delay: 3000,
          stopOnInteraction: false,
          stopOnMouseEnter: false,
        }),
      ]
    );
  };

  if (mobileQuery.matches) {
    initMobileCarousel();
  } else {
    // Дублируем логотипы для плавной бесконечной прокрутки
    container.innerHTML += container.innerHTML;

    let isDown = false;
    let startX = 0;
    let scrollStart = 0;

    const stopDrag = () => {
      isDown = false;
      viewport.classList.remove('dragging');
    };

    viewport.addEventListener('mousedown', (e) => {
      isDown = true;
      startX = e.pageX - viewport.offsetLeft;
      scrollStart = viewport.scrollLeft;
      viewport.classList.add('dragging');
    });
    viewport.addEventListener('mouseleave', stopDrag);
    viewport.addEventListener('mouseup', stopDrag);
    viewport.addEventListener('mousemove', (e) => {
      if (!isDown) return;
      e.preventDefault();
      const x = e.pageX - viewport.offsetLeft;
      const walk = x - startX;
      viewport.scrollLeft = scrollStart - walk;
    });
  }

  mobileQuery.addEventListener('change', (e) => {
    if (e.matches) {
      initMobileCarousel();
    }
  });

  container.querySelectorAll('.partner-logo').forEach((logo) => {
    const highlight = () => logo.classList.add('no-filter');
    const unhighlight = () => logo.classList.remove('no-filter');
    logo.addEventListener('touchstart', highlight);
    logo.addEventListener('touchend', unhighlight);
    logo.addEventListener('pointerdown', highlight);
    logo.addEventListener('pointerup', unhighlight);
  });
});
