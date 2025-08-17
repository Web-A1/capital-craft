'use strict';

export const initPhoneMask = () => {
  const form = document.getElementById('contactForm');
  const phoneInput = form ? form.querySelector('input[name="phone"]') : null;
  if (!phoneInput) return;

  if (typeof window.IMask === 'undefined') {
    console.error('IMask не загружен');
    return;
  }

  try {
    const options = { mask: '+{7} (000) 000-00-00' };
    // Лог на всякий случай
    // console.debug('IMask options:', options);
    const phoneMask = window.IMask(phoneInput, options);

    phoneMask.on('accept', function() {
      const errEl = form ? form.querySelector('.form-error') : null;
      if (errEl) errEl.style.display = 'none';
    });
  
    phoneMask.on('complete', function() {
      // Валидно заполнено
    });
  
    phoneMask.on('incomplete', function() {
      // Не полностью
    });

    // console.debug('Маска телефона инициализирована');
  } catch (error) {
    console.error('Ошибка инициализации маски телефона:', error);
  }
};
