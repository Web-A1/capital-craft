/**
 * Show Cases - Interactive Card Stack
 * Handles card rotation, animations, and user interactions
 */

class ShowCases {
    constructor() {
        this.cases = window.showcasesData || [];
        this.currentIndex = 0;
        this.isAnimating = false;
        this.cards = [];
        this.container = null;
        
        // Touch handling
        this.touchStartX = 0;
        this.touchStartY = 0;
        this.touchEndX = 0;
        this.touchEndY = 0;
        this.minSwipeDistance = 50;
        
        // Scroll debouncing
        this.scrollTimeout = null;
        this.scrollDelay = 300;
        
        // Animation timing
        this.animationDuration = 500;
        
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
        this.container = document.getElementById('cardStack');
        if (!this.container || this.cases.length === 0) {
            console.warn('ShowCases: Container not found or no cases data available');
            return;
        }
        
        this.cards = [
            document.getElementById('card1'),
            document.getElementById('card2'),
            document.getElementById('card3')
        ];
        
        if (this.cards.some(card => !card)) {
            console.warn('ShowCases: One or more card elements not found');
            return;
        }
        
        // Initialize cards with data
        this.updateCards();
        
        // Setup event listeners
        this.setupEventListeners();
        
        // Start pulse animation on active card
        this.startPulseAnimation();
        
        console.log('ShowCases initialized with', this.cases.length, 'cases');
    }
    
