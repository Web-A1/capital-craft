"use strict";

export const initBurger = () => {
  const burger = document.querySelector(".burger");
  const header = document.querySelector(".site-header");
  const mobileNav = document.querySelector(".mobile-nav");

  if (!burger || !header || !mobileNav) return;

  // Инициализация: меню скрыто для скринридеров по умолчанию
  mobileNav.setAttribute("aria-hidden", "true");

  const HEADER_HEIGHT_VAR = "--header-height";
  const MOBILE_NAV_HEIGHT_VAR = "--mobile-nav-open-height";

  const getHeaderHeight = () => {
    const rawValue = getComputedStyle(
      document.documentElement
    ).getPropertyValue(HEADER_HEIGHT_VAR);
    const parsedValue = parseFloat(rawValue);

    return Number.isFinite(parsedValue) && parsedValue > 0 ? parsedValue : 76;
  };

  const setAvailableHeight = (headerHeight = getHeaderHeight()) => {
    const availableHeight = Math.max(window.innerHeight - headerHeight, 0);
    mobileNav.style.setProperty(MOBILE_NAV_HEIGHT_VAR, `${availableHeight}px`);
  };

  const clearAvailableHeight = () => {
    mobileNav.style.removeProperty(MOBILE_NAV_HEIGHT_VAR);
  };

  const onResize = () => {
    if (!document.body.classList.contains("menu-open")) {
      return;
    }

    setAvailableHeight();
  };

  const onHeaderHeightChange = event => {
    if (!document.body.classList.contains("menu-open")) {
      return;
    }

    const detail = event?.detail;
    const measuredHeight =
      detail && typeof detail.height === "number" && detail.height > 0
        ? detail.height
        : undefined;

    setAvailableHeight(measuredHeight);
  };

  window.addEventListener("resize", onResize, { passive: true });
  window.addEventListener("cc:header-height-change", onHeaderHeightChange);

  const cleanup = () => {
    window.removeEventListener("resize", onResize);
    window.removeEventListener("cc:header-height-change", onHeaderHeightChange);
    window.removeEventListener("beforeunload", cleanup);
  };

  window.addEventListener("beforeunload", cleanup);

  const closeMenu = () => {
    burger.classList.remove("active");
    burger.setAttribute("aria-expanded", "false");
    document.body.classList.remove("menu-open");

    // Управление доступностью
    mobileNav.setAttribute("aria-hidden", "true");

    clearAvailableHeight();

    // Восстанавливаем реакцию хедера на скролл после закрытия меню
    if (window.headerControl) {
      setTimeout(() => {
        window.headerControl.unfreeze();
      }, 100);
    }
  };

  const openMenu = () => {
    burger.classList.add("active");
    burger.setAttribute("aria-expanded", "true");
    document.body.classList.add("menu-open");

    // Управление доступностью
    mobileNav.setAttribute("aria-hidden", "false");

    setAvailableHeight();

    // Временно блокируем реакцию хедера на скролл при открытии меню
    if (window.headerControl) {
      window.headerControl.freeze();
    }
  };

  burger.addEventListener("click", () => {
    const isMenuOpen = document.body.classList.contains("menu-open");

    if (isMenuOpen) {
      closeMenu();
    } else {
      openMenu();
    }
  });

  // Обработка клавиши Escape для закрытия меню
  document.addEventListener("keydown", e => {
    if (e.key === "Escape" && document.body.classList.contains("menu-open")) {
      closeMenu();
    }
  });

  // Закрытие при клике на ссылку меню (делегирование событий)
  mobileNav.addEventListener("click", e => {
    if (e.target.matches("a")) {
      closeMenu();
    }
  });
};
