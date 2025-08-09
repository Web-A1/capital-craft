export function initStickyHeader() {
  const header = document.querySelector('.site-header');
  if (!header) return;

  const mediaQuery = window.matchMedia('(max-width: 767px)');
  let lastScrollY = window.scrollY;

  const onScroll = () => {
    const currentScrollY = window.scrollY;

    if (!mediaQuery.matches) {
      header.classList.remove('site-header--hidden');
      lastScrollY = currentScrollY;
      return;
    }

    if (currentScrollY > lastScrollY && currentScrollY > header.offsetHeight) {
      header.classList.add('site-header--hidden');
    } else {
      header.classList.remove('site-header--hidden');
    }

    lastScrollY = currentScrollY;
  };

  window.addEventListener('scroll', onScroll);
  mediaQuery.addEventListener('change', () => {
    header.classList.remove('site-header--hidden');
    lastScrollY = window.scrollY;
  });
}
