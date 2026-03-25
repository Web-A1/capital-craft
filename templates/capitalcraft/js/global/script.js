import "wicg-inert";
import IMask from "imask";
import { initBurger } from "./burger.js";
import { initModal } from "./modal.js";
import { initPhoneMask } from "./phone-mask.js";
import { initFormSubmit } from "./form-submit.js";
import { initScrollTop } from "./scroll-top.js";
import { initTextTruncate } from "./text-truncate.js";

// Делаем IMask доступным глобально
window.IMask = IMask;

const header = document.querySelector(".site-header");

const restoreScrollRestoration = (() => {
  if (typeof window === "undefined") {
    return () => {};
  }

  const { history } = window;

  if (!history || typeof history.scrollRestoration === "undefined") {
    return () => {};
  }

  const previousMode = history.scrollRestoration;

  if (previousMode !== "manual") {
    try {
      history.scrollRestoration = "manual";
    } catch (error) {
      return () => {};
    }

    const restore = () => {
      try {
        history.scrollRestoration = previousMode || "auto";
      } catch (error) {
        /* noop */
      }
    };

    window.addEventListener("beforeunload", restore, { once: true });

    return restore;
  }

  return () => {};
})();

const hasUsableHash = hash => {
  if (typeof hash !== "string") {
    return false;
  }

  const trimmed = hash.trim();

  return trimmed.length > 1 && trimmed !== "#";
};

const initHeaderHeightObserver = headerElement => {
  if (!headerElement) return;

  const root = document.documentElement;
  let lastHeight = null;

  const applyHeight = height => {
    if (!Number.isFinite(height) || height <= 0) {
      return;
    }

    const normalizedHeight = Math.round(height * 1000) / 1000;

    if (lastHeight === normalizedHeight) {
      return;
    }

    lastHeight = normalizedHeight;
    root.style.setProperty("--header-height", `${normalizedHeight}px`);

    const eventDetail = { detail: { height: normalizedHeight } };
    const heightChangeEvent =
      typeof window.CustomEvent === "function"
        ? new CustomEvent("cc:header-height-change", eventDetail)
        : (() => {
            const fallbackEvent = document.createEvent("CustomEvent");
            fallbackEvent.initCustomEvent(
              "cc:header-height-change",
              false,
              false,
              eventDetail.detail
            );
            return fallbackEvent;
          })();

    window.dispatchEvent(heightChangeEvent);
  };

  const updateHeaderHeight = () => {
    applyHeight(headerElement.getBoundingClientRect().height);
  };

  if (typeof ResizeObserver === "function") {
    const observer = new ResizeObserver(entries => {
      const entry = entries && entries[0];
      const newHeight = entry?.contentRect?.height;

      if (Number.isFinite(newHeight)) {
        applyHeight(newHeight);
        return;
      }

      updateHeaderHeight();
    });

    observer.observe(headerElement);
    updateHeaderHeight();

    window.addEventListener(
      "beforeunload",
      () => {
        observer.disconnect();
      },
      { once: true }
    );
  } else {
    updateHeaderHeight();

    const onResize = () => {
      updateHeaderHeight();
    };

    window.addEventListener("resize", onResize, { passive: true });

    window.addEventListener(
      "beforeunload",
      () => {
        window.removeEventListener("resize", onResize);
      },
      { once: true }
    );
  }
};

initHeaderHeightObserver(header);

initBurger();
initModal();
initPhoneMask();
initFormSubmit();
initScrollTop();
initTextTruncate();

