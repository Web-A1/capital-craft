"use strict";

document.addEventListener("DOMContentLoaded", () => {
  const section = document.querySelector(".partners");
  if (!section) {
    return;
  }

  const viewport = section.querySelector(".embla__viewport");
  const container = section.querySelector(".embla__container");
  if (!viewport || !container) {
    return;
  }

  const mobileQuery = window.matchMedia("(max-width: 767px)");
  const originalMarkup = container.innerHTML;

  let emblaInstance = null;
  let desktopDuplicated = false;
  let desktopDragHandlers = null;

  const registerLogoInteractions = logo => {
    if (!logo || logo.dataset.listenersBound === "true") {
      return;
    }

    let resetTimer;

    const highlight = () => {
      if (resetTimer) {
        window.clearTimeout(resetTimer);
        resetTimer = null;
      }
      logo.classList.add("no-filter");
    };

    const clearHighlight = () => {
      if (resetTimer) {
        window.clearTimeout(resetTimer);
        resetTimer = null;
      }
      logo.classList.remove("no-filter");
    };

    const scheduleReset = (delay = 1200) => {
      if (resetTimer) {
        window.clearTimeout(resetTimer);
      }
      resetTimer = window.setTimeout(() => {
        logo.classList.remove("no-filter");
        resetTimer = null;
      }, delay);
    };

    logo.addEventListener(
      "touchstart",
      () => {
        highlight();
      },
      { passive: true }
    );

    logo.addEventListener(
      "touchend",
      () => {
        scheduleReset();
      },
      { passive: true }
    );

    logo.addEventListener("touchcancel", clearHighlight);

    logo.addEventListener("pointerdown", event => {
      if (event.pointerType === "mouse") {
        return;
      }
      highlight();
    });

    logo.addEventListener("pointerup", event => {
      if (event.pointerType === "mouse") {
        clearHighlight();
        return;
      }
      scheduleReset();
    });

    logo.addEventListener("pointerleave", event => {
      if (event.pointerType === "mouse") {
        clearHighlight();
      }
    });

    logo.addEventListener("pointercancel", clearHighlight);

    logo.dataset.listenersBound = "true";
  };

  const bindLogoInteractions = () => {
    container
      .querySelectorAll(".partner-logo")
      .forEach(registerLogoInteractions);
  };

  const waitForEmbla = (attempt = 0) => {
    const hasEmbla =
      typeof window.EmblaCarousel === "function" &&
      typeof window.EmblaCarouselAutoplay === "function";
    if (hasEmbla) {
      if (!emblaInstance) {
        const autoplay = window.EmblaCarouselAutoplay({
          delay: 3000,
          stopOnInteraction: false,
          stopOnMouseEnter: false
        });

        emblaInstance = window.EmblaCarousel(
          viewport,
          {
            loop: true,
            align: "center",
            skipSnaps: false,
            containScroll: false
          },
          [autoplay]
        );
      } else {
        emblaInstance.reInit();
      }

      return;
    }

    if (attempt >= 20) {
      console.warn("Embla Carousel is not available for the partners slider.");
      return;
    }

    window.setTimeout(() => waitForEmbla(attempt + 1), 100);
  };

  const destroyEmbla = () => {
    if (emblaInstance) {
      emblaInstance.destroy();
      emblaInstance = null;
    }
  };

  const attachDesktopDrag = () => {
    if (desktopDragHandlers) {
      return;
    }

    let isDown = false;
    let startX = 0;
    let scrollStart = 0;

    const stopDrag = () => {
      isDown = false;
      viewport.classList.remove("dragging");
    };

    const onMouseDown = event => {
      isDown = true;
      startX = event.pageX - viewport.offsetLeft;
      scrollStart = viewport.scrollLeft;
      viewport.classList.add("dragging");
    };

    const onMouseMove = event => {
      if (!isDown) {
        return;
      }
      event.preventDefault();
      const currentX = event.pageX - viewport.offsetLeft;
      const walk = currentX - startX;
      viewport.scrollLeft = scrollStart - walk;
    };

    viewport.addEventListener("mousedown", onMouseDown);
    viewport.addEventListener("mousemove", onMouseMove);
    viewport.addEventListener("mouseleave", stopDrag);
    viewport.addEventListener("mouseup", stopDrag);

    desktopDragHandlers = {
      onMouseDown,
      onMouseMove,
      stopDrag
    };
  };

  const detachDesktopDrag = () => {
    if (!desktopDragHandlers) {
      return;
    }

    const { onMouseDown, onMouseMove, stopDrag } = desktopDragHandlers;

    viewport.removeEventListener("mousedown", onMouseDown);
    viewport.removeEventListener("mousemove", onMouseMove);
    viewport.removeEventListener("mouseleave", stopDrag);
    viewport.removeEventListener("mouseup", stopDrag);

    desktopDragHandlers = null;
  };

  const setupMobile = () => {
    if (desktopDuplicated) {
      container.innerHTML = originalMarkup;
      desktopDuplicated = false;
    }

    detachDesktopDrag();
    bindLogoInteractions();
    waitForEmbla();
  };

  const setupDesktop = () => {
    destroyEmbla();

    if (!desktopDuplicated) {
      container.innerHTML = originalMarkup + originalMarkup;
      desktopDuplicated = true;
    }

    bindLogoInteractions();
    attachDesktopDrag();
  };

  const applyLayout = matches => {
    if (matches) {
      setupMobile();
    } else {
      setupDesktop();
    }
  };

  applyLayout(mobileQuery.matches);

  const mediaListener = event => {
    applyLayout(event.matches);
  };

  if (typeof mobileQuery.addEventListener === "function") {
    mobileQuery.addEventListener("change", mediaListener);
  } else if (typeof mobileQuery.addListener === "function") {
    mobileQuery.addListener(mediaListener);
  }

  bindLogoInteractions();
});
