<?php
/**
 * @file bootstrap for phpunit
 */

define('ROOT_PATH', __DIR__ . '/../..');
echo ROOT_PATH;
/**
 * Autoloaders
 */
if (!file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    throw new Exception('Please run "composer install" first');
}

/** @var $loader \Composer\Autoload\ClassLoader */
$loader = require ROOT_PATH . '/vendor/autoload.php';

//override Config helper
$loader->addClassMap(['Config' => __DIR__ . '/helpers/Config.php']);
\Config::setEnvironment('testing');

/**
 * Root path
 */
define('MOCK_PATH', __DIR__ . '/Mock');

$loader->addPsr4('', __DIR__ . '/src'); //our base namespace must be here but we don't have one!

/**
 * Also load some vendor tests we depend on.
 */
$loader->addPsr4('Monolog\\', ROOT_PATH . '/vendor/monolog/monolog/tests/Monolog');//used by logger.

/**
 * Configs
 */
if (!file_exists(__DIR__ . '/../../conf_global.php')) {
    throw new Exception('Please create or copy "conf_global.php"');
}
require __DIR__ . '/../../conf_global.php';
