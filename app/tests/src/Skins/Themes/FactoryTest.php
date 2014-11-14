<?php
/**
 * @file
 */

namespace Skins\Themes;

class TestFactoryMock extends AbstractTheme
{

    /**
     * Retrieves result HTML.
     * @param string $path
     * @param mixed $data
     * @return string
     */
    public function getHtml($path, $data)
    {
        //nothing to do
    }
}
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $this->assertInstanceOf('Skins\Themes\BaseTheme', Factory::create('wrongone'));
        $this->assertInstanceOf('Skins\Themes\TestFactoryMock', Factory::create('TestFactoryMock'));
    }
}
