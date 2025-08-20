/**
 * Модуль для управления обрезанием и раскрытием текста описаний товаров
 * Работает только в мобильной версии
 */
export function initTextTruncate() {
  // Проверяем, что мы на мобильном устройстве
  if (window.innerWidth > 767) {
    return;
  }

  const descriptions = document.querySelectorAll('.products__item-description');
  
  if (descriptions.length === 0) {
    return;
  }

  descriptions.forEach((description, index) => {
    const textElement = description.querySelector('p');
    
    if (!textElement) {
      return;
    }

    const originalText = textElement.textContent;
    const lineHeight = parseFloat(getComputedStyle(textElement).lineHeight);
    const maxHeight = lineHeight * 3; // 3 строки
    
    // Проверяем, нужно ли обрезать текст
    if (textElement.scrollHeight <= maxHeight) {
      return; // Текст помещается в 3 строки, обрезание не нужно
    }

    // Создаем кнопку "Читать далее"
    const readMoreBtn = document.createElement('span');
    readMoreBtn.className = 'read-more-btn';
    readMoreBtn.textContent = 'Читать далее';
    
    // Добавляем кнопку после текста
    description.appendChild(readMoreBtn);
    
    // Добавляем класс для обрезанного состояния
    description.classList.add('truncated');
    
    // Обработчик клика по кнопке
    readMoreBtn.addEventListener('click', function() {
      if (description.classList.contains('truncated')) {
        // Разворачиваем текст
        description.classList.remove('truncated');
        description.classList.add('expanded');
        readMoreBtn.textContent = 'Свернуть';
      } else {
        // Сворачиваем текст
        description.classList.remove('expanded');
        description.classList.add('truncated');
        readMoreBtn.textContent = 'Читать далее';
      }
    });
  });
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