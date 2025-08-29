/**
 * Обрезает описания товаров на мобильных устройствах до трёх/четырёх строк
 * и позволяет разворачивать/сворачивать по клику.
 */

// Храним обработчики, чтобы не дублировать слушатели и уметь их снимать
const blockHandlers = new WeakMap();

export function initTextTruncate() {
  // Если DOM ещё не готов — инициализируем после загрузки
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTextTruncate, { once: true });
    return;
  }

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

    const maxHeight = lineHeight * 4;
    const suffix = 'read more';

    let truncated = original;
    const applyContent = () => {
      p.innerHTML = truncated + ` … <span class="products__read-more">${suffix}</span>`;
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

    // Не добавляем обработчик повторно при реинициализации (resize и т.п.)
    if (!blockHandlers.has(block)) {
      const handler = () => {
        const collapsed = p.classList.contains('truncated');
        if (collapsed) {
          p.textContent = p.dataset.originalText;
        } else {
          p.innerHTML = p.dataset.truncatedText + ` … <span class="products__read-more">${suffix}</span>`;
        }
        p.classList.toggle('truncated', !collapsed);
        p.classList.toggle('expanded', collapsed);
      };
      block.addEventListener('click', handler);
      blockHandlers.set(block, handler);
    }
  });
}

function resetTextTruncate() {
  document.querySelectorAll('.products__item-description').forEach(block => {
    const p = block.querySelector('p');
    if (!p) return;

    // Снимаем обработчик клика, если он был навешан
    const handler = blockHandlers.get(block);
    if (handler) {
      block.removeEventListener('click', handler);
      blockHandlers.delete(block);
    }

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
