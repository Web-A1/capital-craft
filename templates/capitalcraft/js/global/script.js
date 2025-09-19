import IMask from "imask";
import { initBurger } from "./burger.js";
import { initModal } from "./modal.js";
import { initPhoneMask } from "./phone-mask.js";
import { initFormSubmit } from "./form-submit.js";
import { initScrollTop } from "./scroll-top.js";
import { initTextTruncate } from "./text-truncate.js";

// Делаем IMaskдоступным глобально
window.IMask = IMask;

initBurger();
initModal();
initPhoneMask();
initFormSubmit();
initScrollTop();
initTextTruncate();

const rootElement = document.documentElement;
const header = document.querySelector(".site-header");

if (header) {
  header.classList.add("pinned", "is-initial");

  let lastScrollY = window.pageYOffset;
  let frozen = false;
  let ignoreInitialScroll = true;
  let allowAutoHide = false;
  const tolerance =
    window.innerWidth <= 767 ? { up: 3, down: 5 } : { up: 5, down: 10 };

  let lastHeaderHeight = null;
  let rafId = null;

  const updateHeaderHeight = () => {
    const newHeight = header.offsetHeight;

    if (newHeight !== lastHeaderHeight) {
      lastHeaderHeight = newHeight;
      rootElement.style.setProperty("--header-height", `${newHeight}px`);
    }
  };

  const scheduleHeaderHeightUpdate = () => {
    if (rafId !== null) return;

    rafId = window.requestAnimationFrame(() => {
      rafId = null;
      updateHeaderHeight();
    });
  };

  updateHeaderHeight();

  const enableAutoHide = () => {
    allowAutoHide = true;
    header.classList.remove("is-initial");
  };

  const interactionEvents = [
    ["pointerdown", { once: true, passive: true }],
    ["touchstart", { once: true, passive: true }],
    ["wheel", { once: true, passive: true }],
    ["keydown", { once: true }]
  ];

  interactionEvents.forEach(([eventName, options]) => {
    window.addEventListener(eventName, enableAutoHide, options);
  });

  const handleResize = () => {
    scheduleHeaderHeightUpdate();
  };

  window.addEventListener("resize", handleResize, { passive: true });
  window.addEventListener("orientationchange", handleResize);

  const onScroll = () => {
    if (frozen) return;
    const currentScrollY = window.pageYOffset;

    if (ignoreInitialScroll) {
      lastScrollY = currentScrollY;
      ignoreInitialScroll = false;
      return;
    }

    if (!allowAutoHide) {
      lastScrollY = currentScrollY;
      return;
    }

    if (
      currentScrollY > lastScrollY &&
      currentScrollY - lastScrollY > tolerance.down
    ) {
      header.classList.remove("pinned");
      header.classList.add("unpinned");
      scheduleHeaderHeightUpdate();
    } else if (
      currentScrollY < lastScrollY &&
      lastScrollY - currentScrollY > tolerance.up
    ) {
      header.classList.remove("unpinned");
      header.classList.add("pinned");
      scheduleHeaderHeightUpdate();
    }

    lastScrollY = currentScrollY;
  };

  window.addEventListener("scroll", onScroll, { passive: true });

  window.addEventListener("load", () => {
    lastScrollY = window.pageYOffset;
    ignoreInitialScroll = false;
    scheduleHeaderHeightUpdate();
  });

  window.headerControl = {
    freeze() {
      frozen = true;
      scheduleHeaderHeightUpdate();
    },
    unfreeze() {
      frozen = false;
      lastScrollY = window.pageYOffset;
      scheduleHeaderHeightUpdate();
    },
    pin() {
      header.classList.remove("unpinned");
      header.classList.add("pinned");
      lastScrollY = window.pageYOffset;
      scheduleHeaderHeightUpdate();
    },
    unpin() {
      header.classList.remove("pinned");
      header.classList.add("unpinned");
      lastScrollY = window.pageYOffset;
      scheduleHeaderHeightUpdate();
    }
  };
}

const supportsScrollMarginTop =
  typeof CSS !== "undefined" && typeof CSS.supports === "function"
    ? CSS.supports("scroll-margin-top: 1px")
    : false;

if (!supportsScrollMarginTop) {
  const parsePixelValue = value => {
    if (typeof value !== "string") {
      return null;
    }

    const trimmed = value.trim();
    if (!trimmed) {
      return null;
    }

    const parsed = parseFloat(trimmed);
    return Number.isNaN(parsed) ? null : parsed;
  };

  const getSectionScrollPadding = element => {
    const styles = window.getComputedStyle(element);
    const customPadding = parsePixelValue(
      styles.getPropertyValue("--section-scroll-padding")
    );

    if (customPadding !== null) {
      return customPadding;
    }

    return parsePixelValue(styles.paddingTop) ?? 0;
  };

  const getSectionScrollOffset = element => {
    const styles = window.getComputedStyle(element);
    const customOffset = parsePixelValue(
      styles.getPropertyValue("--section-scroll-offset")
    );

    return customOffset ?? 0;
  };

  const supportsSmoothScroll =
    "scrollBehavior" in document.documentElement.style;

  const scrollToTarget = target => {
    if (!target) return;

    const headerHeight =
      parsePixelValue(
        window.getComputedStyle(rootElement).getPropertyValue("--header-height")
      ) ?? 0;
    const sectionPadding = getSectionScrollPadding(target);
    const sectionScrollOffset = getSectionScrollOffset(target);
    const offset = headerHeight - sectionPadding + sectionScrollOffset;
    const targetTop =
      window.pageYOffset + target.getBoundingClientRect().top - offset;

    if (supportsSmoothScroll) {
      window.scrollTo({ top: targetTop, behavior: "smooth" });
    } else {
      window.scrollTo(0, targetTop);
    }
  };

  const resolveHashTarget = hash => {
    if (!hash || hash === "#") {
      return null;
    }

    const decoded = decodeURIComponent(hash.slice(1));
    if (!decoded) {
      return null;
    }

    return document.getElementById(decoded);
  };

  let suppressHashChange = false;

  const handleHashNavigation = () => {
    if (suppressHashChange) {
      suppressHashChange = false;
      return;
    }

    const target = resolveHashTarget(window.location.hash);
    if (target) {
      scrollToTarget(target);
    }
  };

  document.addEventListener("click", event => {
    const anchor = event.target.closest("a[href*='#']");
    if (!anchor) return;

    const url = new URL(anchor.getAttribute("href"), window.location.href);

    if (url.origin !== window.location.origin) {
      return;
    }

    const currentPathname = window.location.pathname.replace(/\/+$/, "");
    const targetPathname = url.pathname.replace(/\/+$/, "");

    if (currentPathname !== targetPathname) {
      return;
    }

    const target = resolveHashTarget(url.hash);
    if (!target) {
      return;
    }

    event.preventDefault();

    if (url.hash) {
      if (
        typeof history !== "undefined" &&
        typeof history.pushState === "function"
      ) {
        history.pushState(null, "", url.hash);
      } else {
        suppressHashChange = true;
        window.location.hash = url.hash.slice(1);
      }
    }

    scrollToTarget(target);
  });

  window.addEventListener("hashchange", handleHashNavigation);

  window.addEventListener("load", () => {
    const target = resolveHashTarget(window.location.hash);
    if (target) {
      scrollToTarget(target);
    }
  });
}
