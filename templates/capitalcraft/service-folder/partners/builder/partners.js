(function() {
    'use strict';
    
    class PartnersCarousel {
        constructor() {
            this.init();
        }
        
        init() {
            // Wait for DOM to be ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.setup());
            } else {
                this.setup();
            }
        }
        
        setup() {
            this.carousel = document.getElementById('logoCarousel');
            if (!this.carousel) {
                console.warn('Partners carousel: logoCarousel element not found');
                return;
            }
            
            this.logos = this.carousel.querySelectorAll('.logo-item');
            if (!this.logos.length) {
                console.warn('Partners carousel: No logo items found');
                return;
            }
            
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
            this.initCarousel();
        }
        
        initCarousel() {
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
            
            // Setup hover pause
            this.setupHoverPause();
        }
        
        cloneLogos() {
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
            
            const logoWidth = 100 / (this.totalLogos * 2);
            const translateX = -(this.currentIndex * logoWidth * this.currentVisibleLogos);
            
            this.carousel.style.transform = `translateX(${translateX}%)`;
        }
        
        nextSlide() {
            if (this.isTransitioning) return;
            
            this.isTransitioning = true;
            this.carousel.classList.add('transitioning');
            
            this.currentIndex++;
            
            if (this.currentIndex >= this.totalLogos) {
                this.currentIndex = 0;
                
                const logoWidth = 100 / (this.totalLogos * 2);
                const translateX = -(this.totalLogos * logoWidth * this.currentVisibleLogos);
                this.carousel.style.transform = `translateX(${translateX}%)`;
                
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
            // Use 3 seconds on mobile, 5 seconds on larger screens
            const interval = window.innerWidth <= 767 ? 3000 : 5000;
            this.stopAutoScroll(); // Clear any existing interval
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
                
                this.startAutoScroll();
            }
        }
        
        setupHoverPause() {
            const logosContainer = document.querySelector('.partners-logos');
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
            if (this.handleResize) {
                window.removeEventListener('resize', this.handleResize);
            }
        }
    }
    
    // Initialize carousel
    window.partnersCarousel = new PartnersCarousel();
    
})();
