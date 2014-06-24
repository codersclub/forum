<?php

/**
 * Autoloaders
 */
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    throw new Exception('Please run "composer install" first');
}
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/autoload.php';

/**
 * Helpers
 */
require __DIR__ . '/helpers/helpers.php';

/**
 * Exception handler
 */
new Exceptions\ExceptionHandler(true, E_ERROR);

/**
 * Root path
 */
define('ROOT_PATH', Config::get('path.public') . '/');


/**
 * Configs
 */
if (!file_exists(__DIR__ . '/../conf_global.php')) {
    throw new Exception('Please create or copy "conf_global.php"');
}
require __DIR__ . '/../conf_global.php';
