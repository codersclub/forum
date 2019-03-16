<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yuri
 * Date: 09.03.13
 * Time: 11:34
 * To change this template use File | Settings | File Templates.
 */
class Ibf
{
    private static $application;

    /**
     * Registers the application
     * @param CoreApplication $app
     */
    public static function registerApplication(CoreApplication $app)
    {
        self::$application = $app;
        Debug::instance()->onAfterRegisterApplication($app);
    }

    /**
     * Removes the application
     */
    public static function dropApplication()
    {
        self::$application = null;
    }

    /**
     * Check if application is registered
     * @return bool
     */
    public static function isApplicationRegistered()
    {
        return !empty(self::$application);
    }

    /**
     * Return the application
     * @return CoreApplication
     */
    public static function app()
    {
        return self::$application;
    }

    /**
     * Logs message. Stub.
     * @param $message
     */
    public static function log($message)
    {
        //stub
    }
}
