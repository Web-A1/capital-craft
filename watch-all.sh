#!/bin/bash
set -euo pipefail

# Универсальный мониторинг файлов проекта
# Запуск: ./watch-all.sh

echo "Запуск универсального мониторинга файлов..."
echo "Отслеживаю изменения в проекте..."
echo "Логика обработки:"
echo "   • LESS файлы → форматирование + компиляция + подготовка к коммиту"
echo "   • JS файлы → форматирование + пересборка + подготовка к коммиту"
echo "   • PHP файлы → форматирование + подготовка к коммиту"
echo "Для остановки: Ctrl+C"
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
# Флаг: был ли выполнен пересбор всех less (по триггеру общих partial-ов)
LESS_ALL_BUILT=false
# Lock для исключения параллельных коммитов
COMMIT_LOCK_FILE=".git/.watch_commit_lock"
# Короткий разделитель для финального блока
SEPARATOR='━━━━━━━━━━━━━━━━━━━━━'

# Защита от повторной обработки одного файла в рамках одного цикла
PROCESSED_FILES=""

# Функция для проверки реальных изменений в git
has_git_changes() {
    local file="$1"
    
    # Проверяем существование файла
    if [[ ! -f "$file" ]]; then
        return 1
    fi
    
    # Любое состояние в porcelain (включая untracked '??') считаем изменением
    if [[ -n $(git status --porcelain -- "$file") ]]; then
        return 0
    fi
    
    return 1
}

# Функция для умной компиляции LESS файлов
compile_less_file() {
    local file="$1"
    
    echo "Анализирую зависимости для: $file"
    
    # Определяем, какие CSS файлы нужно перекомпилировать
    if [[ "$file" == *"/_variables.less" || "$file" == *"/_buttons.less" || "$file" == *"/_header.less" || "$file" == *"/_footer.less" || "$file" == *"/_modal.less" || "$file" == *"/_scroll-top.less" || "$file" == *"/_breadcrumbs.less" ]]; then
        echo "    Переменные/компоненты - компилирую все файлы"
        npm run less:all
        LESS_ALL_BUILT=true
    elif [[ "$file" == *"/base.less" ]]; then
        echo "    Базовые стили - компилирую base.css"
        npm run less:base
    elif [[ "$file" == *"/home.less" || "$file" == *"/pages/home/"* || "$file" == *"/_reviews.less" || "$file" == *"/_hero.less" || "$file" == *"/_partners.less" || "$file" == *"/_faq-home.less" || "$file" == *"/_show_case.less" || "$file" == *"/_philosophy.less" || "$file" == *"/_products.less" || "$file" == *"/_team.less" ]]; then
        echo "    Главная страница - компилирую home.css"
        npm run less:home
    elif [[ "$file" == *"/faq.less" || "$file" == *"/pages/faq/"* ]]; then
        echo "    FAQ - компилирую faq.css"
        npm run less:faq
    elif [[ "$file" == *"/critical.less" ]]; then
        echo "    Критические стили - компилирую critical.css"
        npm run less:critical
    else
        echo "    Неизвестный файл - компилирую все для безопасности"
        npm run less:all
    fi
}

