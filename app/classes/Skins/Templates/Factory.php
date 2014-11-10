<?php

namespace Skins\Templates;

class Factory
{

    const DEFAULT_TEMPLATE_CLASS = 'Skins\Templates\BaseTemplate';

    /**
     * @param string $name
     * @return AbstractTemplate
     * @throws \Exception
     */
    public static function create($name)
    {
        $directory = \Config::get('path.templates') . DIRECTORY_SEPARATOR . $name;
        if (!is_dir($directory)) {
            throw new \Exception('Wrong templates path/name');
        }
        $config_path = $directory . DIRECTORY_SEPARATOR . 'config.ini';
        $config      = file_exists($config_path)
            ? parse_ini_file($config_path, true)
            : [];

        $parent = isset($config['main']['parent'])
            ? $config['main']['parent']
            : null;

        $classname = isset($config['main']['classname']) && is_subclass_of(
            $config['main']['classname'],
            'Skins\Templates\AbstractTemplate'
        )
            ? $config['main']['classname']
            : self::DEFAULT_TEMPLATE_CLASS;
        return new $classname($name, $parent);
    }
}
