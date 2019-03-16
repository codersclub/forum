<?php
/**
 * @file
 */

namespace Console\Command;

use Console\Manager;

class Help extends BaseCommand
{

    public function run($args)
    {
        if (count($args) < 1) {
            throw new \Exception('Command name required');
        }
        /** @var $cmd BaseCommand */
        $cmd = Manager::getInstance()
            ->getCommand($args[0]);
        return $cmd->help();
    }

    public function help()
    {
        return 'Usage: ' . SCRIPT_NAME . ' help <command>';
    }
}
