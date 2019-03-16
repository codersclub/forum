<?php
/**
 * @file Tests for \Logs\Logger
 */

namespace Logs;

use Logs\Handler\IPFilterHandler;
use Logs\Handler\TestHandlerWithOptions;
use Monolog\Handler\FilterHandler;
use Monolog\Handler\TestHandler;
use Monolog\Logger as Monologger;
use Monolog\TestCase;

/**
 * Class LoggerTest
 * @package Logs
 */
class LoggerTest extends TestCase
{
    private static $oldPath;
    private static $oldEnv;

    public static function setUpBeforeClass()
    {
        self::$oldEnv  = \Config::getEnvironment();
        self::$oldPath = \Config::setConfigPath(MOCK_PATH . '/LoggerTestEnvironment');
        \Config::setEnvironment(null);
    }

    public static function tearDownAfterClass()
    {
        \Config::setConfigPath(self::$oldPath);
        \Config::setEnvironment(self::$oldEnv);
    }

    protected function setUp()
    {
        Logger::initialize();
    }

    /**
     * @uses \Config\Registry
     */
    public function testInitialize()
    {
        $this->assertInstanceOf('\Monolog\Logger', Logger::getInstance('Ibf'));
        $this->assertInstanceOf('\Monolog\Logger', Logger::getInstance('Stats'));
        $this->assertInstanceOf('\Monolog\Logger', Logger::getInstance('Debug'));
    }

    public function testClear()
    {
        Logger::clear();
        $this->setExpectedException('\InvalidArgumentException');
        Logger::getInstance('Debug');
    }

    /**
     * @uses \Config\Registry
     */
    public function testRegisterChannel()
    {
        $result = Logger::registerChannel('TestingChannel');
        /** @var $channel \Monolog\Logger */
        $channel = Logger::TestingChannel();
        $this->assertSame($result, $channel);
        $this->assertInstanceOf('\Monolog\Logger', $channel);
        //first one is for all channels and the second is only for testing channels
        $this->assertCount(1, $channel->getHandlers());
        $this->assertContainsOnlyInstancesOf('\Monolog\Handler\TestHandler', $channel->getHandlers());
    }

    /**
     * @uses \Config\Registry
     */
    public function testAssigningProcessors()
    {
        //overridden configuration
        $channel = Logger::registerChannel('ChannelWithOwnProcessorsConfig');
        $this->assertCount(1, $channel->getProcessors());
        $this->assertContainsOnlyInstancesOf('\Monolog\Processor\WebProcessor', $channel->getProcessors());
        //default configuration
        $channel = Logger::registerChannel('ChannelWithoutOwnProcessorsConfig');
        $this->assertCount(2, $channel->getProcessors());
    }

    /**
     * @uses \Config\Registry
     */
    public function testIPsOption()
    {
        $channel = Logger::registerChannel('TestingChannel_IP');
        /** @var $handler IPFilterHandler */
        $handler = $channel->popHandler();
        $this->assertInstanceOf('\Logs\Handler\IPFilterHandler', $handler);
        $this->assertContains('0.0.0.0', $handler->getIP());
    }

    /**
     * @uses \Config\Registry
     */
    public function testLevelsOption()
    {
        $channel = Logger::registerChannel('TestingChannel_Level');
        /** @var $handler FilterHandler */
        $handler = $channel->popHandler();
        $this->assertInstanceOf('\Monolog\Handler\FilterHandler', $handler);
        $this->assertContains(Monologger::WARNING, $handler->getAcceptedLevels());
        $this->assertContains(Monologger::ERROR, $handler->getAcceptedLevels());
    }

    /**
     * @uses \Config\Registry
     */
    public function testFormatterOption()
    {
        $channel = Logger::registerChannel('TestingChannel_Formatter');
        /** @var $handler TestHandler */
        $handler = $channel->popHandler();
        $this->assertInstanceOf('\Monolog\Formatter\LineFormatter', $handler->getFormatter());
        //pass options
        $this->assertEquals(
            'blah-blah',
            $handler->getFormatter()
                ->format($this->getRecord())
        );
    }

    /**
     * @uses \Config\Registry
     */
    public function testHandlerOptions()
    {
        $channel = Logger::registerChannel('TestingChannel_Options');
        /** @var $handler TestHandlerWithOptions */
        $handler = $channel->popHandler();
        $this->assertEquals(-1, $handler->getLevel());
        $this->assertEquals('value', $handler->getSetterValue());
    }

    /**
     * @uses \Config\Registry
     */
    public function testErrorHandler()
    {
        \Config::set(
            'logs.error_handler',
            [
                'errorReporting' => E_ALL,
                'channel'        => 'Handler_channel',
            ]
        );

        Logger::initialize(); //reload logger
        $this->assertInstanceOf('\Monolog\Logger', Logger::getInstance('Handler_channel'));

        $current_handler = set_error_handler(
            function ($errno, $errstr, $errfile, $errline) {
                return false;
            }
        );
        restore_error_handler();
        $this->assertInstanceOf('\Logs\ErrorHandler', reset($current_handler));
        //specific tear down
        restore_error_handler();
        restore_exception_handler();
    }

    /**
     * @uses \Config\Registry
     */
    public function testNoChannelSpecified()
    {
        $config   = \Config::get('logs.handlers');
        $config[] = [
            //for all channels.
            'class' => '\Monolog\Handler\TestHandler',
        ];
        \Config::set('logs.handlers', $config);

        Logger::initialize(); //reload logger
        //There are must be two handlers matching TestingChannel
        Logger::registerChannel('TestingChannel');
        $this->assertCount(
            2,
            Logger::TestingChannel()
                ->getHandlers()
        );
    }

    public function testIsRegistered()
    {
        Logger::registerChannel('TestingChannel');
        $this->assertTrue(Logger::isChannelRegistered('TestingChannel'));
        $this->assertFalse(Logger::isChannelRegistered('SecondTestingChannel'));
    }
}
