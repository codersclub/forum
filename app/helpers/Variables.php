<?php

use Config\DBRegistry;

/**
 * Class Variables
 * @see \Config\DBRegistry
 * @method static mixed get($path)
 * @method static void set($path, $value)
 * @method static void commitChanges($name = null)
 */
class Variables
{

    /**
     * @var DBRegistry
     */
    protected static $instance = null;

    /**
     * @return \Config\Registry|null
     */
    protected static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new DBRegistry();
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
