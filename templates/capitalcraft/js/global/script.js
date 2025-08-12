import Headroom from '../vendor/headroom.min.js';
import { initBurger } from './burger.js';
import { initModal } from './modal.js';
import { initPhoneMask } from './phone-mask.js';
import { initFormSubmit } from './form-submit.js';
import { initScrollTop } from './scroll-top.js';

initBurger();
initModal();
initPhoneMask();
initFormSubmit();
initScrollTop();

const header = document.querySelector('.site-header');
if (header) {
  const headroom = new Headroom(header, {
    classes: {
      pinned: 'pinned',
      unpinned: 'unpinned',
    },
  });
  headroom.init();
  window.headroom = headroom;
}
