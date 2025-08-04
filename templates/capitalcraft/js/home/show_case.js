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
    this.touchStartY = 0;
    this.minSwipeDistance = 30;

    this.updateCards();
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

    const nextIndex =
      (this.currentIndex + this.cards.length) % this.cases.length;
    const incomingCard = this.createCard(nextIndex, 'show-case__card--4');
    this.cards.push(incomingCard);

    const exitingCard = this.cards.shift();

    requestAnimationFrame(() => {
      exitingCard.classList.add('show-case__card--exit-down');
      exitingCard.classList.remove('active');

      const currentCard = this.cards[0];
      const nextCard = this.cards[1];

      currentCard.classList.replace('show-case__card--2', 'show-case__card--1');
      currentCard.classList.add('active');

      nextCard.classList.replace('show-case__card--3', 'show-case__card--2');

      incomingCard.classList.replace(
        'show-case__card--4',
        'show-case__card--3'
      );
    });

    exitingCard.addEventListener(
      'transitionend',
      () => {
        exitingCard.remove();
        this.currentIndex = (this.currentIndex + 1) % this.cases.length;
        this.startPulseAnimation();
        this.isAnimating = false;
      },
      { once: true }
    );
  }

  prevCase() {
    if (this.isAnimating) return;
    this.isAnimating = true;

    const prevIndex =
      (this.currentIndex - 1 + this.cases.length) % this.cases.length;
    const incomingCard = this.createCard(prevIndex, 'show-case__card--0');
    this.cards.unshift(incomingCard);
    const exitingCard = this.cards.pop();

    requestAnimationFrame(() => {
      exitingCard.classList.add('show-case__card--exit-down');
      exitingCard.classList.remove('show-case__card--3');

      const currentCard = this.cards[1];
      const nextCard = this.cards[2];

      currentCard.classList.remove('active');
      currentCard.classList.replace('show-case__card--1', 'show-case__card--2');

      nextCard.classList.replace('show-case__card--2', 'show-case__card--3');

      incomingCard.classList.replace(
        'show-case__card--0',
        'show-case__card--1'
      );
      incomingCard.classList.add('active');
    });

    exitingCard.addEventListener(
      'transitionend',
      () => {
        exitingCard.remove();
        this.currentIndex = prevIndex;
        this.startPulseAnimation();
        this.isAnimating = false;
      },
      { once: true }
    );
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

  createCard(index, className) {
    const card = this.cards[0].cloneNode(true);
    card.className = `show-case__card ${className}`;
    card.removeAttribute('id');
    this.updateCardContent(card, this.cases[index]);
    this.container.appendChild(card);
    return card;
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
    this.cards.forEach((card, index) => {
      card.style.animationPlayState = index === 0 ? 'running' : 'paused';
    });
  }
}

window.addEventListener('DOMContentLoaded', () => {
  window.showcasesInstance = new ShowCases();
});
