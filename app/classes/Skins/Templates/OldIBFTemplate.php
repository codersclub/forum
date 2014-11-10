<?php
/**
 * @file
 */

namespace Skins\Templates;

class OldIBFTemplate extends AbstractTemplate
{

    public function getHtml($path, $data)
    {
        static $paths = [];
        static $classes = [];

        if (!isset($paths[$path])) {
            $args = explode('.', $path);
            if (!isset($classes[$args[0]])) {
                $classname = 'skin_' . $args[0];
                if (file_exists($this->getDirectory() . DIRECTORY_SEPARATOR . $classname . '.php')) {
                    $classes[$args[0]] = new $classname();
                } else {
                    return false;
                }
            }

            if (method_exists($classes[$args[0]], $args[1])) {
                $paths[$path] = [$classes[$args[0]], $args[1]];
            } else {
                return false;
            }
        }
        return call_user_func_array($paths[$path], $data);
    }
}
