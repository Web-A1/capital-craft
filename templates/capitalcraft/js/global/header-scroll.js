'use strict';

export const initHeaderScroll = () => {
  const header = document.querySelector('.site-header');
  if (!header) return;

  let lastScroll = window.scrollY;

  const onScroll = () => {
    const currentScroll = window.scrollY;
    if (currentScroll > lastScroll && currentScroll > 0) {
      header.classList.add('site-header--hidden');
    } else {
      header.classList.remove('site-header--hidden');
    }
    lastScroll = currentScroll;
  };

  window.addEventListener('scroll', onScroll);
  onScroll();
};
