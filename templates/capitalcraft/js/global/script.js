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
  header.classList.add("pinned");

  let lastScrollY = window.pageYOffset;
  let frozen = false;
  let ignoreInitialScroll = true;
  const tolerance =
    window.innerWidth <= 767 ? { up: 3, down: 5 } : { up: 5, down: 10 };

  const onScroll = () => {
    if (frozen) return;
    const currentScrollY = window.pageYOffset;

    if (ignoreInitialScroll) {
      lastScrollY = currentScrollY;
      ignoreInitialScroll = false;
      return;
    }

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

  window.addEventListener("load", () => {
    lastScrollY = window.pageYOffset;
    ignoreInitialScroll = false;
  });

  window.headerControl = {
    freeze() {
      frozen = true;
    },
    unfreeze() {
      frozen = false;
      lastScrollY = window.pageYOffset;
    },
    pin() {
      header.classList.remove("unpinned");
      header.classList.add("pinned");
      lastScrollY = window.pageYOffset;
    }
  };
}
