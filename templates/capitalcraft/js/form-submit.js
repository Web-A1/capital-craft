"use strict";

const initFormSubmit = () => {
  const form = document.getElementById('contactForm');
  if (!form) return;

  const phoneInput = form.querySelector('input[name="phone"]');
  const consentInput = form.querySelector('.personal-data input');
  const consentError = form.querySelector('.consent-error');
  const header = document.querySelector('#contact-modal .modal__header');
  const successBox = document.querySelector('#contact-modal .modal__success');

  if (consentInput) {
    consentInput.addEventListener('change', () => {
      if (consentError) consentError.style.display = 'none';
    });
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const phone = phoneInput ? phoneInput.value.replace(/\D/g, '') : '';
    const error = form.querySelector('.form-error');
    let message = '';
    if (!phone) {
      message = 'Пожалуйста, введите номер телефона';
    } else if (phone.length !== 11 || !phone.startsWith('7')) {
      message = 'Введите корректный номер телефона';
    }
    const valid = !message;
    if (message) {
      error.textContent = message;
      error.style.display = 'block';
    } else {
      error.style.display = 'none';
    }

    const consentValid = !consentInput || consentInput.checked;
    if (!consentValid && consentError) {
      consentError.style.display = 'block';
    } else if (consentError) {
      consentError.style.display = 'none';
    }
    if (!valid || !consentValid) return;

    const fd = new FormData(form);
    try {
        const response = await fetch('templates/capitalcraft/partials/_send_to_telegram.php', {
        method: 'POST',
        body: fd,
      });

      if (!response.ok) {
        throw new Error(`Network error: ${response.status}`);
      }

      const data = await response.json();

      if (data.status === 'ok') {
        const errorRes = form.querySelector('.form-result.error');
        if (errorRes) errorRes.style.display = 'none';
        form.style.display = 'none';
        if (header) header.style.display = 'none';
        if (successBox) successBox.style.display = 'flex';
      } else {
        throw new Error();
      }
    } catch (err) {
      form.querySelector('.form-result.error').style.display = 'block';
      if (successBox) successBox.style.display = 'none';
    }
  });
};

window.initFormSubmit = initFormSubmit;
