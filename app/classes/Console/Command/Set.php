<?php

namespace Console\Command;

class Set extends BaseCommand
{

    public function run($args)
    {
        if (count($args) < 2) {
            throw new \InvalidArgumentException('Missed required argument');
        }
        if (isset($this->options['int'])){
            $args[1] = (int)$args[1];
        }elseif(isset($this->options['float'])){
            $args[1] = (float)$args[1];
        }elseif(isset($this->options['comma-array'])){
            $args[1] = explode(',', $args[1]);
        }
        \Variables::set($args[0], $args[1]);
        \Variables::commitChanges();
    }

    public function help()
    {
        return 'Usage: ' . SCRIPT_NAME . ' set [--int|--float|--comma-array] <variable.path> <value>';
    }

}
