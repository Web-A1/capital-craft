document.addEventListener("DOMContentLoaded", () => {
  const mqlDesktop = window.matchMedia("(min-width: 768px)");
  const prefersReduce = window.matchMedia("(prefers-reduced-motion: reduce)");

  // Контроллер печати, чтобы прерывать при смене слайда
  let typingState = { raf: null, running: false };

  const stopTyping = () => {
    if (typingState.raf) cancelAnimationFrame(typingState.raf);
    typingState = { raf: null, running: false };
  };

  const ensureDatasets = (swiper) => {
    swiper.slides.forEach((slide) => {
      const q = slide.querySelector(".reviews__quote-text");
      if (q && !q.dataset.fulltext) {
        q.dataset.fulltext = q.textContent || "";
      }
    });
  };

  // Последовательность для десктопа: печать текста, затем fade-in подписи
  const playSequence = (swiper) => {
    if (!mqlDesktop.matches || prefersReduce.matches) return; // мобайл или reduce motion — без печати

    ensureDatasets(swiper);
    stopTyping();

    const active = swiper.slides[swiper.activeIndex];
    if (!active) return;
    const quote = active.querySelector(".reviews__quote-text");
    const sign = active.querySelector(".reviews__signature");
    if (!quote || !sign) return;

    // Прячем подпись
    sign.classList.add("reviews__signature--hidden");

    // Готовим текст для печати
    const full = quote.dataset.fulltext || quote.textContent || "";
    quote.textContent = "";

    const total = full.length;
    const stepChars = Math.max(2, Math.ceil(total / 60)); // ~до 60 кадров
    let i = 0;

    const typeStep = () => {
      typingState.running = true;
      const next = full.slice(i, i + stepChars);
      quote.textContent += next;
      i += stepChars;
      if (i < total) {
        typingState.raf = requestAnimationFrame(typeStep);
      } else {
        typingState.running = false;
        // Плавно показываем подпись после печати
        sign.classList.remove("reviews__signature--hidden");
        sign.classList.add("reviews__signature--fadein");
        setTimeout(() => sign.classList.remove("reviews__signature--fadein"), 400);
      }
    };

    typingState.raf = requestAnimationFrame(typeStep);
  };
  
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
          setTimeout(() => playSequence(swiper), 50);
        },
        slideChangeTransitionStart() {
          stopTyping();
        },
        slideChangeTransitionEnd(swiper) {
          playSequence(swiper);
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
