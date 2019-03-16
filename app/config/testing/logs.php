<?php
/**
 * @file logs configuration for testing environment.
 * Attention! Logging to anywhere during the passing tests can cause different problems, so change this file at your
 * own risk.
 */

return [
    'handlers' => [
        [
            'class' => '\Monolog\Handler\NullHandler',
        ]
    ]
];
