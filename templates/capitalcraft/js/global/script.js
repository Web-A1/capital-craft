import { initBurger } from './burger.js';
import { initModal } from './modal.js';
import { initPhoneMask } from './phone-mask.js';
import { initFormSubmit } from './form-submit.js';
import { initScrollTop } from './scroll-top.js';

document.addEventListener('DOMContentLoaded', () => {
  initBurger();
  initModal();
  initPhoneMask();
  initFormSubmit();
  initScrollTop();
});
