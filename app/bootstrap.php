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
if (Config::get('app.debug', false) === true){
    new Exceptions\ExceptionHandler(E_ERROR);
}
//Logs and error handler. Must be after all other exception and error handlers
Logs\Logger::initialize();

/**
 * Root path
 */
define('ROOT_PATH', Config::get('path.public') . '/');
define('BASE_PATH', Config::get('path.base'));

/**
 * Configs
 */
if (!file_exists(BASE_PATH . '/conf_global.php')) {
    throw new Exception('Please create or copy "conf_global.php"');
}
require BASE_PATH . '/conf_global.php';

if(@$_SERVER['HTTP_HOST'] && @$_SERVER['REQUEST_SCHEME']) {
    $source_url = 'http://' . $_SERVER['HTTP_HOST'];
    $rewritten_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];

    if ($source_url != $rewritten_url) {
        $url_params = [
            'auto_pm_message',
            'board_url',
            'gl_link',
            'html_url',
            'upload_url'
        ];

        foreach ($url_params as $param) {
            $INFO[$param] = str_replace($source_url, $rewritten_url, $INFO[$param]);
        }
    }
}
