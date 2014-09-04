<?php

namespace Models;

use PHPUnit_Extensions_Database_DataSet_IDataSet;

class SkinsTest extends \PHPUnit_Extensions_Configured_Database_TestCase
{

    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(MOCK_PATH . '/datasets/ibf_skins.xml');
    }

    protected function setUp()
    {
        parent::setUp();
        $this->getConnection()
            ->createDataSet(['ibf_skins']);
    }

    public function testCount()
    {
        $this->assertEquals(2, Skins::count([]));
        $this->assertEquals(1, Skins::count(['uid' => 1]));
        $this->assertEquals(0, Skins::count(['uid' => 2, 'hidden' => 1]));
    }

    public function testFind()
    {
        $data = Skins::find(['macro_id' => 2]);
        $this->assertEquals(2, $data['uid']);
        $data = Skins::find(['macro_id' => 2, 'hidden' => 1]);
        $this->assertEquals(false, $data);
    }

    public function testFindAll()
    {
        $data = Skins::findAll([]);
        $this->assertCount(2, $data);
        $data = Skins::findAll(['uid' => 1]);
        $this->assertCount(1, $data);
        $arr = reset($data);
        $this->assertEquals(1, $arr['uid']);
    }

    public function testAdd()
    {
        Skins::add(['uid' => 5, 'sname' => 'added', 'hidden' => 0]);
        $this->assertEquals(3, Skins::count());
        $data = Skins::find(['sname' => 'added']);
        $this->assertEquals(5, $data['uid']);
    }
}
