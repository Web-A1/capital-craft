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

    const prefersReducedMotion =
      typeof window.matchMedia === "function" &&
      window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    if (prefersReducedMotion) {
      window.scrollTo({ top: 0, behavior: "auto" });
      return;
    }

    const duration = Math.max(220, Math.min(450, startY / 3));

    if (typeof window.requestAnimationFrame !== "function") {
      window.scrollTo({ top: 0, behavior: "smooth" });
      return;
    }

    const startTime = performance.now();

    const easeOutCubic = t => 1 - Math.pow(1 - t, 3);

    const step = now => {
      const progress = Math.min((now - startTime) / duration, 1);
      const eased = easeOutCubic(progress);
      window.scrollTo({
        top: Math.round(startY * (1 - eased)),
        behavior: "auto"
      });

      if (progress < 1) {
        requestAnimationFrame(step);
      }
    };

    requestAnimationFrame(step);
  });

  window.addEventListener("scroll", toggleVisibility, { passive: true });
  toggleVisibility();
};
