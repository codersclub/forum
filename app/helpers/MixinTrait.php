<?php

trait MixinTrait
{
    private $_events;

    public function attachEventHandler($event, Closure $handler)
    {
        $this->_events[$event][] = $handler;
    }

    public function detachEventHandler($event, $id)
    {
        unset($this->_events[$event][$id]);
    }

    public function raiseEvent($event, EventObject $eventObject)
    {
        if (isset($this->_events[$event])) {
            foreach ($this->_events[$event] as $func) {
                $func($eventObject);
            }
        }
    }
}
