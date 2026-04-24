
up:
	docker compose up --build -d

up-prod:
	APP_DEBUG=0 APP_ENV=prod docker compose --profile prod up --build -d

down:
	docker compose down

php:
	docker exec -it sources-forum bash

php-root:
	docker exec -it -uroot sources-forum bash
