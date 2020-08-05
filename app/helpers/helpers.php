<?php
/**
 * This file is part of Forum package.
 *
 * serafim <nesk@xakep.ru> (24.06.2014 19:36)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!function_exists('public_path')) {
    function public_path($postfix = '')
    {
        $path = Config::get('path.public');
        if ($postfix) { $path .= '/' . $postfix; }
        return $path;
    }
}

if (!function_exists('app_path')) {
    function app_path($postfix = '')
    {
        $path = Config::get('path.app');
        if ($postfix) { $path .= '/' . $postfix; }
        return $path;
    }
}

if (!function_exists('base_path')) {
    function base_path($postfix = '')
    {
        $path = Config::get('path.base');
        if ($postfix) { $path .= '/' . $postfix; }
        return $path;
    }
}

if (!function_exists('storage_path')) {
    function storage_path($postfix = '')
    {
        $path = Config::get('path.storage');
        if ($postfix) { $path .= '/' . $postfix; }
        return $path;
    }
}

/**
 * Show debug info
 * @param $data
 * @param string $name
 */
function dump($data, $name = '')
{
    $buf = var_export($data, true);

    $buf = str_replace('\\r', '', $buf);
    $buf = preg_replace('/\=\>\s*\n\s*array/s', '=> array', $buf);

    echo '<pre>';

    if ($name) {
        echo $name, '=';
    }

    echo $buf;
    echo "</pre>\n";
}

