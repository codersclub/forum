<?php
/**
 * @file
 */

namespace Skins\Templates;

class BaseTemplate extends AbstractTemplate
{

    public function getHtml($path, $vars)
    {
        $f    = function () {
            extract(func_get_arg(1));
            ob_start();
            include func_get_arg(0);
            return ob_get_clean();
        };
        $path = $this->extractPath($path);
        if (file_exists($path)) {
            return $f($path, $vars);
        } else {
            return false;
        }
    }

    protected function extractPath($path)
    {
        return $this->getDirectory() . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $path) . '.inc';
    }
}