# Функция для обработки LESS файлов
handle_less() {
    local file="$1"
    
    # Проверяем, действительно ли файл изменился
    if ! has_git_changes "$file"; then
        echo "LESS файл не изменился: $file (пропускаю)"
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
    
    # Не полагаемся только на git-status: событие chokidar уже сигнализирует об изменении.
    if has_git_changes "$file"; then
        echo "Изменен JS файл: $file"
    else
        echo "Получено событие изменения JS, но git чистый: $file (форматирую и пересобираю всё равно)"
    fi
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
        echo "PHP файл не изменился: $file (пропускаю)"
        return
    fi
    
    echo "Изменен PHP файл: $file"
    echo "Запускаю форматирование..."
    
    # 0. Проверяем синтаксис PHP (мягко: при ошибке файл пропускаем)
    if command -v php >/dev/null 2>&1; then
        if ! php -l "$file" >/dev/null 2>&1; then
            echo "     Ошибка синтаксиса PHP (php -l). Пропускаю форматирование."
            return
        fi
    fi

    # 1. Форматируем PHP код (PSR-12 и правила)
    echo "   1. PHP CS Fixer..."
    if command -v php-cs-fixer >/dev/null 2>&1; then
        php-cs-fixer fix "$file"
        echo "     PHP CS Fixer завершен"
    else
        echo "     PHP CS Fixer не найден"
    fi
    
    # 2. Приводим PHP+HTML через Prettier с php-плагином
    echo "   2. Prettier (PHP)..."
    if [ -f "node_modules/.bin/prettier" ]; then
        if npx prettier --plugin=@prettier/plugin-php --write "$file"; then
            echo "     Prettier завершен"
        else
            echo "     Prettier пропущен (ошибка форматирования PHP)"
        fi
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
    
    # Блокировка, чтобы не запускать несколько add/commit/pull/push параллельно
    if [[ -f "$COMMIT_LOCK_FILE" ]]; then
        existing_pid="$(cat "$COMMIT_LOCK_FILE" 2>/dev/null || echo "")"
        if [[ -n "$existing_pid" ]] && kill -0 "$existing_pid" 2>/dev/null; then
            echo "Коммит уже выполняется другим процессом (PID $existing_pid), пропускаю этот цикл..."
            return
        else
            echo "Обнаружен устаревший lock (PID $existing_pid не активен). Удаляю lock и продолжаю..."
            rm -f "$COMMIT_LOCK_FILE" 2>/dev/null || true
        fi
    fi
    # Пытаемся создать lock атомарно
    if ! ( set -o noclobber; echo $$ > "$COMMIT_LOCK_FILE" ) 2>/dev/null; then
        echo "Не удалось установить lock на коммит, пропускаю..."
        return
    fi
    
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
        echo "Автоматически коммичу и пушу изменения..."
        
        # Коммит‑сообщение сформируем позже, уже из реально проиндексированных файлов
        
        # Умно добавляем только нужные файлы вместо git add .
        if [[ "$LESS_CHANGED" == true ]]; then
            less_to_add=$(echo "$PROCESSED_FILES" | tr '|' '\n' | grep '\.less$' || true)
            if [[ -n "$less_to_add" ]]; then
                echo "LESS файлы и CSS:"
                echo "   └─ Добавляю LESS файлы..."
                echo "$less_to_add" | xargs -I {} git add {}
            fi
            
            # Добавляем соответствующие CSS файлы
            if [[ "$LESS_ALL_BUILT" == true ]]; then
                echo "   └─ Выявлен триггер partial‑ов — добавляю все целевые CSS"
                git add templates/capitalcraft/css/home.css \
                        templates/capitalcraft/css/base.css \
                        templates/capitalcraft/css/critical.css \
                        templates/capitalcraft/css/faq.css || true
            fi
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
            js_to_add=$(echo "$PROCESSED_FILES" | tr '|' '\n' | grep '\.js$' || true)
            if [[ -n "$js_to_add" ]]; then
                echo "JS файлы:"
                echo "   └─ Добавляю JS файлы..."
                echo "$js_to_add" | xargs -I {} git add {}
                echo "   └─ Добавляю bundle: bundle.js"
                git add templates/capitalcraft/js/global/bundle.js
                echo ""
            fi
        fi
        
        if [[ "$PHP_CHANGED" == true ]]; then
            php_to_add=$(echo "$PROCESSED_FILES" | tr '|' '\n' | grep '\.php$' || true)
            if [[ -n "$php_to_add" ]]; then
                echo "PHP файлы:"
                echo "   └─ Добавляю PHP файлы..."
                echo "$php_to_add" | xargs -I {} git add {}
                echo ""
            fi
        fi
        
        # Системные файлы не добавляем автоматически, чтобы избежать лишних коммитов

        # Формируем заголовок коммита из реально проиндексированных файлов (staged)
        build_subject_from_staged() {
            local staged_names
            staged_names=$(git diff --cached --name-status | awk '{print $2}')
            local less_list js_list php_list
            less_list=$(echo "$staged_names" | grep -E '\\.less$' | xargs -I {} basename {} | tr '\n' ' ' || true)
            js_list=$(echo "$staged_names" | grep -E '\\.js$' | xargs -I {} basename {} | tr '\n' ' ' || true)
            php_list=$(echo "$staged_names" | grep -E '\\.php$' | xargs -I {} basename {} | tr '\n' ' ' || true)
            local subj=""
            if [[ -n "$less_list" ]]; then
              subj="LESS: ${less_list}"
            fi
            if [[ -n "$js_list" ]]; then
              if [[ -n "$subj" ]]; then subj+=" | "; fi
              subj+="JS: ${js_list}"
            fi
            if [[ -n "$php_list" ]]; then
              if [[ -n "$subj" ]]; then subj+=" | "; fi
              subj+="PHP: ${php_list}"
            fi
            echo "$subj"
        }

        local commit_message
        commit_message="$(build_subject_from_staged)"
        commit_message+=" | $(date +%H:%M\ %d/%m)"

        echo "Коммит: $commit_message"

        echo "выполняю коммит и пуш..."
        
        # Если нечего коммитить (индекс пуст) — пропускаем коммит/пуш
        if git diff --cached --quiet; then
            echo "Нет изменений для коммита — пропускаю пуш"
            # Сбрасываем флаги и очищаем список обработанных файлов
            LESS_CHANGED=false
            JS_CHANGED=false
            PHP_CHANGED=false
            SYSTEM_CHANGED=false
            LESS_ALL_BUILT=false
            PROCESSED_FILES=""
            rm -f "$COMMIT_LOCK_FILE" 2>/dev/null || true
            return
        fi

        git commit -m "$commit_message"
        # Перед пушем подтягиваем изменения с rebase и автосташем, чтобы снизить конфликты
        git -c rebase.autoStash=true pull --rebase origin dev || true
        git push origin dev

        # Единый финальный блок с результатом и содержимым коммита
        echo "$SEPARATOR"
        echo "✅  ИЗМЕНЕНИЯ УСПЕШНО ОТПРАВЛЕНЫ В DEV ВЕТКУ"
        echo ""
        commit_sha=$(git rev-parse --short HEAD 2>/dev/null || echo "-")
        commit_subject=$(git log -1 --pretty=%s 2>/dev/null || echo "-")
        echo "commit: ${commit_sha} — ${commit_subject}"
        echo ""
        echo "files:"
        git show -1 --name-status --pretty=format: HEAD | sed -E 's/^([AMD])\t(.*)$/- \1   \2/'
        echo "$SEPARATOR"
        
        # удалён старый повторяющийся баннер успеха — используется единый финальный блок выше
        
        # Сбрасываем флаги
        LESS_CHANGED=false
        JS_CHANGED=false
        PHP_CHANGED=false
        SYSTEM_CHANGED=false
        LESS_ALL_BUILT=false
        
        # Очищаем список обработанных файлов
        PROCESSED_FILES=""
        echo "Очистил список обработанных файлов"
        rm -f "$COMMIT_LOCK_FILE" 2>/dev/null || true
    else
        echo "Нет изменений — пропускаю финальные действия"
        rm -f "$COMMIT_LOCK_FILE" 2>/dev/null || true
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
      echo "Изменён системный файл: $file (не обрабатывается)"
      return
      ;;
    *)       
      echo "Изменён файл: $file (не обрабатывается)"
      ;;
  esac

  # Добавляем в PROCESSED_FILES только если файл еще не добавлен
  if [[ "$PROCESSED_FILES" != *"|$file|"* ]]; then
    PROCESSED_FILES="$PROCESSED_FILES|$file|"
    echo "Файл добавлен в PROCESSED_FILES: $file"
  else
    echo "Файл уже в PROCESSED_FILES: $file (компиляция выполнена)"
  fi
}

