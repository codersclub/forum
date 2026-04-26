<?php

//
// Удаляем секьюрити env после первого чтения (форум древний, поэтому мало ли,
// вдруг в логах где засветится)
//
$user = (string) ($_ENV['DB_USERNAME'] ?? $_SERVER['DB_USERNAME'] ?? 'user');
unset($_ENV['DB_USERNAME'], $_SERVER['DB_USERNAME']);

$password = (string) ($_ENV['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD'] ?? 'password');
unset($_ENV['DB_PASSWORD'], $_SERVER['DB_PASSWORD']);

return [
    'dsn' => \vsprintf('mysql:dbname=%s;host=%s', [
        (string) ($_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? 'invision'),
        (string) ($_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? 'mysql'),
    ]),
    'user' => $user,
    'password' => $password,
    'persistent' => (bool) ($_ENV['DB_PERSISTENT'] ?? $_SERVER['DB_PERSISTENT'] ?? false),
//    'charset' => '',
];
