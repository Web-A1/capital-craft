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

    this.container.addEventListener('click', () => this.nextCase());
  }

  nextCase() {
    if (this.isAnimating) return;
    this.isAnimating = true;

    const exitingCard = this.cards[0];
    exitingCard.classList.add('show-case__card--exit');

    // Смещаем карточки вниз
    if (this.cards[1]) {
      this.cards[1].classList.replace(
        'show-case__card--2',
        'show-case__card--1'
      );
    }
    if (this.cards[2]) {
      this.cards[2].classList.replace(
        'show-case__card--3',
        'show-case__card--2'
      );
    }

    // Сдвигаем массив
    this.cards.push(this.cards.shift());
    this.currentIndex = (this.currentIndex + 1) % this.cases.length;

    // Добавляем новую карточку сверху
    const newCard = this.cards[2];
    newCard.classList.add('show-case__card--3');
    newCard.style.opacity = '0';
    this.updateCardContent(
      newCard,
      this.cases[(this.currentIndex + 2) % this.cases.length]
    );

    // Обновляем классы и z-index
    this.updateZIndex();

    requestAnimationFrame(() => {
      newCard.style.opacity = '1';
    });

    setTimeout(() => {
      exitingCard.classList.remove(
        'show-case__card--exit',
        'show-case__card--1'
      );
      this.isAnimating = false;
      this.startPulseAnimation();
    }, this.animationDuration);
  }

  prevCase() {
    // not implemented yet
  }

  updateZIndex() {
    this.cards.forEach((card, i) => {
      card.classList.remove(
        'show-case__card--1',
        'show-case__card--2',
        'show-case__card--3',
        'active'
      );
      if (i === 0) card.classList.add('show-case__card--1', 'active');
      if (i === 1) card.classList.add('show-case__card--2');
      if (i === 2) card.classList.add('show-case__card--3');
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
