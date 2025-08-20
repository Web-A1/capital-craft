#!/bin/bash

# =============================================================================
# AUTO-MERGE: Автоматический мердж dev в main с проверками и логированием
# =============================================================================
# Использование: ./auto-merge.sh [--force] [--no-backup] [--dry-run]
# 
# Опции:
#   --force     - принудительный мердж (пропустить некоторые проверки)
#   --no-backup - не создавать бэкап
#   --dry-run   - только проверка без выполнения мерджа
# =============================================================================

set -e  # Остановка при ошибке

# =============================================================================
# КОНФИГУРАЦИЯ
# =============================================================================

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Настройки проекта
PROJECT_NAME="Capital Craft"
BACKUP_DIR="../backup_$(date +%Y%m%d_%H%M%S)"
LOG_FILE="merge_$(date +%Y%m%d_%H%M%S).log"
LESS_COMPILE_CMD="npm run less:all"
JS_BUILD_CMD="npm run js:build"

# Флаги
FORCE_MERGE=false
CREATE_BACKUP=true
DRY_RUN=false

# Счетчик проблем
ISSUES_COUNT=0

# =============================================================================
# ФУНКЦИИ ЛОГИРОВАНИЯ
# =============================================================================

# Инициализация лога
init_log() {
    echo "=== MERGE LOG: $(date) ===" > "$LOG_FILE"
    echo "Проект: $PROJECT_NAME" >> "$LOG_FILE"
    echo "Пользователь: $(whoami)" >> "$LOG_FILE"
    echo "Система: $(uname -a)" >> "$LOG_FILE"
    echo "" >> "$LOG_FILE"
}

# Запись в лог
log_to_file() {
    echo "[$(date '+%H:%M:%S')] $1" >> "$LOG_FILE"
}

# Функции для вывода с логированием
log_info() { 
    echo -e "${BLUE}ℹ️  $1${NC}"
    log_to_file "INFO: $1"
}

log_success() { 
    echo -e "${GREEN}✅ $1${NC}"
    log_to_file "SUCCESS: $1"
}

log_warning() { 
    echo -e "${YELLOW}⚠️  $1${NC}"
    log_to_file "WARNING: $1"
}

log_error() { 
    echo -e "${RED}❌ $1${NC}"
    log_to_file "ERROR: $1"
}

log_step() { 
    echo -e "${PURPLE}🔧 $1${NC}"
    log_to_file "STEP: $1"
}

log_merge() { 
    echo -e "${CYAN}🚀 $1${NC}"
    log_to_file "MERGE: $1"
}

# =============================================================================
# ФУНКЦИИ ПРОВЕРКИ
# =============================================================================

# Функция для увеличения счетчика проблем
add_issue() {
    ISSUES_COUNT=$((ISSUES_COUNT + 1))
    log_to_file "ISSUE_ADDED: $1"
}

# Проверка git репозитория
check_git_repo() {
    log_step "Проверка git репозитория..."
    if git rev-parse --git-dir > /dev/null 2>&1; then
        log_success "Git репозиторий найден"
        log_to_file "Git репозиторий: OK"
    else
        log_error "Git репозиторий не найден"
        add_issue "Git репозиторий не найден"
        return 1
    fi
}

# Проверка текущей ветки
check_current_branch() {
    log_step "Проверка текущей ветки..."
    CURRENT_BRANCH=$(git branch --show-current)
    log_info "Текущая ветка: ${CURRENT_BRANCH}"
    log_to_file "Текущая ветка: $CURRENT_BRANCH"
    
    if [ "$CURRENT_BRANCH" != "dev" ]; then
        log_warning "Рекомендуется работать на ветке dev"
        if [ "$FORCE_MERGE" = false ]; then
            log_error "Для мерджа необходимо быть на ветке dev"
            add_issue "Неверная ветка для мерджа"
            return 1
        fi
    fi
}

# Проверка чистоты рабочего дерева
check_working_tree() {
    log_step "Проверка чистоты рабочего дерева..."
    if git diff-index --quiet HEAD --; then
        log_success "Рабочее дерево чистое"
        log_to_file "Рабочее дерево: чистый"
    else
        log_warning "Есть незакоммиченные изменения:"
        git status --porcelain
        log_to_file "Незакоммиченные изменения: $(git status --porcelain | wc -l) файлов"
        
        if [ "$FORCE_MERGE" = false ]; then
            log_error "Необходимо закоммитить изменения перед мерджем"
            add_issue "Незакоммиченные изменения"
            return 1
        else
            log_info "Автоматический коммит будет выполнен (--force)"
        fi
    fi
}

