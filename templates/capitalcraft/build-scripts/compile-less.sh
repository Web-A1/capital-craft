#!/bin/bash

echo "🚀 Компилирую LESS файлы..."

# Компилируем все LESS файлы
npm run less:all

if [ $? -eq 0 ]; then
    echo "✅ Компиляция завершена успешно!"
    echo "📁 CSS файлы обновлены в templates/capitalcraft/css/"
else
    echo "❌ Ошибка компиляции!"
    exit 1
fi
