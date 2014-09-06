<?php
/**
 * @file
 */

namespace Templates;

class InvisionTest extends \PHPUnit_Framework_TestCase
{

    public function testDirectoryPath()
    {
        $tpl = new Invision();
        $this->assertTrue(is_dir($tpl->getDirectory()));
    }

    public function testGetWrapper()
    {
        $tpl = new Invision();
        $this->assertTrue(is_string($tpl->getWrapper()));
    }
}
