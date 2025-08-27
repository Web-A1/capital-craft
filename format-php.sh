#!/bin/bash

# Скрипт для автоматического форматирования PHP файлов
# Запускает PHP CS Fixer и Prettier последовательно

if [ $# -eq 0 ]; then
    echo "Использование: $0 <путь_к_php_файлу>"
    exit 1
fi

PHP_FILE="$1"

if [ ! -f "$PHP_FILE" ]; then
    echo "Ошибка: Файл $PHP_FILE не найден"
    exit 1
fi

echo "Форматирование файла: $PHP_FILE"

# 1. Запускаем PHP CS Fixer
echo "1. Запуск PHP CS Fixer..."
if command -v php-cs-fixer >/dev/null 2>&1; then
    php-cs-fixer fix "$PHP_FILE" --using-cache=no
    echo "   PHP CS Fixer завершен"
else
    echo "   PHP CS Fixer не найден, пропускаем"
fi

# 2. Запускаем Prettier
echo "2. Запуск Prettier..."
if [ -f "node_modules/.bin/prettier" ]; then
    npx prettier --write "$PHP_FILE"
    echo "   Prettier завершен"
else
    echo "   Prettier не найден, пропускаем"
fi

echo "Форматирование завершено!"
