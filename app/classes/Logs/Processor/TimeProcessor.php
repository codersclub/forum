<?php

namespace Logs\Processor;

class TimeProcessor
{
    private $startTime;

    function __construct()
    {
        $this->startTime = microtime(true);
    }

    function __invoke(array $record)
    {
        $record['extra']['time'] = microtime(true) - $this->startTime;
        return $record;
    }
}
