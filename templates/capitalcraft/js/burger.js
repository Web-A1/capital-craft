"use strict";

const initBurger = () => {
  const burger = document.querySelector('.burger');
  const mobileNav = document.querySelector('.mobile-nav');
  if (!burger || !mobileNav) return;
  burger.addEventListener('click', () => {
    burger.classList.toggle('active');
    mobileNav.classList.toggle('open');
    document.body.classList.toggle('menu-open');
  });
};

window.initBurger = initBurger;
