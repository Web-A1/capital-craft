'use strict';

import MicroModal from '../vendor/micromodal.es.min.js';

export const initModal = () => {
  MicroModal.init({
    disableScroll: true,
    disableFocus: true,
    onShow: () => {
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
      const header = document.querySelector('#contact-modal .modal__header');
      if (header) header.style.display = 'flex';
      const successBox = document.querySelector(
        '#contact-modal .modal__success'
      );
      if (successBox) successBox.style.display = 'none';
      document.body.classList.add('modal-open');
    },
    onClose: () => {
      document.body.classList.remove('modal-open');
      if (window.headroom) {
        window.headroom.pin();
      }
    },
  });
};
