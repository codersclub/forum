<?php

return $overrideDefault(
    [
        '2_key_1'    => 'key_1',
        '2_key_2'    => [
            '2_subkey_1' => 'subkey_1',
        ],
        'common_arr' => [
            'overridden_key'      => 'ckey two',
            '2_key_only'          => 'key2',
            'arr_replaced_by_str' => 'overridden_by_string',
            'str_replaced_by_arr' => [],
        ],
        'name'       => 'second one',
    ]
);
