<?php

namespace Logs\Handler;

use Monolog\Handler\TestHandler;
use Monolog\Logger;

/**
 * Class IPFilterHandlerTest
 * @package Logs\Handler
 */
class IPFilterHandlerTest extends \Monolog\TestCase
{

    public function testGetIP()
    {
        $handler = new IPFilterHandler(new TestHandler(), [
            '0.0.0.1',
            '1.0.0.*'
        ], false, false);
        $this->assertCount(2, $handler->getIP());
        $this->assertContains('0.0.0.1', $handler->getIP());
    }

    public function testIsHandling()
    {
        $test    = new TestHandler();
        $handler = new IPFilterHandler($test, [
            '0.0.0.1',
            '1.0.0.*',
            '2.0.*.0',
            '3.0.*.*',
            '4.*.0.0',
            '*.8.8.8'
        ], false, false);
        $check   = function ($ip, $cond) use ($handler) {
            $_SERVER['REMOTE_ADDR'] = $ip;
            $this->{'assert' . $cond}($handler->isHandling($this->getRecord()));
        };
        $check('0.0.0.1', 'True');
        $check('0.0.1.0', 'False');
        $check('0.1.0.0', 'False');
        $check('1.0.0.5', 'True');
        $check('2.0.12.0', 'True');
        $check('3.0.12.5', 'True');
        $check('4.8.0.0', 'True');
        $check('4.8.0.1', 'False');
        $check('25.8.8.8', 'True');

        $this->setExpectedException('InvalidArgumentException');
        //not an ip address
        (new IPFilterHandler($test, ['hello_there']))->handle($this->getRecord());
    }

    public function testHandleUsesProcessors()
    {
        $test    = new TestHandler();
        $handler = new IPFilterHandler($test, ['127.0.0.1'], false);
        $handler->pushProcessor(
            function ($record) {
                $record['extra']['foo'] = true;

                return $record;
            }
        );
        $handler->handle($this->getRecord(Logger::WARNING));
        $this->assertTrue($test->hasWarningRecords());
        $records = $test->getRecords();
        $this->assertTrue($records[0]['extra']['foo']);
    }

    /**
     * Check using the bubble param
     */
    public function testHandleRespectsBubble()
    {
        $test = new TestHandler();

        $handler = new IPFilterHandler($test, ['127.0.0.1'], false, Logger::DEBUG, false);
        $this->assertTrue($this->handleIt($handler, '127.0.0.1', $this->getRecord(Logger::INFO)));
        $this->assertFalse($this->handleIt($handler, '127.0.0.2', $this->getRecord(Logger::INFO)));

        $handler = new IPFilterHandler($test, ['127.0.0.1'], false, Logger::DEBUG, true);
        $this->assertFalse($this->handleIt($handler, '127.0.0.1', $this->getRecord(Logger::INFO)));
        $this->assertFalse($this->handleIt($handler, '127.0.0.2', $this->getRecord(Logger::INFO)));
    }

    /**
     * Internal wrapper for handling records
     * @param IPFilterHandler $handler
     * @param string $ip ip address
     * @param array $record record to send
     * @return bool
     */
    protected function handleIt(IPFilterHandler $handler, $ip, $record)
    {
        $_SERVER['REMOTE_ADDR'] = $ip;
        return $handler->handle($record);

    }

    public function testHandleWithCallback()
    {
        $test    = new TestHandler();
        $handler = new IPFilterHandler(function ($record, $handler) use ($test) {
            return $test;
        }, ['127.0.0.1'], false, Logger::DEBUG, false);
        $this->handleIt($handler, '127.0.0.1', $this->getRecord(Logger::DEBUG));
        $this->handleIt($handler, '127.0.0.2', $this->getRecord(Logger::INFO));
        $this->assertTrue($test->hasDebugRecords());
        $this->assertFalse($test->hasInfoRecords());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testHandleWithBadCallbackThrowsException()
    {
        $handler = new IPFilterHandler('some crap instead of function name', ['*.*.*.*']);
        $handler->handle($this->getRecord(Logger::WARNING));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testHandleWithBadCallbackResultThrowsException()
    {
        $handler = new IPFilterHandler(function ($record, $handler) {
            return 'foo';
        }, ['*.*.*.*']);
        $handler->handle($this->getRecord(Logger::WARNING));
    }

    public function testIsHandlingWithCacheEnabled()
    {
        $test    = new TestHandler();
        $handler = new IPFilterHandler($test, ['127.0.0.1'], true, Logger::DEBUG, false);
        $this->assertTrue($this->handleIt($handler, '127.0.0.1', $this->getRecord()));
        $this->assertTrue($this->handleIt($handler, '127.0.0.2', $this->getRecord()));
        $this->assertTrue($this->handleIt($handler, 'hello', $this->getRecord()));
    }

    public function testIsHandlingLevels()
    {
        $test    = new TestHandler();
        $handler = new IPFilterHandler($test, ['127.0.0.1'], false, Logger::INFO, false);
        $this->assertTrue($this->handleIt($handler, '127.0.0.1', $this->getRecord(Logger::INFO)));
        $this->assertFalse($this->handleIt($handler, '127.0.0.1', $this->getRecord(Logger::DEBUG)));
    }

    protected function setUp()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    public function testHandleBatch()
    {
        $test                   = new TestHandler();
        $handler                = new IPFilterHandler($test, ['127.0.0.1'], false, Logger::DEBUG, false);
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $handler->handleBatch($this->getMultipleRecords());
        $this->assertTrue($test->hasDebugRecords());

        $test                   = new TestHandler();
        $handler                = new IPFilterHandler($test, ['127.0.0.1'], false, Logger::DEBUG, false);
        $_SERVER['REMOTE_ADDR'] = '127.0.0.2';
        $handler->handleBatch($this->getMultipleRecords());
        $this->assertFalse($test->hasInfoRecords());
    }
}
