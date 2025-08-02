class ShowCases {
    constructor() {
        this.cases = window.showcasesData || [];
        this.currentIndex = 0;
        this.isAnimating = false;
        this.cards = [];
        this.container = null;

        this.touchStartX = 0;
        this.touchStartY = 0;
        this.touchEndX = 0;
        this.touchEndY = 0;
        this.minSwipeDistance = 50;

        this.scrollTimeout = null;
        this.scrollDelay = 300;

        this.animationDuration = 500;

        this.init();
    }

    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    setup() {
        this.container = document.getElementById('show-case-stack');
        if (!this.container || this.cases.length === 0) {
            console.warn('ShowCases: Container not found or no cases data available');
            return;
        }

        this.cards = [
            document.getElementById('show-case-card1'),
            document.getElementById('show-case-card2'),
            document.getElementById('show-case-card3')
        ];

        if (this.cards.some(card => !card)) {
            console.warn('ShowCases: One or more card elements not found');
            return;
        }

        this.updateCards();
        this.setupEventListeners();
        this.startPulseAnimation();
    }

    setupEventListeners() {
        this.container.addEventListener('click', (e) => {
            e.preventDefault();
            this.nextCase();
        });

        this.container.addEventListener('wheel', (e) => {
            e.preventDefault();
            this.handleScroll(e.deltaY);
        });

        this.container.addEventListener('touchstart', (e) => {
            this.handleTouchStart(e);
        }, { passive: true });

        this.container.addEventListener('touchmove', (e) => {
            this.handleTouchMove(e);
        }, { passive: true });

        this.container.addEventListener('touchend', (e) => {
            this.handleTouchEnd(e);
        }, { passive: true });

        document.addEventListener('keydown', (e) => {
            if (this.isElementInViewport(this.container)) {
                this.handleKeydown(e);
            }
        });

        window.addEventListener('resize', () => {
            this.handleResize();
        });

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
            } else if (deltaY < 0) {
                this.prevCase();
            }
        }, this.scrollDelay);
    }

    handleTouchStart(e) {
        const touch = e.changedTouches[0];
        this.touchStartX = touch.screenX;
        this.touchStartY = touch.screenY;
    }

    handleTouchMove(e) {
        const touch = e.changedTouches[0];
        this.touchEndX = touch.screenX;
        this.touchEndY = touch.screenY;
    }

    handleTouchEnd() {
        const deltaX = this.touchEndX - this.touchStartX;
        const deltaY = this.touchEndY - this.touchStartY;

        if (Math.abs(deltaY) > Math.abs(deltaX) && Math.abs(deltaY) > this.minSwipeDistance) {
            if (deltaY > 0) {
                this.nextCase();
            } else {
                this.prevCase();
            }
        }
    }

    handleKeydown(e) {
        if (e.key === 'ArrowDown') {
            this.nextCase();
        } else if (e.key === 'ArrowUp') {
            this.prevCase();
        }
    }

    handleResize() {
        clearTimeout(this.resizeTimeout);
        this.resizeTimeout = setTimeout(() => {
            this.updateCards();
        }, 200);
    }

    nextCase() {
        if (this.isAnimating) return;
        this.isAnimating = true;

        const card1 = this.cards[0];
        const card2 = this.cards[1];
        const card3 = this.cards[2];

        card1.classList.add('slide-down');
        card2.classList.add('slide-up');
        card3.classList.add('slide-to-second');

        setTimeout(() => {
            this.cards.push(this.cards.shift());
            this.updateZIndex();
            this.updateCards();
            this.isAnimating = false;
        }, this.animationDuration);

        this.currentIndex = (this.currentIndex + 1) % this.cases.length;
    }

    prevCase() {
        if (this.isAnimating) return;
        this.isAnimating = true;

        const card1 = this.cards[0];
        const card2 = this.cards[1];
        const card3 = this.cards[2];

        card3.classList.add('slide-up');
        card1.classList.add('slide-to-second');
        card2.classList.add('slide-to-third');

        setTimeout(() => {
            this.cards.unshift(this.cards.pop());
            this.updateZIndex();
            this.updateCards();
            this.isAnimating = false;
        }, this.animationDuration);

        this.currentIndex = (this.currentIndex - 1 + this.cases.length) % this.cases.length;
    }

    updateZIndex() {
        this.cards[0].style.zIndex = 3;
        this.cards[1].style.zIndex = 2;
        this.cards[2].style.zIndex = 1;

        this.cards.forEach(card => {
            card.classList.remove('slide-down', 'slide-up', 'slide-to-second', 'slide-to-third');
        });
    }

    updateCards() {
        const card1Index = this.currentIndex;
        const card2Index = (this.currentIndex + 1) % this.cases.length;
        const card3Index = (this.currentIndex + 2) % this.cases.length;

        this.updateCardContent(this.cards[0], this.cases[card1Index], 1);
        this.updateCardContent(this.cards[1], this.cases[card2Index], 2);
        this.updateCardContent(this.cards[2], this.cases[card3Index], 3);
    }

    updateCardContent(card, caseData, cardNumber) {
        if (!card || !caseData) return;

        const titleElement = card.querySelector('.show-case__card-title');
        if (titleElement) {
            titleElement.textContent = caseData.title;
        }

        if (cardNumber === 1) {
            const businessDesc = card.querySelector('.show-case__business');
            const taskDesc = card.querySelector('.show-case__task');
            const strategyDesc = card.querySelector('.show-case__strategy');
            const resultDesc = card.querySelector('.show-case__result');

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

    goToCase(index) {
        if (index < 0 || index >= this.cases.length || this.isAnimating) return;

        const steps = (index - this.currentIndex + this.cases.length) % this.cases.length;
        if (steps === 0) return;

        const stepsBackward = (this.currentIndex - index + this.cases.length) % this.cases.length;

        if (steps <= stepsBackward) {
            for (let i = 0; i < steps; i++) {
                setTimeout(() => this.nextCase(), i * (this.animationDuration + 100));
            }
        } else {
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
        clearTimeout(this.scrollTimeout);
        clearTimeout(this.resizeTimeout);

        if (this.container) {
            this.container.replaceWith(this.container.cloneNode(true));
        }
    }
}

let showcasesInstance = null;

function initShowCases() {
    try {
        showcasesInstance = new ShowCases();
    } catch (error) {
        console.error('Failed to initialize ShowCases:', error);
    }
}

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

initShowCases();

window.addEventListener('beforeunload', () => {
    if (showcasesInstance) {
        showcasesInstance.destroy();
    }
});

