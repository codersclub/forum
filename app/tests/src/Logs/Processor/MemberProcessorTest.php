<?php

namespace Logs\Processor;

use Monolog\TestCase;

/**
 * Class MemberProcessorTest
 * @package Logs\Processor
 */
class MemberProcessorTest extends TestCase
{

    public function testProcessorForGuest()
    {
        $processor = new MemberProcessor();
        $record    = $processor($this->getRecord());
        $this->assertArrayHasKey('member', $record['extra']);
        $this->assertArrayNotHasKey('member id', $record['extra']);
        $this->assertEquals('Guest', $record['extra']['member']);
    }

    public function testProcessorForRegistered()
    {
        \Ibf::registerApplication(new \TestApplication());
        \Ibf::app()->member = ['id' => -5, 'name' => 'Test member'];

        $processor = new MemberProcessor();
        $record    = $processor($this->getRecord());

        $this->assertArrayHasKey('member', $record['extra']);
        $this->assertArrayHasKey('member id', $record['extra']);
        $this->assertEquals(-5, $record['extra']['member id']);
        $this->assertEquals('Test member', $record['extra']['member']);
    }
}
