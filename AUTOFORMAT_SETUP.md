# Полная настройка автоформатирования в Cursor

## 🎯 Что настроено

### Основные инструменты
- **Prettier** - основной форматтер для всех типов файлов
- **@prettier/plugin-php** - специализированный плагин для PHP
- **prettier-plugin-css-order** - сортировка CSS/LESS свойств
- **prettier-plugin-organize-attributes** - организация HTML атрибутов

### Поддерживаемые типы файлов
- ✅ **PHP** - полная поддержка с автоматическим форматированием
- ✅ **JavaScript/TypeScript** - полная поддержка
- ✅ **LESS/CSS** - с сортировкой свойств
- ✅ **HTML** - с организацией атрибутов
- ✅ **JSON** - полная поддержка
- ✅ **Markdown** - полная поддержка

## ⚙️ Конфигурация

### .prettierrc
```json
{
  "semi": true,
  "singleQuote": true,
  "printWidth": 80,
  "tabWidth": 2,
  "useTabs": false,
  "trailingComma": "es5",
  "plugins": [
    "@prettier/plugin-php",
    "prettier-plugin-css-order",
    "prettier-plugin-organize-attributes"
  ],
  "cssDeclarationSorterOrder": "alphabetical",
  "htmlWhitespaceSensitivity": "css"
}
```

### .vscode/settings.json
```json
{
  "editor.formatOnSave": true,
  "editor.defaultFormatter": "esbenp.prettier-vscode",
  "editor.codeActionsOnSave": {
    "source.fixAll": "explicit"
  },
  "[php]": {
    "editor.defaultFormatter": "esbenp.prettier-vscode"
  },
  "[less]": {
    "editor.defaultFormatter": "esbenp.prettier-vscode",
    "editor.formatOnSave": true
  },
  "[css]": {
    "editor.defaultFormatter": "esbenp.prettier-vscode",
    "editor.formatOnSave": true
  },
  "[html]": {
    "editor.defaultFormatter": "esbenp.prettier-vscode",
    "editor.formatOnSave": true
  }
}
```

## 🚀 Как использовать

### Автоматическое форматирование при сохранении
1. Откройте любой файл (PHP, JS, LESS, HTML)
2. Внесите изменения
3. Сохраните файл (Ctrl+S / Cmd+S)
4. Код автоматически отформатируется

### Ручное форматирование
```bash
# Проверить форматирование всех файлов
npm run format:check

# Отформатировать все файлы
npm run format
```

### Форматирование конкретных файлов
```bash
# Только PHP файлы
npx prettier --write "**/*.php"

# Только LESS файлы
npx prettier --write "**/*.less"

# Только HTML файлы
npx prettier --write "**/*.html"
```

## 🔧 Установка расширений Cursor

### Обязательные расширения
1. **Prettier - Code formatter** (esbenp.prettier-vscode)
2. **LESS** (ms-vscode.vscode-less)
3. **PHP Intelephense** (bmewburn.vscode-intelephense)

### Дополнительные расширения (рекомендуемые)
1. **Auto Rename Tag** (formulahendry.auto-rename-tag)
2. **Bracket Pair Colorizer** (coenraads.bracket-pair-colorizer-2)
3. **Path Intellisense** (christian-kohler.path-intellisense)

## 📋 Правила форматирования

### PHP
- Отступы: 4 пробела
- Ширина строки: 80 символов
- Полуточия в конце строк
- Одинарные кавычки

### LESS/CSS
- Отступы: 2 пробела
- Свойства отсортированы по алфавиту
- Автоматическое удаление лишних пробелов

### HTML
- Отступы: 2 пробела
- Атрибуты организованы логически
- Автоматическое закрытие тегов

### JavaScript
- Отступы: 2 пробела
- Ширина строки: 80 символов
- Полуточия в конце строк
- Одинарные кавычки

## 🚨 Устранение проблем

### Prettier не форматирует файлы
1. Проверьте, что установлено расширение Prettier
2. Убедитесь, что файл не в `.prettierignore`
3. Проверьте синтаксис файла

### Автоформатирование не работает при сохранении
1. Проверьте настройки в `.vscode/settings.json`
2. Убедитесь, что `editor.formatOnSave: true`
3. Перезапустите Cursor

### Ошибки парсинга
1. Проверьте, что установлены нужные плагины
2. Убедитесь, что версии совместимы
3. Проверьте конфигурацию в `.prettierrc`

## 🔄 Обновление

```bash
# Обновить Prettier и плагины
npm update prettier @prettier/plugin-php prettier-plugin-css-order prettier-plugin-organize-attributes

# Проверить версии
npm list prettier @prettier/plugin-php prettier-plugin-css-order prettier-plugin-organize-attributes
```

## 📚 Дополнительные ресурсы

- [Prettier Documentation](https://prettier.io/docs/en/)
- [PHP Plugin for Prettier](https://github.com/prettier/plugin-php)
- [CSS Order Plugin](https://github.com/Siilwyn/prettier-plugin-css-order)
- [HTML Attributes Plugin](https://github.com/niklaspor/prettier-plugin-organize-attributes)

## ✨ Преимущества текущей настройки

1. **Единообразие** - все файлы форматируются по одним правилам
2. **Автоматизация** - форматирование при сохранении
3. **Специализация** - каждый тип файла имеет свой плагин
4. **Производительность** - быстрая работа без задержек
5. **Настраиваемость** - легко изменить правила форматирования
