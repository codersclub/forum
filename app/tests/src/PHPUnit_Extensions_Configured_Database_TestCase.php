<?php

/**
 * @file
 */
abstract class PHPUnit_Extensions_Configured_Database_TestCase extends \PHPUnit_Extensions_Database_TestCase
{

    private static $pdo;
    private $conn;

    /**
     * Returns the test database connection.
     *
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected function getConnection()
    {
        if ($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = new \PDO(
                    \Config::get('database.dsn'),
                    \Config::get('database.user'),
                    \Config::get(
                        'database.password'
                    )
                );
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, \Config::get('database.dsn'));
        }
        return $this->conn;
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        \Config::setEnvironment('testing');
        \Logs\Logger::initialize();
        \Ibf::registerApplication(new \TestApplication(['db']));
    }

    public static function tearDownAfterClass()
    {
        \Ibf::dropApplication();
        parent::tearDownAfterClass();
    }
}
