<?php
/**
 * @file Sample config for logging system
 */
use Monolog\Logger;

//Attention: Most of keys here are case-sensitive. Edit with caution.
return [
    'error_handler' => [
        'errorReporting' => E_ALL,
        'channel'        => 'PHP',
    ],
    'channels'      => [
        '*'   => [
            'processors' => [
                [
                    'class'   => '\Monolog\Processor\IntrospectionProcessor',
                    'options' => [
                        'skipClassesPartials' => [
                            'Monolog\\',
                            'Logs',
                            'PDO',
                        ]
                    ],
                ],
            ],
        ],
        'PHP' => [
            'processors' => [
                '\Monolog\Processor\IntrospectionProcessor',
                '\Monolog\Processor\WebProcessor',
            ],
        ],
    ],
    'handlers'      => [
        //Examples
        //Запись всех сообщенеий уровня WARNING и выше в файл storage/logs/global_%Y%m%d.log с ежедневной ротацией
        [
            'class'   => '\Monolog\Handler\RotatingFileHandler',
            'options' => [
                'filename'       => \Config::get('path.storage') . '/logs/global',
                'maxFiles'       => 15,
                'FilenameFormat' => ['filenameFormat' => '{filename}_{date}.log', 'dateFormat' => 'Ymd',],
                //этот аргумент конструктора у некоторых обработчиков может быть использован вместо levels
                'level'          => Logger::WARNING,
                'filePermission' => 0664,
            ],
        ],
        //Запись всех сообщенеий с канала PDO в файл storage/logs/database_%Y%m%d.log с ежедневной ротацией
        [
            'class'   => '\Monolog\Handler\RotatingFileHandler',
            'channel' => ['PDO'],
            'options' => [
                'filename'       => \Config::get('path.storage') . '/logs/database',
                'maxFiles'       => 7,
                'FilenameFormat' => ['filenameFormat' => '{filename}_{date}.log', 'dateFormat' => 'Ymd',],
                'filePermission' => 0664,//0644 порождает проблемы с вызовом из консоли
            ],
        ],
        //Отправка сообщений уровня ERROR и выше на почту, с html-форматированием и буферизацией сообщений
        [
            'class'          => '\Monolog\Handler\NativeMailerHandler',
            'options'        => [
                'from'        => 'user@example.com',
                'to'          => ['recipient@example.com'],
                'subject'     => 'Forum error',
                'ContentType' => 'text/html',
                'level'       => Logger::ERROR,
            ],
            'formatter'      => [
                'class'   => '\Monolog\Formatter\HtmlFormatter',
                'options' => [
                    'ContentType' => 'text/html',
                ],
            ],
            'buffer_records' => true,
            'buffer_limit'   => 0
        ],
        //Вывод сообщений уровня WARNING со всех каналов, а также уровней DEBUG, INFO, NOTICE со всех кроме PHP
        //в консоль браузера
        [
            'class'            => '\Monolog\Handler\BrowserConsoleHandler',
            'exclude_channels' => ['PHP'],
            'levels'           => [Logger::WARNING, Logger::DEBUG, Logger::INFO, Logger::NOTICE],
        ],
        [
            'class'    => '\Monolog\Handler\BrowserConsoleHandler',
            'channels' => ['PHP'],
            'levels'   => [Logger::WARNING],
        ],
    ],
];
