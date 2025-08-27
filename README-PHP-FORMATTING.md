# Форматирование PHP файлов в Capital Craft

## Обзор

Для автоматического форматирования PHP файлов (включая файлы с HTML) используется **PHP-CS-Fixer** вместо Prettier, который не поддерживает PHP.

## Установка

PHP-CS-Fixer уже установлен в проекте через Composer:

```bash
composer require --dev friendsofphp/php-cs-fixer
```

## Конфигурация

Конфигурация находится в файле `.php-cs-fixer.php` и включает:

- **PSR-12 стандарты** для PHP кода
- **Специальные правила** для HTML в PHP
- **Автоматическое обнаружение** PHP файлов в папке `templates/capitalcraft`
- **Ограниченная область** - только файлы шаблона, где ведется разработка

## Использование

### Команды npm

```bash
# Форматировать все PHP файлы
npm run format:php

# Проверить форматирование без изменений
npm run format:php:check

# Предварительный просмотр изменений
npm run format:php:dry
```

### Прямые команды

```bash
# Форматировать все файлы
vendor/bin/php-cs-fixer fix

# Форматировать конкретный файл
vendor/bin/php-cs-fixer fix path/to/file.php

# Предварительный просмотр изменений
vendor/bin/php-cs-fixer fix --dry-run --diff

# Форматировать конкретную директорию
vendor/bin/php-cs-fixer fix components/
```

## Что форматируется

### PHP код

- Отступы и пробелы
- Синтаксис массивов (`[]` вместо `array()`)
- Пробелы вокруг операторов
- Одинарные кавычки для строк
- PSR-12 стандарты

### HTML в PHP

- HTML остается читаемым
- PHP теги не ломаются
- Сохраняется структура

## Примеры

### До форматирования

```php
<?php
$test=array('key'=>'value');
if($test['key']=='value'){
echo "Test";
}
?>
<div><?php echo $test['key']; ?></div>
```

### После форматирования

```php
<?php
$test = ['key' => 'value'];
if ($test['key'] == 'value') {
    echo 'Test';
}
?>
<div><?php echo $test['key']; ?></div>
```

## Интеграция с Cursor

### Автоформатирование при сохранении

Добавьте в настройки Cursor:

```json
{
  "editor.formatOnSave": true,
  "php-cs-fixer.executablePath": "./vendor/bin/php-cs-fixer",
  "php-cs-fixer.config": ".php-cs-fixer.php"
}
```

### Горячие клавиши

- `Cmd+Shift+P` → "Format Document" → выберите PHP-CS-Fixer
- `Cmd+Shift+P` → "Format Selection" → для частичного форматирования

## Область действия

PHP-CS-Fixer настроен на работу **только с файлами в папке `templates/capitalcraft`**:

- ✅ `templates/capitalcraft/pages/` - страницы
- ✅ `templates/capitalcraft/html/` - HTML шаблоны
- ✅ `templates/capitalcraft/data/` - данные
- ✅ `templates/capitalcraft/partials/` - частичные шаблоны
- ❌ `components/` - компоненты Joomla
- ❌ `modules/` - модули Joomla
- ❌ `plugins/` - плагины Joomla

## Исключения

Файлы исключаются из форматирования:

- `vendor/` - зависимости Composer
- `node_modules/` - зависимости npm
- `media/vendor/` - медиа зависимости
- `media/node_modules/` - медиа npm зависимости

## Troubleshooting

### Конфликты правил

Если возникают ошибки с правилами, проверьте файл `.php-cs-fixer.php` на конфликты.

### Проблемы с кэшем

Кэш отключен по умолчанию для лучшей совместимости.

### Большие файлы

Для больших файлов может потребоваться больше времени. Используйте `--stop-on-violation` для остановки при первой ошибке.

## Сравнение с Prettier

| Функция       | Prettier           | PHP-CS-Fixer        |
| ------------- | ------------------ | ------------------- |
| PHP файлы     | ❌ Не поддерживает | ✅ Полная поддержка |
| HTML в PHP    | ❌ Ломает PHP код  | ✅ Сохраняет PHP    |
| PSR стандарты | ❌ Нет             | ✅ PSR-12           |
| Настройка     | ⚠️ Ограниченная    | ✅ Гибкая           |
| Скорость      | ✅ Быстро          | ✅ Быстро           |

## Рекомендации

1. **Используйте PHP-CS-Fixer** для всех PHP файлов
2. **Оставьте Prettier** для LESS/CSS/JS файлов
3. **Настройте автоформатирование** в Cursor
4. **Запускайте форматирование** перед коммитом

## Полезные ссылки

- [PHP-CS-Fixer документация](https://cs.symfony.com/)
- [PSR-12 стандарты](https://www.php-fig.org/psr/psr-12/)
- [Composer документация](https://getcomposer.org/)
