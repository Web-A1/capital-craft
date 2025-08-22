# MCP Серверы - Настройка

## PageSpeed MCP Server

### Что это такое

PageSpeed MCP Server - это сервер для интеграции с Google PageSpeed Insights через Model Context Protocol (MCP). Он позволяет анализировать производительность веб-сайтов прямо в Cursor.

### Возможности

- **Анализ производительности:** FCP, LCP, TTI, TBT, CLS, Speed Index, TTFB
- **Оценка лучших практик:** HTTPS, JavaScript ошибки, консольные предупреждения
- **SEO анализ:** Meta теги, robots.txt, структурированные данные
- **Аудит доступности:** ARIA атрибуты, контраст цветов, иерархия заголовков
- **Оптимизация ресурсов:** изображения, JavaScript, CSS, кэширование

### Установка

PageSpeed MCP Server уже установлен в проекте:

```bash
npm install github:PhialsBasement/Pagespeed-MCP-Server
```

### Конфигурация

Файл `mcp.json` уже настроен:

```json
{
  "mcpServers": {
    "pagespeed": {
      "command": "node",
      "args": ["node_modules/mcp-pagespeed-server/dist/index.js"]
    }
  }
}
```

---

## Perplexity MCP Server - Ручная настройка

## Что это такое

Perplexity MCP Server - это сервер для интеграции с Perplexity AI API через Model Context Protocol (MCP). Он позволяет использовать возможности Perplexity (веб-поиск, глубокое исследование) прямо в Cursor.

## Что нужно сделать вручную

### 1. Получение API ключа Perplexity

1. Перейдите на [https://www.perplexity.ai/settings/api](https://www.perplexity.ai/settings/api)
2. Войдите в свой аккаунт (или создайте новый)
3. Нажмите "Create API Key"
4. Скопируйте полученный ключ

### 2. Установка MCP сервера

После получения API ключа, установите сервер:

```bash
npm install perplexity-mcp-server
```

### 3. Создание конфигурации

Создайте файл `mcp.json` в корне проекта:

```json
{
  "mcpServers": {
    "perplexity": {
      "command": "node",
      "args": ["node_modules/perplexity-mcp-server/dist/index.js"],
      "env": {
        "PERPLEXITY_API_KEY": "ваш_реальный_api_ключ_здесь"
      }
    }
  }
}
```

### 4. Создание .env файла

Создайте файл `.env` в корне проекта:

```bash
PERPLEXITY_API_KEY=ваш_реальный_api_ключ_здесь
```

## Почему нужен ручной шаг

Perplexity MCP Server требует API ключ для работы. Этот ключ:
- Генерируется индивидуально для каждого пользователя
- Связан с вашим аккаунтом Perplexity
- Не может быть получен автоматически
- Требует регистрации на сайте Perplexity

## После настройки

После получения API ключа и настройки конфигурации:
- MCP сервер будет доступен в Cursor
- Вы сможете использовать веб-поиск и AI анализ
- Доступны все модели Perplexity (sonar-pro, sonar-deep-research и др.)

## Дополнительная информация

- [Perplexity API Documentation](https://docs.perplexity.ai/)
- [MCP Protocol](https://modelcontextprotocol.io/)
- [GitHub Repository](https://github.com/perplexityai/modelcontextprotocol)
