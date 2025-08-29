document.addEventListener("DOMContentLoaded", () => {
  const mqlDesktop = window.matchMedia("(min-width: 768px)");

  // Помечаем активный слайд классом для CSS-анимаций цитаты/подписи
  const applySlideAnimations = swiper => {
    if (!mqlDesktop.matches) return; // Анимация только на десктопе
    swiper.slides.forEach(slide =>
      slide.classList.remove("reviews__slide--animate")
    );
    const active = swiper.slides[swiper.activeIndex];
    if (active) active.classList.add("reviews__slide--animate");
  };

  const makeConfig = () => {
    const isDesktop = mqlDesktop.matches;
    return {
      slidesPerView: 1,
      loop: true,
      // Автоподгонка высоты под контент слайда, чтобы исключить лишние отступы
      autoHeight: true,
      // На десктопе используем исчезновение вместо слайдов
      effect: isDesktop ? "fade" : "slide",
      fadeEffect: { crossFade: true },
      speed: 450,
      // Управление
      navigation: isDesktop
        ? {
            nextEl: ".reviews__arrow--next",
            prevEl: ".reviews__arrow--prev"
          }
        : {},
      pagination: !isDesktop
        ? { el: ".reviews__pagination", clickable: true }
        : false,
      // Производительность
      preloadImages: true,
      lazy: { loadPrevNext: true },
      // Доступность по умолчанию (можно расширить при необходимости)
      a11y: {
        enabled: true,
        prevSlideMessage: "Предыдущий отзыв",
        nextSlideMessage: "Следующий отзыв",
        slideLabelMessage: "Отзыв {{index}} из {{slidesLength}}"
      },
      on: {
        init(swiper) {
          applySlideAnimations(swiper);
        },
        slideChangeTransitionEnd(swiper) {
          applySlideAnimations(swiper);
        }
      }
    };
  };

  let reviewsSwiper = new Swiper(".reviews__swiper", makeConfig());

  // Пересоздаем слайдер при смене брейкпоинта, чтобы обновить эффект
  mqlDesktop.addEventListener("change", () => {
    if (reviewsSwiper && reviewsSwiper.destroy) {
      reviewsSwiper.destroy(true, true);
    }
    reviewsSwiper = new Swiper(".reviews__swiper", makeConfig());
  });

  // Добавляем обработчики для клавиатуры
  document.addEventListener("keydown", e => {
    if (e.key === "ArrowLeft") {
      reviewsSwiper.slidePrev();
    } else if (e.key === "ArrowRight") {
      reviewsSwiper.slideNext();
    }
  });
});
