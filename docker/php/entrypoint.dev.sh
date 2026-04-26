#!/usr/bin/env bash
set -e

export APP_ENV="${APP_ENV:-dev}"

# ------------------------------------------------------------------------------
#   Install Dependencies
# ------------------------------------------------------------------------------

echo "[${APP_ENV}] Install dependencies"
composer install

# ------------------------------------------------------------------------------
#   Copy Configuration Files
# ------------------------------------------------------------------------------

cd /home/sources/forum.sources.ru
cp -n ./conf_global.sample.php ./conf_global.php
cp -n ./app/config/app.sample.php ./app/config/app.php
cp -n ./app/config/database.sample.php ./app/config/database.php
cp -n ./app/config/logs.sample.php ./app/config/logs.php

cp -n ./db_schema/config/db_config.init ./db_schema/config/db_config.php

# ------------------------------------------------------------------------------
#   Ready
# ------------------------------------------------------------------------------

source /usr/local/bin/entrypoint.sh;
