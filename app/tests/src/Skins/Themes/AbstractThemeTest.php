<?php
/**
 * @file
 */

namespace Skins\Themes;

use Exceptions\MissingTemplateException;

class TestAbstractTheme extends AbstractTheme
{

    public function getHtml($path, $data)
    {
        return $path;
    }
}

class TestAbstractTheme2 extends AbstractTheme
{
    public $_parent;

    public function getHtml($path, $data)
    {
        throw new MissingTemplateException('controllable flow');
    }

    protected function getParentThemeName()
    {
        return $this->_parent;
    }

}

class AbstractThemeTest extends \PHPUnit_Framework_TestCase
{

    public function testGetDirectory()
    {
        $test = new TestAbstractTheme();
        $this->assertInternalType('string', $test->getDirectory());
        $this->assertStringEndsWith('TestAbstractTheme', $test->getDirectory());
    }

    public function testParent()
    {
        $test = new TestAbstractTheme2();
        $test->_parent = 'TestAbstractTheme';
        $this->assertInstanceOf('Skins\Themes\TestAbstractTheme', $test->getParent());

    }

    public function testRender()
    {
        $test1 = new TestAbstractTheme();
        $this->assertEquals('some.path', $test1->render('some.path', []));
        //fallback / parent test
        $test2 = new TestAbstractTheme2();
        $test2->_parent = 'TestAbstractTheme';
        $this->assertEquals('some.path', $test2->render('some.path', []));

    }

    public function testWrongPath(){
        $test2 = new TestAbstractTheme2();
        $this->setExpectedException('Exception');
        echo $test2->render('some.wrong.path', []);
    }
}
