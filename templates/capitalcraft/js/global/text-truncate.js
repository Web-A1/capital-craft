/**
 * Модуль для управления обрезанием и раскрытием текста описаний товаров
 * Работает только в мобильной версии
 */
export function initTextTruncate() {
  console.log('=== ИНИЦИАЛИЗАЦИЯ TEXT TRUNCATE ===');
  console.log('Ширина окна:', window.innerWidth);
  
  // Проверяем, что мы на мобильном устройстве
  if (window.innerWidth > 767) {
    console.log('Не мобильное устройство, выход');
    return;
  }

  console.log('Мобильное устройство, продолжаем');
  const descriptions = document.querySelectorAll('.products__item-description');
  console.log('Найдено элементов описания:', descriptions.length);
  
  if (descriptions.length === 0) {
    console.log('Элементы описания не найдены');
    return;
  }

  descriptions.forEach((description, index) => {
    console.log(`Обрабатываем элемент ${index + 1}:`, description);
    
    const textElement = description.querySelector('p');
    
    if (!textElement) {
      console.log(`Элемент ${index + 1}: параграф не найден`);
      return;
    }

    const originalText = textElement.textContent.trim();
    // Получаем line-height из параграфа
    const lineHeight = parseFloat(getComputedStyle(textElement).lineHeight);
    const maxHeight = lineHeight * 3; // 3 строки

    console.log(`Элемент ${index + 1}:`, {
      textLength: originalText.length,
      lineHeight: lineHeight,
      maxHeight: maxHeight,
      scrollHeight: textElement.scrollHeight
    });

    // Дополнительная отладка CSS
    console.log(`Элемент ${index + 1} CSS свойства:`, {
      lineHeight: getComputedStyle(textElement).lineHeight,
      fontSize: getComputedStyle(textElement).fontSize,
      overflow: getComputedStyle(textElement).overflow,
      display: getComputedStyle(textElement).display
    });

    // Проверяем, нужно ли обрезать текст
    if (textElement.scrollHeight <= maxHeight) {
      console.log(`Элемент ${index + 1}: обрезание не нужно`);
      return; // Текст помещается в 3 строки, обрезание не нужно
    }

    console.log(`Элемент ${index + 1}: настраиваем обрезание текста`);

    // Обрезаем текст по целым словам с учетом " ... полностью"
    let truncatedText = originalText;
    const extraText = ' ... полностью';
    textElement.textContent = truncatedText + extraText;
    while (textElement.scrollHeight > maxHeight && /\s/.test(truncatedText)) {
      truncatedText = truncatedText.replace(/\s*\S+\s*$/, '').trim();
      textElement.textContent = truncatedText + extraText;
    }
    textElement.textContent = truncatedText;

    // Сохраняем исходный и обрезанный текст в dataset
    textElement.dataset.originalText = originalText;
    textElement.dataset.truncatedText = truncatedText;

    // Добавляем класс для обрезанного состояния к ПАРАГРАФУ
    textElement.classList.add('truncated');
    console.log(`Элемент ${index + 1}: добавлен класс truncated к параграфу`);

    // Делаем параграф кликабельным
    textElement.style.cursor = 'pointer';
    textElement.setAttribute('title', 'Кликните, чтобы развернуть текст');
    
    // Обработчик клика по параграфу
    textElement.addEventListener('click', function(e) {
      console.log('=== КЛИК ПО ТЕКСТУ ===');
      console.log('Событие клика:', e);
      console.log('Клик по тексту, текущие классы параграфа:', textElement.classList.toString());
      
      if (textElement.classList.contains('truncated')) {
        // Разворачиваем текст
        textElement.classList.remove('truncated');
        textElement.classList.add('expanded');
        textElement.textContent = textElement.dataset.originalText || '';
        textElement.setAttribute('title', 'Кликните, чтобы свернуть текст');
        console.log('Добавлен класс expanded, убран truncated');
        console.log('Новые классы параграфа:', textElement.classList.toString());
        console.log('Текущие CSS свойства параграфа:', {
          overflow: getComputedStyle(textElement).overflow,
          display: getComputedStyle(textElement).display,
          maxHeight: getComputedStyle(textElement).maxHeight,
          webkitLineClamp: getComputedStyle(textElement).webkitLineClamp
        });
      } else {
        // Сворачиваем текст
        textElement.classList.remove('expanded');
        textElement.classList.add('truncated');
        textElement.textContent = textElement.dataset.truncatedText || '';
        textElement.setAttribute('title', 'Кликните, чтобы развернуть текст');
        console.log('Добавлен класс truncated, убран expanded');
        console.log('Новые классы параграфа:', textElement.classList.toString());
      }
    });
    
    // Проверяем, что обработчик привязался
    console.log(`Элемент ${index + 1}: обработчик клика привязан к параграфу`);
  });
  
  console.log('=== ИНИЦИАЛИЗАЦИЯ ЗАВЕРШЕНА ===');
}

// Функция для очистки состояния при изменении размера окна
function resetTextTruncate() {
  const descriptions = document.querySelectorAll('.products__item-description');

  descriptions.forEach(description => {
    // Убираем классы с параграфа
    const textElement = description.querySelector('p');
    if (textElement) {
      textElement.classList.remove('truncated', 'expanded');
      textElement.style.cursor = '';
      textElement.removeAttribute('title');
      if (textElement.dataset.originalText) {
        textElement.textContent = textElement.dataset.originalText;
        delete textElement.dataset.originalText;
        delete textElement.dataset.truncatedText;
      }
    }
  });
}

// Обработчик изменения размера окна
let resizeTimeout;
window.addEventListener('resize', () => {
  clearTimeout(resizeTimeout);
  resizeTimeout = setTimeout(() => {
    resetTextTruncate();
    initTextTruncate();
  }, 250);
});

// Экспортируем функцию для использования в основном файле
export default initTextTruncate;
