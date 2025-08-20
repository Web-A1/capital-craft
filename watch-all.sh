#!/bin/bash

# Универсальный мониторинг файлов проекта
# Запуск: ./watch-all.sh (работает в фоне)

echo "🚀 Запуск универсального мониторинга файлов..."
echo "📁 Отслеживаю изменения в проекте..."
echo "🔄 Логика обработки:"
echo "   • LESS файлы → компиляция + commit + push"
echo "   • JS файлы → пересборка + commit + push"
echo "   • PHP файлы → только commit + push"
echo "⏹️  Для остановки: Ctrl+C"
echo ""

# Проверяем, что мы в dev ветке
current_branch=$(git branch --show-current)
if [ "$current_branch" != "dev" ]; then
    echo "❌ Ошибка: вы не в ветке dev (текущая ветка: $current_branch)"
    echo "Переключитесь на dev: git checkout dev"
    exit 1
fi

# Функция для обработки LESS файлов
handle_less() {
    local file="$1"
    echo "🎨 Изменен LESS файл: $file"
    echo "🔄 Компилирую LESS..."
    
    npm run less:all
    
    if [ $? -eq 0 ]; then
        echo "✅ LESS компиляция завершена!"
        commit_and_push "LESS compilation"
    else
        echo "❌ Ошибка компиляции LESS!"
    fi
}

# Функция для обработки JS файлов
handle_js() {
    local file="$1"
    echo "⚡ Изменен JS файл: $file"
    echo "🔄 Пересобираю JavaScript..."
    
    npm run js:build
    
    if [ $? -eq 0 ]; then
        echo "✅ JavaScript пересборка завершена!"
        commit_and_push "JS rebuild"
    else
        echo "❌ Ошибка пересборки JavaScript!"
    fi
}

# Функция для обработки PHP файлов
handle_php() {
    local file="$1"
    echo "🐘 Изменен PHP файл: $file"
    echo "📝 Только коммит и push..."
    
    commit_and_push "PHP update"
}

# Функция для коммита и push
commit_and_push() {
    local message="$1"
    
    # Проверяем, есть ли изменения для коммита
    if git diff-index --quiet HEAD --; then
        echo "📝 Нет изменений для коммита"
    else
        echo "📝 Коммичу изменения..."
        git add .
        git commit -m "$message $(date '+%Y-%m-%d %H:%M:%S')"
        
        echo "🚀 Пушим в dev..."
        git push origin dev
        
        if [ $? -eq 0 ]; then
            echo "✅ Изменения отправлены в dev!"
        else
            echo "❌ Ошибка при push в dev"
        fi
    fi
    
    echo "🔄 Ожидаю следующие изменения..."
    echo ""
}

# Функция для определения типа файла и вызова соответствующего обработчика
process_file() {
    local file="$1"
    
    if [[ $file =~ \.less$ ]]; then
        handle_less "$file"
    elif [[ $file =~ \.js$ ]]; then
        handle_js "$file"
    elif [[ $file =~ \.php$ ]]; then
        handle_php "$file"
    else
        echo "📄 Изменен файл: $file (не обрабатывается)"
    fi
}

# Запускаем мониторинг с помощью fswatch (macOS)
if command -v fswatch &> /dev/null; then
    echo "🍎 Использую fswatch (macOS)"
    echo "📁 Отслеживаю папки:"
    echo "   • templates/capitalcraft/less/"
    echo "   • templates/capitalcraft/js/"
    echo "   • templates/capitalcraft/"
    echo ""
    
    # Используем fswatch -r для рекурсивного отслеживания с выводом имен файлов
    fswatch -r templates/capitalcraft/less/ templates/capitalcraft/js/ templates/capitalcraft/ | while read file; do
        if [ -f "$file" ]; then
            echo "📄 Обнаружено изменение: $file"
            process_file "$file"
        fi
    done
else
    echo "❌ Не найден fswatch"
    echo "Установите: brew install fswatch"
    exit 1
fi
