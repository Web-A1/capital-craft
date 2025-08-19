# Makefile для компиляции LESS стилей

.PHONY: less compile compile-dev compile-prod dev build clean help deploy merge-to-main

# Компиляция всех LESS файлов
less: compile

# Быстрая компиляция (для разработки)
compile-dev:
	@echo "🚀 Компилирую LESS файлы для разработки..."
	@./templates/capitalcraft/build-scripts/compile-less.sh
	@echo "✅ Компиляция для разработки завершена!"

# Продакшн компиляция (с минификацией)
compile-prod:
	@echo "🚀 Компилирую LESS файлы для продакшна..."
	@./templates/capitalcraft/build-scripts/compile-less-prod.sh
	@echo "✅ Продакшн компиляция завершена!"

# Компиляция и пуш одной командой
deploy:
	@echo "🚀 Компилирую и пушаю..."
	@make compile-prod
	@echo "📤 Пушаю изменения..."
	@git add . && git commit -m "build: продакшн компиляция" && git push
	@echo "✅ Компиляция и пуш завершены!"

# Мердж dev в main
merge-to-main:
	@echo "🔄 Мерджу dev в main..."
	@./merge-dev-to-main.sh
	@echo "✅ Мердж завершен!"

# Компиляция отдельных файлов
critical:
	@echo "🔄 Компилирую critical.less..."
	@npm run less:critical

home:
	@echo "🔄 Компилирую home.less..."
	@npm run less:home

faq:
	@echo "🔄 Компилирую faq.less..."
	@npm run less:faq

base:
	@echo "🔄 Компилирую base.less..."
	@npm run less:base

# Продакшн компиляция
prod:
	@echo "�� Компилирую LESS файлы для продакшна..."
	@npm run less:all:prod
	@echo "✅ Продакшн компиляция завершена!"

# Очистка
clean:
	@echo "🧹 Очищаю временные файлы..."
	@rm -f templates/capitalcraft/less/.last_check
	@echo "✅ Очистка завершена!"

# Помощь
help:
	@echo "Доступные команды:"
	@echo "  make compile-dev  - Компиляция LESS для разработки"
	@echo "  make compile-prod - Продакшн компиляция LESS + JS (с минификацией)"
	@echo "  make deploy       - КОМПИЛЯЦИЯ + ПУШ одной командой"
	@echo "  make merge-to-main - МЕРДЖ dev в main"
	@echo "  make critical     - Компиляция только critical.less"
	@echo "  make home         - Компиляция только home.less"
	@echo "  make faq          - Компиляция только faq.less"
	@echo "  make base         - Компиляция только base.less"
	@echo "  make prod         - Продакшн компиляция LESS (с минификацией)"
	@echo "  make clean        - Очистка временных файлов"
	@echo "  make help         - Показать эту справку"
