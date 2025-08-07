// Partners Section Auto-Scrolling Functionality
document.addEventListener('DOMContentLoaded', function () {
  const partnersSection = document.querySelector('.partners-section');
  if (!partnersSection) return;

  const logosTrack = partnersSection.querySelector('.logos-track');
  const logoItems = partnersSection.querySelectorAll('.logo-item');

  if (!logosTrack || logoItems.length === 0) return;

  let currentIndex = 0;
  let isAnimating = false;
  const totalLogos = logoItems.length;
  const visibleLogos = getVisibleLogosCount();
  let autoScrollInterval;

  // Function to determine how many logos to show based on screen size
  function getVisibleLogosCount() {
    if (window.innerWidth <= 480) {
      return 1; // Mobile: 1 logo
    } else if (window.innerWidth <= 768) {
      return 3; // Tablet: 3 logos
    } else {
      return 3; // Desktop: 3 logos
    }
  }

  // Function to update the transform position
  function updatePosition(animate = true) {
    if (isAnimating && animate) return;

    const visibleCount = getVisibleLogosCount();
    const movePercentage = (100 / totalLogos) * currentIndex;

    if (animate) {
      isAnimating = true;
      logosTrack.style.transition = 'transform 1s ease-in-out';
    } else {
      logosTrack.style.transition = 'none';
    }

    logosTrack.style.transform = `translateX(-${movePercentage}%)`;

    if (animate) {
      setTimeout(() => {
        isAnimating = false;
      }, 1000);
    }
  }

  // Function to move to next logo
  function nextLogo() {
    const visibleCount = getVisibleLogosCount();

    // Calculate how many steps we can take before needing to reset
    const maxIndex = totalLogos - visibleCount;

    currentIndex++;

    // If we've reached the end, reset to beginning
    if (currentIndex > maxIndex) {
      currentIndex = 0;
    }

    updatePosition(true);
  }

  // Function to start auto-scrolling
  function startAutoScroll() {
    stopAutoScroll(); // Clear any existing interval
    autoScrollInterval = setInterval(nextLogo, 3000); // 3 seconds
  }

  // Function to stop auto-scrolling
  function stopAutoScroll() {
    if (autoScrollInterval) {
      clearInterval(autoScrollInterval);
      autoScrollInterval = null;
    }
  }

  // Handle window resize
  function handleResize() {
    // Recalculate position without animation
    updatePosition(false);

    // Restart auto-scroll with new visible count
    startAutoScroll();
  }

  // Pause auto-scroll on hover (desktop only)
  function handleMouseEnter() {
    if (window.innerWidth > 768) {
      stopAutoScroll();
    }
  }

  function handleMouseLeave() {
    if (window.innerWidth > 768) {
      startAutoScroll();
    }
  }

  // Touch events for mobile
  let startX = 0;
  let startY = 0;
  let isDragging = false;

  function handleTouchStart(e) {
    startX = e.touches[0].clientX;
    startY = e.touches[0].clientY;
    isDragging = true;
    stopAutoScroll();
  }

  function handleTouchMove(e) {
    if (!isDragging) return;

    const currentX = e.touches[0].clientX;
    const currentY = e.touches[0].clientY;
    const diffX = startX - currentX;
    const diffY = startY - currentY;

    // If horizontal movement is greater than vertical, prevent vertical scroll
    if (Math.abs(diffX) > Math.abs(diffY)) {
      e.preventDefault();
    }
  }

  function handleTouchEnd(e) {
    if (!isDragging) return;

    const endX = e.changedTouches[0].clientX;
    const diffX = startX - endX;

    // Minimum swipe distance
    if (Math.abs(diffX) > 50) {
      if (diffX > 0) {
        // Swipe left - next logo
        nextLogo();
      } else {
        // Swipe right - previous logo
        const visibleCount = getVisibleLogosCount();
        const maxIndex = totalLogos - visibleCount;

        currentIndex--;
        if (currentIndex < 0) {
          currentIndex = maxIndex;
        }
        updatePosition(true);
      }
    }

    isDragging = false;

    // Restart auto-scroll after touch interaction
    setTimeout(() => {
      startAutoScroll();
    }, 2000);
  }

  // Event listeners
  window.addEventListener('resize', handleResize);
  partnersSection.addEventListener('mouseenter', handleMouseEnter);
  partnersSection.addEventListener('mouseleave', handleMouseLeave);

  // Touch events
  logosTrack.addEventListener('touchstart', handleTouchStart, {
    passive: false,
  });
  logosTrack.addEventListener('touchmove', handleTouchMove, { passive: false });
  logosTrack.addEventListener('touchend', handleTouchEnd);

  // Intersection Observer to pause/resume when section is not visible
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            startAutoScroll();
          } else {
            stopAutoScroll();
          }
        });
      },
      {
        threshold: 0.5,
      }
    );

    observer.observe(partnersSection);
  } else {
    // Fallback for browsers without IntersectionObserver
    startAutoScroll();
  }

  // Initialize position
  updatePosition(false);

  // Start auto-scrolling
  startAutoScroll();
});