# Проверка существования веток
check_branches() {
    log_step "Проверка существования веток..."
    
    if git show-ref --verify --quiet refs/heads/dev; then
        log_success "Ветка dev существует"
        log_to_file "Ветка dev: существует"
    else
        log_error "Ветка dev не найдена"
        add_issue "Ветка dev не найдена"
        return 1
    fi

    if git show-ref --verify --quiet refs/heads/main; then
        log_success "Ветка main существует"
        log_to_file "Ветка main: существует"
    else
        log_error "Ветка main не найдена"
        add_issue "Ветка main не найдена"
        return 1
    fi
}

# Проверка синхронизации с remote
check_remote_sync() {
    log_step "Проверка синхронизации с remote..."
    git fetch origin > /dev/null 2>&1

    # Проверяем dev ветку
    if git show-ref --verify --quiet refs/remotes/origin/dev; then
        DEV_BEHIND=$(git rev-list --count dev..origin/dev 2>/dev/null || echo "0")
        DEV_AHEAD=$(git rev-list --count origin/dev..dev 2>/dev/null || echo "0")
        
        if [ "$DEV_BEHIND" -gt 0 ]; then
            log_warning "Ветка dev отстает от origin/dev на $DEV_BEHIND коммитов"
            add_issue "Dev отстает от remote"
        elif [ "$DEV_AHEAD" -gt 0 ]; then
            log_warning "Ветка dev опережает origin/dev на $DEV_AHEAD коммитов"
            log_to_file "Dev опережает remote на $DEV_AHEAD коммитов"
        else
            log_success "Ветка dev синхронизирована с origin/dev"
            log_to_file "Dev синхронизирована с remote"
        fi
    else
        log_warning "Remote ветка origin/dev не найдена"
        log_to_file "Remote dev не найдена"
    fi

    # Проверяем main ветку
    if git show-ref --verify --quiet refs/remotes/origin/main; then
        MAIN_BEHIND=$(git rev-list --count main..origin/main 2>/dev/null || echo "0")
        MAIN_AHEAD=$(git rev-list --count origin/main..main 2>/dev/null || echo "0")
        
        if [ "$MAIN_BEHIND" -gt 0 ]; then
            log_warning "Ветка main отстает от origin/main на $MAIN_BEHIND коммитов"
            add_issue "Main отстает от remote"
        elif [ "$MAIN_AHEAD" -gt 0 ]; then
            log_warning "Ветка main опережает origin/main на $MAIN_AHEAD коммитов"
            log_to_file "Main опережает remote на $MAIN_AHEAD коммитов"
        else
            log_success "Ветка main синхронизирована с origin/main"
            log_to_file "Main синхронизирована с remote"
        fi
    else
        log_warning "Remote ветка origin/main не найдена"
        log_to_file "Remote main не найдена"
    fi
}

# Проверка различий между dev и main
check_dev_ahead() {
    log_step "Проверка различий между dev и main..."
    COMMITS_AHEAD=$(git rev-list --count main..dev 2>/dev/null || echo "0")
    if [ "$COMMITS_AHEAD" -gt 0 ]; then
        log_success "Ветка dev опережает main на $COMMITS_AHEAD коммитов"
        log_info "Последние коммиты в dev:"
        git log --oneline main..dev | head -5
        log_to_file "Dev опережает main на $COMMITS_AHEAD коммитов"
    else
        log_warning "Ветка dev не содержит новых коммитов по сравнению с main"
        add_issue "Dev не содержит новых коммитов"
        return 1
    fi
}

# Проверка возможных конфликтов
check_conflicts() {
    log_step "Проверка возможных конфликтов..."
    git checkout main > /dev/null 2>&1
    CONFLICTS=$(git merge-tree $(git merge-base main dev) main dev 2>/dev/null | grep -c "<<<<<<< " || echo "0")
    git checkout "${CURRENT_BRANCH}" > /dev/null 2>&1

    if [ "$CONFLICTS" -gt 0 ] 2>/dev/null; then
        log_warning "Обнаружено $CONFLICTS потенциальных конфликтов"
        log_info "Конфликтующие файлы:"
        git merge-tree $(git merge-base main dev) main dev 2>/dev/null | grep "<<<<<<< " -A 1 -B 1 | grep "+++" | sed 's/+++ b\///' | sort | uniq || true
        add_issue "Обнаружены конфликты: $CONFLICTS"
        return 1
    else
        log_success "Конфликтов не обнаружено"
        log_to_file "Конфликты: не обнаружены"
    fi
}

