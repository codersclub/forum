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
     * Текущее окружение
     * @var string
     */
    protected $environment = null;

    /**
     * @param string $path Путь к базовому каталогу конфигурационных файлов
     * @throws \InvalidArgumentException
     */
    public function __construct($path)
    {
        if (!is_dir($path)) {
            throw new \InvalidArgumentException(sprintf('Path %s does not exist', $path));
        }
        $this->path = $path;
    }

    /**
     * Устанавливает окружение
     * @param $name
     * @throws \InvalidArgumentException
     */
    public function setEnvironment($name)
    {
        if ($name !== null && !is_dir($this->path . DIRECTORY_SEPARATOR . $name)) {
            throw new \InvalidArgumentException(sprintf('Path for environment "%s" does not exists', $name));
        }
        $this->environment = $name;
        //clear everything out
        $this->configs = [];
    }

    /**
     * Возвращает текущее окружение
     * @return string Текущее окружение
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Объединяет переданные массивы
     * @param array $array1
     * @param array $array2
     * @param array ...
     * @return array
     */
    protected function map()
    {
        if (func_num_args() < 2) {
            //
            trigger_error('Function Registy::map() requires at least 2 parameters', E_USER_ERROR);
            return [];
        }
        $args     = func_get_args();
        $base_arr = array_shift($args);

        foreach ($args as $arg_num => $array_to_merge) {
            if (!is_array($array_to_merge)) {
                trigger_error(
                    sprintf('Parameter %d for function Registry::map() must be array', $arg_num + 1),
                    E_USER_ERROR
                );
                return $base_arr;
            }
            //а может нафик всё - и в старую добрую рекурсию?
            $ptr1  = & $base_arr;
            $ptr2  = & $array_to_merge;
            $stack = [];
            reset($ptr2);
            while (true) {
                $key   = key($ptr2);
                $value = current($ptr2);
                if ($key === null) {
                    $_ = array_pop($stack);
                    if ($_ === null) {
                        break;
                    }
                    $ptr1 = & $_[0];
                    $ptr2 = & $_[1];
                    next($ptr2);
                    continue;
                } elseif (isset($ptr1[$key])) {
                    if (is_array($value) && is_array($ptr1[$key])) {
                        $stack[] = [&$ptr1, &$ptr2];
                        $ptr1    = & $ptr1[$key];
                        $ptr2    = & $ptr2[$key];
                        reset($ptr2);
                        continue;
                    } else {
                        $ptr1[$key] = $value;
                    }
                } else {
                    $ptr1[$key] = $value;
                }
                next($ptr2);
            }
        }
        return $base_arr;
    }

    /**
     * Загружает кофигурационные файлы
     * @param $name
     * @throws ConfigNotFoundException
     * @return mixed
     */
    protected function getConfigs($name)
    {
        $fullPathDefault = $this->path . '/' . str_replace('.', '/', $name) . '.php';
        if ($this->environment !== null) {
            $fullPathEnv = $this->path . '/' . $this->environment . '/' . str_replace('.', '/', $name) . '.php';
        }
        $overrideDefault = function ($config) use ($fullPathDefault) {
            $default = file_exists($fullPathDefault)
                ? require $fullPathDefault
                : [];
            return $this->map($default, $config);
        };

        if (!isset($this->configs[$name])) {
            if (isset($fullPathEnv) && file_exists($fullPathEnv)) {
                $this->configs[$name] = require $fullPathEnv;
            } elseif (file_exists($fullPathDefault)) {
                $this->configs[$name] = require $fullPathDefault;
            } else {
                throw new ConfigNotFoundException('Can not load config `' . $name . '`');
            }
        }
        return $this->configs[$name];
    }

    /**
     * Возвращает конфиги по ->get('файл.ключ')
     * @param string $path Путь к значению в конфиге
     * @param mixed $defaultValue Значение по умолчанию
     * @throws ConfigNotFoundException
     * @return mixed
     */
    public function get($path, $defaultValue = null)
    {
        $path = explode('.', $path);
        $name = array_shift($path);

        $conf = $this->getConfigs($name);

        foreach ($path as $elem) {
            if (!isset($conf[$elem])) {
                return $defaultValue;
            }
            $conf = $conf[$elem];
        }

        // Fix path for Windows
        if($name=='path') {
            $conf = str_replace('\\', '/', realpath($conf));
        }

        return $conf;
    }

    /**
     * Устанавливает значение в конфиге
     * @param string $path Config Path
     * @param mixed $value
     */
    public function set($path, $value)
    {
        $path_arr = explode('.', $path);
        if (count($path_arr) < 2) {
            trigger_error(sprintf('Path "%s" is invalid', $path), E_USER_ERROR);
        }
        $config = array_shift($path_arr);
        $this->getConfigs($config);
        $ptr = & $this->configs[$config];
        while (true) {
            $key = array_shift($path_arr);
            if (empty($path_arr)) { //the last piece of path
                $ptr[$key] = $value;
                break;
            } else {
                if (!isset($ptr[$key]) || !is_array($ptr[$key])) { //nothing to or isn't an array
                    $ptr[$key] = [];
                }
                $ptr = & $ptr[$key];
            }
        }
    }
}
