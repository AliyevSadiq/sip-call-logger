init: docker-down docker-pull docker-build docker-up

docker-up:
	docker-compose up -d

docker-down:
	docker-compose down --remove-orphans

docker-down-clear:
	docker-compose down -v --remove-orphans

docker-pull:
	docker-compose pull

docker-build:
	docker-compose build

app-init: app-install-composer

app-install-composer:
	docker-compose run --rm php-cli composer install

app-generate-key:
	docker-compose run --rm php-cli php artisan key:generate

app-migrate:
	docker-compose run --rm php-cli php artisan migrate

app-test:
	docker-compose run --rm php-cli php artisan test

doc-generate:
	docker-compose run --rm php-cli php artisan  l5-swagger:generate

call-event-queue:
	docker-compose run --rm php-cli php artisan queue:l --queue=call-event
