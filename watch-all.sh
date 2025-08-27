#!/bin/bash
set -euo pipefail

# тест Универсальный мониторинг файлов проекта
# Запуск: ./watch-all.sh

echo "🚀 Запуск универсального мониторинга файлов..."
echo "📁 Отслеживаю изменения в проекте..."
echo "🔄 Логика обработки:"
echo "   • LESS файлы → форматирование + компиляция + подготовка к коммиту"
echo "   • JS файлы → форматирование + пересборка + подготовка к коммиту"
echo "   • PHP файлы → форматирование + подготовка к коммиту"
echo "⏹️  Для остановки: Ctrl+C"
echo ""

# Guardrails
if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  echo "❌ Не git‑репозиторий"; exit 1
fi
current_branch=$(git branch --show-current)
if [ "$current_branch" != "dev" ]; then
  echo "❌ Ошибка: вы не в ветке dev (сейчас: $current_branch)"; exit 1
fi
command -v npx >/dev/null || { echo "❌ Не найден npx"; exit 1; }

# Переменные для отслеживания состояния
LESS_CHANGED=false
JS_CHANGED=false
PHP_CHANGED=false
SYSTEM_CHANGED=false

# Защита от повторной обработки одного файла в рамках одного цикла
PROCESSED_FILES=""

# Функция для проверки реальных изменений в git
has_git_changes() {
    local file="$1"
    
    # Проверяем существование файла
    if [[ ! -f "$file" ]]; then
        return 1
    fi
    
    # Проверяем, есть ли реальные изменения относительно последнего коммита
    if git diff --quiet -- "$file" 2>/dev/null; then
        return 1
    fi
    
    return 0
}

# Функция для умной компиляции LESS файлов
compile_less_file() {
    local file="$1"
    
    echo "🔍 Анализирую зависимости для: $file"
    
    # Определяем, какие CSS файлы нужно перекомпилировать
    if [[ "$file" == *"/_variables.less" || "$file" == *"/_buttons.less" || "$file" == *"/_header.less" || "$file" == *"/_footer.less" || "$file" == *"/_modal.less" || "$file" == *"/_scroll-top.less" || "$file" == *"/_breadcrumbs.less" ]]; then
        echo "    📦 Переменные/компоненты - компилирую все файлы"
        npm run less:all
    elif [[ "$file" == *"/base.less" ]]; then
        echo "    🎯 Базовые стили - компилирую base.css"
        npm run less:base
    elif [[ "$file" == *"/home.less" || "$file" == *"/pages/home/"* || "$file" == *"/_reviews.less" || "$file" == *"/_hero.less" || "$file" == *"/_partners.less" || "$file" == *"/_faq-home.less" || "$file" == *"/_show_case.less" || "$file" == *"/_philosophy.less" || "$file" == *"/_products.less" || "$file" == *"/_team.less" ]]; then
        echo "    🏠 Главная страница - компилирую home.css"
        npm run less:home
    elif [[ "$file" == *"/faq.less" || "$file" == *"/pages/faq/"* ]]; then
        echo "    ❓ FAQ - компилирую faq.css"
        npm run less:faq
    elif [[ "$file" == *"/critical.less" ]]; then
        echo "    ⚡ Критические стили - компилирую critical.css"
        npm run less:critical
    else
        echo "    ❓ Неизвестный файл - компилирую все для безопасности"
        npm run less:all
    fi
}

# Функция для обработки LESS файлов
handle_less() {
    local file="$1"
    
    # Проверяем, действительно ли файл изменился
    if ! has_git_changes "$file"; then
        echo "📝 LESS файл не изменился: $file (пропускаю)"
        return
    fi
    
    echo "Изменен LESS файл: $file"
    echo "Запускаю форматирование..."
    
    # 1. Форматируем LESS код
    echo "   1. Prettier..."
    if [ -f "node_modules/.bin/prettier" ]; then
        npx prettier --write "$file"
        echo "     Prettier завершен"
    else
        echo "     Prettier не найден"
    fi
    
    echo "Умная компиляция LESS..."
    
    compile_less_file "$file"
    
    if [ $? -eq 0 ]; then
        echo "LESS компиляция завершена!"
        LESS_CHANGED=true
    else
        echo "Ошибка компиляции LESS!"
    fi
}

