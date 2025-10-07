"use strict";

export const initScrollTop = () => {
  const btn = document.querySelector(".scroll-top");
  if (!btn) return;

<<<<<<< ours
  let animationFrameId = null;
  const prefersReducedMotion = window.matchMedia?.(
    "(prefers-reduced-motion: reduce)"
  ).matches;

  const scrollToTopFast = (duration = 250) => {
    if (prefersReducedMotion) {
      window.scrollTo(0, 0);
      return;
    }

    const startY = window.scrollY || document.documentElement.scrollTop;
    if (startY === 0 || duration <= 0) {
      window.scrollTo(0, 0);
      return;
    }

    if (animationFrameId) {
      cancelAnimationFrame(animationFrameId);
    }

    const startTime = performance.now();
    const easeOutCubic = t => 1 - Math.pow(1 - t, 3);

    const step = currentTime => {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);
      const easedProgress = easeOutCubic(progress);
      const nextY = startY * (1 - easedProgress);

      window.scrollTo(0, nextY);

      if (progress < 1) {
        animationFrameId = requestAnimationFrame(step);
      } else {
        animationFrameId = null;
        window.scrollTo(0, 0);
      }
    };

    animationFrameId = requestAnimationFrame(step);
  };

=======
>>>>>>> theirs
  const toggleVisibility = () => {
    if (window.scrollY > 300) {
      btn.classList.add("visible");
    } else {
      btn.classList.remove("visible");
    }
  };

  btn.addEventListener("click", e => {
    e.preventDefault();
    window.scrollTo(0, 0);
  });

  window.addEventListener("scroll", toggleVisibility, { passive: true });
  toggleVisibility();
};
