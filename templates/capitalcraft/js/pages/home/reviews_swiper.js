document.addEventListener('DOMContentLoaded', () => {
  // Проверяем, загружен ли Swiper
  if (typeof Swiper === 'undefined') {
    console.error('Swiper не загружен. Проверьте подключение библиотеки.');
    return;
  }

  // Проверяем наличие необходимых элементов
  const swiperElement = document.querySelector('.reviews__swiper');
  const prevArrow = document.querySelector('.reviews__arrow--prev');
  const nextArrow = document.querySelector('.reviews__arrow--next');
  const pagination = document.querySelector('.reviews__pagination');

  if (!swiperElement) {
    console.error('Элемент слайдера не найден');
    return;
  }

  try {
    const reviewsSwiper = new Swiper('.reviews__swiper', {
      slidesPerView: 1,
      direction: 'horizontal',
      loop: true,
      navigation: {
        nextEl: nextArrow,
        prevEl: prevArrow,
      },
      pagination: {
        el: pagination,
        clickable: true,
        renderBullet: function (index, className) {
          return `<button class="${className}" aria-label="Перейти к отзыву ${index + 1}" type="button"></button>`;
        },
      },
      breakpoints: {
        // Мобильные устройства
        320: {
          direction: 'horizontal',
          pagination: {
            el: pagination,
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
            nextEl: nextArrow,
            prevEl: prevArrow,
          },
        },
      },
      // Плавные переходы
      effect: 'slide',
      speed: 600,
      // Предзагрузка следующего слайда
      preloadImages: true,
      // Ленивая загрузка изображений
      lazy: {
        loadPrevNext: true,
      },
      // Улучшенная доступность
      a11y: {
        prevSlideMessage: 'Предыдущий отзыв',
        nextSlideMessage: 'Следующий отзыв',
        firstSlideMessage: 'Это первый отзыв',
        lastSlideMessage: 'Это последний отзыв',
        paginationBulletMessage: 'Перейти к отзыву',
      },
      // Обработка событий
      on: {
        init: function () {
          console.log('Слайдер отзывов инициализирован');
          // Устанавливаем фокус на первый слайд для скринридеров
          const firstSlide = this.slides[0];
          if (firstSlide) {
            firstSlide.setAttribute('tabindex', '0');
          }
        },
        slideChange: function () {
          // Обновляем aria-label для текущего слайда
          const currentSlide = this.slides[this.activeIndex];
          if (currentSlide) {
            const totalSlides = this.slides.length;
            const currentNumber = this.realIndex + 1;
            currentSlide.setAttribute('aria-label', `Отзыв ${currentNumber} из ${totalSlides}`);
          }
        },
      },
    });

    // Добавляем обработчики для клавиатуры
    document.addEventListener('keydown', (e) => {
      // Проверяем, что фокус находится в области слайдера
      const isInSlider = swiperElement.contains(document.activeElement);
      if (!isInSlider) return;

      if (e.key === 'ArrowLeft') {
        e.preventDefault();
        reviewsSwiper.slidePrev();
        // Переводим фокус на предыдущую стрелку
        if (prevArrow && window.innerWidth >= 768) {
          prevArrow.focus();
        }
      } else if (e.key === 'ArrowRight') {
        e.preventDefault();
        reviewsSwiper.slideNext();
        // Переводим фокус на следующую стрелку
        if (nextArrow && window.innerWidth >= 768) {
          nextArrow.focus();
        }
      }
    });

    // Улучшенная доступность для стрелок
    if (prevArrow) {
      prevArrow.addEventListener('click', () => {
        // Обновляем aria-label для лучшей доступности
        const currentIndex = reviewsSwiper.realIndex;
        const totalSlides = reviewsSwiper.slides.length;
        const prevIndex = currentIndex === 0 ? totalSlides - 1 : currentIndex - 1;
        prevArrow.setAttribute('aria-label', `Предыдущий отзыв (отзыв ${prevIndex + 1} из ${totalSlides})`);
      });
    }

    if (nextArrow) {
      nextArrow.addEventListener('click', () => {
        // Обновляем aria-label для лучшей доступности
        const currentIndex = reviewsSwiper.realIndex;
        const totalSlides = reviewsSwiper.slides.length;
        const nextIndex = currentIndex === totalSlides - 1 ? 0 : currentIndex + 1;
        nextArrow.setAttribute('aria-label', `Следующий отзыв (отзыв ${nextIndex + 1} из ${totalSlides})`);
      });
    }

    // Запускаем слайдер
    reviewsSwiper.init();

  } catch (error) {
    console.error('Ошибка при инициализации слайдера отзывов:', error);
    
    // Fallback: показываем все отзывы без слайдера
    if (swiperElement) {
      swiperElement.style.display = 'block';
      const slides = swiperElement.querySelectorAll('.swiper-slide');
      slides.forEach(slide => {
        slide.style.display = 'block';
        slide.style.marginBottom = '20px';
      });
    }
  }
});
