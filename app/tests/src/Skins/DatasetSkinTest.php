<?php
/**
 * @file
 */

namespace Skins;

class DatasetSkinTest extends \PHPUnit_Framework_TestCase
{

    protected function tearDown()
    {
        \Config::setEnvironment(\Config::getEnvironment());
        parent::tearDown();
    }

    public function testGlobal(){
        $skin = new DatasetSkin([
                'name'   => 'Test skin',
                'id'     => 1,
                'macro'  => 'x1',
                'css'    => 'x1.scss',
                'images' => 'x1',
            ]);
        $this->assertEquals('Test skin', $skin->getName());
        $this->assertEquals(1, $skin->getId());
        $this->assertEquals('x1', $skin->getMacroId());
        $this->assertStringEndsWith('x1.scss', $skin->getCSSFile());
        $this->assertStringEndsWith('x1', $skin->getImagesPath());
    }

    /**
     * @expectedException  \Exception
     */
    public function testFailCreation(){
        new DatasetSkin([]);
    }

    public function testGetSkinsData(){
        \Config::set('path.data', \Config::get('path.app') . '/tests/Mock/SkinsTestFiles/data');
        $data = DatasetSkin::getAllSkinsData();
        $this->assertInternalType('array', $data);
        $this->assertInternalType('array', reset($data));
    }
}