# Проверка LESS компиляции
check_less_compilation() {
    log_step "Проверка возможности компиляции LESS..."
    if [ -f "package.json" ]; then
        if grep -q "less:all" package.json; then
            log_success "Скрипт компиляции LESS найден в package.json"
            log_to_file "LESS скрипт: найден"
        else
            log_warning "Скрипт компиляции LESS не найден в package.json"
            add_issue "LESS скрипт не найден"
        fi
        
        if [ -d "node_modules" ] || [ -d "../node_modules" ]; then
            log_success "Node modules установлены"
            log_to_file "Node modules: установлены"
        else
            log_warning "Node modules не установлены. Выполните: npm install"
            add_issue "Node modules не установлены"
        fi
    else
        log_error "package.json не найден"
        add_issue "package.json не найден"
        return 1
    fi
}

# Проверка доступности remote репозитория
check_remote_access() {
    log_step "Проверка доступности remote репозитория..."
    if git ls-remote origin > /dev/null 2>&1; then
        log_success "Remote репозиторий доступен"
        log_to_file "Remote доступ: OK"
    else
        log_warning "Проблемы с доступом к remote репозиторию"
        add_issue "Проблемы с remote доступом"
        return 1
    fi
}

# Полная проверка готовности
check_merge_readiness() {
    log_info "🔍 Начинаю проверку готовности к мерджу dev -> main"
    echo ""
    
    check_git_repo || return 1
    check_current_branch || return 1
    check_working_tree || return 1
    check_branches || return 1
    check_remote_sync || return 1
    check_dev_ahead || return 1
    check_conflicts || return 1
    check_less_compilation || return 1
    check_remote_access || return 1
    
    echo ""
    echo "=================================="
    if [ $ISSUES_COUNT -eq 0 ]; then
        log_success "🎉 ВСЕ ПРОВЕРКИ ПРОЙДЕНЫ! Готов к мерджу."
        log_to_file "Все проверки пройдены успешно"
        return 0
    else
        log_error "Обнаружено $ISSUES_COUNT проблем(ы). Исправьте их перед мерджем."
        log_to_file "Обнаружено проблем: $ISSUES_COUNT"
        return 1
    fi
}

# =============================================================================
# ФУНКЦИИ МЕРДЖА
# =============================================================================

# Автоматический коммит изменений
auto_commit_changes() {
    if [ -n "$(git status --porcelain)" ]; then
        log_merge "Обнаружены незакоммиченные изменения, коммичу автоматически..."
        git add .
        git commit -m "auto: автоматический коммит перед мерджем - $(date)"
        log_success "Изменения закоммичены автоматически!"
        log_to_file "Автокоммит выполнен"
    fi
}

# Создание бэкапа
create_backup() {
    if [ "$CREATE_BACKUP" = true ]; then
        log_merge "Создаю бэкап текущего состояния..."
        mkdir -p "$BACKUP_DIR"
        cp -r . "$BACKUP_DIR/"
        log_success "Бэкап создан: $BACKUP_DIR"
        log_to_file "Бэкап создан: $BACKUP_DIR"
    else
        log_info "Создание бэкапа пропущено (--no-backup)"
        log_to_file "Бэкап пропущен"
    fi
}

# Компиляция LESS
compile_less() {
    log_merge "Компилирую LESS для продакшна..."
    if npm run less:all; then
        log_success "LESS скомпилирован успешно"
        log_to_file "LESS компиляция: успешно"
    else
        log_error "Ошибка компиляции LESS!"
        log_to_file "LESS компиляция: ошибка"
        return 1
    fi
}

# Компиляция JavaScript
compile_js() {
    log_merge "Компилирую JavaScript..."
    if npm run js:build; then
        log_success "JavaScript скомпилирован успешно"
        log_to_file "JS компиляция: успешно"
    else
        log_error "Ошибка компиляции JavaScript!"
        log_to_file "JS компиляция: ошибка"
        return 1
    fi
}