# --- запуск chokidar с дебаунсом и завершением записи ---
trap 'kill ${chokidar_pid:-0} 2>/dev/null || true; exit' INT TERM EXIT

echo "Запускаю chokidar..."
echo "Ожидаю события..."
echo ""

# Запускаем chokidar и читаем его вывод напрямую
echo "Запускаю chokidar с отладкой..."
echo "Использую дебаунс 800ms для группировки событий..."

# Переменная для отслеживания времени последнего события
last_event_time=0
debounce_delay=800

while IFS= read -r line; do
  # Используем секунды вместо миллисекунд для совместимости с macOS
  current_time=$(date +%s)
  
  echo "Получено событие: $line"
  
  # Формат строки: "change: path" / "add: path" / "unlink: path"
  case "$line" in
    change:*) 
      file=$(echo "$line" | cut -d: -f2- | sed -E 's/^\s+//')
      echo "Обрабатываю изменение: $file"
      process_file "$file" "change" 
      ;;
    add:*)    
      file=$(echo "$line" | cut -d: -f2- | sed -E 's/^\s+//')
      echo "Обрабатываю добавление: $file"
      process_file "$file" "add" 
      ;;
    unlink:*) 
      file=$(echo "$line" | cut -d: -f2- | sed -E 's/^\s+//')
      echo "Обрабатываю удаление: $file"
      process_file "$file" "unlink"
      
      # Автоматически коммитим и пушим удаление файла
      echo "Автоматически коммичу и пушу удаление файла..."
      git add -A
      git commit -m "DELETE: $(basename "$file") | $(date +%H:%M)"
      git -c rebase.autoStash=true pull --rebase origin dev || true
      git push origin dev
      echo "Удаление файла автоматически отправлено в dev ветку!"
      ;;
    *) 
      echo "Неизвестное событие: $line"
      ;;
  esac
  
  # Обновляем время последнего события
  last_event_time=$current_time
  
  # Запускаем таймер для выполнения финальных действий
  (
    sleep 0.8  # 800ms дебаунс
    current_check_time=$(date +%s)
    if [ $((current_check_time - last_event_time)) -ge 1 ]; then
      echo "Дебаунс завершен, выполняю финальные действия..."
      execute_final_actions
    fi
  ) &
  
done < <(npx chokidar-cli \
  "templates/capitalcraft/**" \
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
