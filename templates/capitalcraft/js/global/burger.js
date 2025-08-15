'use strict';

export const initBurger = () => {
  const burger = document.querySelector('.burger');
  const header = document.querySelector('.site-header');
  const mobileNav = document.querySelector('.mobile-nav');
  
  if (!burger || !header || !mobileNav) return;

  const closeMenu = () => {
    burger.classList.remove('active');
    burger.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('menu-open');
    
    // Если есть Headroom, возобновляем его работу
    if (window.headroom) {
      window.headroom.enable(); // ИСПРАВЛЕНО: enable вместо init
    }
  };

  const openMenu = () => {
    burger.classList.add('active');
    burger.setAttribute('aria-expanded', 'true');
    document.body.classList.add('menu-open');
    
    // Если есть Headroom, приостанавливаем его работу
    if (window.headroom) {
      window.headroom.disable(); // ИСПРАВЛЕНО: disable вместо destroy
    }
  };

  burger.addEventListener('click', () => {
    const isMenuOpen = document.body.classList.contains('menu-open');
    
    if (isMenuOpen) {
      closeMenu();
    } else {
      openMenu();
    }
  });

  // Закрытие при клике на ссылку меню
  const navLinks = mobileNav.querySelectorAll('a');
  navLinks.forEach((link) => {
    link.addEventListener('click', closeMenu);
  });
};
