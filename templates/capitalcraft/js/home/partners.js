'use strict';

document.addEventListener('DOMContentLoaded', () => {
  const viewport = document.querySelector('.partners .embla__viewport');
  const container = document.querySelector('.partners .embla__container');
  if (!viewport || !container) return;

  const isMobile = window.matchMedia('(max-width: 767px)').matches;

  if (!isMobile) {
    // Дублируем логотипы для плавной бесконечной прокрутки
    container.innerHTML += container.innerHTML;
  } else {
    let index = 0;
    const slides = container.children;
    const slideCount = slides.length;

    const startAuto = () =>
      setInterval(() => {
        index = (index + 1) % slideCount;
        viewport.scrollTo({
          left: viewport.offsetWidth * index,
          behavior: 'smooth',
        });
      }, 3000);

    let autoId = startAuto();

    const restart = () => {
      clearInterval(autoId);
      autoId = startAuto();
    };

    viewport.addEventListener('touchstart', () => clearInterval(autoId));
    viewport.addEventListener('touchend', restart);
    window.addEventListener('resize', () => {
      index = Math.round(viewport.scrollLeft / viewport.offsetWidth);
      restart();
    });
  }

  container.querySelectorAll('.partner-logo').forEach((logo) => {
    logo.addEventListener('touchstart', () => {
      logo.classList.add('no-filter');
    });
    logo.addEventListener('touchend', () => {
      logo.classList.remove('no-filter');
    });
  });
});
