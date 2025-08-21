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

    console.log(`Элемент ${index + 1}: создаем кнопку "Читать далее"`);

    // Создаем кнопку "Читать далее"
    const readMoreBtn = document.createElement('span');
    readMoreBtn.className = 'read-more-btn';
    readMoreBtn.textContent = 'Читать далее';
    
    // Вставляем кнопку ПОСЛЕ блока описания (вне области overflow: hidden)
    description.insertAdjacentElement('afterend', readMoreBtn);
    
    // Проверяем, что кнопка создалась
    console.log(`Элемент ${index + 1}: кнопка создана:`, readMoreBtn);
    console.log(`Элемент ${index + 1}: кнопка в DOM:`, description.parentNode.querySelector('.read-more-btn'));
    
    // Добавляем класс для обрезанного состояния к ПАРАГРАФУ, а не к контейнеру
    textElement.classList.add('truncated');
    console.log(`Элемент ${index + 1}: добавлен класс truncated к параграфу`);
    
    // Обработчик клика по кнопке
    readMoreBtn.addEventListener('click', function(e) {
      console.log('=== КЛИК ПО КНОПКЕ ===');
      console.log('Событие клика:', e);
      console.log('Клик по кнопке, текущие классы параграфа:', textElement.classList.toString());
      
      if (textElement.classList.contains('truncated')) {
        // Разворачиваем текст
        textElement.classList.remove('truncated');
        textElement.classList.add('expanded');
        readMoreBtn.textContent = 'Свернуть';
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
        readMoreBtn.textContent = 'Читать далее';
        console.log('Добавлен класс truncated, убран expanded');
        console.log('Новые классы параграфа:', textElement.classList.toString());
      }
    });
    
    // Проверяем, что обработчик привязался
    console.log(`Элемент ${index + 1}: обработчик клика привязан`);
  });
  
  console.log('=== ИНИЦИАЛИЗАЦИЯ ЗАВЕРШЕНА ===');
}

// Функция для очистки состояния при изменении размера окна
function resetTextTruncate() {
  const descriptions = document.querySelectorAll('.products__item-description');
  
  descriptions.forEach(description => {
    // Удаляем все добавленные элементы и классы
    const readMoreBtn = description.parentNode.querySelector('.read-more-btn');
    if (readMoreBtn) {
      readMoreBtn.remove();
    }
    
    // Убираем классы с параграфа
    const textElement = description.querySelector('p');
    if (textElement) {
      textElement.classList.remove('truncated', 'expanded');
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

//тест1
