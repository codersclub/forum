<?php
/**
 * @file
 */

class skin_test
{

    function test()
    {
        return 'success';
    }

    function renderTest2()
    {
        return 'success';
    }

    function long_path_test()
    {
        return 'success';
    }

    function testParams($arg1, $arg2)
    {
        return [
            'param1' => $arg1,
            'param2' => $arg2
        ];
    }
}
