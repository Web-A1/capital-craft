#!/bin/bash

echo "👀 Запускаю watch режим для LESS файлов..."
echo "📁 Отслеживаю изменения в: templates/capitalcraft/less/"
echo "🔄 Автоматическая компиляция при изменении файлов"
echo "🚀 Нажмите Ctrl+C для остановки"
echo ""

# Функция для компиляции всех LESS файлов
compile_less() {
    echo "🔄 Компилирую LESS файлы..."
    npm run less:all
    if [ $? -eq 0 ]; then
        echo "✅ Компиляция завершена!"
    else
        echo "❌ Ошибка компиляции!"
    fi
    echo ""
}

# Инициализируем компиляцию
compile_less

# Отслеживаем изменения в LESS файлах
while true; do
    # Ждем изменения в LESS файлах
    inotifywait -r -e modify,create,delete templates/capitalcraft/less/ 2>/dev/null || \
    fswatch -r templates/capitalcraft/less/ 2>/dev/null || \
    (sleep 1 && find templates/capitalcraft/less/ -name "*.less" -newer templates/capitalcraft/less/.last_check 2>/dev/null && touch templates/capitalcraft/less/.last_check)
    
    # Если файл изменился, компилируем
    if [ $? -eq 0 ]; then
        compile_less
    fi
    
    # Небольшая задержка
    sleep 0.5
done
