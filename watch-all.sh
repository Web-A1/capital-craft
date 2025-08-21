#!/bin/bash
set -euo pipefail

# Универсальный мониторинг файлов проекта
# Запуск: ./watch-all.sh (работает в фоне)

echo "🚀 Запуск универсального мониторинга файлов..."
echo "📁 Отслеживаю изменения в проекте..."
echo "🔄 Логика обработки:"
echo "   • LESS файлы → компиляция + commit + push"
echo "   • JS файлы → пересборка + commit + push"
echo "   • PHP файлы → только commit + push"
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

# Функция для обработки LESS файлов
handle_less() {
    local file="$1"
    echo "🎨 Изменен LESS файл: $file"
    echo "🔄 Компилирую LESS..."
    
    npm run less:all
    
    if [ $? -eq 0 ]; then
        echo "✅ LESS компиляция завершена!"
        commit_and_push "LESS compilation"
    else
        echo "❌ Ошибка компиляции LESS!"
    fi
}

# Функция для обработки JS файлов
handle_js() {
    local file="$1"
    echo "⚡ Изменен JS файл: $file"
    echo "🔄 Пересобираю JavaScript..."
    
    npm run js:build
    
    if [ $? -eq 0 ]; then
        echo "✅ JavaScript пересборка завершена!"
        commit_and_push "JS rebuild"
    else
        echo "❌ Ошибка пересборки JavaScript!"
    fi
}

# Функция для обработки PHP файлов
handle_php() {
    local file="$1"
    echo "🐘 Изменен PHP файл: $file"
    echo "📝 Только коммит и push..."
    
    commit_and_push "PHP update"
}

# --- commit/push (исправления) ---
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

  # Игноры выходных/служебных
  case "$file" in
    *.css|*.map|*/bundle.js|*/bundle.min.js) echo "📄 Игнор артефакта: $file"; return ;;
  esac
  [[ "$file" == *node_modules/* || "$file" == *vendor/* || "$file" == *".git/"* ]] && { echo "📄 Игнор служебного: $file"; return; }

  # Для unlink файла уже нет на диске — всё равно коммитим удаление
  if [[ "$kind" == "unlink" ]]; then
    echo "🗑️ Удалён файл: $file"
    commit_and_push "Remove file"
    return
  fi

  # Для add/change проверяем расширение
  if [[ ! -f "$file" ]]; then return; fi

  case "$file" in
    *.less)  handle_less "$file" ;;
    *.js)
      if [[ "$file" =~ templates/capitalcraft/js/ ]]; then
        handle_js "$file"
      else
        echo "📄 Игнор JS вне src: $file"
      fi
      ;;
    *.php)   handle_php "$file" ;;
    *)       echo "📄 Изменён файл: $file (не обрабатывается)";;
  esac
}

# --- запуск chokidar с дебаунсом и завершением записи ---
trap 'kill ${chokidar_pid:-0} 2>/dev/null || true; exit' INT TERM EXIT

echo "🔄 Запускаю chokidar..."
echo "📝 Ожидаю события..."
echo ""

# Запускаем chokidar и читаем его вывод напрямую
while IFS= read -r line; do
  # Формат строки: "change: path" / "add: path" / "unlink: path"
  case "$line" in
    change:*) file="${line#change: }"; process_file "$file" "change" ;;
    add:*)    file="${line#add: }";    process_file "$file" "add" ;;
    unlink:*) file="${line#unlink: }"; process_file "$file" "unlink" ;;
    *) : ;;
  esac
done < <(npx chokidar-cli \
  "templates/capitalcraft/less/**" \
  "templates/capitalcraft/js/**" \
  "templates/capitalcraft/**" \
  --ignore "**/*.css" \
  --ignore "**/*.map" \
  --ignore "**/*bundle.js" \
  --ignore "**/node_modules/**" \
  --ignore "**/.git/**" \
  --ignore "**/vendor/**" \
  --await-write-finish 200 \
  --debounce 800 \
  --initial)
