<?php
/**
 * @file
 */

namespace Skins;

class TestInvisionBaseSkin extends InvisionBaseSkin
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
}

class InvisionBaseSkinTest extends \PHPUnit_Framework_TestCase {

    public function testGetTemplateDirectory(){
        $skin = new TestInvisionBaseSkin();
        $this->assertInternalType('string', $skin->getTemplatesDirectory());
    }
}
