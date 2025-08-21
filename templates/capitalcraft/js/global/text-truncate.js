/**
 * Обрезает описания товаров на мобильных устройствах до трёх строк
 * с добавлением "… полностью" и переключением полного текста по клику.
 */
export function initTextTruncate() {
  if (window.innerWidth > 767) return;

  const blocks = document.querySelectorAll('.products__item-description');

  blocks.forEach(block => {
    const p = block.querySelector('p');
    if (!p) return;

    const original = p.textContent.trim();

    const computed = getComputedStyle(p);
    let lineHeight = parseFloat(computed.lineHeight);
    if (isNaN(lineHeight)) {
      lineHeight = parseFloat(getComputedStyle(block).lineHeight);
    }
    if (isNaN(lineHeight) || lineHeight === 0) {
      lineHeight = parseFloat(computed.fontSize) * 1.2;
    }

    const maxHeight = lineHeight * 3;
    const suffix = ' … полностью';

    let truncated = original;
    const applyContent = () => {
      p.innerHTML = truncated + `<span class="products__read-more">${suffix}</span>`;
    };

    applyContent();
    while (p.scrollHeight > maxHeight && /\s/.test(truncated)) {
      truncated = truncated.replace(/\s*\S+\s*$/, '').trim();
      applyContent();
    }

    if (truncated === original) {
      p.textContent = original;
      return;
    }

    p.dataset.originalText = original;
    p.dataset.truncatedText = truncated;
    p.classList.add('truncated');
    block.style.cursor = 'pointer';

    block.addEventListener('click', () => {
      const collapsed = p.classList.contains('truncated');
      if (collapsed) {
        p.textContent = p.dataset.originalText;
      } else {
        p.innerHTML = p.dataset.truncatedText + `<span class="products__read-more">${suffix}</span>`;
      }
      p.classList.toggle('truncated', !collapsed);
      p.classList.toggle('expanded', collapsed);
    });
  });
}

function resetTextTruncate() {
  document.querySelectorAll('.products__item-description').forEach(block => {
    const p = block.querySelector('p');
    if (!p) return;
    p.classList.remove('truncated', 'expanded');
    p.textContent = p.dataset.originalText || p.textContent;
    delete p.dataset.originalText;
    delete p.dataset.truncatedText;
    block.style.cursor = '';
  });
}

let resizeTimeout;
window.addEventListener('resize', () => {
  clearTimeout(resizeTimeout);
  resizeTimeout = setTimeout(() => {
    resetTextTruncate();
    initTextTruncate();
  }, 250);
});

export default initTextTruncate;

