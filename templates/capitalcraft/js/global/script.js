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

// Делаем Headroom доступным глобально
window.Headroom = Headroom;

const header = document.querySelector('.site-header');
if (header) {
  const headroom = new Headroom(header, {
    classes: {
      pinned: 'pinned',
      unpinned: 'unpinned',
    },
    offset: 0,
    tolerance: {
      up: 5,
      down: 10
    }
  });
  
  headroom.init();
  window.headroom = headroom;
  
  // Настройки для мобильных устройств
  if (window.innerWidth <= 767) {
    headroom.options.tolerance = {
      up: 3,
      down: 5
    };
  }
}
