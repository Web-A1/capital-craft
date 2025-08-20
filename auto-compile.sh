#!/bin/bash

# Автоматическая компиляция LESS и push в dev
# Запуск: ./auto-compile.sh

echo "🚀 Запуск автоматической компиляции LESS..."

# Проверяем, что мы в dev ветке
current_branch=$(git branch --show-current)
if [ "$current_branch" != "dev" ]; then
    echo "❌ Ошибка: вы не в ветке dev (текущая ветка: $current_branch)"
    echo "Переключитесь на dev: git checkout dev"
    exit 1
fi

# Компилируем LESS
echo "🎨 Компилирую LESS файлы..."
npm run less:all

if [ $? -eq 0 ]; then
    echo "✅ LESS компиляция завершена успешно!"
    
    # Проверяем, есть ли изменения для коммита
    if git diff-index --quiet HEAD --; then
        echo "📝 Нет изменений для коммита"
    else
        echo "📝 Коммичу изменения..."
        git add .
        git commit -m "Auto-compile LESS $(date '+%Y-%m-%d %H:%M:%S')"
        
        echo "🚀 Пушим в dev..."
        git push origin dev
        
        if [ $? -eq 0 ]; then
            echo "✅ Изменения успешно отправлены в dev!"
        else
            echo "❌ Ошибка при push в dev"
            exit 1
        fi
    fi
else
    echo "❌ Ошибка компиляции LESS!"
    exit 1
fi

echo "🎉 Автоматизация завершена!"
