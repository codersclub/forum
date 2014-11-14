<?php

namespace Skins\Themes;

class TestBaseTheme extends BaseTheme
{
    public function getDirectory()
    {
        return MOCK_PATH . '/SkinsTestFiles/themes/base';
    }

    public function beforeTstBefore($vars){
        $this->skipRendering();
    }

    public function afterTstAfter(&$text){
        $text = 'some_text';
    }

}

class BaseThemeTest extends \PHPUnit_Framework_TestCase
{
    public function testGetHtml()
    {
        $test = new TestBaseTheme();
        $this->assertContains('success', $test->getHtml('test', []));
        $this->assertContains('success', $test->getHtml('tst.test2', ['arg2' => '']));
        //args/variables test
        $this->assertContains('hello', $test->getHtml('tst.test2', ['arg2' => 'hello']));

        $this->assertEmpty($test->getHtml('tst.before', []));
        $this->assertEquals('some_text', $test->getHtml('tst.after', []));
    }

    public function testWrongPath(){
        $this->setExpectedException('Exception');
        $test = new TestBaseTheme();
        $test->getHtml('wrong.path', []);
    }
}
