document.addEventListener('DOMContentLoaded', () => {
  const data = Array.isArray(window.showcasesData) ? window.showcasesData : [];
  const container = document.querySelector('.show-case-swiper');
  if (!container || data.length === 0) {
    console.warn('ShowCases: контейнер или данные не найдены');
    return;
  }

  const swiper = new Swiper(container, {
    direction: 'vertical',
    effect: 'creative',
    creativeEffect: {
      limitProgress: 3,
      prev: {
        translate: [0, '-120%', 0],
        scale: 0.85,
        origin: 'center bottom',
      },
      next: {
        translate: [0, '120%', 0],
        scale: 1.15,
        origin: 'center top',
      },
    },
    mousewheel: true,
    speed: 600,
    allowTouchMove: true,
    watchSlidesProgress: true,
    loop: false,
  });

  swiper.on('reachEnd', () => {
    swiper.slideTo(0, 0);
  });

  swiper.on('reachBeginning', () => {
    swiper.slideTo(swiper.slides.length - 1, 0);
  });
});
