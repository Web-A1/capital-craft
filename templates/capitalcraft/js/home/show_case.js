class ShowCases {
  constructor() {
    this.container = document.getElementById('show-case-stack');
    this.cards = Array.from(
      this.container.querySelectorAll('.show-case__card')
    );
    this.cases = window.showcasesData || [];
    this.currentIndex = 0;
    this.isAnimating = false;
    this.animationDuration = 500;
    this.touchStartX = 0;
    this.touchStartY = 0;
    this.minSwipeDistance = 50;

    this.updateCards();
    this.updateZIndex();
    this.bindEvents();
    this.startPulseAnimation();
  }

  bindEvents() {
    this.container.addEventListener(
      'wheel',
      (e) => {
        e.preventDefault();
        if (e.deltaY > 0) this.nextCase();
        else this.prevCase();
      },
      { passive: false }
    );

    this.container.addEventListener(
      'touchstart',
      (e) => this.handleTouchStart(e),
      { passive: true }
    );

    this.container.addEventListener(
      'touchend',
      (e) => this.handleTouchEnd(e),
      { passive: true }
    );

    this.container.addEventListener('click', () => this.nextCase());
    window.addEventListener('resize', () => {
      this.updateCards();
      this.updateZIndex();
    });
  }

  nextCase() {
    if (this.isAnimating) return;
    this.isAnimating = true;

    this.cards.push(this.cards.shift());
    this.currentIndex = (this.currentIndex + 1) % this.cases.length;

    this.updateCards();
    this.updateZIndex();

    setTimeout(() => {
      this.isAnimating = false;
    }, this.animationDuration);
  }

  prevCase() {
    if (this.isAnimating) return;
    this.isAnimating = true;

    this.cards.unshift(this.cards.pop());
    this.currentIndex =
      (this.currentIndex - 1 + this.cases.length) % this.cases.length;

    this.updateCards();
    this.updateZIndex();

    setTimeout(() => {
      this.isAnimating = false;
    }, this.animationDuration);
  }

  handleTouchStart(e) {
    const touch = e.touches[0];
    if (!touch) return;
    this.touchStartX = touch.clientX;
    this.touchStartY = touch.clientY;
  }

  handleTouchEnd(e) {
    if (this.isAnimating) return;
    const touch = e.changedTouches[0];
    if (!touch) return;
    const deltaX = touch.clientX - this.touchStartX;
    const deltaY = touch.clientY - this.touchStartY;
    if (
      Math.abs(deltaX) > Math.abs(deltaY) &&
      Math.abs(deltaX) > this.minSwipeDistance
    ) {
      if (deltaX < 0) this.nextCase();
      else this.prevCase();
    }
  }

  updateZIndex() {
    this.cards.forEach((card, i) => {
      card.classList.remove(
        'show-case__card--1',
        'show-case__card--2',
        'show-case__card--3',
        'active'
      );
      card.style.zIndex = '';
      card.style.opacity = '';
      if (i === 0) {
        card.classList.add('show-case__card--1', 'active');
      } else if (i === 1) {
        card.classList.add('show-case__card--2');
      } else if (i === 2) {
        card.classList.add('show-case__card--3');
      } else {
        card.style.zIndex = -1;
        card.style.opacity = 0;
      }
    });
  }

  updateCards() {
    const [card1, card2, card3] = this.cards;
    const i1 = this.currentIndex;
    const i2 = (i1 + 1) % this.cases.length;
    const i3 = (i1 + 2) % this.cases.length;

    this.updateCardContent(card1, this.cases[i1]);
    this.updateCardContent(card2, this.cases[i2]);
    this.updateCardContent(card3, this.cases[i3]);
  }

  updateCardContent(card, caseData) {
    if (!card || !caseData) return;

    const titleElement = card.querySelector('.show-case__card-title');
    if (titleElement) titleElement.textContent = caseData.title;

    const businessDesc = card.querySelector('.show-case__business');
    const taskDesc = card.querySelector('.show-case__task');
    const strategyDesc = card.querySelector('.show-case__strategy');
    const resultDesc = card.querySelector('.show-case__result');

    if (businessDesc) businessDesc.textContent = caseData.business;
    if (taskDesc) taskDesc.textContent = caseData.task;
    if (strategyDesc) strategyDesc.textContent = caseData.strategy;
    if (resultDesc) resultDesc.textContent = caseData.result;
  }

  startPulseAnimation() {
    const activeCard = this.cards[0];
    if (activeCard) {
      activeCard.style.animationPlayState = 'running';
    }
  }
}

window.addEventListener('DOMContentLoaded', () => {
  window.showcasesInstance = new ShowCases();
});
