<?php
/**
 * @file
 */

namespace Templates;

class TestClassForFactoryTests extends BaseTemplate
{
    //nothing here
}

class FactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testCreate()
    {
        $this->assertInstanceOf(
            '\Templates\TestClassForFactoryTests',
            Factory::create('\Templates\TestClassForFactoryTests')
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testCreateNonExisting(){
        Factory::create('NonExistingClass');
    }
}
