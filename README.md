# Структура проекта Capital Craft

## Общий обзор
- CMS: **Joomla 5.3.2**
- Пользовательский шаблон: `templates/capitalcraft`
- Сборка: Node.js scripts (`npm run less:all`, `npm run js:build`)
- Все стили пишутся на **Less**, все скрипты — нативный **JavaScript**.

## Основные директории шаблона
| Путь | Назначение |
| --- | --- |
| `css/` | Скомпилированные стили. Не редактировать вручную. |
| `data/` | Вспомогательные данные. |
| `html/` | Override'ы стандартных компонентов Joomla (`com_content`, `mod_breadcrumbs` и т.п.). |
| `images/<страница>/` | Изображения страниц (`home`, `faq`, `favicon`). Новые папки не создавать без крайней необходимости. |
| `js/global/` | Глобальные модули (`script.js` — точка входа, собирается в `bundle.js`). |
| `js/pages/<page>/` | Локальные скрипты страниц. |
| `less/` | Исходники стилей. Частичные файлы начинаются с `_`. Основные: `base.less`, `critical.less`, `home.less`, `faq.less`. |
| `less/pages/<page>/` | Подключаемые стили блоков страниц. |
| `pages/<page>/` | PHP‑шаблоны блоков страниц. |
| `partials/` | Общие части шаблона (`_header.php`, `_footer.php`, `_modal.php`). |
| `index.php` | Точка сборки страниц. Подключает блоки и стили по условиям (`$isHome`, `$isFaq`, …). |

## Правила разработки
- **CSS**:
  - Использовать существующие переменные из `less/_variables.less`.
  - Вложенность только через `&`; медиазапросы сразу после класса с миксином `.media(@breakpoint, @rules)`.
- **JavaScript**:
  - Структура модульная: `js/global/` для общих модулей, `js/pages/<page>/` для конкретных страниц.
  - Для глобальных изменений пересоберите бандл: `npm run js:build`.
- **Изображения**: складывать в `images/<page>/`, новых папок избегать.

## Чек‑лист нового блока
1. PHP: `templates/capitalcraft/pages/<page>/<block>.php`
2. Less: `templates/capitalcraft/less/pages/<page>/_<block>.less` и `@import` в `<page>.less`
3. JS: `templates/capitalcraft/js/pages/<page>/<block>.js`
4. Изображения: `templates/capitalcraft/images/<page>/`
5. Подключение в `templates/capitalcraft/index.php`
6. После правок выполнить `npm run less:all` (и при необходимости `npm run js:build`)

## Добавление новой страницы
1. Создать PHP‑блоки в `pages/<page>/`.
2. Создать стили `less/<page>.less` и папку `less/pages/<page>/` для блоков.
3. JS: `js/pages/<page>/`.
4. Изображения: `images/<page>/`.
5. В `index.php` добавить условие `$is<Page>` и подключить CSS/JS.

## Текущие страницы и блоки
- **home**: `hero`, `partners`, `philosophy`, `team`, `faq-home`, `products`, `show_case`, `reviews`
- **faq**: стили и JS есть, PHP‑блоков нет (используется компонент содержимого).

## Сборка и проверка
- Скомпилировать все стили: `npm run less:all`
- Пересобрать глобальный JS: `npm run js:build`
- Тесты (запускают компиляцию less): `npm test`
