document.addEventListener('DOMContentLoaded', () => {
  new Swiper('.show-case__swiper', {
    slidesPerView: 1,
    direction: 'horizontal',
    mousewheel: { enabled: false }, // базовый вариант для мобильных
    pagination: {
      el: '.show-case__pagination',
      clickable: true,
    },
    breakpoints: {
      768: {
        direction: 'vertical',
        mousewheel: { enabled: true }, // включаем колесо и вертикальное направление
      },
    },
  });
});
