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
    window.scrollTo(0, 0);
  });

  window.addEventListener("scroll", toggleVisibility, { passive: true });
  toggleVisibility();
};
