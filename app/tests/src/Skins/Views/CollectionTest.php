<?php
/**
 * @file
 */

namespace Skins\Views;

use Skins\BaseSkin;

class TestCollectionOwner extends BaseSkin
{

    public function getName()
    {
    }

    public function getMacroId()
    {
    }

    public function getCSSFile()
    {
    }

    public function getImagesPath()
    {
    }

    public function getId()
    {
    }

    public function getViewsDirectory()
    {
        return \Config::get('path.app') . '/tests/Mock/SkinsTestFiles/template';
    }
}

class CollectionTest extends \PHPUnit_Framework_TestCase
{

    public function testGlobal()
    {
        $c = new Collection(new TestCollectionOwner());
        $this->assertInstanceOf('skin_test', $c->getView('test'));
        $this->assertInstanceOf('skin_test', $c['test']);
    }

    /**
     * @expectedException \Exception
     */
    public function testOffsetSet()
    {
        $c         = new Collection(new TestCollectionOwner());
        $c['view'] = null;
    }

    /**
     * @expectedException \Exception
     */
    public function testOffsetUnset()
    {
        $c = new Collection(new TestCollectionOwner());
        unset($c['view']);
    }
}
