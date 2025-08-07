'use strict';

export const initPhoneMask = () => {
  const form = document.getElementById('contactForm');
  const phoneInput = form ? form.querySelector('input[name="phone"]') : null;
  if (!phoneInput) return;

  phoneInput.addEventListener('input', function () {
    let digits = this.value.replace(/\D/g, '').replace(/^8/, '7');
    if (digits.charAt(0) !== '7') {
      digits = '7' + digits;
    }
    if (digits.length > 11) {
      digits = digits.slice(0, 11);
    }
    let formatted = '+7';
    if (digits.length > 1) {
      formatted += ' (' + digits.slice(1, 4);
      if (digits.length >= 4) formatted += ') ';
    }
    if (digits.length >= 4) {
      formatted += digits.slice(4, 7);
    }
    if (digits.length >= 7) {
      formatted += '-' + digits.slice(7, 9);
    }
    if (digits.length >= 9) {
      formatted += '-' + digits.slice(9, 11);
    }
    this.value = formatted;
    const errEl = form ? form.querySelector('.form-error') : null;
    if (errEl) errEl.style.display = 'none';
  });
};
