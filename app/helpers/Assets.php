<?php
/**
 * This file is part of Forum package.
 *
 * serafim <nesk@xakep.ru> (24.06.2014 19:34)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Asset\Compiler;
use Asset\Config;

/**
 * Class Assets
 */
class Assets
{
    protected static $compiler;

    protected static function getCompiler()
    {
        if (!self::$compiler) {
            $configs = [
                Config::PATH_PUBLIC => public_path('assets'),
                Config::PATH_SOURCE => app_path(),
                Config::PATH_TEMP   => storage_path('assets'),
                Config::PATH_URL    => '/assets/'
            ];

            self::$compiler = new Compiler(
                new Config($configs)
            );
        }
        return self::$compiler;
    }

    public static function make($path)
    {
        return self::getCompiler()->make($path);
    }
}
