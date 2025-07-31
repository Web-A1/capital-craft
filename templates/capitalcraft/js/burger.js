'use strict';

const initBurger = () => {
  const burger = document.querySelector('.burger');
  const mobileNav = document.querySelector('.mobile-nav');
  if (!burger || !mobileNav) return;

  const closeMenu = () => {
    burger.classList.remove('active');
    mobileNav.classList.remove('open');
    document.body.classList.remove('menu-open');
  };

  burger.addEventListener('click', () => {
    burger.classList.toggle('active');
    mobileNav.classList.toggle('open');
    document.body.classList.toggle('menu-open');
  });

  const navLinks = mobileNav.querySelectorAll('a');
  navLinks.forEach((link) => {
    link.addEventListener('click', closeMenu);
  });
};

window.initBurger = initBurger;
