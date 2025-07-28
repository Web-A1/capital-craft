class LogoCarousel {
    constructor() {
        this.carousel = document.getElementById('logoCarousel');
        this.logos = this.carousel.querySelectorAll('.logo-item');
        this.totalLogos = this.logos.length;
        this.currentIndex = 0;
        this.isTransitioning = false;
        
        // Configuration for different screen sizes
        this.config = {
            desktop: { minWidth: 1024, visibleLogos: 3 },
            tablet: { minWidth: 768, maxWidth: 1023, visibleLogos: 2 },
            mobile: { maxWidth: 767, visibleLogos: 1 }
        };
        
        this.currentVisibleLogos = this.getVisibleLogos();
        this.init();
    }
    
    init() {
        // Clone logos for infinite scroll
        this.cloneLogos();
        
        // Start auto-scroll
        this.startAutoScroll();
        
        // Handle window resize
        window.addEventListener('resize', () => {
            this.handleResize();
        });
        
        // Set initial position
        this.updateCarouselPosition();
    }
    
    cloneLogos() {
        // Clone logos at the beginning for infinite scroll
        const fragment = document.createDocumentFragment();
        this.logos.forEach(logo => {
            const clone = logo.cloneNode(true);
            fragment.appendChild(clone);
        });
        this.carousel.appendChild(fragment);
    }
    
    getVisibleLogos() {
        const width = window.innerWidth;
        
        if (width >= this.config.desktop.minWidth) {
            return this.config.desktop.visibleLogos;
        } else if (width >= this.config.tablet.minWidth && width <= this.config.tablet.maxWidth) {
            return this.config.tablet.visibleLogos;
        } else {
            return this.config.mobile.visibleLogos;
        }
    }
    
    updateCarouselPosition() {
        if (this.isTransitioning) return;
        
        const logoWidth = 100 / (this.totalLogos * 2); // Account for cloned logos
        const translateX = -(this.currentIndex * logoWidth * this.currentVisibleLogos);
        
        this.carousel.style.transform = `translateX(${translateX}%)`;
    }
    
    nextSlide() {
        if (this.isTransitioning) return;
        
        this.isTransitioning = true;
        this.carousel.classList.add('transitioning');
        
        this.currentIndex++;
        
        // If we've reached the end of original logos, reset to beginning
        if (this.currentIndex >= this.totalLogos) {
            this.currentIndex = 0;
            
            // First, move to the next position
            const logoWidth = 100 / (this.totalLogos * 2);
            const translateX = -(this.totalLogos * logoWidth * this.currentVisibleLogos);
            this.carousel.style.transform = `translateX(${translateX}%)`;
            
            // After transition, reset to beginning without animation
            setTimeout(() => {
                this.carousel.classList.remove('transitioning');
                this.carousel.style.transform = 'translateX(0%)';
                this.isTransitioning = false;
            }, 500);
        } else {
            this.updateCarouselPosition();
            
            setTimeout(() => {
                this.carousel.classList.remove('transitioning');
                this.isTransitioning = false;
            }, 500);
        }
    }
    
    startAutoScroll() {
        this.autoScrollInterval = setInterval(() => {
            this.nextSlide();
        }, 5000);
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
            this.currentIndex = 0; // Reset to beginning on resize
            this.updateCarouselPosition();
        }
    }
    
    destroy() {
        this.stopAutoScroll();
        window.removeEventListener('resize', this.handleResize);
    }
}

// Initialize carousel when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new LogoCarousel();
});

// Pause carousel on hover (optional enhancement)
document.addEventListener('DOMContentLoaded', () => {
    const carousel = document.querySelector('.partners-logos');
    let logoCarouselInstance;
    
    // Store reference to carousel instance
    window.logoCarousel = new LogoCarousel();
    
    carousel.addEventListener('mouseenter', () => {
        if (window.logoCarousel) {
            window.logoCarousel.stopAutoScroll();
        }
    });
    
    carousel.addEventListener('mouseleave', () => {
        if (window.logoCarousel) {
            window.logoCarousel.startAutoScroll();
        }
    });
});
