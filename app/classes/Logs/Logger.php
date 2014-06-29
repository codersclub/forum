<?php

namespace Logs;

use Monolog\Registry;
use Monolog\Handler\FilterHandler;

class Logger extends Registry
{
    private static $handlers;

    public static function initialize()
    {
        self::$handlers        = [];
        $load_time_execeptions = [];

        try {
            $handler_configs = \Config::get('logs.handlers');
        } catch (\Exception $e) {
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

                $construct_args = [];
                $ref_class      = new \ReflectionClass($config['class']);
                $ref_method     = $ref_class->getConstructor();
                if ($ref_method !== null) {
                    $construct_args = self::filterArgumentsfromOptions($ref_method, $config['options']);
                }
                $handler_info['instance'] = $ref_class->newInstanceArgs($construct_args);
                unset($ref_method, $ref_class, $construct_args);
                //non-constructor options
                if (isset($config['options'])) {
                    foreach ($config['options'] as $option => $value) {
                        $method = 'set' . $option;
                        if (property_exists($handler_info['instance'], $option)) {
                            $handler_info['instance']->option = $value;
                        } elseif (method_exists($handler_info['instance'], $method)) {
                            $value      = (array)$value;
                            $ref_method = new \ReflectionMethod($handler_info['instance'], $method);
                            $args       = self::filterArgumentsfromOptions($ref_method, $value);
                            $ref_method->invokeArgs($handler_info['instance'], $args);
                        }
                    }
                }

                if (isset($config['levels'])) {
                    $handler_info['instance'] = new FilterHandler($handler_info['instance'], $config['levels']);
                }
                self::$handlers[] = $handler_info;
            } catch (\Exception $e) {
                $load_time_execeptions[] = $e;
            }
        }

        //Register global channel
        self::registerChannel('Ibf');

        try {
            //register error_handler
            $error_handler_config = \Config::get('logs.error_handler');
        }catch(\Exception $e){
            $load_time_execeptions[] = $e;
            $error_handler_config = false;
        }

        if ($error_handler_config) {
            $channel                        = self::registerChannel($error_handler_config['channel']);
            $error_handler_config['logger'] = $channel;

            $ref_method = new \ReflectionMethod('\Logs\ErrorHandler', 'register');
            $ref_method->invokeArgs(null, self::filterArgumentsfromOptions($ref_method, $error_handler_config));
        }

        if (!empty($load_time_execeptions)) {
            self::Ibf()->addAlert(
                'Exceptions raised during registering the handlers',
                ['exceptions' => $load_time_execeptions]
            );
        }
    }

    private static function filterArgumentsfromOptions(\ReflectionMethod $method, &$options)
    {
        $args = [];
        foreach ($method->getParameters() as $ref_param) {
            $param_name = $ref_param->getName();

            if (!$ref_param->isOptional()) {
                if (!isset($options[$param_name])) {
                    throw new \InvalidArgumentException(sprintf(
                        'Missing required option "%s" for class %s',
                        $param_name,
                        $method->getDeclaringClass()
                            ->getName()
                    ));
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

    public static function registerChannel($name)
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
        } catch (\Exception $e) {
            $config = false;
        }

        if ($config) { //Всё ещё сомневаемся
            if (isset($config['processors'])) {
                foreach ($config['processors'] as $p) {
                    if (!isset($processors_cache[$p])) {
                        $processors_cache[$p] = new $p();
                    }
                    $channel->pushProcessor($processors_cache[$p]);
                }
            }
        }

        self::addLogger($channel);
        self::Ibf()
            ->debug(sprintf('Channel %s added', $name));
        return $channel;
    }
}
