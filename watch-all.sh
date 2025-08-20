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
    
    # Исключаем выходные файлы
    if [[ $file =~ /bundle\.js$ ]] || [[ $file =~ /\.css$ ]] || [[ $file =~ /\.map$ ]]; then
        echo "📄 Игнорирую выходной файл: $file"
        return
    fi
    
    # Проверяем, что файл существует и не является директорией
    if [ ! -f "$file" ]; then
        return
    fi
    
    if [[ $file =~ \.less$ ]]; then
        handle_less "$file"
    elif [[ $file =~ \.js$ ]]; then
        # Проверяем, что это исходный JS файл (не в node_modules или других папках)
        if [[ $file =~ templates/capitalcraft/js/ ]] && [[ ! $file =~ /node_modules/ ]] && [[ ! $file =~ /vendor/ ]]; then
            handle_js "$file"
        else
            echo "📄 Игнорирую JS файл: $file"
        fi
    elif [[ $file =~ \.php$ ]]; then
        handle_php "$file"
    else
        echo "📄 Изменен файл: $file (не обрабатывается)"
    fi
}

# Запускаем мониторинг с помощью chokidar-cli
if command -v npx &> /dev/null; then
    echo "📦 Использую chokidar-cli (Node.js)"
    echo "📁 Отслеживаю папки:"
    echo "   • templates/capitalcraft/less/"
    echo "   • templates/capitalcraft/js/global/"
    echo "   • templates/capitalcraft/"
    echo ""
    
    # Исправляем проблему с pipe - используем временный файл для событий
    temp_file=$(mktemp)
    trap "rm -f $temp_file; exit" INT TERM EXIT
    
    npx chokidar-cli templates/capitalcraft/less/ templates/capitalcraft/js/global/ templates/capitalcraft/ --initial > "$temp_file" &
    chokidar_pid=$!
    
    echo "🔄 Chokidar запущен (PID: $chokidar_pid)"
    echo "📝 Ожидаю события в файле: $temp_file"
    echo ""
    
    # Мониторим временный файл на предмет новых событий
    while true; do
        if [ -s "$temp_file" ]; then
            # Читаем все новые строки
            while IFS= read -r line; do
                if [[ $line =~ ^(change|add|unlink): ]]; then
                    file="${line#*:}"
                    if [ -f "$file" ]; then
                        echo "📄 Обнаружено изменение: $file"
                        process_file "$file"
                    fi
                fi
            done < "$temp_file"
            
            # Очищаем файл после обработки
            > "$temp_file"
        fi
        
        sleep 0.1
    done
else
    echo "❌ Не найден npx"
    echo "Установите Node.js: https://nodejs.org/"
    exit 1
fi
