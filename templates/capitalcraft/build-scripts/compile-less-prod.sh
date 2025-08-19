#!/bin/bash

echo "🚀 Компилирую LESS файлы для продакшна..."

# Компилируем все LESS файлы с минификацией
npm run less:all:prod

if [ $? -eq 0 ]; then
    echo "✅ Продакшн компиляция LESS завершена успешно!"
    echo "📁 CSS файлы обновлены в templates/capitalcraft/css/"
else
    echo "❌ Ошибка компиляции LESS!"
    exit 1
fi

echo "🔄 Компилирую JavaScript для продакшна..."
npm run js:build

if [ $? -eq 0 ]; then
    echo "✅ Продакшн компиляция JavaScript завершена успешно!"
    echo "📁 JS bundle обновлен в templates/capitalcraft/js/global/"
else
    echo "❌ Ошибка компиляции JavaScript!"
    exit 1
fi

echo "🎉 Вся продакшн сборка завершена успешно!"
