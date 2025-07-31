class PartnersCarousel {
  constructor() {
    this.carousel = document.querySelector('.logos-track');
    if (!this.carousel) return;

    this.logos = this.carousel.querySelectorAll('.logo-item');
    this.totalLogos = this.logos.length;
    this.currentIndex = 0;
    this.isTransitioning = false;

    // Конфигурация брейкпоинтов
    this.config = {
      desktop: { minWidth: 1024, visibleLogos: 3 },
      tablet: { minWidth: 768, maxWidth: 1023, visibleLogos: 2 },
      mobile: { maxWidth: 767, visibleLogos: 1 },
    };

    this.currentVisibleLogos = this.getVisibleLogos();

    this.init();
  }

  init() {
    this.startAutoScroll();
    this.updateCarouselPosition();
    this.setupHoverPause();

    window.addEventListener('resize', () => {
      this.handleResize();
    });
  }

  getVisibleLogos() {
    const width = window.innerWidth;
    if (width >= this.config.desktop.minWidth) {
      return this.config.desktop.visibleLogos;
    } else if (
      width >= this.config.tablet.minWidth &&
      width <= this.config.tablet.maxWidth
    ) {
      return this.config.tablet.visibleLogos;
    } else {
      return this.config.mobile.visibleLogos;
    }
  }

  getLogoStep() {
    if (window.innerWidth <= 767) {
      return this.carousel.parentElement.offsetWidth;
    }
    return 260 + 40; // Шаг для десктопа и планшета
  }

  updateCarouselPosition() {
    const translateX = -(this.currentIndex * this.getLogoStep());
    this.carousel.style.transition = 'transform 0.5s ease';
    this.carousel.style.transform = `translateX(${translateX}px)`;
  }

  nextSlide() {
    if (this.isTransitioning) return;

    this.isTransitioning = true;
    this.currentIndex++;
    this.updateCarouselPosition();

    const onTransitionEnd = () => {
      this.carousel.removeEventListener('transitionend', onTransitionEnd);

      if (this.currentIndex >= this.totalLogos / 2) {
        this.carousel.style.transition = 'none';
        this.carousel.style.transform = 'translateX(0px)';
        void this.carousel.offsetWidth;
        this.carousel.style.transition = 'transform 0.5s ease';
        this.currentIndex = 0;
      }

      this.isTransitioning = false;
    };

    this.carousel.addEventListener('transitionend', onTransitionEnd);
  }

  startAutoScroll() {
    const interval = window.innerWidth <= 767 ? 3000 : 5000;
    this.autoScrollInterval = setInterval(() => {
      this.nextSlide();
    }, interval);
  }

  stopAutoScroll() {
    if (this.autoScrollInterval) {
      clearInterval(this.autoScrollInterval);
      this.autoScrollInterval = null;
    }
  }

  handleResize() {
    const newVisibleLogos = this.getVisibleLogos();
    if (newVisibleLogos !== this.currentVisibleLogos) {
      this.currentVisibleLogos = newVisibleLogos;
      this.currentIndex = 0;
      this.updateCarouselPosition();
      this.stopAutoScroll();
      this.startAutoScroll();
    }
  }

  setupHoverPause() {
    const logosContainer = document.querySelector('.partners__logos');
    if (!logosContainer) return;

    logosContainer.addEventListener('mouseenter', () => {
      this.stopAutoScroll();
    });

    logosContainer.addEventListener('mouseleave', () => {
      this.startAutoScroll();
    });
  }

  destroy() {
    this.stopAutoScroll();
    window.removeEventListener('resize', this.handleResize);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  window.partnersCarousel = new PartnersCarousel();

  // Добавление стрелок для ручной прокрутки (моб. версия)
  const track = document.querySelector('.logos-track');
  const arrowLeft = document.querySelector('.partners__arrow--left');
  const arrowRight = document.querySelector('.partners__arrow--right');
  let scrollX = 0;
  const step = 260;

  if (arrowLeft && arrowRight && track) {
    arrowLeft.addEventListener('click', () => {
      scrollX = Math.max(scrollX - step, 0);
      track.style.transform = `translateX(-${scrollX}px)`;
    });

    arrowRight.addEventListener('click', () => {
      const maxScroll = track.scrollWidth - track.clientWidth;
      scrollX = Math.min(scrollX + step, maxScroll);
      track.style.transform = `translateX(-${scrollX}px)`;
    });
  }
});

window.addEventListener('load', () => {
  if (!window.partnersCarousel) {
    window.partnersCarousel = new PartnersCarousel();
  }
});
