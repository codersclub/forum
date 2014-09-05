<?php
/**
 * @file
 */

class TestApplication extends CoreApplication {

    public function __construct($initOptions = [])
    {
        $this->vars      = & $INFO;
        $this->functions = new functions();

        if (in_array('db', $initOptions)) {
            $this->InitDB();
        }
    }
}
