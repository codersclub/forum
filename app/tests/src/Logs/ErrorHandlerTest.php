<?php
/**
 * @file
 */

namespace Logs;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Monolog\TestCase;

/**
 * Class ErrorHandlerTest
 * @package Logs
 */
class ErrorHandlerTest extends TestCase
{

    public function testHandleError()
    {
        $old_errreporting = error_reporting(0);
        $logger           = new Logger('test', [$handler = new TestHandler()]);
        $errHandler       = new ErrorHandler($logger);
        $errHandler->setErrorReportingLevel(E_USER_NOTICE | E_USER_ERROR);
        $errHandler->registerErrorHandler(array(E_USER_NOTICE => Logger::EMERGENCY), false);
        trigger_error('Foo', E_USER_ERROR);
        $this->assertCount(1, $handler->getRecords());
        $this->assertTrue($handler->hasErrorRecords());
        trigger_error('Foo', E_USER_NOTICE);
        $this->assertCount(2, $handler->getRecords());
        $this->assertTrue($handler->hasEmergencyRecords());

        trigger_error('Foo', E_USER_WARNING);
        $this->assertCount(2, $handler->getRecords());
        $this->assertFalse($handler->hasWarningRecords());
        error_reporting($old_errreporting);
    }
}
