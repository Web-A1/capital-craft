document.addEventListener('DOMContentLoaded', () => {
  const reviewsSwiper = new Swiper('.reviews__swiper', {
    slidesPerView: 1,
    direction: 'horizontal',
    loop: true,
    autoplay: false, 
    /*{
      //delay: 5000,
      //disableOnInteraction: false,
      //pauseOnMouseEnter: true,
    },*/
    navigation: {
      nextEl: '.reviews__arrow--next',
      prevEl: '.reviews__arrow--prev',
    },
    pagination: {
      el: '.reviews__pagination',
      clickable: true,
      dynamicBullets: true,
    },
    breakpoints: {
      // Мобильные устройства
      320: {
        direction: 'horizontal',
        pagination: {
          el: '.reviews__pagination',
          clickable: true,
        },
        navigation: {
          nextEl: null,
          prevEl: null,
        },
      },
      // Планшеты и выше
      768: {
        direction: 'horizontal',
        pagination: false,
        navigation: {
          nextEl: '.reviews__arrow--next',
          prevEl: '.reviews__arrow--prev',
        },
      },
    },
    // Плавные переходы
    effect: 'slide',
    speed: 600,
    // Убираем autoHeight, так как теперь фиксированная высота
    // autoHeight: true,
    // Предзагрузка следующего слайда
    preloadImages: true,
    // Ленивая загрузка изображений
    lazy: {
      loadPrevNext: true,
    },
  });

  // Добавляем обработчики для клавиатуры
  document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') {
      reviewsSwiper.slidePrev();
    } else if (e.key === 'ArrowRight') {
      reviewsSwiper.slideNext();
    }
  });

  // Останавливаем автопрокрутку при наведении мыши
  const swiperContainer = document.querySelector('.reviews__swiper');
  if (swiperContainer) {
    swiperContainer.addEventListener('mouseenter', () => {
      reviewsSwiper.autoplay.stop();
    });

    swiperContainer.addEventListener('mouseleave', () => {
      reviewsSwiper.autoplay.start();
    });
  }

  // Добавляем анимацию появления слайдов
  reviewsSwiper.on('slideChange', () => {
    const activeSlide = reviewsSwiper.slides[reviewsSwiper.activeIndex];
    if (activeSlide) {
      activeSlide.style.opacity = '0';
      activeSlide.style.transform = 'translateY(20px)';
      
      setTimeout(() => {
        activeSlide.style.transition = 'all 0.6s ease';
        activeSlide.style.opacity = '1';
        activeSlide.style.transform = 'translateY(0)';
      }, 100);
    }
  });

  // Инициализация первого слайда
  reviewsSwiper.on('init', () => {
    const firstSlide = reviewsSwiper.slides[0];
    if (firstSlide) {
      firstSlide.style.opacity = '1';
      firstSlide.style.transform = 'translateY(0)';
    }
  });

  // Запускаем слайдер
  reviewsSwiper.init();
});
