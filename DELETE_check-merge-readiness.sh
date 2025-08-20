#!/bin/bash

# Скрипт для проверки готовности к мерджу dev -> main
# Использование: ./check-merge-readiness.sh

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Функции для вывода
log_info() { echo -e "${BLUE}ℹ️  $1${NC}"; }
log_success() { echo -e "${GREEN}✅ $1${NC}"; }
log_warning() { echo -e "${YELLOW}⚠️  $1${NC}"; }
log_error() { echo -e "${RED}❌ $1${NC}"; }

# Счетчик проблем
ISSUES_COUNT=0

# Функция для увеличения счетчика проблем
add_issue() {
    ISSUES_COUNT=$((ISSUES_COUNT + 1))
}

echo ""
log_info "🔍 Проверяю готовность к мерджу dev -> main"
echo ""

# 1. Проверяем git репозиторий
log_info "1. Проверка git репозитория..."
if git rev-parse --git-dir > /dev/null 2>&1; then
    log_success "Git репозиторий найден"
else
    log_error "Git репозиторий не найден"
    add_issue
fi

# 2. Проверяем текущую ветку
log_info "2. Проверка текущей ветки..."
CURRENT_BRANCH=$(git branch --show-current)
log_info "Текущая ветка: ${CURRENT_BRANCH}"

# 3. Проверяем чистоту рабочего дерева
log_info "3. Проверка чистоты рабочего дерева..."
if git diff-index --quiet HEAD --; then
    log_success "Рабочее дерево чистое"
else
    log_error "Есть незакоммиченные изменения:"
    git status --porcelain
    add_issue
fi

# 4. Проверяем существование веток
log_info "4. Проверка существования веток..."
if git show-ref --verify --quiet refs/heads/dev; then
    log_success "Ветка dev существует"
else
    log_error "Ветка dev не найдена"
    add_issue
fi

if git show-ref --verify --quiet refs/heads/main; then
    log_success "Ветка main существует"
else
    log_error "Ветка main не найдена"
    add_issue
fi

# 5. Проверяем синхронизацию с remote
log_info "5. Проверка синхронизации с remote..."
git fetch origin > /dev/null 2>&1

# Проверяем dev ветку
if git show-ref --verify --quiet refs/remotes/origin/dev; then
    DEV_BEHIND=$(git rev-list --count dev..origin/dev 2>/dev/null || echo "0")
    DEV_AHEAD=$(git rev-list --count origin/dev..dev 2>/dev/null || echo "0")
    
    if [ "$DEV_BEHIND" -gt 0 ]; then
        log_warning "Ветка dev отстает от origin/dev на $DEV_BEHIND коммитов"
        add_issue
    elif [ "$DEV_AHEAD" -gt 0 ]; then
        log_warning "Ветка dev опережает origin/dev на $DEV_AHEAD коммитов"
    else
        log_success "Ветка dev синхронизирована с origin/dev"
    fi
else
    log_warning "Remote ветка origin/dev не найдена"
fi

# Проверяем main ветку
if git show-ref --verify --quiet refs/remotes/origin/main; then
    MAIN_BEHIND=$(git rev-list --count main..origin/main 2>/dev/null || echo "0")
    MAIN_AHEAD=$(git rev-list --count origin/main..main 2>/dev/null || echo "0")
    
    if [ "$MAIN_BEHIND" -gt 0 ]; then
        log_warning "Ветка main отстает от origin/main на $MAIN_BEHIND коммитов"
        add_issue
    elif [ "$MAIN_AHEAD" -gt 0 ]; then
        log_warning "Ветка main опережает origin/main на $MAIN_AHEAD коммитов"
    else
        log_success "Ветка main синхронизирована с origin/main"
    fi
else
    log_warning "Remote ветка origin/main не найдена"
fi

# 6. Проверяем различия между dev и main
log_info "6. Проверка различий между dev и main..."
COMMITS_AHEAD=$(git rev-list --count main..dev 2>/dev/null || echo "0")
if [ "$COMMITS_AHEAD" -gt 0 ]; then
    log_info "Ветка dev опережает main на $COMMITS_AHEAD коммитов"
    log_info "Последние коммиты в dev:"
    git log --oneline main..dev | head -5
else
    log_warning "Ветка dev не содержит новых коммитов по сравнению с main"
fi

# 7. Проверяем возможные конфликты
log_info "7. Проверка возможных конфликтов..."
git checkout main > /dev/null 2>&1
CONFLICTS=$(git merge-tree $(git merge-base main dev) main dev 2>/dev/null | grep -c "<<<<<<< " || echo "0")
git checkout "${CURRENT_BRANCH}" > /dev/null 2>&1

if [ "$CONFLICTS" -gt 0 ] 2>/dev/null; then
    log_warning "Обнаружено $CONFLICTS потенциальных конфликтов"
    log_info "Конфликтующие файлы:"
    git merge-tree $(git merge-base main dev) main dev 2>/dev/null | grep "<<<<<<< " -A 1 -B 1 | grep "+++" | sed 's/+++ b\///' | sort | uniq || true
else
    log_success "Конфликтов не обнаружено"
fi

# 8. Проверяем LESS компиляцию
log_info "8. Проверка возможности компиляции LESS..."
if [ -f "package.json" ]; then
    if grep -q "less:all" package.json; then
        log_success "Скрипт компиляции LESS найден в package.json"
    else
        log_warning "Скрипт компиляции LESS не найден в package.json"
        add_issue
    fi
    
    if [ -d "node_modules" ] || [ -d "../node_modules" ]; then
        log_success "Node modules установлены"
    else
        log_warning "Node modules не установлены. Выполните: npm install"
        add_issue
    fi
else
    log_error "package.json не найден"
    add_issue
fi

# 9. Проверяем доступность remote репозитория
log_info "9. Проверка доступности remote репозитория..."
if git ls-remote origin > /dev/null 2>&1; then
    log_success "Remote репозиторий доступен"
else
    log_warning "Проблемы с доступом к remote репозиторию"
    add_issue
fi

# Итоговый результат
echo ""
echo "=================================="
if [ $ISSUES_COUNT -eq 0 ]; then
    log_success "🎉 ВСЕ ПРОВЕРКИ ПРОЙДЕНЫ! Готов к мерджу."
    echo ""
    log_info "Для выполнения мерджа запустите:"
    echo "    ./merge-dev-to-main.sh"
    echo ""
    exit 0
else
    log_error "Обнаружено $ISSUES_COUNT проблем(ы). Исправьте их перед мерджем."
    echo ""
    log_info "После исправления проблем запустите проверку снова:"
    echo "    ./check-merge-readiness.sh"
    echo ""
    exit 1
fi
