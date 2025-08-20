#!/bin/bash

# Скрипт для мерджа dev в main с компиляцией и бэкапом
# Использование: ./merge-dev-to-main.sh

set -e  # Остановка при ошибке

echo "🚀 Начинаю мердж dev в main..."

# Проверка текущей ветки
CURRENT_BRANCH=$(git branch --show-current)
if [ "$CURRENT_BRANCH" != "dev" ]; then
    echo "❌ Ошибка: вы должны быть на ветке dev для мерджа!"
    echo "Текущая ветка: $CURRENT_BRANCH"
    echo "Переключитесь на dev: git checkout dev"
    exit 1
fi

# Автоматический коммит незакоммиченных изменений
if [ -n "$(git status --porcelain)" ]; then
    echo "🔄 Обнаружены незакоммиченные изменения, коммичу автоматически..."
    git add .
    git commit -m "auto: автоматический коммит перед мерджем - $(date)"
    echo "✅ Изменения закоммичены автоматически!"
fi

# Проверка что dev синхронизирована с remote
echo "🔄 Проверяю синхронизацию с remote..."
git fetch origin
LOCAL_COMMIT=$(git rev-parse HEAD)
REMOTE_COMMIT=$(git rev-parse origin/dev)
if [ "$LOCAL_COMMIT" != "$REMOTE_COMMIT" ]; then
    echo "❌ Ошибка: локальная ветка dev не синхронизирована с remote!"
    echo "Сначала запушите изменения: git push origin dev"
    exit 1
fi

# Создание бэкапа
echo "💾 Создаю бэкап текущего состояния..."
BACKUP_DIR="backup_$(date +%Y%m%d_%H%M%S)"
mkdir -p "../$BACKUP_DIR"
cp -r . "../$BACKUP_DIR/"
echo "✅ Бэкап создан: ../$BACKUP_DIR"

# Компиляция LESS для продакшна
echo "🔄 Компилирую LESS для продакшна..."
npm run build
if [ $? -ne 0 ]; then
    echo "❌ Ошибка компиляции LESS!"
    exit 1
fi

# Коммит скомпилированных файлов
echo "📝 Коммичу скомпилированные файлы..."
git add .
git commit -m "build: продакшн компиляция перед мерджем" || true

# Переключение на main
echo "🔄 Переключаюсь на ветку main..."
git checkout main

# Обновление main
echo "🔄 Обновляю main с remote..."
git pull origin main

# Мердж dev в main
echo "🔄 Мерджу dev в main..."
git merge dev --no-ff -m "Merge dev into main - $(date)"

# Пуш main
echo "📤 Пушаю main в remote..."
git push origin main

# Возврат на dev
echo "🔄 Возвращаюсь на ветку dev..."
git checkout dev

echo "✅ Мердж успешно завершен!"
echo "�� Что было сделано:"
echo "  - Создан бэкап: ../$BACKUP_DIR"
echo "  - LESS скомпилирован для продакшна"
echo "  - dev мерджнут в main"
echo "  - main запушен в remote"
echo "  - Возврат на ветку dev"
echo ""
echo "🚀 Автодеплой на основной сайт запущен!"
