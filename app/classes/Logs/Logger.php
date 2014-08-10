<?php

namespace Logs;

use Config\ConfigNotFoundException;
use Logs\Handler\IPFilterHandler;
use Monolog\Handler\BufferHandler;
use Monolog\Registry;
use Monolog\Handler\FilterHandler;

/**
 * Класс инициализации и работы с логами.
 * @package Logs
 *
 * Channels
 * @method static \Monolog\Logger Debug() Developers channel
 * @method static \Monolog\Logger Ibf() Common messages channel
 * @method static \Monolog\Logger PDO() Low level PDO queries channel
 * @method static \Monolog\Logger PHP() PHP errors channel
 * @method static \Monolog\Logger Stats() Statistics channel
 */
class Logger extends Registry
{
    /**
     * Загруженные обработчики
     * @var array|\Monolog\Handler\HandlerInterface
     */
    private static $handlers;

    /**
     * Инициализирует Monolog.
     * Парсит конфиг logs.* и создаёт на его основе обработчики и перехватчики ошибок/исключений.
     * Также инициализирует каналы Ibf и Debug
     */
    public static function initialize()
    {
        self::$handlers        = [];
        $load_time_execeptions = [];

        self::clear();

        try {
            $handler_configs = \Config::get('logs.handlers');
        } catch (ConfigNotFoundException $e) {
            $handler_configs = [];
        }

        foreach ($handler_configs as $config) {
            $handler_info = [];
            try {
                if (!isset($config['class']) || !class_exists($config['class'])) {
                    throw new \Exception("{$config['class']} does not exist");
                }

                if (isset($config['channels'])) {
                    $handler_info['channels'] = array_fill_keys($config['channels'], true);
                }

                if (isset($config['exclude_channels'])) {
                    $handler_info['exclude_channels'] = array_fill_keys($config['exclude_channels'], true);
                }

                $handler_info['instance'] = self::createClassFromConfig($config);
                //non-constructor options
                if (isset($config['options']) && is_array($config['options'])) {
                    foreach ($config['options'] as $option => $value) {
                        $method = 'set' . $option;
                        if (property_exists($handler_info['instance'], $option)) {
                            $handler_info['instance']->option = $value;

                        } elseif (method_exists($handler_info['instance'], $method)) {
                            $ref_method = new \ReflectionMethod($handler_info['instance'], $method);
                            self::invokeMethodWithOptions($handler_info['instance'], $ref_method, $value);
                        }
                    }
                }
                //Обрабатываем опцию форматтера
                if (isset($config['formatter'])) {
                    $handler_info['instance']->setFormatter(self::createClassFromConfig($config['formatter']));
                }

                // Добавляем буферизацию сообщений. Для этого заменяем наш обработчик специальномым BufferHandler,
                //которому передаём уже наш в качестве параметра
                if (isset($config['buffer_records']) && $config['buffer_records'] === true) {
                    $buffer_limit             = isset($config['buffer_limit'])
                        ? $config['buffer_limit']
                        : 0;
                    $handler_info['instance'] = new BufferHandler($handler_info['instance'], $buffer_limit);
                }

                //Фильтр по уровням.
                if (isset($config['levels'])) {
                    $handler_info['instance'] = new FilterHandler($handler_info['instance'], $config['levels']);
                }

                //Фильтр по IP
                if (isset($config['ip'])) {
                    $handler_info['instance'] = new IPFilterHandler($handler_info['instance'], $config['ip']);
                }

                self::$handlers[] = $handler_info;
            } catch (ConfigNotFoundException $e) {
                $load_time_execeptions[] = $e;
            }
        }

        //Register global channel
        self::registerChannel('Ibf');
        //Register Development channel
        self::registerChannel('Debug');
        //Register statistics channel
        self::registerChannel('Stats');

        try {
            //register error_handler
            $error_handler_config = \Config::get('logs.error_handler');
        } catch (ConfigNotFoundException $e) {
            $load_time_execeptions[] = $e;
            $error_handler_config    = false;
        }

        if ($error_handler_config) {
            $channel                        = self::registerChannel($error_handler_config['channel']);
            $error_handler_config['logger'] = $channel;

            $ref_method = new \ReflectionMethod('\Logs\ErrorHandler', 'register');
            $ref_method->invokeArgs(null, self::filterArgumentsfromOptions($ref_method, $error_handler_config));
        }

        if (!empty($load_time_execeptions)) {
            self::Ibf()
                ->addAlert(
                    'Exceptions raised during registering the handlers',
                    ['exceptions' => $load_time_execeptions]
                );
        }
    }

