##
## HELP
help: ## Show this help.
	@echo "Command helper"
	@echo "---------------------------"
	@echo "Usage: make [target]"
	@echo ""
	@echo "Targets:"
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

##
## Application
info: ## Shows Php and doctolib app version
	@php --version

.PHONY: info

##
## COMPOSER
install: ## Installs composer dependencies
	@COMPOSER_MEMORY_LIMIT=-1 composer install --optimize-autoloader

update: ## Updates composer dependencies
	@COMPOSER_MEMORY_LIMIT=-1 composer update

validate: ## Validates composer.json file content
	@composer validate --no-check-version

.PHONY: install update validate

##
## PHP QA
phpstan: ## Run PHP Stan analyse
	echo "Executing php stan analyse..."
	@./vendor/bin/phpstan analyse -c phpstan.neon --memory-limit=-1 src tests

phpcs: ## Runs PHP CodeSniffer to analyze php files
	echo "Executing php codeSniffer to analyze files..."
	@./vendor/bin/phpcs --standard=PSR12 --colors --standard=phpcs.xml -p src tests

phpcbf: ## Runs PHP cbf to fix php files syntax errors
	echo "Executing php cbf to fix files syntax errors..."
	@./vendor/bin/phpcbf --standard=PSR12 --colors --standard=phpcs.xml -p src tests

ecs: ## Runs Easy Coding Standard tool
	echo "Executing easy coding standard tool..."
	@./vendor/bin/ecs --clear-cache check src tests

ecs-fix: ## Runs Easy Coding Standard tool to fix issues
	echo "Executing ecs fixer..."
	@./vendor/bin/ecs --clear-cache --fix check src tests

check-code-quality: phpstan ecs ecs-fix phpcs phpcbf ## Check Code Quality


.PHONY: phpstan phpcs phpcbf ecs ecs-fix check-code-quality

##
## TESTS & Crons
tests: ## Run tests.
	@APP_ENV=test ./vendor/bin/phpunit tests --testdox --colors=always

.PHONY: tests