<?php
/**
 * @file autoload functions
 */

spl_autoload_register(function ($class) {
        $map = explode('\\', $class);
    if (count($map) > 2 && $map[0] == 'Skins' && $map[1] == 'Themes') {
        $file = Config::get('path.templates') . DIRECTORY_SEPARATOR . $map[2] . DIRECTORY_SEPARATOR . $map[2] . '.php';
        if (file_exists($file)) {
            include $file;
        }
    }
});
