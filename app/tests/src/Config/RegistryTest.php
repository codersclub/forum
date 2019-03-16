<?php

namespace Config;

/**
 * Class RegistryTest
 * @package Config
 */
class RegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Registry
     */
    protected $reg;

    protected function setUp()
    {
        $this->reg = new Registry(MOCK_PATH . '/RegistryTestFiles');
    }

    public function testEnvironment()
    {
        $this->reg->setEnvironment('env1');
        $this->assertTrue($this->reg->getEnvironment() === 'env1');
        //we don't want to test merging here
        $this->assertTrue($this->reg->get('conf_no_default.name') === 'first one');
        $this->reg->setEnvironment('env2');
        $this->assertTrue($this->reg->get('conf_no_default.name') === 'second one');
        $this->assertEmpty($this->reg->get('conf_no_default.1_key_1')); //key from env1 doesn't exist in env2
    }

    /**
     * @expectedException  \InvalidArgumentException
     */
    public function testEnvironmentIsWrong()
    {
        $this->reg->setEnvironment('wrongEnv');
    }

    public function testGet()
    {
        $this->assertTrue($this->reg->get('conf_not_overridden.key_1') === 'key_1');
        $this->assertTrue($this->reg->get('conf_not_overridden.key_3') === 'key_3');
        //multisection path
        $this->assertTrue($this->reg->get('conf_not_overridden.arr.subkey_1') === 'subkey_1');
        $this->assertTrue($this->reg->get('conf_not_overridden.non_existing_key') === null);
        $this->assertTrue($this->reg->get('conf_not_overridden.key_1.strings_substring') === null);
    }

    public function testMap()
    {
        $this->reg->setEnvironment('env2');

        $this->assertTrue($this->reg->get('conf.2_key_1') === 'key_1'); //not exists in default
        $this->assertTrue($this->reg->get('conf.name') === 'second one'); //overridden
        //sub array test
        $this->assertTrue($this->reg->get('conf.common_arr.default_key') === 'defkey'); //not overridden
        $this->assertTrue($this->reg->get('conf.common_arr.2_key_only') === 'key2');
        $this->assertTrue($this->reg->get('conf.common_arr.overridden_key') === 'ckey two');
        $this->assertTrue($this->reg->get('conf.common_arr.arr_replaced_by_str') === 'overridden_by_string');
        $this->assertTrue(is_array($this->reg->get('conf.common_arr.str_replaced_by_arr')));
    }

    /**
     * @expectedException  \Config\ConfigNotFoundException
     */
    public function testGetWrongPath()
    {
        $this->reg->get('wrong_path.test');
    }

    public function testSet()
    {
        //simple
        $this->reg->set('conf_not_overridden.key_1', 'new value');
        $this->assertTrue($this->reg->get('conf_not_overridden.key_1') === 'new value');
        //harder
        $this->reg->set('conf_not_overridden.arr.subkey_1', 'new value 2');
        $this->assertTrue($this->reg->get('conf_not_overridden.arr.subkey_1') === 'new value 2');
        //just check other keys aren't overridden
        $this->assertTrue($this->reg->get('conf_not_overridden.arr.subkey_2') === 'subkey_2');

        //hardest
        $this->reg->set('conf_not_overridden.arr.subarr.key', 'new value 3'); //sub array does not exist
        $this->assertArrayHasKey('key', $this->reg->get('conf_not_overridden.arr.subarr'));
        $this->assertTrue($this->reg->get('conf_not_overridden.arr.subarr.key') === 'new value 3');

        //unreal
        $this->reg->set('conf_not_overridden.key_3.subkey', 'new value 4'); //to key as array
        $this->assertArrayHasKey('subkey', $this->reg->get('conf_not_overridden.key_3'));
        $this->assertTrue($this->reg->get('conf_not_overridden.key_3.subkey') === 'new value 4');
        //.....
        $this->reg->set('conf_not_overridden.....', 'o_O');
        $this->assertTrue($this->reg->get('conf_not_overridden.....') === 'o_O');
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testSetWrongPath()
    {
        $old = error_reporting(E_ALL);
        $this->reg->set('short_path', 'some');
        error_reporting($old);
    }

    /**
     * @expectedException  \InvalidArgumentException
     */
    public function testContructorWrongPath()
    {
        new Registry(MOCK_PATH . 'some_wrong_path');
    }

    public function testGetDefaultValue()
    {
        $this->assertEquals(
            'some default value',
            $this->reg->get('conf_not_overridden.wrong_path', 'some default value')
        );
    }
}
