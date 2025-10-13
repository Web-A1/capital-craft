"use strict";

document.addEventListener("DOMContentLoaded", () => {
  const section = document.querySelector(".partners");
  if (!section) {
    return;
  }

  const viewport = section.querySelector(".embla__viewport");
  const container = viewport
    ? viewport.querySelector(".embla__container")
    : null;
  if (!viewport || !container) {
    return;
  }

  const mobileQuery = window.matchMedia("(max-width: 767px)");
  const originalMarkup = container.innerHTML;

  let emblaInstance = null;
  let autoplayPlugin = null;
  let desktopDuplicated = false;
  let desktopDragHandlers = null;
  let mobileInitToken = 0;
  let logosReadyPromise = null;
  let viewportReadyPromise = null;
  let emblaReadyPromise = null;
  let autoplayStartTimeout = null;

  const viewportReadyClass = "is-embla-ready";
  const autoplayStartDelay = 1500;

  const registerLogoInteractions = logo => {
    if (!logo || logo.dataset.listenersBound === "true") {
      return;
    }

    let resetTimer = null;

    const clearTimer = () => {
      if (resetTimer !== null) {
        window.clearTimeout(resetTimer);
        resetTimer = null;
      }
    };

    const highlight = () => {
      clearTimer();
      logo.classList.add("no-filter");
    };

    const clearHighlight = () => {
      clearTimer();
      logo.classList.remove("no-filter");
    };

    const scheduleReset = (delay = 1200) => {
      clearTimer();
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

    logo.addEventListener(
      "touchcancel",
      () => {
        clearHighlight();
      },
      { passive: true }
    );

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

    logo.addEventListener("pointercancel", () => {
      clearHighlight();
    });

    logo.dataset.listenersBound = "true";
  };

  const bindLogoInteractions = () => {
    container
      .querySelectorAll(".partner-logo")
      .forEach(registerLogoInteractions);
  };

  const awaitImage = image => {
    if (!image) {
      return Promise.resolve();
    }

    if (image.complete && image.naturalWidth > 0) {
      return Promise.resolve();
    }

    return new Promise(resolve => {
      let settled = false;
      let fallbackId = null;

      const cleanup = () => {
        image.removeEventListener("load", onLoad);
        image.removeEventListener("error", onError);
      };

      const finalize = () => {
        if (settled) {
          return;
        }
        settled = true;
        if (fallbackId !== null) {
          window.clearTimeout(fallbackId);
          fallbackId = null;
        }
        cleanup();
        resolve();
      };

      const runDecode = () => {
        if (typeof image.decode === "function") {
          image
            .decode()
            .catch(() => undefined)
            .finally(finalize);
          return;
        }

        finalize();
      };

      const onLoad = () => {
        runDecode();
      };

      const onError = finalize;

      image.addEventListener("load", onLoad, { once: true });
      image.addEventListener("error", onError, { once: true });

      fallbackId = window.setTimeout(() => {
        finalize();
      }, 2500);
    });
  };

  const ensureLogosReady = () => {
    if (logosReadyPromise) {
      return logosReadyPromise;
    }

    const logos = Array.from(container.querySelectorAll(".partner-logo"));
    if (!logos.length) {
      return Promise.resolve();
    }

    logosReadyPromise = Promise.all(logos.map(awaitImage)).finally(() => {
      logosReadyPromise = null;
    });

    return logosReadyPromise;
  };

  const hasViewportWidth = () => viewport.clientWidth > 0;

  const ensureViewportReady = () => {
    if (hasViewportWidth()) {
      return Promise.resolve();
    }

    if (viewportReadyPromise) {
      return viewportReadyPromise;
    }

    viewportReadyPromise = new Promise(resolve => {
      let resolved = false;
      const cleanups = [];

      const finish = () => {
        if (resolved) {
          return;
        }
        resolved = true;
        while (cleanups.length) {
          const cleanup = cleanups.pop();
          if (typeof cleanup === "function") {
            cleanup();
          }
        }
        resolve();
      };

      const checkViewport = () => {
        if (hasViewportWidth()) {
          finish();
        }
      };

      if (typeof ResizeObserver === "function") {
        const observer = new ResizeObserver(() => {
          checkViewport();
        });
        observer.observe(viewport);
        cleanups.push(() => observer.disconnect());
      } else {
        const onResize = () => {
          checkViewport();
        };
        window.addEventListener("resize", onResize);
        cleanups.push(() => window.removeEventListener("resize", onResize));
      }

      if (typeof window.requestAnimationFrame === "function") {
        const rafId = window.requestAnimationFrame(() => {
          checkViewport();
        });
        cleanups.push(() => window.cancelAnimationFrame(rafId));
      } else {
        const timeoutId = window.setTimeout(() => {
          checkViewport();
        }, 0);
        cleanups.push(() => window.clearTimeout(timeoutId));
      }

      const onLoad = () => {
        checkViewport();
      };
      window.addEventListener("load", onLoad, { once: true });
      cleanups.push(() => window.removeEventListener("load", onLoad));

      const fallbackId = window.setTimeout(() => {
        console.warn(
          "Partners slider viewport is still zero width after 4s; continuing with current layout."
        );
        finish();
      }, 4000);
      cleanups.push(() => window.clearTimeout(fallbackId));
    }).finally(() => {
      viewportReadyPromise = null;
    });

    return viewportReadyPromise;
  };

  const isEmblaReady = () =>
    typeof window.EmblaCarousel === "function" &&
    typeof window.EmblaCarouselAutoplay === "function";

  const ensureEmbla = () => {
    if (isEmblaReady()) {
      return Promise.resolve();
    }

    if (emblaReadyPromise) {
      return emblaReadyPromise;
    }

    emblaReadyPromise = new Promise(resolve => {
      let resolved = false;
      const cleanups = [];

      const finish = () => {
        if (resolved) {
          return;
        }
        resolved = true;
        while (cleanups.length) {
          const cleanup = cleanups.pop();
          if (typeof cleanup === "function") {
            cleanup();
          }
        }
        resolve();
      };

      const checkReady = () => {
        if (isEmblaReady()) {
          finish();
        }
      };

      if (typeof window.requestAnimationFrame === "function") {
        const rafId = window.requestAnimationFrame(() => {
          checkReady();
        });
        cleanups.push(() => window.cancelAnimationFrame(rafId));
      } else {
        const timeoutId = window.setTimeout(() => {
          checkReady();
        }, 0);
        cleanups.push(() => window.clearTimeout(timeoutId));
      }

      const timeoutId = window.setTimeout(() => {
        console.warn(
          "Embla Carousel scripts did not finish loading within 8s; initializing with current availability."
        );
        finish();
      }, 8000);
      cleanups.push(() => window.clearTimeout(timeoutId));

      const onWindowLoad = () => {
        checkReady();
      };
      window.addEventListener("load", onWindowLoad);
      cleanups.push(() => window.removeEventListener("load", onWindowLoad));

      const scriptNodes = document.querySelectorAll(
        'script[src*="embla-carousel"]'
      );
      scriptNodes.forEach(script => {
        const onScriptLoad = () => {
          checkReady();
        };
        script.addEventListener("load", onScriptLoad, { once: true });
        cleanups.push(() => script.removeEventListener("load", onScriptLoad));
      });
    }).finally(() => {
      emblaReadyPromise = null;
    });

    return emblaReadyPromise;
  };

  const getAutoplayPlugin = () => {
    if (!autoplayPlugin && typeof window.EmblaCarouselAutoplay === "function") {
      autoplayPlugin = window.EmblaCarouselAutoplay({
        delay: 2200,
        playOnInit: false,
        stopOnInteraction: false,
        stopOnMouseEnter: false
      });
    }

    return autoplayPlugin;
  };

  const initEmbla = token => {
    if (!isEmblaReady()) {
      return;
    }

    const autoplay = getAutoplayPlugin();

    if (!emblaInstance) {
      emblaInstance = window.EmblaCarousel(
        viewport,
        {
          loop: true,
          align: "center",
          skipSnaps: false,
          containScroll: false
        },
        autoplay ? [autoplay] : undefined
      );
    } else {
      emblaInstance.reInit();
    }

    viewport.classList.add(viewportReadyClass);
    viewport.scrollLeft = 0;

    if (emblaInstance && typeof emblaInstance.scrollTo === "function") {
      emblaInstance.scrollTo(0, true);
    }

    if (autoplayStartTimeout !== null) {
      window.clearTimeout(autoplayStartTimeout);
      autoplayStartTimeout = null;
    }

    if (autoplay) {
      if (typeof autoplay.reset === "function") {
        autoplay.reset();
      }

      autoplayStartTimeout = window.setTimeout(() => {
        if (token !== mobileInitToken) {
          return;
        }
        if (typeof autoplay.play === "function") {
          autoplay.play();
        }
      }, autoplayStartDelay);
    }
  };

  const destroyEmbla = () => {
    if (!emblaInstance) {
      return;
    }

    viewport.classList.remove(viewportReadyClass);
    viewport.scrollLeft = 0;

    if (autoplayStartTimeout !== null) {
      window.clearTimeout(autoplayStartTimeout);
      autoplayStartTimeout = null;
    }

    if (autoplayPlugin) {
      if (typeof autoplayPlugin.stop === "function") {
        autoplayPlugin.stop();
      }
      if (typeof autoplayPlugin.reset === "function") {
        autoplayPlugin.reset();
      }
    }

    emblaInstance.destroy();
    emblaInstance = null;
    autoplayPlugin = null;
  };

  const attachDesktopDrag = () => {
    if (desktopDragHandlers) {
      return;
    }

    let isDragging = false;
    let startX = 0;
    let scrollStart = 0;

    const stopDrag = () => {
      isDragging = false;
      viewport.classList.remove("dragging");
    };

    const onMouseDown = event => {
      isDragging = true;
      startX = event.pageX - viewport.offsetLeft;
      scrollStart = viewport.scrollLeft;
      viewport.classList.add("dragging");
    };

    const onMouseMove = event => {
      if (!isDragging) {
        return;
      }

      event.preventDefault();
      const currentX = event.pageX - viewport.offsetLeft;
      const delta = currentX - startX;
      viewport.scrollLeft = scrollStart - delta;
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
    mobileInitToken += 1;
    const token = mobileInitToken;

    destroyEmbla();
    viewport.scrollLeft = 0;

    if (desktopDuplicated) {
      container.innerHTML = originalMarkup;
      desktopDuplicated = false;
    }

    logosReadyPromise = null;

    detachDesktopDrag();
    bindLogoInteractions();

    Promise.all([ensureEmbla(), ensureViewportReady(), ensureLogosReady()])
      .then(() => {
        if (token !== mobileInitToken || !mobileQuery.matches) {
          return;
        }

        const launch = () => {
          if (token !== mobileInitToken || !mobileQuery.matches) {
            return;
          }
          initEmbla(token);
        };

        if (typeof window.requestAnimationFrame === "function") {
          window.requestAnimationFrame(launch);
        } else {
          window.setTimeout(launch, 0);
        }
      })
      .catch(error => {
        console.error(error);
      });
  };

  const setupDesktop = () => {
    mobileInitToken += 1;

    destroyEmbla();

    if (!desktopDuplicated) {
      container.innerHTML = originalMarkup + originalMarkup;
      desktopDuplicated = true;
    }

    logosReadyPromise = null;

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
    if (event.matches) {
      setupMobile();
    } else {
      setupDesktop();
    }
  };

  if (typeof mobileQuery.addEventListener === "function") {
    mobileQuery.addEventListener("change", mediaListener);
  } else if (typeof mobileQuery.addListener === "function") {
    mobileQuery.addListener(mediaListener);
  }

  window.addEventListener("pageshow", event => {
    if (event.persisted) {
      applyLayout(mobileQuery.matches);
    }
  });

  bindLogoInteractions();
});
