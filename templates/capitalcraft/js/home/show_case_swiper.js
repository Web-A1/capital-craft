document.addEventListener('DOMContentLoaded', () => {
  new Swiper('.show-case__swiper', {
    direction: 'horizontal',
    mousewheel: false,
    slidesPerView: 1,
    pagination: {
      el: '.show-case__pagination',
      clickable: true,
    },
    breakpoints: {
      768: {
        direction: 'vertical',
        mousewheel: true,
      },
    },
  });
});
