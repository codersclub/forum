#!/bin/bash

cd /var/www/sources.ru

# ==============================================================================
#   Install Configuration
# ==============================================================================

cp -n conf_global.sample.php conf_global.php
cp -n ip_table.sample.php ip_table.php

cp -n app/config/app.sample.php app/config/app.php
cp -n app/config/database.sample.php app/config/database.php
cp -n app/config/logs.sample.php app/config/logs.php

# ==============================================================================
#   Install Dependencies
# ==============================================================================

composer install

chmod -R 0777 vendor
chown -R www-data:www-data vendor

# ==============================================================================
#   Boot Migrations
# ==============================================================================

cp -n db_schema/config/db_config.docker.php db_schema/config/db_config.php

php-fpm
