<?php

/**
 * Autoloaders
 */
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    throw new Exception('Please run "composer install" first');
}
$loader = require __DIR__ . '/../vendor/autoload.php';

/**
 * Helpers
 */
require __DIR__ . '/helpers/helpers.php';

Config::setEnvironment('console');
Logs\Logger::initialize();

/**
 * Root path
 */
define('ROOT_PATH', Config::get('path.public') . '/');

/**
 * global $INFO
 */
if (!file_exists(__DIR__ . '/../conf_global.php')) {
    throw new Exception('Please create or copy "conf_global.php"');
}
require __DIR__ . '/../conf_global.php';

Ibf::registerApplication(new ConsoleApplication());
