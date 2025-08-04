class ShowCases {
  constructor() {
    this.container = document.getElementById('show-case-stack');
    this.cases = Array.isArray(window.showcasesData)
      ? window.showcasesData
      : [];

    if (!this.container || this.cases.length < 3) {
      console.warn('ShowCases: контейнер или данные не найдены');
      return;
    }

    this.cards = Array.from(
      this.container.querySelectorAll('.show-case__card')
    );
    this.currentIndex = 0;
    this.isAnimating = false;
    this.touchStartY = 0;
    this.minSwipeDistance = 30;

    this.updateCards();

    this.reducedMotion = window.matchMedia(
      '(prefers-reduced-motion: reduce)'
    ).matches;
    if (this.reducedMotion) {
      this.cards.slice(1).forEach((card) => card.remove());
      return;
    }

    this.bindEvents();
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

    this.container.addEventListener('click', () => this.nextCase());
    this.container.addEventListener(
      'touchstart',
      (e) => this.handleTouchStart(e),
      { passive: true }
    );
    this.container.addEventListener('touchend', (e) => this.handleTouchEnd(e), {
      passive: true,
    });
    window.addEventListener('resize', () => {
      this.updateCards();
    });
  }

  nextCase() {
    if (this.isAnimating) return;
    this.isAnimating = true;

    const [card1, card2, card3] = this.cards;
    const nextIndex = (this.currentIndex + 3) % this.cases.length;

    card1.addEventListener(
      'transitionend',
      () => {
        this.currentIndex = (this.currentIndex + 1) % this.cases.length;
        this.isAnimating = false;
      },
      { once: true }
    );

    card1.classList.replace('show-case__card--1', 'show-case__card--3');
    card1.classList.remove('active');
    this.updateCardContent(card1, this.cases[nextIndex]);

    card2.classList.replace('show-case__card--2', 'show-case__card--1');
    card2.classList.add('active');

    card3.classList.replace('show-case__card--3', 'show-case__card--2');

    this.cards = [card2, card3, card1];
  }

  prevCase() {
    if (this.isAnimating) return;
    this.isAnimating = true;

    const [card1, card2, card3] = this.cards;
    const prevIndex =
      (this.currentIndex - 1 + this.cases.length) % this.cases.length;
    this.updateCardContent(card3, this.cases[prevIndex]);

    card3.addEventListener(
      'transitionend',
      () => {
        this.currentIndex = prevIndex;
        this.isAnimating = false;
      },
      { once: true }
    );

    card1.classList.replace('show-case__card--1', 'show-case__card--2');
    card1.classList.remove('active');

    card2.classList.replace('show-case__card--2', 'show-case__card--3');

    card3.classList.replace('show-case__card--3', 'show-case__card--1');
    card3.classList.add('active');

    this.cards = [card3, card1, card2];
  }

  handleTouchStart(e) {
    const touch = e.touches[0];
    if (!touch) return;
    this.touchStartY = touch.clientY;
  }

  handleTouchEnd(e) {
    if (this.isAnimating) return;
    const touch = e.changedTouches[0];
    if (!touch) return;
    const deltaY = touch.clientY - this.touchStartY;
    if (Math.abs(deltaY) > this.minSwipeDistance) {
      if (deltaY < 0) this.nextCase();
      else this.prevCase();
    }
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
}

window.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('show-case-stack');
  if (
    container &&
    Array.isArray(window.showcasesData) &&
    window.showcasesData.length >= 3
  ) {
    window.showcasesInstance = new ShowCases();
  }
});