if (header) {
  const MOBILE_BREAKPOINT = 767;
  const DESKTOP_STICKY_MIN_WIDTH = 1024;
  const root = document.documentElement;

  const readHeaderHeightFromStyles = () => {
    const rawValue = getComputedStyle(root).getPropertyValue("--header-height");
    const parsedValue = parseFloat(rawValue);

    return Number.isFinite(parsedValue) && parsedValue > 0 ? parsedValue : null;
  };

  const measureHeaderHeight = () => {
    const rect = header.getBoundingClientRect();
    const { height } = rect;

    return Number.isFinite(height) && height > 0 ? height : null;
  };

  const resolveHeaderHeight = fallback => {
    if (Number.isFinite(fallback) && fallback > 0) {
      return fallback;
    }

    const fromStyles = readHeaderHeightFromStyles();

    if (fromStyles !== null) {
      return fromStyles;
    }

    const measured = measureHeaderHeight();

    return Number.isFinite(measured) ? measured : 0;
  };

  const getToleranceForViewport = isMobile =>
    isMobile ? { up: 3, down: 5 } : { up: 5, down: 10 };

  let isMobileViewport = window.innerWidth <= MOBILE_BREAKPOINT;
  let tolerance = getToleranceForViewport(isMobileViewport);
  let mobileHideThreshold = resolveHeaderHeight();
  let desktopStickyThreshold = Math.max(
    window.innerHeight - resolveHeaderHeight(),
    0
  );
  let lastScrollY = window.pageYOffset;
  let frozen = false;
  let autoHideSuspended = false;
  let autoHideReady = true;

  const shouldDeferAutoHide =
    isMobileViewport && hasUsableHash(window.location.hash);

  const initializingClass = "is-header-initializing";

  if (shouldDeferAutoHide) {
    autoHideReady = false;

    try {
      window.scrollTo(0, 0);
    } catch (error) {
      document.documentElement.scrollTop = 0;
    }

    root.classList.add(initializingClass);
  }

  const flagInitializing = () => {
    if (!root.classList.contains(initializingClass)) {
      root.classList.add(initializingClass);
    }
  };

  const clearInitializing = () => {
    root.classList.remove(initializingClass);
  };

  const clampScrollY = value => {
    if (!Number.isFinite(value)) {
      return window.pageYOffset;
    }

    const maxScroll = Math.max(
      document.documentElement.scrollHeight - window.innerHeight,
      0
    );

    if (value <= 0) {
      return 0;
    }

    if (value >= maxScroll) {
      return maxScroll;
    }

    return value;
  };

  const syncLastScrollY = value => {
    lastScrollY = clampScrollY(value);
    return lastScrollY;
  };

  const enforcePinnedState = scrollY => {
    header.classList.remove("unpinned");
    header.classList.add("pinned");

    if (Number.isFinite(scrollY)) {
      syncLastScrollY(scrollY);
    }
  };

  const suspendAutoHide = (options = {}) => {
    const { scrollY } = options;

    autoHideSuspended = true;
    flagInitializing();
    enforcePinnedState(
      Number.isFinite(scrollY) ? scrollY : clampScrollY(window.pageYOffset)
    );
  };

  const resumeAutoHide = (options = {}) => {
    const { scrollY } = options;

    autoHideSuspended = false;
    autoHideReady = true;
    clearInitializing();
    syncLastScrollY(
      Number.isFinite(scrollY) ? scrollY : clampScrollY(window.pageYOffset)
    );
  };

  const updateMobileHideThreshold = newHeight => {
    mobileHideThreshold = resolveHeaderHeight(newHeight);
  };

  const updateDesktopStickyThreshold = newHeight => {
    const headerHeight = resolveHeaderHeight(newHeight);
    desktopStickyThreshold = Math.max(window.innerHeight - headerHeight, 0);
  };

  const updateDesktopStickyState = scrollY => {
    if (window.innerWidth < DESKTOP_STICKY_MIN_WIDTH) {
      header.classList.remove("desktop-sticky-active");
      return;
    }

    header.classList.toggle(
      "desktop-sticky-active",
      scrollY > desktopStickyThreshold
    );
  };

  const onHeaderHeightChange = event => {
    const nextHeight = event?.detail?.height;

    updateMobileHideThreshold(nextHeight);
    updateDesktopStickyThreshold(nextHeight);
  };

  const onResize = () => {
    isMobileViewport = window.innerWidth <= MOBILE_BREAKPOINT;
    tolerance = getToleranceForViewport(isMobileViewport);

    if (isMobileViewport) {
      updateMobileHideThreshold();
    }

    updateDesktopStickyThreshold();
    updateDesktopStickyState(clampScrollY(window.pageYOffset));
  };

  window.addEventListener("cc:header-height-change", onHeaderHeightChange);
  window.addEventListener("resize", onResize, { passive: true });

  updateDesktopStickyThreshold();
  updateDesktopStickyState(clampScrollY(window.pageYOffset));

  let scrollScheduled = false;

  const evaluateScroll = () => {
    scrollScheduled = false;
    if (frozen) return;
    const currentScrollY = clampScrollY(window.pageYOffset);
    updateDesktopStickyState(currentScrollY);

    if (!autoHideReady) {
      enforcePinnedState(currentScrollY);
      return;
    }

    if (autoHideSuspended) {
      enforcePinnedState(currentScrollY);
      return;
    }

    if (isMobileViewport && currentScrollY <= mobileHideThreshold) {
      header.classList.remove("unpinned");
      header.classList.add("pinned");
      syncLastScrollY(currentScrollY);
      return;
    }

    const isScrollingDown =
      currentScrollY > lastScrollY &&
      currentScrollY - lastScrollY > tolerance.down;
    const isScrollingUp =
      currentScrollY < lastScrollY &&
      lastScrollY - currentScrollY > tolerance.up;

    if (
      isScrollingDown &&
      (!isMobileViewport || currentScrollY > mobileHideThreshold)
    ) {
      header.classList.remove("pinned");
      header.classList.add("unpinned");
    } else if (isScrollingUp) {
      header.classList.remove("unpinned");
      header.classList.add("pinned");
    }

    syncLastScrollY(currentScrollY);
  };

  const onScroll = () => {
    if (!scrollScheduled) {
      scrollScheduled = true;

      if (typeof window.requestAnimationFrame === "function") {
        window.requestAnimationFrame(evaluateScroll);
      } else {
        evaluateScroll();
      }
    }
  };

  if (shouldDeferAutoHide) {
    suspendAutoHide({ scrollY: window.pageYOffset });
  }

  window.addEventListener("scroll", onScroll, { passive: true });

  const dispatchHeaderControlReady = control => {
    if (!control) {
      return;
    }

    const readyDetail = { detail: { control } };
    const readyEvent =
      typeof window.CustomEvent === "function"
        ? new CustomEvent("cc:header-control-ready", readyDetail)
        : (() => {
            const fallbackEvent = document.createEvent("CustomEvent");
            fallbackEvent.initCustomEvent(
              "cc:header-control-ready",
              false,
              false,
              readyDetail.detail
            );
            return fallbackEvent;
          })();

    window.dispatchEvent(readyEvent);
  };

  window.headerControl = {
    freeze(options = {}) {
      const { scrollY } = options;

      syncLastScrollY(Number.isFinite(scrollY) ? scrollY : window.pageYOffset);
      frozen = true;
    },
    unfreeze(options = {}) {
      const { scrollY } = options;

      syncLastScrollY(Number.isFinite(scrollY) ? scrollY : window.pageYOffset);
      frozen = false;
    },
    pin(options = {}) {
      const { scrollY } = options;

      header.classList.remove("unpinned");
      header.classList.add("pinned");
      syncLastScrollY(Number.isFinite(scrollY) ? scrollY : window.pageYOffset);
    },
    suspendAutoHide(options = {}) {
      suspendAutoHide(options);
    },
    resumeAutoHide(options = {}) {
      resumeAutoHide(options);
    },
    isAutoHideSuspended() {
      return autoHideSuspended;
    }
  };

  dispatchHeaderControlReady(window.headerControl);
}

window.addEventListener(
  "pageshow",
  () => {
    if (typeof restoreScrollRestoration === "function") {
      restoreScrollRestoration();
    }
  },
  { once: true }
);
