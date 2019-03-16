<?php

namespace Skins\Themes;

class Factory
{

    const DEFAULT_THEME_CLASS = 'Skins\Themes\BaseTheme';

    /**
     * @param string $name
     * @return AbstractTheme
     */
    public static function create($name)
    {
        if (is_subclass_of('Skins\Themes\\' . $name, 'Skins\Themes\AbstractTheme')) {
            $classname = 'Skins\Themes\\' . $name;
        } else {
            $classname = self::DEFAULT_THEME_CLASS;
        }
        return $classname::getInstance();
    }
}
