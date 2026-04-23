<?php
/**
 * @file
 */
return [
//    'environment' => 'local',
    'debug' => (bool) ($_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? false),
    'skins' => [
        'default' => 0,
        'hidden' => [
            2,//faq
        ]
    ]
];
