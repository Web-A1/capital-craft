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

    const originalText = textElement.textContent;
    const lineHeight = parseFloat(getComputedStyle(textElement).lineHeight);
    const maxHeight = lineHeight * 3; // 3 строки
    
    console.log(`Элемент ${index + 1}:`, {
      textLength: originalText.length,
      lineHeight: lineHeight,
      maxHeight: maxHeight,
      scrollHeight: textElement.scrollHeight
    });
    
    // Проверяем, нужно ли обрезать текст
    if (textElement.scrollHeight <= maxHeight) {
      console.log(`Элемент ${index + 1}: обрезание не нужно`);
      return; // Текст помещается в 3 строки, обрезание не нужно
    }

    console.log(`Элемент ${index + 1}: создаем кнопку "Читать далее"`);

    // Создаем кнопку "Читать далее"
    const readMoreBtn = document.createElement('span');
    readMoreBtn.className = 'read-more-btn';
    readMoreBtn.textContent = 'Читать далее';
    
    // Добавляем кнопку после текста
    description.appendChild(readMoreBtn);
    
    // Добавляем класс для обрезанного состояния
    description.classList.add('truncated');
    console.log(`Элемент ${index + 1}: добавлен класс truncated`);
    
    // Обработчик клика по кнопке
    readMoreBtn.addEventListener('click', function() {
      console.log('=== КЛИК ПО КНОПКЕ ===');
      console.log('Клик по кнопке, текущие классы:', description.classList.toString());
      
      if (description.classList.contains('truncated')) {
        // Разворачиваем текст
        description.classList.remove('truncated');
        description.classList.add('expanded');
        readMoreBtn.textContent = 'Свернуть';
        console.log('Добавлен класс expanded, убран truncated');
        console.log('Новые классы:', description.classList.toString());
        console.log('Текущие CSS свойства:', {
          overflow: getComputedStyle(description).overflow,
          display: getComputedStyle(description).display,
          maxHeight: getComputedStyle(description).maxHeight,
          webkitLineClamp: getComputedStyle(description).webkitLineClamp
        });
      } else {
        // Сворачиваем текст
        description.classList.remove('expanded');
        description.classList.add('truncated');
        readMoreBtn.textContent = 'Читать далее';
        console.log('Добавлен класс truncated, убран expanded');
        console.log('Новые классы:', description.classList.toString());
      }
    });
  });
  
  console.log('=== ИНИЦИАЛИЗАЦИЯ ЗАВЕРШЕНА ===');
}

// Функция для очистки состояния при изменении размера окна
function resetTextTruncate() {
  const descriptions = document.querySelectorAll('.products__item-description');
  
  descriptions.forEach(description => {
    // Удаляем все добавленные элементы и классы
    const readMoreBtn = description.querySelector('.read-more-btn');
    if (readMoreBtn) {
      readMoreBtn.remove();
    }
    
    description.classList.remove('truncated', 'expanded');
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

//тест1
