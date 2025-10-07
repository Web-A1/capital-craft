"use strict";

export const initScrollTop = () => {
  const btn = document.querySelector(".scroll-top");
  if (!btn) return;

  const toggleVisibility = () => {
    if (window.scrollY > 300) {
      btn.classList.add("visible");
    } else {
      btn.classList.remove("visible");
    }
  };

  btn.addEventListener("click", e => {
    e.preventDefault();
    const startY = window.scrollY;

    if (!startY) {
      return;
    }

    const duration = Math.max(120, Math.min(220, startY / 5));

    if (typeof window.requestAnimationFrame !== "function") {
      window.scrollTo({ top: 0, behavior: "smooth" });
      return;
    }

    const startTime = performance.now();

    const step = now => {
      const progress = Math.min((now - startTime) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 2);
      window.scrollTo(0, Math.round(startY * (1 - eased)));

      if (progress < 1) {
        requestAnimationFrame(step);
      }
    };

    requestAnimationFrame(step);
  });

  window.addEventListener("scroll", toggleVisibility, { passive: true });
  toggleVisibility();
};
