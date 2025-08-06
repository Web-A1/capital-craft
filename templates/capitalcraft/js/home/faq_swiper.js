document.addEventListener('DOMContentLoaded', () => {
  new Swiper('.faq__slider', {
    pagination: {
      el: '.faq__pagination',
      clickable: true,
    },
  });
});

