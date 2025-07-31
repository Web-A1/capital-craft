"use strict";

const initModal = () => {
  const openBtn = document.querySelector('.js-open-modal');
  const modal = document.getElementById('contact-modal');
  if (!modal) return;
  const closeBtn = modal.querySelector('.modal__close');

  const openModal = () => {
    modal.classList.add('open');
    document.body.classList.add('modal-open');
    const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
    if (scrollbarWidth > 0) {
      document.body.style.paddingRight = `${scrollbarWidth}px`;
    }
    const form = document.getElementById('contactForm');
    if (form) {
      form.style.display = 'flex';
      form.reset();
      const err = form.querySelector('.form-error');
      if (err) err.style.display = 'none';
      const consentErr = form.querySelector('.consent-error');
      if (consentErr) consentErr.style.display = 'none';
      const errorResult = form.querySelector('.form-result.error');
      if (errorResult) errorResult.style.display = 'none';
    }
    const header = modal.querySelector('.modal__header');
    if (header) header.style.display = 'flex';
    const successBox = modal.querySelector('.modal__success');
    if (successBox) successBox.style.display = 'none';
  };

  const closeModal = () => {
    modal.classList.remove('open');
    document.body.classList.remove('modal-open');
    document.body.style.paddingRight = '';
  };

  if (openBtn) openBtn.addEventListener('click', openModal);
  if (closeBtn) closeBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', (e) => {
    if (e.target === modal || e.target.classList.contains('modal__overlay')) {
      closeModal();
    }
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('open')) {
      closeModal();
    }
  });
};

window.initModal = initModal;
