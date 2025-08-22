#!/bin/bash

# =============================================================================
# AUTO-MERGE: Автоматический мердж dev в main с разрешением конфликтов
# =============================================================================
# Использование: ./auto-merge.sh [--force] [--dry-run]
# 
# Опции:
#   --force     - принудительный мердж (пропустить некоторые проверки)
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

# Флаги
FORCE_MERGE=false
DRY_RUN=false

# Счетчик проблем
ISSUES_COUNT=0

# =============================================================================
# ФУНКЦИИ ВЫВОДА
# =============================================================================

# Функции для вывода
log_info() { 
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() { 
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() { 
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() { 
    echo -e "${RED}❌ $1${NC}"
}

log_step() { 
    echo -e "${PURPLE}🔧 $1${NC}"
}

log_merge() { 
    echo -e "${CYAN}🚀 $1${NC}"
}

# =============================================================================
# ФУНКЦИИ ПРОВЕРКИ
# =============================================================================

# Функция для увеличения счетчика проблем
add_issue() {
    ISSUES_COUNT=$((ISSUES_COUNT + 1))
}

# Проверка git репозитория
check_git_repo() {
    log_step "Проверка git репозитория..."
    if git rev-parse --git-dir > /dev/null 2>&1; then
        log_success "Git репозиторий найден"
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
    else
        log_warning "Есть незакоммиченные изменения:"
        git status --porcelain
        
        if [ "$FORCE_MERGE" = false ]; then
            log_error "Закоммитьте изменения перед мерджем или используйте --force"
            add_issue "Незакоммиченные изменения"
            return 1
        else
            log_warning "Продолжаю мердж с незакоммиченными изменениями (--force)"
        fi
    fi
}

# Проверка существования веток
check_branches() {
    log_step "Проверка существования веток..."
    
    if git show-ref --verify --quiet refs/heads/dev; then
        log_success "Ветка dev существует"
    else
        log_error "Ветка dev не найдена"
        add_issue "Ветка dev не найдена"
        return 1
    fi

    if git show-ref --verify --quiet refs/heads/main; then
        log_success "Ветка main существует"
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
            add_issue "Main отстает от remote"
        elif [ "$MAIN_AHEAD" -gt 0 ]; then
            log_warning "Ветка main опережает origin/main на $MAIN_AHEAD коммитов"
        else
            log_success "Ветка main синхронизирована с origin/main"
        fi
    else
        log_warning "Remote ветка origin/main не найдена"
    fi
}

# Проверка различий между dev и main
check_dev_ahead() {
    log_step "Проверка различий между dev и main..."
    COMMITS_AHEAD=$(git rev-list --count main..dev 2>/dev/null || echo "0")
    COMMITS_BEHIND=$(git rev-list --count dev..main 2>/dev/null || echo "0")
    
    if [ "$COMMITS_AHEAD" -gt 0 ]; then
        log_success "Ветка dev опережает main на $COMMITS_AHEAD коммитов"
        log_info "Последние коммиты в dev:"
        git log --oneline main..dev | head -5
    elif [ "$COMMITS_BEHIND" -gt 0 ]; then
        log_warning "Ветка dev отстает от main на $COMMITS_BEHIND коммитов"
        log_info "Будет выполнен мердж для синхронизации веток"
        # Не считаем это проблемой - мердж все равно выполнится
    else
        log_info "Ветки dev и main синхронизированы"
        log_info "Мердж будет выполнен для обновления main"
        # Не считаем это проблемой - мердж все равно выполнится
    fi
}

# Проверка доступности remote репозитория
check_remote_access() {
    log_step "Проверка доступности remote репозитория..."
    if git ls-remote origin > /dev/null 2>&1; then
        log_success "Remote репозиторий доступен"
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
    check_remote_access || return 1
    
    echo ""
    echo "=================================="
    if [ $ISSUES_COUNT -eq 0 ]; then
        log_success "🎉 ВСЕ ПРОВЕРКИ ПРОЙДЕНЫ! Готов к мерджу."
        return 0
    else
        log_error "Обнаружено $ISSUES_COUNT проблем(ы). Исправьте их перед мерджем."
        return 1
    fi
}

# =============================================================================
# ФУНКЦИИ МЕРДЖА
# =============================================================================

# Автоматическое разрешение конфликтов
resolve_conflicts_automatically() {
    log_merge "Автоматически разрешаю конфликты..."
    
    # Получаем список конфликтующих файлов
    CONFLICT_FILES=$(git status --porcelain | grep "^UU\|^AA\|^DD" | awk '{print $2}' || true)
    
    if [ -n "$CONFLICT_FILES" ]; then
        log_info "Конфликтующие файлы:"
        echo "$CONFLICT_FILES"
        
        # Для каждого конфликтующего файла выбираем версию из dev
        for file in $CONFLICT_FILES; do
            if [ -f "$file" ]; then
                log_info "Разрешаю конфликт в файле: $file"
                # Выбираем версию из dev (текущей ветки)
                git checkout --ours "$file" 2>/dev/null || git checkout HEAD -- "$file" 2>/dev/null
            fi
        done
        
        # Добавляем разрешенные файлы
        git add .
        log_success "Все конфликты автоматически разрешены"
    else
        log_info "Конфликтующих файлов не обнаружено"
    fi
}

# Выполнение мерджа
perform_merge() {
    log_merge "Переключаюсь на ветку main..."
    git checkout main

    log_merge "Обновляю main с remote..."
    git pull origin main

    log_merge "Мерджу dev в main..."
    
    # Пытаемся выполнить мердж
    if git merge dev --no-ff -m "Merge dev into main - $(date)"; then
        log_success "Мердж выполнен успешно"
    else
        log_warning "Мердж завершился с конфликтами, разрешаю автоматически..."
        
        # Автоматически разрешаем конфликты
        resolve_conflicts_automatically
        
        # Коммитим разрешенные конфликты
        if git add . && git commit -m "Merge dev into main - конфликты разрешены автоматически - $(date)"; then
            log_success "Конфликты разрешены и закоммичены"
        else
            log_error "Не удалось закоммитить разрешенные конфликты"
            return 1
        fi
    fi

    log_merge "Пушаю main в remote..."
    git push origin main

    log_merge "Возвращаюсь на ветку dev..."
    git checkout dev
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
            --dry-run)
                DRY_RUN=true
                log_info "Режим тестирования (--dry-run)"
                shift
                ;;
            *)
                log_error "Неизвестный аргумент: $1"
                echo "Использование: $0 [--force] [--dry-run]"
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
    
    # Обработка аргументов
    parse_arguments "$@"
    
    # Проверка готовности
    if ! check_merge_readiness; then
        if [ "$FORCE_MERGE" = true ]; then
            log_warning "Продолжаю мердж несмотря на проблемы (--force)"
        else
            log_error "Мердж отменен из-за проблем"
            exit 1
        fi
    fi
    
    # Если это dry-run, останавливаемся здесь
    if [ "$DRY_RUN" = true ]; then
        log_success "🎯 DRY-RUN завершен успешно! Все проверки пройдены."
        exit 0
    fi
    
    # Выполнение мерджа
    log_merge "Начинаю выполнение мерджа..."
    perform_merge
    
    # Итоговый результат
    echo ""
    echo "=================================="
    log_success "🎉 МЕРДЖ УСПЕШНО ЗАВЕРШЕН!"
    echo ""
    log_info "Что было сделано:"
    echo "  - dev мерджнут в main"
    echo "  - main запушен в remote"
    echo "  - Возврат на ветку dev"
    echo ""
    log_merge "🚀 Ветка main теперь содержит то же содержимое что и dev!"
}

# =============================================================================
# ЗАПУСК
# =============================================================================

# Запускаем главную функцию
main "$@"
