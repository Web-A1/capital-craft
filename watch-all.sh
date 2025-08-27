#!/bin/bash
set -euo pipefail

# Универсальный мониторинг файлов проекта
# Запуск: ./watch-all.sh (работает в фоне)

echo "🚀 Запуск универсального мониторинга файлов..."
echo "📁 Отслеживаю изменения в проекте..."
echo "🔄 Логика обработки:"
echo "   • LESS файлы → форматирование + компиляция + подготовка к коммиту"
echo "   • JS файлы → форматирование + пересборка + подготовка к коммиту"
echo "   • PHP файлы → форматирование + подготовка к коммиту"
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

# Защита от повторной обработки одного файла в рамках одного цикла
PROCESSED_FILES=""

# Функция для проверки реальных изменений в git
has_git_changes() {
    local file="$1"
    
    # Простая проверка: если файл существует и не пустой, считаем измененным
    if [ -f "$file" ] && [ -s "$file" ]; then
        return 0  # Есть изменения
    fi
    
    return 1  # Нет изменений
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
    
    echo "🎨 Изменен LESS файл: $file"
    echo "🔄 Запускаю форматирование..."
    
    # 1. Форматируем LESS код
    echo "   1️⃣ Prettier..."
    if [ -f "node_modules/.bin/prettier" ]; then
        npx prettier --write "$file"
        echo "      ✅ Prettier завершен"
    else
        echo "      ❌ Prettier не найден"
    fi
    
    echo "🔄 Умная компиляция LESS..."
    
    compile_less_file "$file"
    
    if [ $? -eq 0 ]; then
        echo "✅ LESS компиляция завершена!"
        LESS_CHANGED=true
    else
        echo "❌ Ошибка компиляции LESS!"
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
    
    echo "⚡ Изменен JS файл: $file"
    echo "🔄 Запускаю форматирование..."
    
    # 1. Форматируем JavaScript код
    echo "   1️⃣ Prettier..."
    if [ -f "node_modules/.bin/prettier" ]; then
        npx prettier --write "$file"
        echo "      ✅ Prettier завершен"
    else
        echo "      ❌ Prettier не найден"
    fi
    
    echo "🔄 Пересобираю JavaScript..."
    
    npm run js:build
    
    if [ $? -eq 0 ]; then
        echo "✅ JavaScript пересборка завершена!"
        JS_CHANGED=true
    else
        echo "❌ Ошибка пересборки JavaScript!"
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
    
    echo "🐘 Изменен PHP файл: $file"
    echo "🔄 Запускаю форматирование..."
    
    # 1. Форматируем PHP код
    echo "   1️⃣ PHP CS Fixer..."
    if command -v php-cs-fixer >/dev/null 2>&1; then
        php-cs-fixer fix "$file" --using-cache=no
        echo "      ✅ PHP CS Fixer завершен"
    else
        echo "      ❌ PHP CS Fixer не найден"
    fi
    
    # 2. Форматируем HTML код
    echo "   2️⃣ Prettier..."
    if [ -f "node_modules/.bin/prettier" ]; then
        npx prettier --write "$file"
        echo "      ✅ Prettier завершен"
    else
        echo "      ❌ Prettier не найден"
    fi
    
    echo "🎨 Форматирование PHP файла завершено!"
    
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
    
    if [ ${#actions[@]} -gt 0 ]; then
        message=$(IFS=" + "; echo "${actions[*]}")
        prepare_for_commit "$message" "$changed_file"
        
        # Автоматически коммитим и пушим изменения
        echo "🚀 Автоматически коммичу и пушу изменения..."
        
        # Создаем информативное сообщение коммита
        local commit_message=""
        if [[ "$LESS_CHANGED" == true ]]; then
            commit_message="LESS: "
            # Получаем только имена файлов (без путей)
            local less_files=$(echo "$PROCESSED_FILES" | tr '|' '\n' | grep '\.less$' | sed 's/.*\///' | tr '\n' ' ')
            commit_message+="$less_files"
        elif [[ "$JS_CHANGED" == true ]]; then
            commit_message="JS: "
            local js_files=$(echo "$PROCESSED_FILES" | tr '|' '\n' | grep '\.js$' | sed 's/.*\///' | tr '\n' ' ')
            commit_message+="$js_files"
        elif [[ "$PHP_CHANGED" == true ]]; then
            commit_message="PHP: "
            local php_files=$(echo "$PROCESSED_FILES" | tr '|' '\n' | grep '\.php$' | sed 's/.*\///' | tr '\n' ' ')
            commit_message+="$php_files"
        fi
        
        # Добавляем временную метку в формате: время_день/месяц
        commit_message+=" | $(date +%H:%M:%S_%d/%m)"
        
        # Добавляем информацию об изменениях
        if [[ "$LESS_CHANGED" == true ]]; then
            # Получаем номера измененных строк для LESS файлов
            local less_file=$(echo "$PROCESSED_FILES" | tr '|' '\n' | grep '\.less$' | head -1)
            if [[ -n "$less_file" ]]; then
                local changed_lines=$(git diff --unified=0 "$less_file" | grep '^@@' | sed 's/^@@ -[0-9,]* +\([0-9,]*\) @@.*/\1/' | tr ',' ' ' | tr '\n' ' ')
                if [[ -n "$changed_lines" ]]; then
                    commit_message+=" | lines: $changed_lines"
                fi
            fi
        elif [[ "$JS_CHANGED" == true ]]; then
            local js_file=$(echo "$PROCESSED_FILES" | tr '|' '\n' | grep '\.js$' | head -1)
            if [[ -n "$js_file" ]]; then
                local changed_lines=$(git diff --unified=0 "$js_file" | grep '^@@' | sed 's/^@@ -[0-9,]* +\([0-9,]*\) @@.*/\1/' | tr ',' ' ' | tr '\n' ' ')
                if [[ -n "$changed_lines" ]]; then
                    commit_message+=" | lines: $changed_lines"
                fi
            fi
        elif [[ "$PHP_CHANGED" == true ]]; then
            local php_file=$(echo "$PROCESSED_FILES" | tr '|' '\n' | grep '\.php$' | head -1)
            if [[ -n "$php_file" ]]; then
                local changed_lines=$(git diff --unified=0 "$php_file" | grep '^@@' | sed 's/^@@ -[0-9,]* +\([0-9,]*\) @@.*/\1/' | tr ',' ' ' | tr '\n' ' ')
                if [[ -n "$changed_lines" ]]; then
                    commit_message+=" | lines: $changed_lines"
                fi
            fi
        fi
        
        echo "📝 Сообщение коммита: $commit_message"
        
        git add .
        git commit -m "$commit_message"
        git push origin dev
        echo "✅ Изменения автоматически отправлены в dev ветку!"
        
        # Сбрасываем флаги
        LESS_CHANGED=false
        JS_CHANGED=false
        PHP_CHANGED=false
        
        # Очищаем список обработанных файлов
        PROCESSED_FILES=""
        echo "🧹 Очистил список обработанных файлов"
    fi
}

# --- подготовка к коммиту (без автоматического push) ---
prepare_for_commit() {
  local message="$1"
  local changed_file="$2"

  echo "📝 Подготавливаю изменения к коммиту..."
  
  # Очищаем staged area от предыдущих изменений
  git reset HEAD
  
  # Добавляем файлы из PROCESSED_FILES (файлы, которые мы реально обработали)
  if [[ -n "$PROCESSED_FILES" ]]; then
    echo "📦 Добавляю обработанные файлы:"
    
    # Разбиваем PROCESSED_FILES на массив
    IFS='|' read -ra FILES <<< "$PROCESSED_FILES"
    
    for file in "${FILES[@]}"; do
      if [[ -n "$file" ]]; then
        echo "   • $file"
        git add "$file"
        
        # Для LESS файлов добавляем соответствующие CSS
        if [[ "$file" == *".less" ]]; then
          if [[ "$file" == *"/pages/home/"* ]]; then
            echo "   • templates/capitalcraft/css/home.css"
            git add templates/capitalcraft/css/home.css
          elif [[ "$file" == *"/base.less" ]]; then
            echo "   • templates/capitalcraft/css/base.css"
            git add templates/capitalcraft/css/base.css
          elif [[ "$file" == *"/critical.less" ]]; then
            echo "   • templates/capitalcraft/css/critical.css"
            git add templates/capitalcraft/css/critical.css
          elif [[ "$file" == *"/faq.less" ]]; then
            echo "   • templates/capitalcraft/css/faq.css"
            git add templates/capitalcraft/css/faq.css
          fi
        fi
      fi
    done
  fi
  

  
  # Показываем количество изменений
  local changed_files=$(git diff --cached --name-only | wc -l)
  echo "📊 Готово к коммиту: $changed_files файлов изменено"
  
  # Показываем список изменений
  echo "📝 Измененные файлы:"
  git diff --cached --name-only | sed 's/^/   • /'
  
  echo ""
  echo "🚀 Для отправки изменений выполните:"
  echo "   npm run push:dev"
  echo ""
}

# --- обработка unlink и игноры ---
process_file() {
  local file="$1"
  local kind="$2"  # add|change|unlink

  echo "🔍 process_file: обработка $kind для файла '$file'"

  # Защита от повторной обработки одного файла в рамках одного цикла
  # НО для LESS файлов разрешаем повторную обработку (компиляция при каждом сохранении)
  if [[ "$PROCESSED_FILES" == *"|$file|"* && "$file" != *".less" ]]; then
    echo "🔄 Файл уже обработан в этом цикле: $file (пропускаю)"
    return
  fi

  # Игноры выходных/служебных
  case "$file" in
    *.css|*.map|*/bundle.js|*/bundle.min.js) echo "📄 Игнор артефакта: $file"; return ;;
    .prettierrc|.php-cs-fixer.php|.editorconfig|.gitignore|.cursorrules|README*.md|format-php.sh|temp_file) echo "📄 Игнор конфигурации: $file"; return ;;
  esac
  [[ "$file" == *node_modules/* || "$file" == *vendor/* || "$file" == *".git/"* || "$file" == *".vscode/"* ]] && { echo "📄 Игнор служебного: $file"; return; }

  # Для unlink файла уже нет на диске — всё равно коммитим удаление
  if [[ "$kind" == "unlink" ]]; then
    echo "🗑️ Удалён файл: $file"
    prepare_for_commit "Remove file"
    return
  fi

  # Для add/change проверяем расширение
  if [[ ! -f "$file" ]]; then 
    echo "❌ Файл не найден: $file"
    return
  fi

  echo "✅ Файл найден, определяю тип..."

  # Сначала обрабатываем файл, потом добавляем в PROCESSED_FILES
  case "$file" in
    *.less)  
      echo "🎨 Обрабатываю как LESS файл: $file"
      handle_less "$file" 
      ;;
    *.js)
      echo "⚡ Обрабатываю как JS файл: $file"
      handle_js "$file"
      ;;
    *.php)   
      echo "🐘 Обрабатываю как PHP файл: $file"
      handle_php "$file" 
      ;;

    *)       
      echo "📄 Изменён файл: $file (не обрабатывается)"
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
  "templates/capitalcraft/less/**" \
  "templates/capitalcraft/js/**/*.js" \
  "templates/capitalcraft/**/*.php" \

  --ignore "**/*.css" \
  --ignore "**/*.map" \
  --ignore "**/*bundle.js" \
  --ignore "**/node_modules/**" \
  --ignore "**/.git/**" \
  --ignore "**/vendor/**" \
  --ignore "**/.vscode/**" \
  --ignore "format-php.sh" \
  --ignore ".php-cs-fixer.php" \
  --ignore ".prettierrc" \
  --await-write-finish 200 \
  --debounce 800)
