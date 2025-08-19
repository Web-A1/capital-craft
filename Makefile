# Makefile для компиляции LESS стилей

.PHONY: less compile dev build clean

# Компиляция всех LESS файлов
less: compile

# Быстрая компиляция
compile:
	@echo "🚀 Компилирую LESS файлы..."
	@npm run less:all
	@echo "✅ Компиляция завершена!"

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
	@echo "  make compile  - Компиляция всех LESS файлов"
	@echo "  make critical - Компиляция только critical.less"
	@echo "  make home     - Компиляция только home.less"
	@echo "  make faq      - Компиляция только faq.less"
	@echo "  make base     - Компиляция только base.less"
	@echo "  make prod     - Продакшн компиляция (с минификацией)"
	@echo "  make clean    - Очистка временных файлов"
	@echo "  make help     - Показать эту справку"
