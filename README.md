CMS Joomla 5.3.2, шаблон capitalcraft.
Все стили только в Less. Вложенность через &, медиа-запросы под каждым классом.
Использовать существующие переменные, классы, структуру.

JS — модульно: js/global/ (общие), js/pages/<страница>/ (локальные).
Изображения: images/<страница>/. Новые папки не создавать без необходимости.
Новые блоки: pages/<страница>/<block>.php, стили в less/<страница>.less, JS в js/pages/<страница>/.
Любые изменения — только на основе реально существующего кода.

## Чек-лист блока

- `templates/capitalcraft/pages/<страница>/<block>.php`
- `templates/capitalcraft/less/pages/<страница>/_<block>.less` + `@import` в `<страница>.less`
- `templates/capitalcraft/js/pages/<страница>/<block>.js`
- изображения в `templates/capitalcraft/images/<страница>/`
- подключение в `templates/capitalcraft/index.php`

После правок выполните `npm run less:all` и при необходимости пересоберите JS.

# Стиль и адаптив

- Используйте существующие переменные из файла `templates/capitalcraft/less/_variables.less`.
- Для адаптивной вёрстки применяйте миксин `.media(@breakpoint, @rules)`.

# Пример подключения в index.php:

$document->addStyleSheet('/templates/capitalcraft/css/<page>.css');

<script src="templates/capitalcraft/js/pages/<page>/<block>.js"></script>

Для каждой новой страницы необходимо создавать условие `$is<ИмяСтраницы>`.
