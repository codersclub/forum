<?php
/**
 * @file Sample database configuration
 */
return [
    'dsn' => \vsprintf('mysql:dbname=%s;host=%s', [
        (string) ($_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? 'invision'),
        (string) ($_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? 'mysql'),
    ]),
    'user' => (string) ($_ENV['DB_USERNAME'] ?? $_SERVER['DB_USERNAME'] ?? 'user'),
    'password' => (string) ($_ENV['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD'] ?? 'password'),
    'persistent' => (bool) ($_ENV['DB_PERSISTENT'] ?? $_SERVER['DB_PERSISTENT'] ?? false),
//    'charset' => '',
];
