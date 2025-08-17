document.addEventListener('DOMContentLoaded', () => {
  new Swiper('.faq-home__swiper', {
    slidesPerView: 1,
    pagination: {
      el: '.faq-home__pagination',
      clickable: true,
    },
    breakpoints: {
      768: {
        direction: 'vertical',
        mousewheel: { enabled: true }, // включаем колесо и вертикальное направление
      },
    },
  });

  const faqBtn = document.querySelector('.faq-home__btn--mobile');
  if (faqBtn) {
    faqBtn.addEventListener('click', (e) => {
      e.preventDefault();
      window.location.href = faqBtn.href;
    });
  }
});
