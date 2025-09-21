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
  let lastScrollY = window.pageYOffset;
  let frozen = false;
  const tolerance =
    window.innerWidth <= 767 ? { up: 3, down: 5 } : { up: 5, down: 10 };

  const onScroll = () => {
    if (frozen) return;
    const currentScrollY = window.pageYOffset;

    if (
      currentScrollY > lastScrollY &&
      currentScrollY - lastScrollY > tolerance.down
    ) {
      header.classList.remove("pinned");
      header.classList.add("unpinned");
    } else if (
      currentScrollY < lastScrollY &&
      lastScrollY - currentScrollY > tolerance.up
    ) {
      header.classList.remove("unpinned");
      header.classList.add("pinned");
    }

    lastScrollY = currentScrollY;
  };

  window.addEventListener("scroll", onScroll, { passive: true });

  window.headerControl = {
    freeze() {
      frozen = true;
    },
    unfreeze() {
      frozen = false;
    },
    pin() {
      header.classList.remove("unpinned");
      header.classList.add("pinned");
    }
  };
}
