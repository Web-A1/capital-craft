import IMask from "imask";
import { initBurger } from "./burger.js";
import { initModal } from "./modal.js";
import { initPhoneMask } from "./phone-mask.js";
import { initFormSubmit } from "./form-submit.js";
import { initScrollTop } from "./scroll-top.js";
import { initTextTruncate } from "./text-truncate.js";

// Делаем IMask доступным глобально
window.IMask = IMask;

const initArticleH3Indent = () => {
  const articleBody = document.querySelector(".com-content-article__body");

  if (!articleBody) {
    return;
  }

  const indentClass = "article__h3-indent";
  const children = Array.from(articleBody.children);
  let indentActive = false;

  children.forEach(node => {
    if (node.matches("h3")) {
      indentActive = true;
      node.classList.remove(indentClass);
      return;
    }

    if (node.matches("h2, h4, h5, h6")) {
      indentActive = false;
      node.classList.remove(indentClass);
      return;
    }

    if (indentActive) {
      node.classList.add(indentClass);
    } else {
      node.classList.remove(indentClass);
    }
  });
};

const header = document.querySelector(".site-header");

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
  let lastScrollY = window.pageYOffset;
  let frozen = false;

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

  const updateMobileHideThreshold = newHeight => {
    mobileHideThreshold = resolveHeaderHeight(newHeight);
  };

  const onHeaderHeightChange = event => {
    const nextHeight = event?.detail?.height;

    updateMobileHideThreshold(nextHeight);
  };

  const onResize = () => {
    isMobileViewport = window.innerWidth <= MOBILE_BREAKPOINT;
    tolerance = getToleranceForViewport(isMobileViewport);

    if (isMobileViewport) {
      updateMobileHideThreshold();
    }
  };

  window.addEventListener("cc:header-height-change", onHeaderHeightChange);
  window.addEventListener("resize", onResize, { passive: true });

  const onScroll = () => {
    if (frozen) return;
    const currentScrollY = clampScrollY(window.pageYOffset);

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
    }
  };

  dispatchHeaderControlReady(window.headerControl);
}
