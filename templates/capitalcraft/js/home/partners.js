class PartnersCarousel {
  constructor() {
    this.carousel = document.querySelector('.logos-track');
    if (!this.carousel) return;

    this.logos = this.carousel.querySelectorAll('.logo-item');
    this.totalLogos = this.logos.length;
    this.currentIndex = 0;
    this.isTransitioning = false;

    // Configuration for different screen sizes
    this.config = {
      desktop: { minWidth: 1024, visibleLogos: 3 },
      tablet: { minWidth: 768, maxWidth: 1023, visibleLogos: 2 },
      mobile: { maxWidth: 767, visibleLogos: 1 },
    };

    this.currentVisibleLogos = this.getVisibleLogos();
    this.init();
  }

  init() {
    // Start auto-scroll
    this.startAutoScroll();

    // Handle window resize
    window.addEventListener('resize', () => {
      this.handleResize();
    });

    // Set initial position
    this.updateCarouselPosition();

    // Setup hover pause
    this.setupHoverPause();
  }

  // Logos are duplicated in markup to ensure seamless looping

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

  updateCarouselPosition() {
    const logoWidth = 100 / this.totalLogos;
    const translateX = -(this.currentIndex * logoWidth);

    this.carousel.style.transform = `translateX(${translateX}%)`;
  }

  nextSlide() {
    if (this.isTransitioning) return;

    this.isTransitioning = true;
    this.carousel.classList.add('transitioning');

    this.currentIndex++;
    this.updateCarouselPosition();

    const onTransitionEnd = () => {
      this.carousel.removeEventListener('transitionend', onTransitionEnd);
      this.carousel.classList.remove('transitioning');

      if (this.currentIndex >= this.totalLogos / 2) {
        this.carousel.style.transition = 'none';
        this.carousel.style.transform = 'translateX(0%)';
        // force reflow to apply the style reset immediately
        void this.carousel.offsetWidth;
        this.carousel.style.transition = '';
        this.currentIndex = 0;
      }

      this.isTransitioning = false;
    };

    this.carousel.addEventListener('transitionend', onTransitionEnd);
  }

  startAutoScroll() {
    // Use 3 seconds on mobile, 5 seconds on larger screens
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

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  window.partnersCarousel = new PartnersCarousel();
});

// For Joomla compatibility - also try to initialize on window load
window.addEventListener('load', () => {
  if (!window.partnersCarousel) {
    window.partnersCarousel = new PartnersCarousel();
  }
});
