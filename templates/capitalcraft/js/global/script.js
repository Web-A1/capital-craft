import IMask from "imask";
import { initBurger } from "./burger.js";
import { initModal } from "./modal.js";
import { initPhoneMask } from "./phone-mask.js";
import { initFormSubmit } from "./form-submit.js";
import { initScrollTop } from "./scroll-top.js";
import { initTextTruncate } from "./text-truncate.js";

// Делаем IMask доступным глобально
window.IMask = IMask;

initBurger();
initModal();
initPhoneMask();
initFormSubmit();
initScrollTop();
initTextTruncate();

const header = document.querySelector(".site-header");

if (header) {
  header.classList.add("pinned", "is-initial");

  let lastScrollY = window.pageYOffset;
  let frozen = false;
  let ignoreInitialScroll = true;
  let allowAutoHide = false;
  const tolerance =
    window.innerWidth <= 767 ? { up: 3, down: 5 } : { up: 5, down: 10 };

  const rootElement = document.documentElement;
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
