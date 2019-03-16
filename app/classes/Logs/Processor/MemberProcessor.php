<?php
/**
 * @file
 */

namespace Logs\Processor;

class MemberProcessor
{

    function __invoke(array $record)
    {
        static $processing;
        if (!$processing) {
            $processing = true;
            if (isset(\Ibf::app()->member)) {
                $record['extra'] = array_merge(
                    $record['extra'],
                    [
                        'member'    => \Ibf::app()->member['name'],
                        'member id' => \Ibf::app()->member['id'],
                    ]
                );
            } else {
                $record['extra']['member'] = 'Guest';
            }
            $processing = false;
        }
        return $record;
    }
}
