"use strict";

(() => {
  document.addEventListener('DOMContentLoaded', () => {
    if (typeof initBurger === 'function') initBurger();
    if (typeof initModal === 'function') initModal();
    if (typeof initPhoneMask === 'function') initPhoneMask();
    if (typeof initFormSubmit === 'function') initFormSubmit();
    if (typeof initScrollTop === 'function') initScrollTop();
  });
})();
