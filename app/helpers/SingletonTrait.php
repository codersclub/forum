<?php

trait SingletonTrait
{
    private static $_instance = [];

    public static function getInstance()
    {
        $class = get_called_class();
        if (!isset(self::$_instance[$class])) {
            call_user_func_array([$class, 'setInstance'], func_get_args());
        }
        return self::$_instance[$class];
    }

    public static function setInstance()
    {
        $reflection      = new ReflectionClass(get_called_class());
        self::$_instance[get_called_class()] = $reflection->newInstanceArgs(
            func_get_args()
        );
    }
}
