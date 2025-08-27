#!/bin/bash
set -euo pipefail

# Универсальный мониторинг файлов проекта
# Запуск: ./watch-all.sh (работает в фоне)

echo "🚀 Запуск универсального мониторинга файлов..."
echo "📁 Отслеживаю изменения в проекте..."
echo "🔄 Логика обработки:"
echo "   • LESS файлы → компиляция + commit + push"
echo "   • JS файлы → пересборка + commit + push"
echo "   • PHP файлы → commit + push"
echo "   • sitemap.xml → только commit + push"
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
    if git diff --quiet "$file" 2>/dev/null; then
        if git diff --cached --quiet "$file" 2>/dev/null; then
            return 1  # Нет изменений
        fi
    fi
    return 0  # Есть изменения
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
    echo "🔄 Компилирую LESS..."
    
    npm run less:all
    
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
    
    if [ "$LESS_CHANGED" = true ]; then
        actions+=("LESS compilation")
    fi
    
    if [ "$JS_CHANGED" = true ]; then
        actions+=("JS rebuild")
    fi
    
    if [ "$PHP_CHANGED" = true ]; then
        actions+=("PHP update")
    fi
    
    if [ ${#actions[@]} -gt 0 ]; then
        message=$(IFS=" + "; echo "${actions[*]}")
        commit_and_push "$message"
        
        # Сбрасываем флаги
        LESS_CHANGED=false
        JS_CHANGED=false
        PHP_CHANGED=false
        
        # Очищаем список обработанных файлов
        PROCESSED_FILES=""
        echo "🧹 Очистил список обработанных файлов"
    fi
}

# --- commit/push (оптимизированная версия) ---
commit_and_push() {
  local message="$1"

  # Стадим всё, включая удаления
  git add -A

  # Безопасная проверка на изменения (HEAD может отсутствовать)
  if git diff --cached --quiet; then
    echo "📝 Нет изменений для коммита"
    echo ""; return
  fi

  echo "📝 Коммичу изменения..."
  git commit -m "$message $(date '+%Y-%m-%d %H:%M:%S')"

  echo "🚀 Пушим в dev..."
  for i in 1 2 3; do
    if git push origin dev; then
      echo "✅ Изменения отправлены в dev!"
      echo ""; return
    fi
    echo "⚠️ Push не удался, попытка $i/3 → pull --rebase --autostash…"
    git pull --rebase --autostash origin dev || true
    sleep 2
  done
  echo "❌ Не удалось отправить изменения"; echo ""
}

# --- обработка unlink и игноры ---
process_file() {
  local file="$1"
  local kind="$2"  # add|change|unlink

  echo "🔍 process_file: обработка $kind для файла '$file'"

  # Защита от повторной обработки одного файла в рамках одного цикла
  if [[ "$PROCESSED_FILES" == *"|$file|"* ]]; then
    echo "🔄 Файл уже обработан в этом цикле: $file (пропускаю)"
    return
  fi

  # Игноры выходных/служебных
  case "$file" in
    *.css|*.map|*/bundle.js|*/bundle.min.js) echo "📄 Игнор артефакта: $file"; return ;;
  esac
  [[ "$file" == *node_modules/* || "$file" == *vendor/* || "$file" == *".git/"* || "$file" == *".vscode/"* || "$file" == "format-php.sh" ]] && { echo "📄 Игнор служебного: $file"; return; }

  # Для unlink файла уже нет на диске — всё равно коммитим удаление
  if [[ "$kind" == "unlink" ]]; then
    echo "🗑️ Удалён файл: $file"
    commit_and_push "Remove file"
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
    sitemap.xml)
      echo "🗺️ Изменен sitemap.xml: $file"
      ;;
    *)       
      echo "📄 Изменён файл: $file (не обрабатывается)"
      ;;
  esac

  # Добавляем в PROCESSED_FILES только после успешной обработки
  PROCESSED_FILES="$PROCESSED_FILES|$file|"
  echo "✅ Файл добавлен в PROCESSED_FILES: $file"
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
  "sitemap.xml" \
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
