<?php
/**
 * @file phpunit tests
 */

namespace Logs\Handler;

use Monolog\Logger;
use Monolog\TestCase;

/**
 * Class PMHandlerTest
 * @package Logs\Handler
 */
class PMHandlerTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        \Ibf::registerApplication(new \TestApplication());
    }

    protected function setUp()
    {
        \Ibf::app()->functions = $this->getMock('functions', ['sendpm']);
    }

    public function testHandle()
    {
        $header = 'test pm';

        \Ibf::app()->functions->expects($this->exactly(3))
            ->method('sendpm')
            ->withConsecutive(
                [
                    $this->equalTo(1),
                    $this->anything(),
                    $this->equalTo($header),
                    $this->equalTo(15)
                ],
                [
                    $this->equalTo(3),
                    $this->anything(),
                    $this->equalTo($header),
                    $this->equalTo(15)
                ],
                [
                    $this->equalTo(5),
                    $this->anything(),
                    $this->equalTo($header),
                    $this->equalTo(15)
                ]
            );

        $handler = new PMHandler($header, [1, 3, 5], 15);
        $handler->handle($this->getRecord());

    }

    public function testHandleBatch()
    {
        $header = 'test pm';
        \Ibf::app()->functions->expects($this->once())
            ->method('sendpm')
            ->with(
                $this->equalTo(1),
                $this->anything(),
                $this->equalTo($header),
                $this->equalTo(15)
            );
        $handler = new PMHandler($header, [1], 15);
        $handler->handleBatch($this->getMultipleRecords());
    }

    public function testHandleRespectsBubble()
    {
        $handler = new PMHandler('subject', [1], 2, Logger::INFO, false);
        $this->assertTrue($handler->handle($this->getRecord(Logger::INFO)));
        $this->assertFalse($handler->handle($this->getRecord(Logger::DEBUG)));

        $handler = new PMHandler('subject', [1], 2, Logger::INFO, true);
        $this->assertFalse($handler->handle($this->getRecord(Logger::INFO)));
        $this->assertFalse($handler->handle($this->getRecord(Logger::DEBUG)));
    }

    public function testHandleLevels()
    {
        $handler = new PMHandler('subject', [1], 2, Logger::INFO, false);
        $this->assertTrue($handler->handle($this->getRecord(Logger::INFO)));
        $this->assertFalse($handler->handle($this->getRecord(Logger::DEBUG)));
    }
}
