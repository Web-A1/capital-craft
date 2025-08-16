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
  let lastScrollY = window.pageYOffset;
  let frozen = false;
  const tolerance = window.innerWidth <= 767 ? { up: 3, down: 5 } : { up: 5, down: 10 };

  const onScroll = () => {
    if (frozen) return;
    const currentScrollY = window.pageYOffset;

    if (currentScrollY > lastScrollY && currentScrollY - lastScrollY > tolerance.down) {
      header.classList.remove('pinned');
      header.classList.add('unpinned');
    } else if (currentScrollY < lastScrollY && lastScrollY - currentScrollY > tolerance.up) {
      header.classList.remove('unpinned');
      header.classList.add('pinned');
    }

    lastScrollY = currentScrollY;
  };

  window.addEventListener('scroll', onScroll);

  window.headerControl = {
    freeze() {
      frozen = true;
    },
    unfreeze() {
      frozen = false;
    },
    pin() {
      header.classList.remove('unpinned');
      header.classList.add('pinned');
    }
  };
}

