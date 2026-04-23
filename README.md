forum.sources.ru
=====

## Запуск

```shell
docker compose up -d
```

1) Поднимется форум (fcgi на 9000ом)
2) Angie на 80 и 443 порах
3) MySQL 9.6 на 3306

## Установка

1) Выполнить `./db_schema/db/initial/db_struct.sql` в БД
2) Выполнить `./db_schema/db/initial/migrations_table.sql` в БД
3) Выполнить `./db_schema/db/initial/db_insert.sql` в БД

Далее:

```shell
docker exec -it sources-forum bash

cp ./conf_global.sample.php ./conf_global.php
cp ./app/config/app.sample.php ./app/config/app.php
cp ./app/config/database.sample.php ./app/config/database.php
cp ./app/config/logs.sample.php ./app/config/logs.php
cp ./db_schema/config/db_config.init ./db_schema/config/db_config.php
```

В файле `./app/config/database.php` выставляем:
```php
return [
    'dsn' => 'mysql:dbname=invision;host=mysql',
    'user' => 'user',
    'password' => 'password',
];
```

В файле `./db_schema/config/db_config.php` выставляем:
```php
$db_config->port = '3306';
$db_config->user = 'user';
$db_config->pass = 'password';
$db_config->name = 'invision';
$db_config->db_path = __DIR__ . '/../db/';
```

Теперь опять в докере (`docker exec -it sources-forum bash`) запускаем:
```shell
cd ./db_schema
php ./migrate.php latest
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
