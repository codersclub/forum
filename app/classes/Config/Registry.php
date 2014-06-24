<?php
namespace Config;

/**
 * This file is part of forum package.
 *
 * serafim <nesk@xakep.ru> (10.06.2014 14:42)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Exception;

/**
 * Class Registry
 * @package Config
 */
class Registry
{
    /**
     * Путь к папочке конфигов
     * @var string
     */
    protected $path = '';

    /**
     * Конфиги
     * @var array
     */
    protected $configs = [];

    /**
     * @param $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @param $value
     * @return mixed
     * @throws \Exception
     */
    protected function getConfigs($value)
    {
        $fullPath = $this->path . '/' . str_replace('.', '/', $value) . '.php';

        if (!isset($this->configs[$value])) {
            if (file_exists($fullPath)) {
                $this->configs[$value] = require $fullPath;

                return $this->configs[$value];
            }
            throw new Exception('Can not load undefined config `' . $fullPath . '`');
        }
        return $this->configs[$value];
    }

    /**
     * Возвращает конфиги по ->get('файл.ключ')
     * @param $value
     * @return array|null
     */
    public function get($value)
    {
        $value = explode('.', $value);
        $conf = $this->configs;
        $part = 0;

        foreach ($value as $path) {
            $part++;
            if ($part == 1) {
                $conf = $this->getConfigs($path);
                continue;
            }

            if (!isset($conf[$path])) {
                return null;
            }
            $conf = $conf[$path];

        }
        return $conf;
    }
}
