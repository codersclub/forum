<?php

namespace Logs\Handler;

use Monolog\Handler\AbstractHandler;

/**
     * Class HandlerWithOptions Needed for tests
     * @package Logs
     */
class TestHandlerWithOptions extends AbstractHandler {

    protected $setterValue;

    /**
     * @param mixed $setterValue
     */
    public function setSetterValue($setterValue)
    {
        $this->setterValue = $setterValue;
    }

    /**
     * @return mixed
     */
    public function getSetterValue()
    {
        return $this->setterValue;
    }

    public function handle(array $record)
    {
        //useless
    }
}
