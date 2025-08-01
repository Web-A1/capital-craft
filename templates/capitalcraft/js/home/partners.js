'use strict';

document.addEventListener('DOMContentLoaded', () => {
  const emblaNode = document.querySelector('.embla');
  if (!emblaNode) return;

  const viewportNode = emblaNode.querySelector('.embla__viewport');
  EmblaCarousel(
    viewportNode,
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

  document.querySelectorAll('.partner-logo').forEach((logo) => {
    logo.addEventListener('touchstart', () => {
      logo.classList.add('no-filter');
    });
    logo.addEventListener('touchend', () => {
      logo.classList.remove('no-filter');
    });
  });
});
