# Capital Craft

Корпоративный сайт инвестиционного агентства Capital Craft: https://capital-craft.ru

## Кратко о проекте
- Моя роль: fullstack-разработчик.
- Ключевые SEO-разделы: https://capital-craft.ru, https://capital-craft.ru/blog, https://capital-craft.ru/faq
- Статус: проект приостановлен по инициативе заказчика (реализовано ~90%)
- Продакшен-окружение (на 25.03.2026): Joomla 6.0.3, PHP 8.4
- Проект реализован как кастомный маркетинговый сайт с упором на SEO-архитектуру, скорость загрузки и mobile-first UX.

Сайт разработан на чистом коде, без готовых шаблонов и конструкторов: собственный дизайн, верстка и серверная логика. Визуальная концепция отражает характер бизнеса: крафтовый подход, индивидуальность, точность и надежность без агрессивной маркетинговой подачи.

## Что реализовано технически
- Разработан кастомный production-шаблон `templates/capitalcraft` без использования готовых шаблонов и конструкторов.
- Реализован серверный слой шаблона (`SeoHelper`, `TagFilterHelper`, `FaqHelper`, `RelatedHelper`) для SEO-логики, теговой фильтрации и подбора связанных материалов.
- Настроены Joomla overrides (`com_content`, `com_finder`, `com_tags`, `mod_breadcrumbs`) для управления выводом контента и поисковой навигацией под задачи проекта.
- Реализована фильтрация по тегам (`?tag=...`) для связки страниц `/blog` и `/faq`, AJAX-подгрузка материалов, синхронизация URL через History API и блоки релевантных материалов внутри страниц.
- Используется SEO-разметка и метаданные (`Article`, `FAQPage`, `BreadcrumbList`, Open Graph, Twitter meta) для корректной индексации и представления страниц в поиске и социальных сетях.

## Интеграция заявок в Telegram
Форма обратной связи отправляет данные напрямую в Telegram Bot API:
- frontend: `templates/capitalcraft/js/global/form-submit.js`
- backend endpoint: `templates/capitalcraft/partials/_send_to_telegram.php`
- config: `templates/capitalcraft/telegram_config.php`

В штатных условиях заявка обычно доставляется в Telegram-чат за ~300-600 мс.

## Производительность
Mobile-first архитектура подтверждена замером PageSpeed Insights (25.03.2026):
- Mobile: Performance `97`, Accessibility `89`, Best Practices `96`, SEO `100`
- Desktop: Performance `100`, Accessibility `89`, Best Practices `96`, SEO `100`

Проверка: https://pagespeed.web.dev/report?url=https://capital-craft.ru

## CI/CD и процесс разработки
1. Разработка ведется локально.
2. Изменения публикуются в ветку `dev`.
3. После push в `dev` запускается автоматический деплой на DEV-поддомен `div.capital-craft.ru` (`.github/workflows/deploy-dev.yml`).
4. На DEV-поддомене выполняется проверка функционала и интерфейса.
5. После проверки ветка `dev` объединяется с `main`.
6. После обновления `main` (merge из `dev`) запускается автоматический деплой на основной домен `capital-craft.ru` (`.github/workflows/deploy-prod.yml`).
7. DEV и PROD используют раздельные базы данных, поэтому тестовые изменения не влияют на production-данные.

## Ограничения (известные незавершенные части)
- Пункты меню `/team` и `/cases` сейчас ведут на якорные секции главной страницы, а не на отдельные страницы (временный компромисс из-за приостановки проекта).
- Не вынесена отдельная страница `/team`.
- Не реализована отдельная страница `/cases` с планировавшейся серверной логикой тегов в связке с `/blog` и `/faq`.

## Структура репозитория
- `.github/workflows/` — CI/CD (DEV и PROD)
- `templates/capitalcraft/` — основной кастомный шаблон
- `templates/capitalcraft/helpers/` — серверная бизнес-логика шаблона
- `templates/capitalcraft/html/` — Joomla overrides
- `scripts/` — служебные задачи проекта

## Полезные команды
- `npm run less:all` — компиляция Less
- `npm run js:build` — сборка JS
- `npm run build` — полная сборка
- `npm run sitemap` — генерация sitemap
- `npm run format` / `npm run format:check` — форматирование
- `npm run format:php:check` — проверка PHP-форматирования
