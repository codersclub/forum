<?php

namespace Console\Command;

class Get extends BaseCommand
{

    public function run($args)
    {
        if (count($args) < 1) {
            throw new \InvalidArgumentException('Missed required argument');
        }
        if (isset($this->options['export'])) {
            $formatter = 'var_export';
        } elseif (isset($this->options['dump'])) {
            $formatter = 'var_dump';
        } else {
            $formatter = 'print_r';
        }
        return $formatter(\Variables::get($args[0]), true);
    }

    public function help()
    {
        return 'Usage: ' . SCRIPT_NAME . ' get [--dump|--export] <variable.path>';
    }
}
