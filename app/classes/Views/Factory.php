<?php

namespace Views;

final class Factory
{

    public static function create($path, $data = [])
    {
        $class = __NAMESPACE__ . '\\' . self::pathToClassName($path);
        return class_exists($class)
            ? new $class($path, $data)
            : new BaseView($path, $data);//default one
    }

    private static function pathToClassName($path)
    {
        return str_replace(' ', '', ucwords(str_replace('.', ' ', $path)));
    }
}
