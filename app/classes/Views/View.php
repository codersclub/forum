<?php

namespace Views;

class View
{
    /**
     * Main static call
     * @param string $path
     * @param array $data
     * @return BaseView
     */
    public static function make($path, $data = [])
    {
        return Factory::create($path, $data);
    }
}
