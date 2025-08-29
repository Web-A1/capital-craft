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
      // Фиксированная высота: зададим вручную по самому высокому слайду
      autoHeight: false,
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
        ? {
            el: ".reviews__pagination",
            type: "fraction",
            renderFraction: (currentClass, totalClass) =>
              `<span class="reviews__pagination-current ${currentClass}"></span>/<span class="reviews__pagination-total ${totalClass}"></span>`
          }
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
          // Вычисляем максимальную высоту слайда и фиксируем её
          setTimeout(() => setFixedHeight(swiper), 0);
        },
        resize(swiper) {
          // Пересчитываем при изменении размера/ориентации
          setTimeout(() => setFixedHeight(swiper), 0);
        },
        imagesReady(swiper) {
          setTimeout(() => setFixedHeight(swiper), 0);
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
    // После пересоздания фиксируем высоту
    setTimeout(() => setFixedHeight(reviewsSwiper), 0);
  });

  // Добавляем обработчики для клавиатуры
  document.addEventListener("keydown", e => {
    if (e.key === "ArrowLeft") {
      reviewsSwiper.slidePrev();
    } else if (e.key === "ArrowRight") {
      reviewsSwiper.slideNext();
    }
  });

  // Утилита: фиксируем высоту контейнера по самому высокому слайду
  function setFixedHeight(swiper) {
    if (!swiper || !swiper.el) return;
    const root = swiper.el;
    const slides = root.querySelectorAll(".swiper-slide");
    let max = 0;
    slides.forEach(slide => {
      const content = slide.querySelector(".reviews__figure") || slide;
      const h = content.getBoundingClientRect().height;
      if (h > max) max = h;
    });
    if (max > 0) {
      root.style.height = `${Math.ceil(max)}px`;
    }
  }

  // Дополнительно ждём загрузки шрифтов (в Safari поддерживается)
  if (document.fonts && document.fonts.ready) {
    document.fonts.ready.then(() => setFixedHeight(reviewsSwiper));
  }
});
