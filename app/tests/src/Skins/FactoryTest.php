<?php
/**
 * @file
 */

namespace Skins;


use Models\Skins;

class FactoryTest extends \PHPUnit_Extensions_Configured_Database_TestCase {

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

    public function testCreate(){
        $this->assertInstanceOf('\Skins\BaseSkin', Factory::create(Skins::find(['uid' => 1])));
    }

    public function testCreateDefault(){
        $this->assertInstanceOf('\Skins\BaseSkin', Factory::createDefaultSkin());
    }

    /**
     * @expectedException \Exception
     */
    public function testCreateWrongDefault(){
        \Config::set('app.default_skin', 12);
        Factory::createDefaultSkin();
    }
}
