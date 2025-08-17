'use strict';

export const initPhoneMask = () => {
  const form = document.getElementById('contactForm');
  const phoneInput = form ? form.querySelector('input[name="phone"]') : null;
  if (!phoneInput) return;

  // Проверяем, что IMask доступен
  if (typeof window.IMask === 'undefined') {
    console.error('IMask не загружен');
    return;
  }

  try {
    // Создаем маску для телефона в формате +7 (XXX) XXX-XX-XX
    const phoneMask = window.IMask(phoneInput, {
      mask: '+{7} (000) 000-00-00',
      lazy: false,
      placeholderChar: '_',
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

    // Обработчик полного заполнения
    phoneMask.on('complete', function() {
      // Маска полностью заполнена - номер корректен
    });

    // Обработчик неполного заполнения
    phoneMask.on('incomplete', function() {
      // Маска не полностью заполнена
    });

    console.log('Маска телефона успешно инициализирована');
  } catch (error) {
    console.error('Ошибка инициализации маски телефона:', error);
  }
};
