ci: covers phpunit phpunit-system cs npm-ci validate-app-config stan

test: covers phpunit

setup:
	docker-compose run --rm app ./vendor/bin/doctrine orm:schema-tool:create

covers:
	docker-compose run --rm --no-deps app ./vendor/bin/covers-validator

phpunit:
	docker-compose run --rm app ./vendor/bin/phpunit

phpunit-system:
	docker-compose run --rm app ./vendor/bin/phpunit tests/System/

cs:
	docker-compose run --rm --no-deps app ./vendor/bin/phpcs

stan:
	docker-compose run --rm --no-deps app php -d memory_limit=-1 vendor/bin/phpstan analyse --level=1 --no-progress cli/ contexts/ src/ tests/

validate-app-config:
	docker-compose run --rm --no-deps app ./console validate-config app/config/config.dist.json app/config/config.test.json

phpmd:
	docker-compose run --rm --no-deps app ./vendor/bin/phpmd src/ text phpmd.xml

npm-ci:
	docker run -it -v $(shell pwd):/code -w /code digitallyseamless/nodejs-bower-grunt npm run ci

.PHONY: setup ci test covers phpunit phpunit-system cs stan validate-app-config phpmd npm-ci
