document.addEventListener('DOMContentLoaded', () => {
  new Swiper('.faq__swiper', {
    slidesPerView: 1,
    pagination: {
      el: '.faq__pagination',
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