# Функция для обработки JS файлов
handle_js() {
    local file="$1"
    
    # Проверяем, действительно ли файл изменился
    if ! has_git_changes "$file"; then
        echo "📝 JS файл не изменился: $file (пропускаю)"
        return
    fi
    
    echo "Изменен JS файл: $file"
    echo "Запускаю форматирование..."
    
    # 1. Форматируем JavaScript код
    echo "   1. Prettier..."
    if [ -f "node_modules/.bin/prettier" ]; then
        npx prettier --write "$file"
        echo "     Prettier завершен"
    else
        echo "     Prettier не найден"
    fi
    
    echo "Пересобираю JavaScript..."
    
    npm run js:build
    
    if [ $? -eq 0 ]; then
        echo "JavaScript пересборка завершена!"
        JS_CHANGED=true
    else
        echo "Ошибка пересборки JavaScript!"
    fi
}

# Функция для обработки PHP файлов
handle_php() {
    local file="$1"
    
    # Проверяем, действительно ли файл изменился
    if ! has_git_changes "$file"; then
        echo "📝 PHP файл не изменился: $file (пропускаю)"
        return
    fi
    
    echo "Изменен PHP файл: $file"
    echo "Запускаю форматирование..."
    
    # 1. Форматируем PHP код
    echo "   1. PHP CS Fixer..."
    if command -v php-cs-fixer >/dev/null 2>&1; then
        php-cs-fixer fix "$file" --using-cache=no
        echo "     PHP CS Fixer завершен"
    else
        echo "     PHP CS Fixer не найден"
    fi
    
    # 2. Форматируем HTML код
    echo "   2. Prettier..."
    if [ -f "node_modules/.bin/prettier" ]; then
        npx prettier --write "$file"
        echo "     Prettier завершен"
    else
        echo "     Prettier не найден"
    fi
    
    echo "Форматирование PHP файла завершено!"
    
    # Небольшая задержка для стабильности записи файла
    sleep 0.5
    
    PHP_CHANGED=true
}



