<?php
/**
 * This file is part of Forum package.
 *
 * serafim <nesk@xakep.ru> (24.06.2014 20:23)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Config\Registry;

/**
 * Class Config
 * @see \Config\Registry
 * @method static mixed get($path)
 * @method static void set($path, $value)
 * @method static string getEnvironment()
 * @method static void setEnvironment($name)
 */
class Config
{
    /**
     * @var Registry
     */
    protected static $instance = null;

    /**
     * @return \Config\Registry|null
     */
    protected static function getInstance()
    {
        if (!self::$instance) {
            self::$instance =  new Registry(__DIR__ . '/../config');
            self::$instance->setEnvironment(self::$instance->get('app.environment'));
        }
        return self::$instance;
    }

    /**
     * @param $method
     * @param array $args
     * @return mixed
     */
    public static function __callStatic($method, $args = [])
    {
        return call_user_func_array([self::getInstance(), $method], $args);
    }
}
