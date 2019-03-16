<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yuri
 * Date: 16.03.13
 * Time: 13:12
 */

/**
 * Class EventObject
 * Used by trait Mixin
 */
class EventObject
{
    public $owner;

    function __construct($owner)
    {
        $this->owner = $owner;
    }
}
