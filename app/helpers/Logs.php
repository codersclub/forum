<?php
/**
 * @file Logs helper
 */
use Monolog\Logger;

/**
 * @class Small helper for logging system
 */
class Logs
{

    public static function addRecord($channel, $message, $level, $context = [])
    {
        try {
            \Logs\Logger::getInstance($channel)
                ->addRecord($level, $message, $context);
        } catch (\InvalidArgumentException $e) {
            //nothing
        }
    }

    public static function debug($channel, $message, $context = [])
    {
        self::addRecord($channel, $message, Logger::DEBUG, $context);
    }

    public static function info($channel, $message, $context = [])
    {
        self::addRecord($channel, $message, Logger::INFO, $context);
    }

    public static function warning($channel, $message, $context = [])
    {
        self::addRecord($channel, $message, Logger::WARNING, $context);
    }

    public static function error($channel, $message, $context = [])
    {
        self::addRecord($channel, $message, Logger::ERROR, $context);
    }

    public static function alter($channel, $message, $context = [])
    {
        self::addRecord($channel, $message, Logger::ALERT, $context);
    }

    public static function emergency($channel, $message, $context = [])
    {
        self::addRecord($channel, $message, Logger::EMERGENCY, $context);
    }

    public static function notice($channel, $message, $context = [])
    {
        self::addRecord($channel, $message, Logger::NOTICE, $context);
    }

    public static function critical($channel, $message, $context = [])
    {
        self::addRecord($channel, $message, Logger::CRITICAL, $context);
    }
}