# Функция для выполнения финальных действий
execute_final_actions() {
    local message=""
    local actions=()
    local changed_file=""
    
    if [ "$LESS_CHANGED" = true ]; then
        actions+=("LESS compilation")
        # Находим последний измененный LESS файл
        changed_file=$(find templates/capitalcraft/less/ -name "*.less" -newer templates/capitalcraft/css/home.css 2>/dev/null | head -1 || echo "")
    fi
    
    if [ "$JS_CHANGED" = true ]; then
        actions+=("JS rebuild")
        if [ -z "$changed_file" ]; then
            changed_file=$(find templates/capitalcraft/js/ -name "*.js" -newer templates/capitalcraft/js/global/bundle.js 2>/dev/null | head -1 || echo "")
        fi
    fi
    
    if [ "$PHP_CHANGED" = true ]; then
        actions+=("PHP update")
        if [ -z "$changed_file" ]; then
            changed_file=$(find templates/capitalcraft/ -name "*.php" -newer templates/capitalcraft/css/home.css 2>/dev/null | head -1 || echo "")
        fi
    fi
    
    if [ "$SYSTEM_CHANGED" = true ]; then
        actions+=("System files update")
        if [ -z "$changed_file" ]; then
            changed_file="system"
        fi
    fi
    
    if [ ${#actions[@]} -gt 0 ]; then
        message=$(IFS=" + "; echo "${actions[*]}")
        
        # Автоматически коммитим и пушим изменения
        echo "🚀 Автоматически коммичу и пушу изменения..."
        
        # Создаем информативное сообщение коммита для всех типов файлов
        local commit_message=""
        local all_files=""
        
        if [[ "$LESS_CHANGED" == true ]]; then
            local less_files=$(echo "$PROCESSED_FILES" | tr '|' '\n' | grep '\.less$' | sed 's/.*\///' | tr '\n' ' ')
            if [[ -n "$less_files" ]]; then
                all_files+="LESS: $less_files "
            fi
        fi
        
        if [[ "$JS_CHANGED" == true ]]; then
            local js_files=$(echo "$PROCESSED_FILES" | tr '|' '\n' | grep '\.js$' | sed 's/.*\///' | tr '\n' ' ')
            if [[ -n "$js_files" ]]; then
                all_files+="JS: $js_files "
            fi
        fi
        
        if [[ "$PHP_CHANGED" == true ]]; then
            local php_files=$(echo "$PROCESSED_FILES" | tr '|' '\n' | grep '\.php$' | sed 's/.*\///' | tr '\n' ' ')
            if [[ -n "$php_files" ]]; then
                all_files+="PHP: $php_files "
            fi
        fi
        
        # Добавляем системные файлы
        local system_files=$(echo "$PROCESSED_FILES" | tr '|' '\n' | grep -E '(watch-all\.sh|package\.json|\.prettierrc|\.php-cs-fixer\.php|robots\.txt|\.gitignore|README\.md)' | sed 's/.*\///' | tr '\n' ' ')
        if [[ -n "$system_files" ]]; then
            all_files+="SYSTEM: $system_files "
        fi
        
        commit_message="$all_files"
        
        # Добавляем временную метку в формате: время_день/месяц
        commit_message+=" | $(date +%H:%M:%S_%d/%m)"
        
        # Добавляем информацию об изменениях для всех типов файлов
        local all_changes=""
        
        if [[ "$LESS_CHANGED" == true ]]; then
            local less_file=$(echo "$PROCESSED_FILES" | tr '|' '\n' | grep '\.less$' | head -1)
            if [[ -n "$less_file" ]]; then
                local changed_lines=$(git diff --unified=0 "$less_file" | grep '^@@' | sed 's/^@@ -[0-9,]* +\([0-9,]*\) @@.*/\1/' | tr ',' ' ' | tr '\n' ' ')
                if [[ -n "$changed_lines" ]]; then
                    all_changes+="LESS lines: $changed_lines "
                fi
            fi
        fi
        
        if [[ "$JS_CHANGED" == true ]]; then
            local js_file=$(echo "$PROCESSED_FILES" | tr '|' '\n' | grep '\.js$' | head -1)
            if [[ -n "$js_file" ]]; then
                local changed_lines=$(git diff --unified=0 "$js_file" | grep '^@@' | sed 's/^@@ -[0-9,]* +\([0-9,]*\) @@.*/\1/' | tr ',' ' ' | tr '\n' ' ')
                if [[ -n "$changed_lines" ]]; then
                    all_changes+="JS lines: $changed_lines "
                fi
            fi
        fi
        
        if [[ "$PHP_CHANGED" == true ]]; then
            local php_file=$(echo "$PROCESSED_FILES" | tr '|' '\n' | grep '\.php$' | head -1)
            if [[ -n "$php_file" ]]; then
                local changed_lines=$(git diff --unified=0 "$php_file" | grep '^@@' | sed 's/^@@ -[0-9,]* +\([0-9,]*\) @@.*/\1/' | tr ',' ' ' | tr '\n' ' ')
                if [[ -n "$changed_lines" ]]; then
                    all_changes+="PHP lines: $changed_lines "
                fi
            fi
        fi
        
        if [[ -n "$all_changes" ]]; then
            commit_message+=" | $all_changes"
        fi
        
        echo ""
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        echo "📝 СООБЩЕНИЕ КОММИТА: $commit_message"
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        echo ""
        
        # Умно добавляем только нужные файлы вместо git add .
        if [[ "$LESS_CHANGED" == true ]]; then
            echo "🎨 LESS ФАЙЛЫ И CSS:"
            echo "   └─ Добавляю LESS файлы..."
            echo "$PROCESSED_FILES" | tr '|' '\n' | grep '\.less$' | xargs -I {} git add {}
            
            # Добавляем соответствующие CSS файлы
            if echo "$PROCESSED_FILES" | grep -q "pages/home"; then
                echo "   └─ Добавляю CSS: home.css"
                git add templates/capitalcraft/css/home.css
            fi
            if echo "$PROCESSED_FILES" | grep -q "base.less"; then
                echo "   └─ Добавляю CSS: base.css"
                git add templates/capitalcraft/css/base.css
            fi
            if echo "$PROCESSED_FILES" | grep -q "critical.less"; then
                echo "   └─ Добавляю CSS: critical.css"
                git add templates/capitalcraft/css/critical.css
            fi
            if echo "$PROCESSED_FILES" | grep -q "faq.less"; then
                echo "   └─ Добавляю CSS: faq.css"
                git add templates/capitalcraft/css/faq.css
            fi
            echo ""
        fi
        
        if [[ "$JS_CHANGED" == true ]]; then
            echo "⚡ JS ФАЙЛЫ:"
            echo "   └─ Добавляю JS файлы..."
            echo "$PROCESSED_FILES" | tr '|' '\n' | grep '\.js$' | xargs -I {} git add {}
            echo "   └─ Добавляю bundle: bundle.js"
            git add templates/capitalcraft/js/global/bundle.js
            echo ""
        fi
        
        if [[ "$PHP_CHANGED" == true ]]; then
            echo "🐘 PHP ФАЙЛЫ:"
            echo "   └─ Добавляю PHP файлы..."
            echo "$PROCESSED_FILES" | tr '|' '\n' | grep '\.php$' | xargs -I {} git add {}
            echo ""
        fi
        
        # Добавляем системные файлы
        local system_files=$(echo "$PROCESSED_FILES" | tr '|' '\n' | grep -E '(watch-all\.sh|package\.json|\.prettierrc|\.php-cs-fixer\.php|robots\.txt|\.gitignore|README\.md)')
        if [[ -n "$system_files" ]]; then
            echo "⚙️ СИСТЕМНЫЕ ФАЙЛЫ:"
            echo "   └─ Добавляю системные файлы..."
            echo "$system_files" | xargs -I {} git add {}
            echo ""
        fi
        
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        echo "🚀 ВЫПОЛНЯЮ КОММИТ И ПУШ..."
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        echo ""
        
        git commit -m "$commit_message"
        git push origin dev
        
        echo ""
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        echo "✅ ИЗМЕНЕНИЯ УСПЕШНО ОТПРАВЛЕНЫ В DEV ВЕТКУ!"
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        echo ""
        
        # Сбрасываем флаги
        LESS_CHANGED=false
        JS_CHANGED=false
        PHP_CHANGED=false
        SYSTEM_CHANGED=false
        
        # Очищаем список обработанных файлов
        PROCESSED_FILES=""
        echo "🧹 Очистил список обработанных файлов"
    fi
}



# --- обработка unlink и игноры ---
process_file() {
  local file="$1"
  local kind="$2"  # add|change|unlink

  echo "process_file: обработка $kind для файла '$file'"

  # Защита от повторной обработки одного файла в рамках одного цикла
  # НО для LESS файлов разрешаем повторную обработку (компиляция при каждом сохранении)
  if [[ "$PROCESSED_FILES" == *"|$file|"* && "$file" != *".less" ]]; then
    echo "Файл уже обработан в этом цикле: $file (пропускаю)"
    return
  fi

  # Игноры выходных/служебных
  case "$file" in
    *.css|*.map|*/bundle.js|*/bundle.min.js) echo "Игнор артефакта: $file"; return ;;
    .prettierrc|.php-cs-fixer.php|.editorconfig|.gitignore|.cursorrules|README*.md|format-php.sh|temp_file) echo "Игнор конфигурации: $file"; return ;;
  esac
  [[ "$file" == *node_modules/* || "$file" == *vendor/* || "$file" == *".git/"* || "$file" == *".vscode/"* ]] && { echo "Игнор служебного: $file"; return; }

  # Для unlink файла уже нет на диске — всё равно коммитим удаление
  if [[ "$kind" == "unlink" ]]; then
    echo "Удалён файл: $file"
    return
  fi

  # Для add/change проверяем расширение
  if [[ ! -f "$file" ]]; then 
    echo "Файл не найден: $file"
    return
  fi

  echo "Файл найден, определяю тип..."

  # Сначала обрабатываем файл, потом добавляем в PROCESSED_FILES
  case "$file" in
    *.less)  
      echo "Обрабатываю как LESS файл: $file"
      handle_less "$file" 
      ;;
    *.js)
      echo "Обрабатываю как JS файл: $file"
      handle_js "$file"
      ;;
    *.php)   
      echo "Обрабатываю как PHP файл: $file"
      handle_php "$file" 
      ;;
    watch-all.sh|package.json|.prettierrc|.php-cs-fixer.php|robots.txt|.gitignore|README.md)
      echo "Изменён системный файл: $file"
      # Системные файлы только добавляем в PROCESSED_FILES, без специальной обработки
      SYSTEM_CHANGED=true
      ;;
    *)       
      echo "Изменён файл: $file (не обрабатывается)"
      ;;
  esac

  # Добавляем в PROCESSED_FILES только если файл еще не добавлен
  if [[ "$PROCESSED_FILES" != *"|$file|"* ]]; then
    PROCESSED_FILES="$PROCESSED_FILES|$file|"
    echo "✅ Файл добавлен в PROCESSED_FILES: $file"
  else
    echo "🔄 Файл уже в PROCESSED_FILES: $file (компиляция выполнена)"
  fi
}

# --- запуск chokidar с дебаунсом и завершением записи ---
trap 'kill ${chokidar_pid:-0} 2>/dev/null || true; exit' INT TERM EXIT

echo "🔄 Запускаю chokidar..."
echo "📝 Ожидаю события..."
echo ""

# Запускаем chokidar и читаем его вывод напрямую
echo "🔍 Запускаю chokidar с отладкой..."
echo "⏰ Использую дебаунс 800ms для группировки событий..."

# Переменная для отслеживания времени последнего события
last_event_time=0
debounce_delay=800

while IFS= read -r line; do
  # Используем секунды вместо миллисекунд для совместимости с macOS
  current_time=$(date +%s)
  
  echo "📨 Получено событие: $line"
  
  # Формат строки: "change: path" / "add: path" / "unlink: path"
  case "$line" in
    change:*) 
      file=$(echo "$line" | cut -d: -f2-)
      echo "🔄 Обрабатываю изменение: $file"
      process_file "$file" "change" 
      ;;
    add:*)    
      file=$(echo "$line" | cut -d: -f2-)
      echo "➕ Обрабатываю добавление: $file"
      process_file "$file" "add" 
      ;;
    unlink:*) 
      file=$(echo "$line" | cut -d: -f2-)
      echo "🗑️ Обрабатываю удаление: $file"
      process_file "$file" "unlink"
      
      # Автоматически коммитим и пушим удаление файла
      echo "🚀 Автоматически коммичу и пушу удаление файла..."
      git add -A
      git commit -m "DELETE: $(basename "$file") | $(date +%H:%M:%S_%d/%m)"
      git push origin dev
      echo "✅ Удаление файла автоматически отправлено в dev ветку!"
      ;;
    *) 
      echo "❓ Неизвестное событие: $line"
      ;;
  esac
  
  # Обновляем время последнего события
  last_event_time=$current_time
  
  # Запускаем таймер для выполнения финальных действий
  (
    sleep 0.8  # 800ms дебаунс
    current_check_time=$(date +%s)
    if [ $((current_check_time - last_event_time)) -ge 1 ]; then
      echo "⏰ Дебаунс завершен, выполняю финальные действия..."
      execute_final_actions
    fi
  ) &
  
done < <(npx chokidar-cli \
  "templates/capitalcraft/**" \
  "watch-all.sh" \
  "package.json" \
  ".prettierrc" \
  ".php-cs-fixer.php" \
  "robots.txt" \
  ".gitignore" \
  "README.md" \
  --ignore "**/*.css" \
  --ignore "**/*.map" \
  --ignore "**/*bundle.js" \
  --ignore "**/node_modules/**" \
  --ignore "**/.git/**" \
  --ignore "**/vendor/**" \
  --ignore "**/.vscode/**" \
  --ignore "format-php.sh" \
  --ignore "sitemap.xml" \
  --ignore "temp_file" \
  --await-write-finish 200 \
  --debounce 800)
