'use strict';

import IMask from 'imask';

export const initPhoneMask = () => {
  const form = document.getElementById('contactForm');
  const phoneInput = form ? form.querySelector('input[name="phone"]') : null;
  if (!phoneInput) return;

  // Создаем маску для телефона в формате +7 (XXX) XXX-XX-XX
  const phoneMask = IMask(phoneInput, {
    mask: '+{7} (000) 000-00-00',
    lazy: false, // Показываем маску сразу
    placeholderChar: '_', // Символ-заполнитель
    definitions: {
      '0': {
        validator: '[0-9]',
        cardinality: 1
      }
    }
  });

  // Обработчик изменения значения для скрытия ошибки
  phoneMask.on('accept', function() {
    const errEl = form ? form.querySelector('.form-error') : null;
    if (errEl) errEl.style.display = 'none';
  });

  // Обработчик неполного ввода для показа ошибки
  phoneMask.on('complete', function() {
    // Маска полностью заполнена - номер корректен
  });

  // Обработчик неполного ввода
  phoneMask.on('incomplete', function() {
    // Маска не полностью заполнена - можно показать подсказку
  });
};
