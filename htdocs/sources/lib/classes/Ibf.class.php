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

	public static function registerApplication(CoreApplication $app)
	{
		self::$application = $app;
	}

	public static function isApplicationRegistered()
	{
		return !empty(self::$application);
	}

	public static function app()
	{
		return self::$application;
	}

	public static function log($message)
	{

	}
}

