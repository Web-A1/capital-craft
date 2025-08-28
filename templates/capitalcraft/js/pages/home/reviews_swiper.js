document.addEventListener("DOMContentLoaded", () => {
  const mqlDesktop = window.matchMedia("(min-width: 768px)");
  
  // Desktop-only reveal animation for quote text
  const runReveal = (swiper) => {
    if (!mqlDesktop.matches) return;
    try {
      swiper.slides.forEach((slide) => {
        const q = slide.querySelector(".reviews__quote-text");
        if (q) q.classList.remove("reviews__quote--enter");
      });
      const active = swiper.slides[swiper.activeIndex];
      const qa = active ? active.querySelector(".reviews__quote-text") : null;
      if (qa) {
        // restart animation
        // eslint-disable-next-line no-unused-expressions
        qa.offsetWidth;
        qa.classList.add("reviews__quote--enter");
      }
    } catch (_) {
      /* noop */
    }
  };

  const makeConfig = () => {
    const isDesktop = mqlDesktop.matches;
    return {
      slidesPerView: 1,
      loop: true,
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
          runReveal(swiper);
        },
        slideChangeTransitionStart(swiper) {
          runReveal(swiper);
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
