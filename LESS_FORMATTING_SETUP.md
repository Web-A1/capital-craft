# Настройка автоматического форматирования LESS файлов

## 🎯 Что настроено для автоматического удаления пробелов

### 1. EditorConfig настройки
```ini
[*]
trim_trailing_whitespace = true  # Автоматически удаляет пробелы в конце строк
indent_style = space             # Использует пробелы вместо табов
indent_size = 2                  # Размер отступа: 2 пробела

[*.{js,jsx,ts,tsx,json,less,css,html,md}]
indent_size = 2                  # Специально для LESS файлов
```

### 2. VS Code настройки
```json
{
  "files.trimTrailingWhitespace": true,    # Удаляет пробелы при сохранении
  "files.insertFinalNewline": true,        # Добавляет пустую строку в конце
  "files.trimFinalNewlines": true,         # Убирает лишние пустые строки
  "[less]": {
    "editor.trimAutoWhitespace": true,     # Автоматически убирает пробелы
    "editor.formatOnSave": true            # Форматирует при сохранении
  }
}
```

### 3. Prettier конфигурация
```json
{
  "printWidth": 80,                        # Максимальная ширина строки
  "tabWidth": 2,                           # Размер таба: 2 пробела
  "useTabs": false,                        # Используем пробелы
  "endOfLine": "lf",                       # Unix-style окончания строк
  "overrides": [
    {
      "files": "*.{css,less,scss}",
      "options": {
        "printWidth": 80,
        "tabWidth": 2,
        "useTabs": false,
        "bracketSpacing": true,             # Пробелы в скобках
        "bracketSameLine": false           # Скобки на новой строке
      }
    }
  ]
}
```

## 🚀 Как это работает

### Автоматическое удаление пробелов при сохранении:
1. **EditorConfig** - удаляет пробелы в конце строк
2. **VS Code** - автоматически убирает лишние пробелы
3. **Prettier** - форматирует код с правильными интервалами

### Что происходит при сохранении LESS файла:
1. Удаляются все пробелы в конце строк
2. Код автоматически форматируется
3. CSS свойства сортируются по алфавиту
4. Устанавливаются правильные отступы
5. Убираются лишние пустые строки

## 🔧 Дополнительные настройки

### Для лучшего отображения пробелов в Cursor:
```json
{
  "editor.renderWhitespace": "boundary",   # Показывает пробелы на границах
  "editor.guides.indentation": true,       # Показывает направляющие отступов
  "editor.rulers": [80]                    # Показывает вертикальную линию на 80 символов
}
```

### Автоматическое исправление при сохранении:
```json
{
  "editor.codeActionsOnSave": {
    "source.fixAll": "explicit",           # Исправляет все проблемы
    "source.organizeImports": "explicit"   # Организует импорты
  }
}
```

## 📋 Примеры форматирования

### До форматирования:
```less
.reviews {
    padding: @section-padding 0;  
    display: flex;
    flex-direction: column;  
    gap: @section-padding;  
}
```

### После форматирования:
```less
.reviews {
  display: flex;
  flex-direction: column;
  gap: @section-padding;
  padding: @section-padding 0;
}
```

## ✅ Проверка работы

### 1. Откройте LESS файл
### 2. Добавьте лишние пробелы:
```less
.reviews {
    padding: @section-padding 0;  
    display: flex;  
    flex-direction: column;  
}
```

### 3. Сохраните файл (Ctrl+S / Cmd+S)
### 4. Проверьте результат:
- Лишние пробелы удалены
- Код отформатирован
- Свойства отсортированы

## 🚨 Устранение проблем

### Пробелы не удаляются:
1. Проверьте настройки EditorConfig
2. Убедитесь, что `files.trimTrailingWhitespace: true`
3. Перезапустите Cursor

### Форматирование не работает:
1. Проверьте, что установлен Prettier
2. Убедитесь, что `editor.formatOnSave: true`
3. Проверьте конфигурацию `.prettierrc`

### Неправильные отступы:
1. Проверьте `indent_size` в EditorConfig
2. Убедитесь, что `tabWidth: 2` в Prettier
3. Проверьте настройки VS Code

## 🔄 Команды для ручного форматирования

```bash
# Отформатировать конкретный LESS файл
npx prettier --write "path/to/file.less"

# Отформатировать все LESS файлы
npx prettier --write "**/*.less"

# Проверить форматирование
npx prettier --check "**/*.less"
```

## ✨ Результат

После настройки LESS файлы будут автоматически:
- ✅ Удалять лишние пробелы при сохранении
- ✅ Форматироваться с правильными отступами
- ✅ Сортировать CSS свойства по алфавиту
- ✅ Устанавливать корректные интервалы
- ✅ Убирать лишние пустые строки
- ✅ Использовать единообразное форматирование
