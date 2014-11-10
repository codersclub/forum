<?php
/**
 * @file
 */

namespace Skins;

class TestBaseSkinManager extends BaseSkinManager {

    public function getName()
    {
    }

    public function getMacroId()
    {
        return 'test';
    }

    public function getCSSFile()
    {
    }

    public function getImagesPath()
    {
    }

    public function getId()
    {
        return 'test';
    }

    public function getTemplatesPath()
    {
        return \Config::get('path.app') . '/tests/Mock/SkinsTestFiles/template';
    }
}

class BaseSkinTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        //трясём конфиг для сброса установленных значений
        \Config::setEnvironment(\Config::getEnvironment());
        parent::tearDown();
    }

    public function testGlobal()
    {
        //creation
        $skin = new TestBaseSkinManager();
        $this->assertInstanceOf('\Skins\BaseSkin', $skin);
        //views
        $this->assertInstanceOf('\Skins\Views\Collection', $skin->getViews());
        //wrapper
        $this->assertContains('test wrapper', $skin->getWrapper());
        //macro values
        \Config::set('path.data', \Config::get('path.app') . '/tests/Mock/SkinsTestFiles/data');
        $this->assertInternalType('array', $skin->getMacroValues());
        $this->assertArrayHasKey('test item', $skin->getMacroValues());
        //isHidden
        \Config::set('app.skins', []);//todo remove after fixing Registry::set().
        \Config::set('app.skins.hidden', [ $skin->getId() ]);
        $this->assertTrue($skin->isHidden());
        \Config::set('app.skins.hidden', [ ]);
        $this->assertFalse($skin->isHidden());
    }

    public function testOffsetGetAndExists(){
        $skin = new TestBaseSkinManager();
        $this->assertInstanceOf('\Skins\Views\Collection', $skin['views']);
        $this->assertNull($skin['non_existing_value']);
        $this->assertTrue(isset($skin['views']));
        $this->assertFalse(isset($skin['non_existing_value']));
    }

    /**
     * @expectedException \Exception
     */
    public function testOffsetSet(){
        $skin = new TestBaseSkinManager();
        $skin['views'] = null;
    }

    /**
     * @expectedException \Exception
     */
    public function testOffsetUnset(){
        $skin = new TestBaseSkinManager();
        unset($skin['views']);
    }

}
