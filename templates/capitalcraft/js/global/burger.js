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

  const closeMenu = ({ shouldUnfreeze = true, unfreezeDelay = 100 } = {}) => {
    burger.classList.remove("active");
    burger.setAttribute("aria-expanded", "false");
    document.body.classList.remove("menu-open");

    // Управление доступностью
    mobileNav.setAttribute("aria-hidden", "true");

    clearAvailableHeight();

    // Восстанавливаем реакцию хедера на скролл после закрытия меню
    if (window.headerControl && shouldUnfreeze) {
      const delay =
        Number.isFinite(unfreezeDelay) && unfreezeDelay > 0
          ? unfreezeDelay
          : 0;

      if (delay > 0) {
        window.setTimeout(() => {
          window.headerControl.unfreeze();
        }, delay);
      } else {
        window.headerControl.unfreeze();
      }
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
  const normalizePathname = pathname => {
    if (typeof pathname !== "string") return "/";

    const trimmed = pathname.replace(/\/+$/, "");

    return trimmed === "" ? "/" : trimmed;
  };

  mobileNav.addEventListener("click", event => {
    const link = event.target.closest("a");

    if (!link) {
      return;
    }

    const { hash } = link;
    const hasHash = typeof hash === "string" && hash.length > 1;
    const isSameOrigin = link.origin === window.location.origin;
    const isSamePath =
      normalizePathname(link.pathname) ===
      normalizePathname(window.location.pathname);

    if (!hasHash || !isSameOrigin || !isSamePath) {
      closeMenu();
      return;
    }

    const targetId = decodeURIComponent(hash.slice(1));

    if (!targetId) {
      closeMenu();
      return;
    }

    const target = document.getElementById(targetId);

    if (!target) {
      closeMenu();
      return;
    }

    event.preventDefault();

    if (window.headerControl) {
      window.headerControl.freeze();
      window.headerControl.pin();
    }

    const headerHeight = getHeaderHeight();
    const targetPosition =
      target.getBoundingClientRect().top + window.scrollY - headerHeight;

    let released = false;
    let releaseTimeoutId = null;

    const releaseHeader = () => {
      if (released) {
        return;
      }

      released = true;

      if (releaseTimeoutId !== null) {
        window.clearTimeout(releaseTimeoutId);
        releaseTimeoutId = null;
      }

      if (window.headerControl) {
        window.headerControl.unfreeze();
      }
    };

    if ("onscrollend" in window) {
      window.addEventListener(
        "scrollend",
        () => {
          releaseHeader();
        },
        { once: true }
      );
    }

    window.scrollTo({
      top: Math.max(targetPosition, 0),
      behavior: "smooth"
    });

    releaseTimeoutId = window.setTimeout(() => {
      releaseTimeoutId = null;
      releaseHeader();
    }, 700);

    closeMenu({ shouldUnfreeze: false });

    if (typeof history.replaceState === "function") {
      history.replaceState(null, "", hash);
    } else {
      window.location.hash = hash;
    }
  });
};
