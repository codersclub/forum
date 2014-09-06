<?php

namespace Templates;

class TestClassForBaseTemplateTests extends BaseTemplate
{

}

class BaseTemplateTest extends \PHPUnit_Framework_TestCase
{

    public function testGetDirectory()
    {
        $t = new TestClassForBaseTemplateTests();
        $this->assertTrue(is_string($t->getDirectory()));
    }

    public function testGetName()
    {
        $t = new TestClassForBaseTemplateTests();
        $this->assertEquals('TestClassForBaseTemplateTests', $t->getName());
    }
}
