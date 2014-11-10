<?php
/**
 * @file
 */

namespace Skins;

use Models\Skins;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        \Config::set('path.data', \Config::get('path.app') . '/tests/Mock/SkinsTestFiles/data');
    }

    protected function tearDown()
    {
        \Config::setEnvironment(\Config::getEnvironment());
        parent::tearDown();
    }

    public function testCreate()
    {
        //uses DatasetSkinManager
        $skin = Factory::create(0);
        $this->assertInstanceOf('\Skins\BaseSkin', $skin);
        $this->assertAttributeEquals('Default test skin', 'name', $skin);
    }

    public function testCreateDefault()
    {
        \Config::set('app.skins.default', 1);
        $skin = Factory::createDefaultSkin();
        $this->assertInstanceOf('\Skins\BaseSkin', $skin);
        $this->assertTrue($skin->getId() === 1);
    }

    /**
     * @expectedException \Exception
     */
    public function testCreateWrongDefault()
    {
        \Config::set('app.skins.default', 12);
        Factory::createDefaultSkin();
    }
}