# Коммит скомпилированных файлов
commit_compiled_files() {
    log_merge "Коммичу скомпилированные файлы..."
    if git add . && git commit -m "build: продакшн компиляция перед мерджем"; then
        log_success "Скомпилированные файлы закоммичены"
        log_to_file "Коммит скомпилированных файлов: успешно"
    else
        log_warning "Не удалось закоммитить скомпилированные файлы (возможно, нет изменений)"
        log_to_file "Коммит скомпилированных файлов: пропущен"
    fi
}

# Выполнение мерджа
perform_merge() {
    log_merge "Переключаюсь на ветку main..."
    git checkout main
    log_to_file "Переключение на main: выполнено"

    log_merge "Обновляю main с remote..."
    git pull origin main
    log_to_file "Обновление main: выполнено"

    log_merge "Мерджу dev в main..."
    git merge dev --no-ff -m "Merge dev into main - $(date)"
    log_to_file "Мердж dev в main: выполнено"

    log_merge "Пушаю main в remote..."
    git push origin main
    log_to_file "Пуш main: выполнен"

    log_merge "Возвращаюсь на ветку dev..."
    git checkout dev
    log_to_file "Возврат на dev: выполнен"
}

# =============================================================================
# ГЛАВНАЯ ФУНКЦИЯ
# =============================================================================

# Обработка аргументов командной строки
parse_arguments() {
    while [[ $# -gt 0 ]]; do
        case $1 in
            --force)
                FORCE_MERGE=true
                log_info "Включен принудительный мердж (--force)"
                shift
                ;;
            --no-backup)
                CREATE_BACKUP=false
                log_info "Создание бэкапа отключено (--no-backup)"
                shift
                ;;
            --dry-run)
                DRY_RUN=true
                log_info "Режим тестирования (--dry-run)"
                shift
                ;;
            *)
                log_error "Неизвестный аргумент: $1"
                echo "Использование: $0 [--force] [--no-backup] [--dry-run]"
                exit 1
                ;;
        esac
    done
}

# Главная функция
main() {
    echo ""
    log_info "🚀 AUTO-MERGE: Автоматический мердж dev в main"
    echo "=================================================="
    
    # Инициализация лога
    init_log
    
    # Обработка аргументов
    parse_arguments "$@"
    
    # Проверка готовности
    if ! check_merge_readiness; then
        if [ "$FORCE_MERGE" = true ]; then
            log_warning "Продолжаю мердж несмотря на проблемы (--force)"
            log_to_file "Мердж продолжен с флагом --force"
        else
            log_error "Мердж отменен из-за проблем"
            log_to_file "Мердж отменен"
            exit 1
        fi
    fi
    
    # Если это dry-run, останавливаемся здесь
    if [ "$DRY_RUN" = true ]; then
        log_success "🎯 DRY-RUN завершен успешно! Все проверки пройдены."
        log_to_file "Dry-run завершен успешно"
        exit 0
    fi
    
    # Выполнение мерджа
    log_merge "Начинаю выполнение мерджа..."
    
    auto_commit_changes
    create_backup
    compile_less
    compile_js
    commit_compiled_files
    perform_merge
    
    # Итоговый результат
    echo ""
    echo "=================================="
    log_success "🎉 МЕРДЖ УСПЕШНО ЗАВЕРШЕН!"
    echo ""
    log_info "Что было сделано:"
    if [ "$CREATE_BACKUP" = true ]; then
        echo "  - Создан бэкап: $BACKUP_DIR"
    fi
    echo "  - LESS скомпилирован для продакшна"
    echo "  - JavaScript скомпилирован для продакшна"
    echo "  - dev мерджнут в main"
    echo "  - main запушен в remote"
    echo "  - Возврат на ветку dev"
    echo ""
    log_merge "🚀 Автодеплой на основной сайт запущен!"
    
    log_to_file "Мердж завершен успешно"
    log_to_file "Лог сохранен в: $LOG_FILE"
    
    echo ""
    log_info "📋 Лог операции сохранен в: $LOG_FILE"
}

# =============================================================================
# ЗАПУСК
# =============================================================================

# Проверяем, что скрипт запущен из правильной директории
if [ ! -f "package.json" ]; then
    echo "❌ Ошибка: запустите скрипт из корневой директории проекта (где находится package.json)"
    exit 1
fi

# Запускаем главную функцию
main "$@"
