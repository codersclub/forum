forum.sources.ru
=====

## Запуск

```shell
docker compose up -d
```

1) Поднимется форум
   - Порт: 9000
   - Протокол: FCGI
2) Angie Server
   - Порт: 80 и 443
3) MySQL 9.6 
   - Порт: 3306

## Установка

1) Выполнить `./db_schema/db/initial/db_struct.sql` в БД
2) Выполнить `./db_schema/db/initial/migrations_table.sql` в БД
3) Выполнить `./db_schema/db/initial/db_insert.sql` в БД

Далее:

```shell
docker exec -it sources-forum bash

php ./db_schema/migrate.php latest
```

Всё!

## Билд + Пуш Образа

```shell
# Билд
APP_ENV=prod BUILD_VERSION=0.0.42 docker compose --profile build build

# Пуш
APP_ENV=prod BUILD_VERSION=0.0.42 docker compose --profile build push
```

После этого можно использовать образ: `docker pull ghcr.io/codersclub/forum:0.0.42`
