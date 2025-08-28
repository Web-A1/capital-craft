document.addEventListener("DOMContentLoaded", () => {
  const reviewsSwiper = new Swiper(".reviews__swiper", {
    slidesPerView: 1,
    direction: "horizontal",
    loop: true,
    navigation: {
      nextEl: ".reviews__arrow--next",
      prevEl: ".reviews__arrow--prev"
    },
    pagination: {
      el: ".reviews__pagination",
      clickable: true
    },
    breakpoints: {
      // Мобильные устройства
      320: {
        direction: "horizontal",
        pagination: {
          el: ".reviews__pagination",
          clickable: true
        },
        navigation: {
          nextEl: null,
          prevEl: null
        }
      },
      // Планшеты и выше
      768: {
        direction: "horizontal",
        pagination: false,
        navigation: {
          nextEl: ".reviews__arrow--next",
          prevEl: ".reviews__arrow--prev"
        }
      }
    },
    // Плавные переходы
    effect: "slide",
    speed: 600,
    // Предзагрузка следующего слайда
    preloadImages: true,
    // Ленивая загрузка изображений
    lazy: {
      loadPrevNext: true
    }
  });

  // Добавляем обработчики для клавиатуры
  document.addEventListener("keydown", e => {
    if (e.key === "ArrowLeft") {
      reviewsSwiper.slidePrev();
    } else if (e.key === "ArrowRight") {
      reviewsSwiper.slideNext();
    }
  });

  // Запускаем слайдер
  reviewsSwiper.init();
});
