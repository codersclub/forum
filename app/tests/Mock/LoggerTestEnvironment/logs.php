<?php
/**
 * @file Log configuration for tests environment
 */
use Monolog\Logger;

return [
    //Обработчик ошибок
//    'error_handler' => [
//        'errorReporting' => E_ALL,
//        'channel'        => 'Handler_channel',
//    ],
    'channels'      => [
        '*'              => [
            'processors' => [ //Перечень процессоров для подключения
                              '\Monolog\Processor\IntrospectionProcessor',
                              '\Monolog\Processor\WebProcessor',
            ],
        ],
        'ChannelWithOwnProcessorsConfig' => [
            'processors' => [
                '\Monolog\Processor\WebProcessor',
            ]
        ]
    ],
    'handlers'      => [
//        [
//            //for all channels.
//            'class' => '\Monolog\Handler\TestHandler',
//        ],
        [
            'class'            => '\Monolog\Handler\TestHandler',
            'exclude_channels' => ['TestingChannel'],
        ],
        [
            'class'    => '\Monolog\Handler\TestHandler',
            'channels' => ['TestingChannel'],
        ],
        [
            'class'    => '\Monolog\Handler\TestHandler',
            'channels' => ['TestingChannel_IP'],
            'ip'       => ['0.0.0.0'],
        ],
        [
            'class'    => '\Monolog\Handler\TestHandler',
            'channels' => ['TestingChannel_Level'],
            'levels'   => [Logger::WARNING, Logger::ERROR],
        ],
        [
            'class'     => '\Monolog\Handler\TestHandler',
            'channels'  => ['TestingChannel_Formatter'],
            'formatter' => [
                'class' => '\Monolog\Formatter\LineFormatter',
                'options' => [
                  'format' => 'blah-blah',
                ]
            ],
        ],
        [
            'class'     => '\Logs\Handler\TestHandlerWithOptions',
            'options'   => [
                'level' => -1,
                'SetterValue' => 'value',
            ],
            'channels'  => ['TestingChannel_Options'],
        ],
    ],
];