    /**
     * Ищет параметры для выполнения метода и исполняет его.
     * @param object $classInstance Экземпляр класса, которому принадлежит метод.
     * Детали в описании ReflectionMethod::invokeArgs()
     * @param \ReflectionMethod $method Метод для исполнения
     * @param array|mixed $options Массив аргументов метода в виде 'имя аргумента - значение'.
     * Для исполнения методов с одним обязательным параметром может принять вид самого параметра вместо массива
     * @return mixed Результат выполнения метода
     * @throws \InvalidArgumentException
     */
    private static function invokeMethodWithOptions(&$classInstance, \ReflectionMethod $method, &$options)
    {
        if (is_array($options)) {
            $args = self::filterArgumentsfromOptions($method, $options);
        } else {
            if ($method->getNumberOfRequiredParameters() === 1) {
                $args    = [$options];
                $options = null;
            } else {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Wrong number of parameters passed to %s:%s ',
                        $method->getName(),
                        get_class($classInstance)
                    )
                );
            }
        }
        return $method->invokeArgs($classInstance, $args);
    }

    /**
     * Фильтрует список аргументов метода из указанного массива. Найденные элементы из массива удаляёются.
     * @param \ReflectionMethod $method Метод, чьи аргументы надо найти
     * @param array $options Массив для поиска в виде 'имя аргумента' => 'значение'.
     * @return array Упорядоченный для использования функций вроде call_user_func_array() или
     * \ReflectionMethod::InvokeArgs(), массив значений.
     * @throws \InvalidArgumentException
     */
    private static function filterArgumentsfromOptions(\ReflectionMethod $method, array &$options)
    {
        $args = [];
        foreach ($method->getParameters() as $ref_param) {
            $param_name = $ref_param->getName();

            if (!$ref_param->isOptional()) {
                if (!isset($options[$param_name])) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Missing required option "%s" for class %s',
                            $param_name,
                            $method->getDeclaringClass()
                                ->getName()
                        )
                    );
                }
                $args[] = $options[$param_name];
            } else {
                $args[] = isset($options[$param_name])
                    ? $options[$param_name]
                    : $ref_param->getDefaultValue();

            }
            unset($options[$param_name]);
        }
        return $args;
    }

    /**
     * Создаёт класс на основе имени и аргументов для конструктора
     * @param string $classname имя класса
     * @param array|mixed $options Аргументы для запуска в виде 'имя аргумента' => 'значение'. Вместо массива может быть
     * значение произвольного типа если конструктор требует только один аргумент.
     * @return object Созданный объект
     * @throws \InvalidArgumentException
     */
    private static function createClassFromNameAndOptions($classname, $options = [])
    {
        $class  = new \ReflectionClass($classname);
        $method = $class->getConstructor();

        if ($method !== null) {
            if (is_array($options)) {
                $args = self::filterArgumentsfromOptions($method, $options);
            } else {
                if ($method->getNumberOfRequiredParameters() === 1) {
                    $args = [$options];
                } else {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Wrong number of parameters passed to constuctor of class %s ',
                            $classname
                        )
                    );
                }
            }
        } else {
            $args = [];
        }
        return $class->newInstanceArgs($args);

    }

    /**
     * Создаёт класс из куска конфига. Никакой особой магии.
     * @param array $options Часть конгфига для отдельного элемента(e.g. обработчика)
     * @return object
     */
    private static function createClassFromConfig($options)
    {
        if (!isset($options['options'])) {
            $options['options'] = [];
        }
        return self::createClassFromNameAndOptions($options['class'], $options['options']);
    }

    /**
     * Регистрирует канал и добавляет в него все обработчики в соответствии с настройками
     * @param string $name Наименование канала
     * @return \Monolog\Logger
     */
    public static function registerChannel($name, $overwrite = false)
    {
        static $processors_cache = [];

        $channel = new \Monolog\Logger($name);
        //add handlers
        foreach (self::$handlers as $h_info) {
            if ((!isset($h_info['channels']) || isset($h_info['channels'][$name])) && !isset($h_info['exclude_channels'][$name])) {
                $channel->pushHandler($h_info['instance']);
            }
        }

        try {
            $config = \Config::get('logs.channels.' . $name);
            if (!$config) {
                $config = \Config::get('logs.channels.*');
            }
        } catch (ConfigNotFoundException $e) {
            $config = false;
        }

        if ($config) { //Всё ещё сомневаемся
            if (isset($config['processors']) && is_array($config['processors'])) {
                foreach ($config['processors'] as $p) {
                    if (!isset($processors_cache[$p])) {
                        $processors_cache[$p] = new $p();
                    }
                    $channel->pushProcessor($processors_cache[$p]);
                }
            }
        }

        self::addLogger($channel, null, $overwrite);
        self::Ibf()
            ->debug(sprintf('Channel %s added', $name));
        return $channel;
    }

    /**
     * Проверка существования канала
     * @param string $name
     * @return bool
     */
    public static function isChannelRegistered($name)
    {
        //Других способов нет. Честно.
        try {
            //На самом деле эта часть всегда истинна, но пусть будет так.
            return self::getInstance($name) instanceof \Monolog\Logger;
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * Потуга сделать откат до запасного журнала.
     * @param string $name
     * @param array $arguments
     * @return \Monolog\Logger
     * @throws \InvalidArgumentException
     */
    public static function __callStatic($name, $arguments)
    {
        try {
            return parent::__callStatic($name, $arguments);
        } catch (\InvalidArgumentException $e) {
            if (!self::isChannelRegistered('fallback')) {
                self::registerChannel('fallback');
            }
            return parent::__callStatic('fallback', $arguments);
        }
    }
}
