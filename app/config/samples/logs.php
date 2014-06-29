<?php
/**
 * @file
 */
use Monolog\Logger;

//Attention: Most of keys here are case-sensitive. Edit with caution.
return [
    //Обработчик ошибок
    'error_handler' => [
        //маска уровней сообшений для перехвата. Аналогично error_reporting, но не переопределяет её и не связана с ней
        'errorReporting' => E_ALL,
        //Имя канала для записи сообщений
        'channel' => 'PHP',
    ],
    //Настройки каналов. В ключе - имя канала, * - значения по умолчанию для всех. При наличии настроек отдельного
    //канала, они целиком переопределяют настройки по-умолчанию.
    //hint: Каналы здесь не создаются.
    'channels' => [
        '*' => [
            'processors' => [ //Перечень процессоров для подключения
                '\Monolog\Processor\IntrospectionProcessor',
            ],
        ],
        'PHP' => [
            'processors' => [
                '\Monolog\Processor\IntrospectionProcessor',
                '\Monolog\Processor\WebProcessor',
            ],
        ],
    ],
    //Обработчики логов. Каждый элемент - отдельный обработчик, можно добавлять один и тот-же обработчик несколько раз
    //с разными параметрами (или теми-же если совсем скучно)
    'handlers' => [
        //[
            //Полное имя класса требуемого обработчика
            //'class' => '\Monolog\Handler\AbstractHandler',

            //Список каналов для обработки, при отсутствии обратываются все каналы
            //'channels' => [ 'PHP' ],

            //Список каналов, исключаемых из обработки
            //'exclude_channels' => [ 'PHP' ],

            //Список уровней (описанных в \Monolog\Logger), при отсутствии обрабатываются все с учётом возможных
            //настроек самого обработчика
            //'levels' => [ Logger::WARNING ],

            //Настройки специфичные для обработчика. Сюда включаются:
            // * Параметры вызова конструктора (поиск осущуствляется по именам)
            // * Публичные свойства
            // * Параметры вызова сеттеров в виде 'имя сеттера без префикса set' => [ параметры вызова сеттера ],
            //'options' => [],
         //],
//        [
//            'class' => '\Monolog\Handler\RotatingFileHandler',
//            'options' => [
//                'filename' => \Config::get('path.storage') . '/logs/global',
//                'maxFiles' => 15,
//                'FilenameFormat' => [ 'filenameFormat' => '{filename}_{date}.log', 'dateFormat' => 'Ymd', ],
//                'level' => Logger::WARNING,//min level
//            ],
//        ],
//            'class' => '\Monolog\Handler\RotatingFileHandler',
//            'channel' => [ 'Database' ],
//            'options' => [
//                'filename' => \Config::get('path.storage') . '/logs/database',
//                'maxFiles' => 7,
//                'FilenameFormat' => [ 'filenameFormat' => '{filename}_{date}.log', 'dateFormat' => 'Ymd', ],
//                'level' => Logger::DEBUG,//min level
//            ],
//        ],
//        [
//            'class' => '\Monolog\Handler\NativeMailerHandler',
//            'options' => [
//                'from' => 'from_header',
//                'to' => [ 'recipient@example.com' ],
//                'subject' => 'Forum error',
//                'level' => Logger::ERROR, //min level
//            ],
//        ],
//        [
//            'class' => '\Monolog\Handler\BrowserConsoleHandler',
//            'exclude_channels' => [ 'PHP' ],
//            'levels' => [ Logger::WARNING, Logger::DEBUG, Logger::INFO, Logger::NOTICE ],
//        ],
//        [
//            'class' => '\Monolog\Handler\BrowserConsoleHandler',
//            'channels' => [ 'PHP' ],
//            'levels' => [ Logger::WARNING ],
//        ],
    ],
];