    setupEventListeners() {
        // Click handler
        this.container.addEventListener('click', (e) => {
            e.preventDefault();
            this.nextCase();
        });
        
        // Mouse wheel handler (desktop)
        this.container.addEventListener('wheel', (e) => {
            e.preventDefault();
            this.handleScroll(e.deltaY);
        });
        
        // Touch handlers (mobile)
        this.container.addEventListener('touchstart', (e) => {
            this.handleTouchStart(e);
        }, { passive: true });
        
        this.container.addEventListener('touchmove', (e) => {
            this.handleTouchMove(e);
        }, { passive: true });
        
        this.container.addEventListener('touchend', (e) => {
            this.handleTouchEnd(e);
        }, { passive: true });
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (this.isElementInViewport(this.container)) {
                this.handleKeydown(e);
            }
        });
        
        // Resize handler
        window.addEventListener('resize', () => {
            this.handleResize();
        });
        
        // Visibility change handler (pause animations when not visible)
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pausePulseAnimation();
            } else {
                this.startPulseAnimation();
            }
        });
    }
    
    handleScroll(deltaY) {
        if (this.isAnimating) return;
        
        clearTimeout(this.scrollTimeout);
        this.scrollTimeout = setTimeout(() => {
            if (deltaY > 0) {
                this.nextCase();
            } else {
                this.prevCase();
            }
        }, 50); // Small delay to prevent excessive triggering
    }
    
    handleTouchStart(e) {
        const touch = e.touches[0];
        this.touchStartX = touch.clientX;
        this.touchStartY = touch.clientY;
    }
    
    handleTouchMove(e) {
        if (!e.touches[0]) return;
        
        const touch = e.touches[0];
        this.touchEndX = touch.clientX;
        this.touchEndY = touch.clientY;
    }
    
    handleTouchEnd(e) {
        if (this.isAnimating) return;
        
        const deltaX = this.touchEndX - this.touchStartX;
        const deltaY = this.touchEndY - this.touchStartY;
        
        // Check if horizontal swipe is greater than vertical (avoid conflicts with scroll)
        if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > this.minSwipeDistance) {
            if (deltaX > 0) {
                this.prevCase(); // Swipe right - go to previous
            } else {
                this.nextCase(); // Swipe left - go to next
            }
        }
        
        // Reset touch coordinates
        this.touchStartX = 0;
        this.touchStartY = 0;
        this.touchEndX = 0;
        this.touchEndY = 0;
    }
    
    handleKeydown(e) {
        if (this.isAnimating) return;
        
        switch(e.key) {
            case 'ArrowLeft':
            case 'ArrowUp':
                e.preventDefault();
                this.prevCase();
                break;
            case 'ArrowRight':
            case 'ArrowDown':
            case ' ':
                e.preventDefault();
                this.nextCase();
                break;
        }
    }
    
    handleResize() {
        // Debounce resize handler
        clearTimeout(this.resizeTimeout);
        this.resizeTimeout = setTimeout(() => {
            this.updateCards();
        }, 250);
    }
    
    nextCase() {
        if (this.isAnimating) return;
        
        this.currentIndex = (this.currentIndex + 1) % this.cases.length;
        this.animateCardsForward();
    }
    
    prevCase() {
        if (this.isAnimating) return;
        
        this.currentIndex = (this.currentIndex - 1 + this.cases.length) % this.cases.length;
        this.animateCardsBackward();
    }
    
    animateCardsForward() {
        this.isAnimating = true;
        this.pausePulseAnimation();
        
        const [card1, card2, card3] = this.cards;
        
        // Add animation classes
        card1.classList.add('slide-down');
        card2.classList.add('slide-up');
        card3.classList.add('slide-to-second');
        
        // After animation completes, rearrange cards
        setTimeout(() => {
            this.rearrangeCardsForward();
            this.updateCards();
            this.cleanupAnimationClasses();
            this.isAnimating = false;
            this.startPulseAnimation();
        }, this.animationDuration);
    }
    
    animateCardsBackward() {
        this.isAnimating = true;
        this.pausePulseAnimation();
        
        // For backward animation, we need to prepare the new top card first
        const newTopIndex = this.currentIndex;
        const newSecondIndex = (this.currentIndex + 1) % this.cases.length;
        const newThirdIndex = (this.currentIndex + 2) % this.cases.length;
        
        // Update the back card with new data before animation
        this.updateCardContent(this.cards[2], this.cases[newTopIndex], 3);
        
        const [card1, card2, card3] = this.cards;
        
        // Create reverse animation
        card3.classList.add('slide-up');
        card1.classList.add('slide-to-second');
        card2.classList.add('slide-to-third');
        
        setTimeout(() => {
            this.rearrangeCardsBackward();
            this.updateCards();
            this.cleanupAnimationClasses();
            this.isAnimating = false;
            this.startPulseAnimation();
        }, this.animationDuration);
    }
    
    rearrangeCardsForward() {
        // Move first card to end, shift others forward
        const [card1, card2, card3] = this.cards;
        
        // Reset positions and classes
        card1.className = 'card card-3';
        card2.className = 'card card-1 active';
        card3.className = 'card card-2';
        
        // Update cards array order
        this.cards = [card2, card3, card1];
        
        // Update IDs for consistency
        this.cards[0].id = 'card1';
        this.cards[1].id = 'card2';
        this.cards[2].id = 'card3';
    }
    
    rearrangeCardsBackward() {
        // Move last card to front, shift others backward
        const [card1, card2, card3] = this.cards;
        
        card3.className = 'card card-1 active';
        card1.className = 'card card-2';
        card2.className = 'card card-3';
        
        // Update cards array order
        this.cards = [card3, card1, card2];
        
        // Update IDs for consistency
        this.cards[0].id = 'card1';
        this.cards[1].id = 'card2';
        this.cards[2].id = 'card3';
    }
    
    cleanupAnimationClasses() {
        this.cards.forEach(card => {
            card.classList.remove('slide-down', 'slide-up', 'slide-to-second', 'slide-to-third');
        });
    }
    
    updateCards() {
        const activeIndex = this.currentIndex;
        const secondIndex = (this.currentIndex + 1) % this.cases.length;
        const thirdIndex = (this.currentIndex + 2) % this.cases.length;
        
        // Update content for each card
        this.updateCardContent(this.cards[0], this.cases[activeIndex], 1);
        this.updateCardContent(this.cards[1], this.cases[secondIndex], 2);
        this.updateCardContent(this.cards[2], this.cases[thirdIndex], 3);
    }
    
    updateCardContent(card, caseData, cardNumber) {
        if (!card || !caseData) return;
        
        const titleElement = card.querySelector('.card-title');
        if (titleElement) {
            titleElement.textContent = caseData.title;
        }
        
        // Only update detail content for the active card (card 1)
        if (cardNumber === 1) {
            const businessDesc = card.querySelector('.business-description');
            const taskDesc = card.querySelector('.task-description');
            const strategyDesc = card.querySelector('.strategy-description');
            const resultDesc = card.querySelector('.result-description');
            
            if (businessDesc) businessDesc.textContent = caseData.business;
            if (taskDesc) taskDesc.textContent = caseData.task;
            if (strategyDesc) strategyDesc.textContent = caseData.strategy;
            if (resultDesc) resultDesc.textContent = caseData.result;
        }
    }
    
    startPulseAnimation() {
        const activeCard = this.cards[0];
        if (activeCard && !this.isAnimating) {
            activeCard.style.animationPlayState = 'running';
        }
    }
    
    pausePulseAnimation() {
        const activeCard = this.cards[0];
        if (activeCard) {
            activeCard.style.animationPlayState = 'paused';
        }
    }
    
    isElementInViewport(element) {
        const rect = element.getBoundingClientRect();
        const windowHeight = window.innerHeight || document.documentElement.clientHeight;
        const windowWidth = window.innerWidth || document.documentElement.clientWidth;
        
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= windowHeight &&
            rect.right <= windowWidth
        );
    }
    
    // Public methods for external control
    goToCase(index) {
        if (index < 0 || index >= this.cases.length || this.isAnimating) return;
        
        const steps = (index - this.currentIndex + this.cases.length) % this.cases.length;
        
        if (steps === 0) return; // Already at target
        
        // Determine direction (forward or backward)
        const stepsBackward = (this.currentIndex - index + this.cases.length) % this.cases.length;
        
        if (steps <= stepsBackward) {
            // Go forward
            for (let i = 0; i < steps; i++) {
                setTimeout(() => this.nextCase(), i * (this.animationDuration + 100));
            }
        } else {
            // Go backward
            for (let i = 0; i < stepsBackward; i++) {
                setTimeout(() => this.prevCase(), i * (this.animationDuration + 100));
            }
        }
    }
    
    getCurrentIndex() {
        return this.currentIndex;
    }
    
    getCasesCount() {
        return this.cases.length;
    }
    
    destroy() {
        // Cleanup event listeners and timers
        clearTimeout(this.scrollTimeout);
        clearTimeout(this.resizeTimeout);
        
        if (this.container) {
            this.container.replaceWith(this.container.cloneNode(true));
        }
    }
}

// Auto-initialize when script loads
let showcasesInstance = null;

// Initialize with error handling
function initShowCases() {
    try {
        showcasesInstance = new ShowCases();
    } catch (error) {
        console.error('Failed to initialize ShowCases:', error);
    }
}

// Expose global interface
window.ShowCases = {
    init: initShowCases,
    getInstance: () => showcasesInstance,
    destroy: () => {
        if (showcasesInstance) {
            showcasesInstance.destroy();
            showcasesInstance = null;
        }
    }
};

// Auto-initialize
initShowCases();

// Handle page unload
window.addEventListener('beforeunload', () => {
    if (showcasesInstance) {
        showcasesInstance.destroy();
    }
});
