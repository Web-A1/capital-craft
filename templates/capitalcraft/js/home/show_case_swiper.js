document.addEventListener('DOMContentLoaded', () => {
  new Swiper('.show-case__swiper', {
    mousewheel: true,
    slidesPerView: 1,
    direction: 'vertical',
    pagination: {
      el: '.show-case__pagination',
      clickable: true,
    },
    breakpoints: {
      0: {
        direction: 'horizontal',
        mousewheel: false,
      },
      768: {
        direction: 'vertical',
        mousewheel: true,
      },
    },
  });
});
