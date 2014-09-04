<?php
/**
 * @file
 */

namespace Skins;

class BaseSkinTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BaseSkin
     */
    private $testInstance;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        \Ibf::registerApplication(new \TestApplication());
    }

    protected function setUp()
    {
        $this->testInstance = new BaseSkin(
            [
                //data similar to ibf_skins
                'uid'            => 1,
                'img_dir'        => 'testing',
                'css_id'         => 'testCss',
                'template_class' => '\Templates\Invision',
            ]
        );
        parent::setUp();
    }

    public function testGetArray()
    {
        //isset
        $this->assertFalse(isset($this->testInstance['nouid']));
        $this->assertTrue(isset($this->testInstance['uid']));
        //get
        $this->assertEquals(1, $this->testInstance['uid']);
        $this->assertNotNull($this->testInstance['CSSFile']);
        $this->assertNull($this->testInstance['nouid']);
    }

    /**
     * @expectedException \Exception
     */
    public function testSetArray()
    {
        $this->testInstance['some'] = 'value';
    }

    /**
     * @expectedException \Exception
     */
    public function testUnsetArray()
    {
        unset($this->testInstance['template']);
    }

    public function testLoadTemplate()
    {
        $this->assertInstanceOf('\Templates\BaseTemplate', $this->testInstance->getTemplate());
    }

    public function testCss()
    {
        $this->assertStringEndsWith('testCss', $this->testInstance->getCSSFile());
    }

    public function testImagePath()
    {
        $this->assertStringEndsWith('testing', $this->testInstance->getImagesPath(false));
        $this->assertStringEndsWith('testing', $this->testInstance->getImagesPath(true));
    }
}
