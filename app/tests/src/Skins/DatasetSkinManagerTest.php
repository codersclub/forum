<?php
/**
 * @file
 */

namespace Skins;

class DatasetSkinManagerTest extends \PHPUnit_Framework_TestCase
{

    protected function tearDown()
    {
        \Config::setEnvironment(\Config::getEnvironment());
        parent::tearDown();
    }

    public function testGlobal()
    {
        $skin = new DatasetSkinManager(
            [
                'name'   => 'Test skin',
                'id'     => 1,
                'macro'  => 'x1',
                'css'    => 'x1.scss',
                'images' => 'x1',
                'theme'  => 'invi',
            ]
        );
        $this->assertEquals('Test skin', $skin->getName());
        $this->assertEquals(1, $skin->getId());
        $this->assertEquals('x1', $skin->getMacroId());
        $this->assertStringEndsWith('x1.scss', $skin->getCSSFile());
        $this->assertStringEndsWith('x1', $skin->getImagesPath());
        $this->assertEquals('invi', $skin->getThemeName());
    }

    /**
     * @expectedException  \Exception
     */
    public function testFailCreation()
    {
        new DatasetSkinManager([]);
    }

    public function testGetSkinsData()
    {
        \Config::set('path.data', \Config::get('path.app') . '/tests/Mock/SkinsTestFiles/data');
        $data = DatasetSkinManager::getAllSkinsData();
        $this->assertInternalType('array', $data);
        $this->assertInternalType('array', reset($data));
    }
}
