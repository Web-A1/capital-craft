'use strict';

export const initBurger = () => {
  const burger = document.querySelector('.burger');
  const header = document.querySelector('.site-header');
  const mobileNav = document.querySelector('.mobile-nav');
  
  if (!burger || !header || !mobileNav) return;
  
  // Сохраняем элемент, который имел фокус до открытия меню
  let previouslyFocusedElement = null;

  const closeMenu = () => {
    burger.classList.remove('active');
    burger.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('menu-open');
    
    // Управление доступностью
    mobileNav.setAttribute('aria-hidden', 'true');
    
    // Возвращаем фокус на кнопку бургера
    burger.focus();
    
    // Восстанавливаем реакцию хедера на скролл после закрытия меню
    if (window.headerControl) {
      setTimeout(() => {
        window.headerControl.unfreeze();
      }, 100);
    }
  };

  const openMenu = () => {
    burger.classList.add('active');
    burger.setAttribute('aria-expanded', 'true');
    document.body.classList.add('menu-open');
    
    // Управление доступностью
    mobileNav.setAttribute('aria-hidden', 'false');
    
    // Сохраняем текущий фокус
    previouslyFocusedElement = document.activeElement;
    
    // Переводим фокус на первый пункт меню
    const firstMenuItem = mobileNav.querySelector('a');
    if (firstMenuItem) {
      firstMenuItem.focus();
    }
    
    // Временно блокируем реакцию хедера на скролл при открытии меню
    if (window.headerControl) {
      window.headerControl.freeze();
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
  
  // Обработка клавиши Escape для закрытия меню
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && document.body.classList.contains('menu-open')) {
      closeMenu();
    }
  });

  // Закрытие при клике на ссылку меню (делегирование событий)
  mobileNav.addEventListener('click', (e) => {
    if (e.target.matches('a')) {
      closeMenu();
    }
  });
};
