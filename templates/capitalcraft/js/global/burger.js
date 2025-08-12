'use strict';

export const initBurger = () => {
  const burger = document.querySelector('.burger');
  const mobileNav = document.getElementById('mobile-nav');
  if (!burger || !mobileNav) return;

  const closeMenu = () => {
    burger.classList.remove('active');
    burger.setAttribute('aria-expanded', 'false');
    mobileNav.classList.remove('open');
    document.body.classList.remove('menu-open');
  };

  burger.addEventListener('click', () => {
    const isActive = burger.classList.toggle('active');
    burger.setAttribute('aria-expanded', isActive);
    mobileNav.classList.toggle('open');
    document.body.classList.toggle('menu-open');
  });

  const navLinks = mobileNav.querySelectorAll('a');
  navLinks.forEach((link) => {
    link.addEventListener('click', closeMenu);
  });

  const closeBtn = mobileNav.querySelector('.mobile-nav__close');
  if (closeBtn) {
    closeBtn.addEventListener('click', (e) => {
      e.preventDefault();
      closeMenu();
    });
  }
};
