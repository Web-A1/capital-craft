document.addEventListener('DOMContentLoaded', () => {
  new Swiper('.show-case__swiper', {
    mousewheel: true,
    slidesPerView: 1,
    direction: 'vertical',
    pagination: {
      el: '.show-case__pagination',
      clickable: true,
    },
  });
});
