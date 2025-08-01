'use strict';

document.addEventListener('DOMContentLoaded', () => {
  const container = document.querySelector('.partners .embla__container');
  if (!container) return;

  // Дублируем логотипы для плавной бесконечной прокрутки
  container.innerHTML += container.innerHTML;

  container.querySelectorAll('.partner-logo').forEach((logo) => {
    logo.addEventListener('touchstart', () => {
      logo.classList.add('no-filter');
    });
    logo.addEventListener('touchend', () => {
      logo.classList.remove('no-filter');
    });
  });
});
