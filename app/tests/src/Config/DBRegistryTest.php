<?php
/**
 * @file
 */

namespace Config;

use Illuminate\Support\Facades\DB;
use PHPUnit_Extensions_Database_DataSet_IDataSet;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;
use PHPUnit_Framework_TestCase;

class DBRegistryTest extends \PHPUnit_Extensions_Database_TestCase
{
    private static $pdo;
    private $conn;
    /**
     * @var DBRegistry
     */
    protected $reg;

    /**
     * Returns the test database connection.
     *
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected function getConnection()
    {
        if ($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = new \PDO(\Config::get('database.dsn'), \Config::get('database.user'), \Config::get(
                    'database.password'
                ));
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, \Config::get('database.dsn'));
        }
        return $this->conn;
    }

    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(MOCK_PATH . '/datasets/ibf_variables.xml');
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

    protected function setUp()
    {
        parent::setUp();
        $this->getConnection()
            ->createDataSet(['ibf_variables']);
        $this->reg = new DBRegistry();
    }

    public function testGet()
    {
        $this->assertEquals('some key', $this->reg->get('one.key2'));
        $this->assertEquals('key1', $this->reg->get('one.key1.subkey1'));
    }

    public function testGetCorrupted()
    {
        $this->assertEmpty($this->reg->get('corrupted'));
        $this->assertNull($this->reg->get('corrupted.key'));
    }

    public function testGetMissing()
    {
        $this->assertNull($this->reg->get('missing.key'));
        $this->assertNull($this->reg->get('two.missing'));
    }

    public function testCommit()
    {

        $cnt = $this->getConnection()
            ->getRowCount('ibf_variables');
        //not existing data
        $this->reg->set('write.one.two.three', 'something');
        $this->reg->commitChanges('write');

        $this->assertEquals(
            $cnt + 1,
            $this->getConnection()
                ->getRowCount('ibf_variables')
        );

        $reg2 = new DBRegistry();
        $this->assertEquals('something', $reg2->get('write.one.two.three'));

    }

    public function testCommitAllAndUpdate()
    {
        //commit all
        $this->reg->set('one.written_key', 'something');
        $this->reg->commitChanges();

        $reg2 = new DBRegistry();
        $this->assertEquals('something', $reg2->get('one.written_key'));
        $this->assertEquals('key1', $reg2->get('one.key1.subkey1')); //check for not overriding other values

    }

}
