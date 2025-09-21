"use strict";

export const initBurger = () => {
  const burger = document.querySelector(".burger");
  const header = document.querySelector(".site-header");
  const mobileNav = document.querySelector(".mobile-nav");

  if (!burger || !header || !mobileNav) return;

  let suppressHashChange = false;

  const safeFocus = element => {
    if (!element || typeof element.focus !== "function") {
      return;
    }

    try {
      element.focus({ preventScroll: true });
    } catch (error) {
      element.focus();
    }
  };

  const FOCUSABLE_SELECTOR = [
    'a[href]:not([tabindex="-1"]):not([aria-disabled="true"])',
    "button:not([disabled])",
    'input:not([disabled]):not([type="hidden"])',
    "select:not([disabled])",
    "textarea:not([disabled])",
    '[tabindex]:not([tabindex="-1"]):not([disabled])'
  ].join(",");

  const setMenuAccessibility = isOpen => {
    if (isOpen) {
      mobileNav.setAttribute("aria-hidden", "false");
      mobileNav.removeAttribute("inert");
      mobileNav.removeAttribute("tabindex");
      if ("inert" in mobileNav) {
        mobileNav.inert = false;
      }
    } else {
      mobileNav.setAttribute("aria-hidden", "true");
      mobileNav.setAttribute("inert", "");
      mobileNav.setAttribute("tabindex", "-1");
      if ("inert" in mobileNav) {
        mobileNav.inert = true;
      }
    }
  };

  const focusFirstNavigationItem = () => {
    const focusTarget = mobileNav.querySelector(FOCUSABLE_SELECTOR);

    if (!focusTarget) {
      return;
    }

    if (typeof window.requestAnimationFrame === "function") {
      window.requestAnimationFrame(() => {
        safeFocus(focusTarget);
      });
    } else {
      safeFocus(focusTarget);
    }
  };

  // Инициализация: меню скрыто для скринридеров по умолчанию
  setMenuAccessibility(false);

  const HEADER_HEIGHT_VAR = "--header-height";
  const MOBILE_NAV_HEIGHT_VAR = "--mobile-nav-open-height";
  const FOOTER_CONTACTS_ANCHOR_GAP_VAR = "--footer-contacts-anchor-gap";
  const FOOTER_CONTACTS_ANCHOR_BUFFER_VAR = "--footer-contacts-anchor-buffer";
  const MOBILE_VIEWPORT_QUERY = "(max-width: 767px)";

  const isMobileViewport = () => {
    if (typeof window.matchMedia === "function") {
      try {
        return window.matchMedia(MOBILE_VIEWPORT_QUERY).matches;
      } catch (error) {
        // ignore query issues and fallback to width check
      }
    }

    return window.innerWidth <= 767;
  };

  const getPositiveFloat = value => {
    const parsed = Number.parseFloat(value);

    return Number.isFinite(parsed) && parsed > 0 ? parsed : 0;
  };

  const getFloatValue = value => {
    const parsed = Number.parseFloat(value);

    return Number.isFinite(parsed) ? parsed : 0;
  };

  const getContactsAnchorOffset = element => {
    if (!element || typeof window.getComputedStyle !== "function") {
      return 0;
    }

    const styles = window.getComputedStyle(element);

    if (!styles) {
      return 0;
    }

    const baseGap = getPositiveFloat(
      styles.getPropertyValue(FOOTER_CONTACTS_ANCHOR_GAP_VAR)
    );
    const buffer = getFloatValue(
      styles.getPropertyValue(FOOTER_CONTACTS_ANCHOR_BUFFER_VAR)
    );

    return baseGap + buffer;
  };

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

  const normalizeScrollTarget = value => {
    const maxScroll = Math.max(
      document.documentElement.scrollHeight - window.innerHeight,
      0
    );

    if (!Number.isFinite(value)) {
      return window.pageYOffset;
    }

    if (value <= 0) {
      return 0;
    }

    if (value >= maxScroll) {
      return maxScroll;
    }

    return value;
  };

  const createScrollCompletionWatcher = (
    targetPosition,
    { tolerance = 1, idleFrameThreshold = 5 } = {}
  ) => {
    const hasRAF = typeof window.requestAnimationFrame === "function";
    const hasCancelRAF = typeof window.cancelAnimationFrame === "function";

    if (!hasRAF || !hasCancelRAF) {
      const resolvedPosition = Number.isFinite(targetPosition)
        ? targetPosition
        : window.scrollY;

      return {
        promise: Promise.resolve(resolvedPosition),
        finish() {}
      };
    }

    let rafId = null;
    let resolved = false;
    let resolvePromise = null;
    let lastY = window.scrollY;
    let stableFrames = 0;

    const cleanup = () => {
      if (rafId !== null) {
        window.cancelAnimationFrame(rafId);
        rafId = null;
      }
    };

    const finish = finalPosition => {
      if (resolved) {
        return;
      }

      resolved = true;
      cleanup();

      const normalizedPosition = Number.isFinite(finalPosition)
        ? finalPosition
        : window.scrollY;

      if (typeof resolvePromise === "function") {
        resolvePromise(normalizedPosition);
      }
    };

    const tick = () => {
      if (resolved) {
        return;
      }

      const currentY = window.scrollY;

      if (Math.abs(currentY - targetPosition) <= tolerance) {
        if (Math.abs(currentY - lastY) <= 0.5) {
          stableFrames += 1;
        } else {
          stableFrames = 0;
        }

        if (stableFrames >= idleFrameThreshold) {
          finish(currentY);
          return;
        }
      } else {
        stableFrames = 0;
      }

      lastY = currentY;
      rafId = window.requestAnimationFrame(tick);
    };

    const promise = new Promise(resolve => {
      resolvePromise = resolve;
      rafId = window.requestAnimationFrame(tick);
    });

    return { promise, finish };
  };

  const hasUsableHash = hash => {
    if (typeof hash !== "string") {
      return false;
    }

    const trimmedHash = hash.trim();

    return trimmedHash.length > 1 && trimmedHash !== "#";
  };

  const scrollToHashWithCompensation = (
    rawHash,
    { behavior = "smooth" } = {}
  ) => {
    if (!hasUsableHash(rawHash)) {
      return false;
    }

    const trimmedHash = rawHash.trim();

    const normalizedHash = trimmedHash.startsWith("#")
      ? trimmedHash
      : `#${trimmedHash}`;

    let targetId = normalizedHash.slice(1);

    try {
      targetId = decodeURIComponent(targetId);
    } catch (error) {
      // Игнорируем ошибки декодирования и используем исходное значение
    }

    if (!targetId) {
      return false;
    }

    const target = document.getElementById(targetId);

    if (!target) {
      return false;
    }

    const headerHeight = getHeaderHeight();
    let extraOffset = 0;

    if (targetId === "contacts" && isMobileViewport()) {
      extraOffset = getContactsAnchorOffset(target);
    }

    const targetPosition = normalizeScrollTarget(
      target.getBoundingClientRect().top +
        window.scrollY -
        headerHeight -
        extraOffset
    );

    if (window.headerControl) {
      window.headerControl.freeze();
      window.headerControl.pin({ scrollY: targetPosition });
    }

    let released = false;

    const releaseHeader = (finalScrollPosition = targetPosition) => {
      if (released) {
        return;
      }

      released = true;

      if (window.headerControl) {
        window.headerControl.unfreeze({ scrollY: finalScrollPosition });
      }
    };

    const { promise: scrollCompletionPromise, finish: finishScrollWatch } =
      createScrollCompletionWatcher(targetPosition);

    if ("onscrollend" in window) {
      window.addEventListener(
        "scrollend",
        () => {
          finishScrollWatch(window.pageYOffset);
        },
        { once: true }
      );
    }

    try {
      window.scrollTo({
        top: targetPosition,
        behavior
      });
    } catch (error) {
      window.scrollTo(0, targetPosition);
    }

    const fallbackTimeoutId = window.setTimeout(() => {
      finishScrollWatch(window.pageYOffset);
    }, 700);

    scrollCompletionPromise.then(finalScrollPosition => {
      window.clearTimeout(fallbackTimeoutId);
      releaseHeader(finalScrollPosition);
    });

    return true;
  };

  const handleCurrentHashNavigation = ({ behavior = "auto" } = {}) => {
    return scrollToHashWithCompensation(window.location.hash, { behavior });
  };

  const scheduleInitialHashNavigation = () => {
    const invoke = () => {
      handleCurrentHashNavigation({ behavior: "auto" });
    };

    if (typeof window.requestAnimationFrame === "function") {
      window.requestAnimationFrame(invoke);
    } else {
      invoke();
    }
  };

  if (hasUsableHash(window.location.hash)) {
    if (document.readyState === "loading") {
      document.addEventListener(
        "DOMContentLoaded",
        scheduleInitialHashNavigation,
        {
          once: true
        }
      );
    } else {
      scheduleInitialHashNavigation();
    }
  }

  window.addEventListener("hashchange", () => {
    if (suppressHashChange) {
      suppressHashChange = false;
      return;
    }

    if (!hasUsableHash(window.location.hash)) {
      return;
    }

    handleCurrentHashNavigation({ behavior: "auto" });
  });

  const closeMenu = ({ shouldUnfreeze = true, unfreezeDelay = 100 } = {}) => {
    burger.classList.remove("active");
    burger.setAttribute("aria-expanded", "false");
    document.body.classList.remove("menu-open");

    // Управление доступностью
    setMenuAccessibility(false);
    safeFocus(burger);

    clearAvailableHeight();

    // Восстанавливаем реакцию хедера на скролл после закрытия меню
    if (window.headerControl && shouldUnfreeze) {
      const delay =
        Number.isFinite(unfreezeDelay) && unfreezeDelay > 0 ? unfreezeDelay : 0;

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
    setMenuAccessibility(true);

    setAvailableHeight();

    focusFirstNavigationItem();

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

    event.preventDefault();

    const navigationHandled = scrollToHashWithCompensation(hash, {
      behavior: "smooth"
    });

    if (!navigationHandled) {
      closeMenu();
      return;
    }

    closeMenu({ shouldUnfreeze: false });

    if (typeof history.replaceState === "function") {
      history.replaceState(null, "", hash);
    } else {
      suppressHashChange = true;
      window.location.hash = hash;
      window.setTimeout(() => {
        suppressHashChange = false;
      }, 0);
    }
  });
};
